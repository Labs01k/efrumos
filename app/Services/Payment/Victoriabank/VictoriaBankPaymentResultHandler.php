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

        $completion = $this->client->complete((string) $order->id, $amount, (string) $rrn, (string) $intRef);
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
