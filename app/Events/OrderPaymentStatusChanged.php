<?php

namespace App\Events;

use App\Enums\PaymentStatus;
use App\Models\Orders;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired by OrderPaymentStatusService on every committed transition.
 * Epic 1 / 1.3 listens for this to forward confirmed payments onward —
 * today to a stub, later to the real Epic 0 integration layer.
 */
class OrderPaymentStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Orders $order,
        public readonly ?PaymentStatus $from,
        public readonly PaymentStatus $to,
        public readonly string $source,
    ) {
    }
}
