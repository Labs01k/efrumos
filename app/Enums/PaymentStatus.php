<?php

namespace App\Enums;

/**
 * Epic 1 / 1.2 — the 4 payment states a manual/automatic transition may put
 * an order into, and which transitions between them are allowed.
 */
enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('variables.payment_status_pending'),
            self::Paid => __('variables.payment_status_paid'),
            self::Failed => __('variables.payment_status_failed'),
            self::Cancelled => __('variables.payment_status_cancelled'),
            self::Refunded => __('variables.payment_status_refunded'),
        };
    }

    /**
     * Transitions allowed through the normal (automatic) flow. The manual
     * admin override (1.4) bypasses this on purpose — that is what it is
     * for — but every other caller must go through it.
     *
     * Failed → Paid: повтор оплаты после отказа. Обычно повтор сначала
     * возвращает заказ в Pending (VictoriaBankController::initiate), но
     * опрос статуса по первой, неуспешной попытке может снова поставить
     * Failed, пока покупатель платит второй раз. Подтверждённый банком
     * успешный платёж обязан стать Paid — иначе деньги списаны, а заказ
     * висит «ошибкой оплаты».
     */
    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Pending => in_array($next, [self::Paid, self::Failed, self::Cancelled], true),
            self::Failed => in_array($next, [self::Pending, self::Paid, self::Cancelled], true),
            // Задача с refund (16.10.2026/26.10.2026) — единственный легальный
            // путь из Paid. Деньги уже списаны банком, назад в Pending/Failed
            // смысла нет.
            self::Paid => in_array($next, [self::Refunded], true),
            self::Cancelled, self::Refunded => false,
        };
    }
}
