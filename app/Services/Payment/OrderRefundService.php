<?php

namespace App\Services\Payment;

use App\Contracts\Integration\OneCOrderGateway;
use App\Enums\PaymentStatus;
use App\Exceptions\Payment\InvalidPaymentStatusTransitionException;
use App\Models\IntegrationIdMapping;
use App\Models\OrderRefund;
use App\Models\Orders;
use App\Services\Payment\Victoriabank\VictoriaBankClient;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Задача с рефандом (тикет SK, 16.10.2026) — «права доступа, подтверждение,
 * журнал операций», ответ 1С получен 26.10.2026 (CreateRefunds). Полная или
 * частичная сумма, причина — обязательна. Банк разрешает только ОДИН
 * reversal на транзакцию (TRTYPE=24, RC=95 на повторный) — поэтому здесь
 * нет «ещё один возврат по этому платежу», только первый успешный.
 *
 * 1С уведомляется ПОСЛЕ успешного возврата в банке, не вместо него и не до
 * него — деньги у банка уже настоящие, уведомление 1С — учётная запись.
 * Если 1С отказала/недоступна, возврат клиенту НЕ откатывается (это было
 * бы хуже, чем расхождение учёта на время) — ошибка логируется громко для
 * ручной сверки, see onec_notified_at=null в order_refunds.
 */
class OrderRefundService
{
    public function __construct(
        private readonly VictoriaBankClient $victoriaBankClient,
        private readonly OneCOrderGateway $oneC,
        private readonly OrderPaymentStatusService $paymentStatusService,
    ) {
    }

    public function refund(Orders $order, float $amountMdl, string $reason, int $adminId): OrderRefund
    {
        if ($order->payment_status !== PaymentStatus::Paid) {
            throw new RuntimeException('Заказ не в статусе «Оплачен» — возврат невозможен.');
        }

        $payment = $order->payments()
            ->where('provider', 'victoriabank')
            ->whereNotNull('confirmed_at')
            ->latest()
            ->first();

        if (!$payment || !$payment->rrn || !$payment->int_ref) {
            throw new RuntimeException('Подтверждённый платёж VictoriaBank по этому заказу не найден.');
        }

        $paidMdl = $payment->amount_bani / 100;

        if ($amountMdl <= 0 || $amountMdl > $paidMdl) {
            throw new RuntimeException("Сумма возврата должна быть больше 0 и не больше оплаченной ({$paidMdl} MDL).");
        }

        if (OrderRefund::where('order_payments_id', $payment->id)->where('status', 'success')->exists()) {
            throw new RuntimeException('По этому платежу уже выполнен успешный возврат — банк не разрешает повторный.');
        }

        $bankResult = $this->victoriaBankClient->reverse((string) $order->id, $amountMdl, $payment->rrn, (string) $payment->int_ref);
        $rc = $bankResult['RC'] ?? null;

        $refund = OrderRefund::create([
            'orders_id' => $order->id,
            'order_payments_id' => $payment->id,
            'amount_bani' => (int) round($amountMdl * 100),
            'reason' => $reason,
            'initiated_by_admin_id' => $adminId,
            'status' => $rc === '00' ? 'success' : 'failed',
            'rrn' => $bankResult['RRN'] ?? null,
            'int_ref' => $bankResult['INT_REF'] ?? null,
            'provider_response' => 'RC=' . ($rc ?? '?'),
        ]);

        if ($rc !== '00') {
            throw new RuntimeException('Банк отклонил возврат: RC=' . ($rc ?? '?'));
        }

        $this->notifyOneC($order, $refund, $amountMdl);

        try {
            $this->paymentStatusService->transition(
                order: $order,
                to: PaymentStatus::Refunded,
                source: 'admin',
                changedByAdminId: $adminId,
                comment: "Возврат {$amountMdl} MDL. Причина: {$reason}",
                force: true,
            );
        } catch (InvalidPaymentStatusTransitionException $e) {
            // force=true — не должно происходить; деньги уже вернулись в банке,
            // статус заказа — вторичная проблема, не причина откатывать возврат.
            Log::error('OrderRefundService: возврат выполнен, но смена статуса заказа упала', [
                'orders_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $refund;
    }

    private function notifyOneC(Orders $order, OrderRefund $refund, float $amountMdl): void
    {
        $mapping = IntegrationIdMapping::where('orders_id', $order->id)->first();

        if (!$mapping || !$mapping->isFullySynced()) {
            Log::warning('OrderRefundService: нет синхронизированного 1С-документа — возврат не передан в 1С, нужна ручная сверка', [
                'orders_id' => $order->id,
                'order_refunds_id' => $refund->id,
            ]);

            return;
        }

        try {
            $this->oneC->markRefunded($mapping->onec_document_id, $amountMdl, (string) $refund->id);
            $refund->update(['onec_notified_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('OrderRefundService: 1С CreateRefunds упал — возврат у банка уже выполнен, нужна ручная сверка', [
                'orders_id' => $order->id,
                'order_refunds_id' => $refund->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
