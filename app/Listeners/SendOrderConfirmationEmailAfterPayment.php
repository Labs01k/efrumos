<?php

namespace App\Listeners;

use App\Enums\PaymentStatus;
use App\Events\OrderPaymentStatusChanged;
use App\Services\Order\OrderConfirmationMailer;
use Illuminate\Support\Facades\Log;

/**
 * VictoriaBank compliance — чек с RRN/кодом авторизации/картой можно
 * собрать только после реального платежа, поэтому у card-заказов письмо
 * НЕ шлётся при создании (см. OrderConfirmationMailer/AppOrderService/
 * Front\OrderController — там отправка пропускается для pay_method==card),
 * а ждёт именно этого события. Cash-заказы сюда не попадают вовсе — им
 * письмо уже ушло сразу при оформлении, банковских полей у них не бывает.
 */
class SendOrderConfirmationEmailAfterPayment
{
    public function __construct(private readonly OrderConfirmationMailer $mailer)
    {
    }

    public function handle(OrderPaymentStatusChanged $event): void
    {
        if ($event->to !== PaymentStatus::Paid || $event->order->pay_method !== 'card') {
            return;
        }

        try {
            $this->mailer->send($event->order);
        } catch (\Throwable $e) {
            Log::error('VictoriaBank: не удалось отправить письмо-подтверждение после оплаты', [
                'orders_id' => $event->order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
