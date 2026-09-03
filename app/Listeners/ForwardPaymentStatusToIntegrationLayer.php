<?php

namespace App\Listeners;

use App\Contracts\OrderIntegrationNotifier;
use App\Enums\PaymentStatus;
use App\Events\OrderPaymentStatusChanged;

/**
 * Epic 1 / 1.3 — after a confirmed bank callback moves the order to Paid,
 * forward the result to the integration layer (Epic 0). The distribution
 * logic itself lives in Epic 0; this only calls it and reacts to whether
 * it accepted the update.
 */
class ForwardPaymentStatusToIntegrationLayer
{
    public function __construct(private readonly OrderIntegrationNotifier $notifier)
    {
    }

    public function handle(OrderPaymentStatusChanged $event): void
    {
        if ($event->to !== PaymentStatus::Paid) {
            return;
        }

        $accepted = $this->notifier->notifyPaymentStatusChanged($event->order, $event->to);

        if (!$accepted) {
            report(new \RuntimeException(
                "Order #{$event->order->id}: integration layer rejected/failed to accept the Paid status update."
            ));
        }
    }
}
