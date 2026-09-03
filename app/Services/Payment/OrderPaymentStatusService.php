<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Events\OrderPaymentStatusChanged;
use App\Exceptions\Payment\InvalidPaymentStatusTransitionException;
use App\Models\OrderPaymentStatusLog;
use App\Models\Orders;

/**
 * Epic 1 / 1.2 — the single place that is allowed to change an order's
 * payment status. Both the bank callback (1.1/1.3) and the manual CMS
 * action (1.4) go through this, so every change — automatic or manual —
 * ends up validated (unless forced) and logged the same way.
 */
class OrderPaymentStatusService
{
    /**
     * @throws InvalidPaymentStatusTransitionException
     */
    public function transition(
        Orders $order,
        PaymentStatus $to,
        string $source,
        ?int $changedByAdminId = null,
        ?string $comment = null,
        bool $force = false,
    ): Orders {
        /** @var PaymentStatus $from */
        $from = $order->payment_status ?? PaymentStatus::Pending;

        if (!$force && $from !== $to && !$from->canTransitionTo($to)) {
            throw new InvalidPaymentStatusTransitionException($order->id, $from, $to);
        }

        if ($from === $to) {
            return $order;
        }

        $order->payment_status = $to;
        $order->payment_status_changed_at = now();
        $order->save();

        OrderPaymentStatusLog::create([
            'orders_id' => $order->id,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'source' => $source,
            'changed_by_admin_id' => $changedByAdminId,
            'comment' => $comment,
        ]);

        event(new OrderPaymentStatusChanged($order, $from, $to, $source));

        return $order;
    }
}
