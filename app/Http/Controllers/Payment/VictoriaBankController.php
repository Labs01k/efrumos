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

        try {
            $form = $client->buildAuthorizationForm((string) $order->id, $amount, $email, $backrefUrl);
        } catch (\Throwable $e) {
            // Ключи мерчанта не заведены или эквайер недоступен. Заказ уже создан,
            // поэтому вместо 500 отправляем покупателя на экран неуспешной оплаты
            // с возможностью повторить — заказ остаётся в Pending и виден менеджеру.
            Log::error('VictoriaBank: не удалось собрать форму оплаты', [
                'order' => $order->id,
                'error' => $e->getMessage(),
            ]);

            // payment_error — заказ остаётся Pending (банк вообще не отвечал),
            // но покупателю показываем ошибку с повтором, а не «обрабатывается»
            return redirect($this->localizedHomeUrl($lang, 'checkout-fail') . '?' . http_build_query([
                'order' => $order->id,
                'payment_error' => 1,
            ]));
        }

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
     * GET redirect can be replayed/edited by the customer) — they're never
     * used to decide anything. The actual status was already decided by
     * callback() below, which normally arrives first or within moments of
     * this request; we only read that already-decided status from the DB
     * to pick which of the two routes to send the browser to.
     */
    public function backref(Request $request, Orders $order): RedirectResponse
    {
        $lang = $this->resolveLang($request);

        // [FE+BE] Экран возврата с оплаты — route choice is cosmetic, both
        // point at the same checkoutSuccess() handler, which re-reads
        // payment_status itself and would show the right thing either way.
        // Splitting them here just satisfies "separate route for Ok/Fail"
        // without weakening the "status only from Callback" guarantee.
        $path = $order->payment_status === \App\Enums\PaymentStatus::Failed
            || $order->payment_status === \App\Enums\PaymentStatus::Cancelled
            ? 'checkout-fail'
            : 'checkout-success';

        $url = $this->localizedHomeUrl($lang, $path) . '?' . http_build_query(['order' => $order->id]);

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

    /**
     * Языковой префикс нужен ВСЕГДА, включая fallback-локаль: роуты сайта
     * регистрируются через LocalizationService::langRoutePrefix(), и адрес без
     * префикса отдаёт 404 (проверено: /checkout-fail → 404, /ru/checkout-fail → 200).
     * Раньше здесь для fallback-локали префикс отбрасывался, и покупатель,
     * вернувшись из банка, попадал на несуществующую страницу.
     */
    private function localizedHomeUrl(string $lang, string $path = ''): string
    {
        return $path !== '' ? url('/' . $lang . '/' . ltrim($path, '/')) : url('/' . $lang);
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
