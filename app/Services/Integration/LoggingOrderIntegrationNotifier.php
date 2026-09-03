<?php

namespace App\Services\Integration;

use App\Contracts\OrderIntegrationNotifier;
use App\Enums\PaymentStatus;
use App\Models\Orders;
use Illuminate\Support\Facades\Log;

/**
 * Placeholder implementation of OrderIntegrationNotifier — Epic 0 (the
 * actual интеграционный слой talking to 1С/Bitrix24) does not exist in
 * this repo yet. This only logs the call so the payment flow (Epic 1)
 * can be built and tested end-to-end today; swap the binding in
 * AppServiceProvider for a real HTTP client once Epic 0 is ready.
 */
class LoggingOrderIntegrationNotifier implements OrderIntegrationNotifier
{
    public function notifyPaymentStatusChanged(Orders $order, PaymentStatus $status): bool
    {
        Log::channel(config('logging.default'))->info('[integration-layer stub] payment status change not forwarded — Epic 0 not implemented', [
            'orders_id' => $order->id,
            'status' => $status->value,
        ]);

        return true;
    }
}
