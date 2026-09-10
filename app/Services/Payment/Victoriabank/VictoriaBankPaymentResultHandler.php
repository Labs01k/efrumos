<?php

namespace App\Services\Payment\Victoriabank;

use App\Enums\PaymentStatus;
use App\Exceptions\Payment\InvalidPaymentStatusTransitionException;
use App\Models\OrderPayment;
use App\Models\Orders;
use App\Services\Payment\OrderPaymentStatusService;
use Illuminate\Support\Facades\Log;

/**
 * Shared between the callback webhook and the TRTYPE=90 polling fallback —
 * both eventually learn the same thing (ACTION/RC/RRN/AMOUNT for an order)
 * and need to react identically: capture via TRTYPE=21 on success, then move
 * the order's payment status. Kept as one place so that logic can't drift
 * between the two entry points.
 */
class VictoriaBankPaymentResultHandler
{
    public function __construct(
        private readonly VictoriaBankClient $client,
        private readonly OrderPaymentStatusService $paymentStatusService,
    ) {
    }

    /**
     * @param array{ACTION?:string,RC?:string,RRN?:string,INT_REF?:string,AMOUNT?:string} $result
     */
    public function handle(Orders $order, OrderPayment $payment, array $result, string $source): void
    {
        $action = trim((string) ($result['ACTION'] ?? ''));
        $rc = trim((string) ($result['RC'] ?? ''));
        $rrn = trim((string) ($result['RRN'] ?? '')) ?: $payment->rrn;
        $intRef = trim((string) ($result['INT_REF'] ?? '')) ?: $payment->int_ref;
        $amount = (float) ($result['AMOUNT'] ?? ($payment->amount_bani / 100));

        $payment->update([
            'rrn' => $rrn,
            'int_ref' => $intRef,
            'provider_status' => "ACTION={$action} RC={$rc}",
        ]);

        if ($action !== '0' || $rc !== '00') {
            $target = $rc === '-25' ? PaymentStatus::Cancelled : PaymentStatus::Failed;
            $this->safeTransition($order, $target, $source, "VictoriaBank ACTION={$action} RC={$rc}");
            return;
        }

        // Тестовый сценарий банка №2 (0 -> 24): авторизация без капчура,
        // потом отмена. Флаг снимается сразу после теста, в обычной работе
        // всегда false — см. config/services.php.
        if (config('services.victoriabank.skip_autocapture')) {
            Log::warning('VictoriaBank: авто-капчур пропущен (VICTORIABANK_SKIP_AUTOCAPTURE) — заказ авторизован, не списан', [
                'order' => $order->id,
                'rrn' => $rrn,
            ]);
            return;
        }

        // Идемпотентность capture: TRTYPE=21 должен уйти в банк ровно один раз
        // на авторизацию. handle() может вызваться повторно — банк дублирует
        // server-callback (ретраи), плюс возможна гонка callback vs
        // PollVictoriaBankStatusJob. Атомарная заявка: кто первым проставил
        // capture_requested_at (WHERE ... IS NULL), тот и делает капчур.
        $claimed = OrderPayment::query()
            ->whereKey($payment->id)
            ->whereNull('capture_requested_at')
            ->update(['capture_requested_at' => now()]);

        if ($claimed === 0) {
            // Другой вызов handle() (callback или опрос) уже забрал заявку и
            // отвечает за capture и перевод статуса — второй TRTYPE=21 не шлём.
            Log::info('VictoriaBank: повторный TRTYPE=21 пропущен — capture уже инициирован', [
                'order' => $order->id,
                'source' => $source,
            ]);

            return;
        }

        try {
            $completion = $this->client->complete((string) $order->id, $amount, (string) $rrn, (string) $intRef);
        } catch (\Throwable $e) {
            // Сетевой сбой при обращении к банку — TRTYPE=21 в банк не ушёл,
            // снимаем заявку, чтобы следующий callback/опрос смог повторить.
            $payment->forceFill(['capture_requested_at' => null])->save();
            throw $e;
        }

        $payment->update([
            'provider_status' => 'CAPTURE RC=' . ($completion['RC'] ?? '?'),
            'confirmed_at' => now(),
        ]);

        if (($completion['RC'] ?? null) === '00') {
            $this->safeTransition($order, PaymentStatus::Paid, $source, 'RRN=' . $rrn);
        } else {
            Log::error('VictoriaBank TRTYPE=21 capture failed after successful authorization', [
                'order' => $order->id,
                'rc' => $completion['RC'] ?? null,
            ]);
            $this->safeTransition($order, PaymentStatus::Failed, $source, 'capture failed RC=' . ($completion['RC'] ?? '?'));
        }
    }

    /** @return bool true if the result is final (no more polling needed) */
    public static function isFinal(array $result): bool
    {
        $action = (string) ($result['ACTION'] ?? '');
        $rc = (string) ($result['RC'] ?? '');

        if ($action === '0' && $rc === '00') {
            return true; // paid
        }

        return in_array($action, ['2', '3', '6', '14'], true) || in_array($rc, ['-25', '-30'], true);
    }

    private function safeTransition(Orders $order, PaymentStatus $to, string $source, string $comment): void
    {
        try {
            $this->paymentStatusService->transition(order: $order, to: $to, source: $source, comment: $comment);
        } catch (InvalidPaymentStatusTransitionException $e) {
            Log::error($e->getMessage());
        }
    }
}
