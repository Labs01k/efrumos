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
 * нет «ещё один возврат по этому платежу», только первый успешный (решение
 * команды 26.10.2026, после живого теста обеих сторон — см. ниже).
 *
 * Порядок 26.10.2026 (живой тест показал обратный порядок небезопасным —
 * см. комментарий у refundInOneC()): 1С — ПЕРВЫМ, банк — ВТОРЫМ. Если 1С
 * отказала, к банку вообще не идём — деньги клиента не трогаем, пока 1С не
 * подтвердила. Проверка суммы считается по той же формуле, что у 1С
 * (подтверждено живьём: «остаток = оплата минус сумма успешных возвратов»),
 * хотя на практике при единственном разрешённом возврате это равно просто
 * оплаченной сумме.
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

        // Банк разрешает только один reversal на транзакцию — решение команды
        // 26.10.2026: не пытаться поддержать несколько частичных возвратов
        // (как умеет 1С), банковская сторона физически не даст второй раз
        // вернуть деньги по этой же оплате.
        if (OrderRefund::where('order_payments_id', $payment->id)->where('status', 'success')->exists()) {
            throw new RuntimeException('По этому платежу уже выполнен успешный возврат — банк не разрешает повторный.');
        }

        $paidMdl = $payment->amount_bani / 100;
        $alreadyRefundedMdl = OrderRefund::where('order_payments_id', $payment->id)
            ->where('status', 'success')
            ->sum('amount_bani') / 100;
        $remainingMdl = $paidMdl - $alreadyRefundedMdl;

        if ($amountMdl <= 0 || $amountMdl > $remainingMdl) {
            throw new RuntimeException("Сумма возврата должна быть больше 0 и не больше остатка ({$remainingMdl} MDL).");
        }

        $refund = OrderRefund::create([
            'orders_id' => $order->id,
            'order_payments_id' => $payment->id,
            'amount_bani' => (int) round($amountMdl * 100),
            'reason' => $reason,
            'initiated_by_admin_id' => $adminId,
            'status' => 'pending',
        ]);

        // Бросает исключение и останавливает всё здесь, если 1С отказала —
        // до банка в этом случае дело не доходит вообще, $refund остаётся
        // status=pending в журнале (видно, что попытка была и чем закончилась).
        $this->refundInOneC($order, $refund, $amountMdl);

        $bankResult = $this->victoriaBankClient->reverse((string) $order->id, $amountMdl, $payment->rrn, (string) $payment->int_ref);
        $rc = $bankResult['RC'] ?? null;

        $refund->update([
            'status' => $rc === '00' ? 'success' : 'failed',
            'rrn' => $bankResult['RRN'] ?? null,
            'int_ref' => $bankResult['INT_REF'] ?? null,
            'provider_response' => 'RC=' . ($rc ?? '?'),
        ]);

        if ($rc !== '00') {
            // Обратное расхождение: 1С уже подтвердила возврат (onec_notified_at
            // заполнен), а банк деньги так и не вернул. Нет метода "отменить"
            // CreateRefunds — громкая видимая ошибка, не тихий лог, как раньше
            // было в обратную сторону.
            Log::error('OrderRefundService: 1С подтвердила возврат, но банк отказал — расхождение учёта, нужна ручная сверка', [
                'orders_id' => $order->id,
                'order_refunds_id' => $refund->id,
                'rc' => $rc,
            ]);

            throw new RuntimeException('Банк отклонил возврат (RC=' . ($rc ?? '?') . '), хотя 1С его уже подтвердила — нужна ручная сверка.');
        }

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

    /**
     * 1С — гейт ДО банка, не уведомление ПОСЛЕ (решение команды 26.10.2026).
     * Раньше было наоборот (банк первым, 1С — best-effort лог после), и живой
     * тест в тот же день показал, почему это небезопасно: при отказе 1С банк
     * уже вернул деньги, а администратор в интерфейсе видел чистый успех —
     * узнать о расхождении можно было только по логам или по пустому
     * onec_notified_at в БД, вручную. Теперь отказ 1С останавливает всё до
     * того, как деньги у банка тронуты.
     *
     * @throws RuntimeException если нет синхронизированного документа 1С или
     *                           сама 1С отклонила CreateRefunds.
     */
    private function refundInOneC(Orders $order, OrderRefund $refund, float $amountMdl): void
    {
        $mapping = IntegrationIdMapping::where('orders_id', $order->id)->first();

        if (!$mapping || !$mapping->isFullySynced()) {
            Log::warning('OrderRefundService: нет синхронизированного 1С-документа — возврат остановлен до похода в банк', [
                'orders_id' => $order->id,
                'order_refunds_id' => $refund->id,
            ]);

            throw new RuntimeException('Заказ не синхронизирован с 1С — возврат невозможен без ручной сверки.');
        }

        try {
            $this->oneC->markRefunded($mapping->onec_document_id, $amountMdl, (string) $refund->id);
            $refund->update(['onec_notified_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('OrderRefundService: 1С CreateRefunds отказала — возврат остановлен до похода в банк', [
                'orders_id' => $order->id,
                'order_refunds_id' => $refund->id,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException('1С отклонила возврат: ' . $e->getMessage());
        }
    }
}
