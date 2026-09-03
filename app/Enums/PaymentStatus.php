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

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('variables.payment_status_pending'),
            self::Paid => __('variables.payment_status_paid'),
            self::Failed => __('variables.payment_status_failed'),
            self::Cancelled => __('variables.payment_status_cancelled'),
        };
    }

    /**
     * Transitions allowed through the normal (automatic) flow. The manual
     * admin override (1.4) bypasses this on purpose — that is what it is
     * for — but every other caller must go through it.
     */
    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Pending => in_array($next, [self::Paid, self::Failed, self::Cancelled], true),
            self::Failed => in_array($next, [self::Pending, self::Cancelled], true),
            self::Paid, self::Cancelled => false,
        };
    }
}
