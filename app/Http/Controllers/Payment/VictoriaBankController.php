<?php

namespace App\Http\Controllers\Payment;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Jobs\Payment\PollVictoriaBankStatusJob;
use App\Models\OrderPayment;
use App\Models\Orders;
use App\Services\Payment\Victoriabank\VictoriaBankClient;
use App\Services\Payment\Victoriabank\VictoriaBankPaymentResultHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Epic 1 / 1.1 — VictoriaBank e-Gateway. authorize() starts TRTYPE=0 and
 * redirects the customer to the bank; backref() is where their browser
 * lands afterwards (informational only — never trust it); callback() is
 * the server-to-server webhook and the only place a payment status ever
 * moves, per the bank's own docs ("always process from the server-side
 * callback, not from BACKREF").
 */
class VictoriaBankController extends Controller
{
    public function initiate(Request $request, Orders $order, VictoriaBankClient $client): View|RedirectResponse
    {
        $lang = $this->resolveLang($request);

        // Only card orders that haven't already been paid get sent to the
        // bank — guards against someone hitting this URL directly for a
        // cash order, or clicking a stale "pay" link on an order that
        // already confirmed.
        if ($order->pay_method !== 'card' || $order->payment_status === PaymentStatus::Paid) {
            return redirect($this->localizedHomeUrl($lang));
        }

        $amount = (float) $order->ordersData->total_price + (float) $order->ordersData->delivery_cost;
        $email = $order->ordersUsers->email ?? $order->ordersFrontUser->email ?? '';

        // Reuse the pending attempt if the customer is retrying (e.g. they
        // abandoned 3DS and came back) instead of piling up new rows.
        $payment = $order->payments()
            ->where('provider', 'victoriabank')
            ->whereNull('confirmed_at')
            ->first();

        if ($payment) {
            $payment->update(['amount_bani' => (int) round($amount * 100)]);
        } else {
            OrderPayment::create([
                'orders_id' => $order->id,
                'provider' => 'victoriabank',
                'amount_bani' => (int) round($amount * 100),
                'currency' => 'MDL',
            ]);
        }

        $backrefUrl = route('payments.bank.backref', ['order' => $order->id, 'lang' => $lang]);
        $form = $client->buildAuthorizationForm((string) $order->id, $amount, $email, $backrefUrl);

        // Fallback in case the callback never arrives (bank docs: TRTYPE=90
        // polling) — no-ops on its own once the callback resolves the order first.
        PollVictoriaBankStatusJob::startFor($order);

        return view('payment.victoriabank-redirect', [
            'endpoint' => $form['endpoint'],
            'fields' => $form['fields'],
        ]);
    }

    /**
     * BACKREF — browser redirect after the bank page. Informational only;
     * ACTION/RC here are query params and are not verified (P_SIGN on a
     * GET redirect can be replayed/edited by the customer). Only shows a
     * message — the actual status was already decided by callback() below,
     * which normally arrives first or within moments of this request.
     */
    public function backref(Request $request, Orders $order): RedirectResponse
    {
        $lang = $this->resolveLang($request);

        // [FE+BE] Экран возврата с оплаты — checkoutSuccess() decides what to
        // actually show from the order's real payment_status, never from
        // ACTION here (informational/unverified, see class docblock).
        $url = $this->localizedHomeUrl($lang, 'checkout-success') . '?' . http_build_query(['order' => $order->id]);

        return redirect($url);
    }

    /**
     * payments/bank/* routes sit outside the locale-prefixed route group
     * (see routes/web.php) — a bank redirect/webhook URL isn't a localized
     * page — so LANG isn't derivable from the request's own URL the way the
     * rest of the site does it. The customer's language is captured once,
     * in OrderController at checkout time (where LANG is still valid), and
     * threaded through as a ?lang= param on every hop from there on.
     */
    private function resolveLang(Request $request): string
    {
        $lang = (string) $request->query('lang', '');

        return in_array($lang, config('app.locales'), true) ? $lang : config('app.locale');
    }

    /** Mirrors App\Traits\LocaleTrait::SetLangPrefix() — the fallback locale is served bare, others get a /xx prefix. */
    private function localizedHomeUrl(string $lang, string $path = ''): string
    {
        $prefix = $lang === config('app.fallback_locale') ? '' : '/' . $lang;

        return $path !== '' ? url($prefix . '/' . ltrim($path, '/')) : url($prefix ?: '/');
    }

    public function callback(Request $request, VictoriaBankClient $client, VictoriaBankPaymentResultHandler $resultHandler): JsonResponse
    {
        // Bank docs: "Always respond HTTP 200 ... even when rejecting an
        // invalid P_SIGN" — otherwise the bank retries indefinitely.
        if (!$client->verifyCallbackSignature($request)) {
            Log::warning('VictoriaBank callback: P_SIGN verification failed', [
                'order' => $request->input('ORDER'),
                'ip' => $request->ip(),
            ]);

            return response()->json(['status' => 'ignored'], 200);
        }

        $orderId = (int) ltrim((string) $request->input('ORDER', '0'), '0');
        $order = Orders::find($orderId);
        $payment = $order?->payments()->where('provider', 'victoriabank')->first();

        if (!$order || !$payment) {
            Log::warning('VictoriaBank callback for unknown order', ['order' => $orderId]);
            return response()->json(['status' => 'ignored'], 200);
        }

        $payment->update(['signature_verified' => true, 'raw_callback_payload' => $request->all()]);
        $resultHandler->handle($order, $payment, $request->only(['ACTION', 'RC', 'RRN', 'INT_REF', 'AMOUNT']), 'bank_callback');

        return response()->json(['status' => 'ok']);
    }
}
