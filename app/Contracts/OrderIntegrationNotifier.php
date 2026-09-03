<?php

namespace App\Contracts;

use App\Enums\PaymentStatus;
use App\Models\Orders;

/**
 * The boundary between the site's payment flow (Epic 1) and the
 * integration layer that pushes the order/status into 1С and Bitrix24
 * (Epic 0 — not part of this repo yet). Epic 1 / 1.3 only needs to call
 * this and handle its response; whoever builds Epic 0 implements a real
 * version of it and swaps the binding in AppServiceProvider.
 */
interface OrderIntegrationNotifier
{
    /**
     * @return bool true if the integration layer accepted/queued the update.
     */
    public function notifyPaymentStatusChanged(Orders $order, PaymentStatus $status): bool;
}
