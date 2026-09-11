<?php

namespace App\Services\Integration;

use App\Contracts\Integration\OneCOrderGateway;
use App\Contracts\OrderIntegrationNotifier;
use App\Enums\PaymentStatus;
use App\Models\IntegrationIdMapping;
use App\Models\Orders;
use Illuminate\Support\Facades\Log;

/**
 * Epic 0 / 0.5 — the real implementation of the Epic 1 boundary interface.
 * Called by Epic 1's ForwardPaymentStatusToIntegrationLayer listener once a
 * payment is confirmed; updates 1С (status, sum, payment id).
 *
 * Requires the order to already have a synced IntegrationIdMapping (i.e.
 * OrderIntegrationService::submitOrder() ran at order-creation time) — if
 * not, there's no 1С document to update yet, which is itself a desync worth
 * surfacing rather than silently no-op-ing.
 *
 * 2026-09-11 — Bitrix24 integration cancelled by the client (was mock-only
 * anyway, never had real credentials). This used to be
 * OneCBitrixOrderIntegrationNotifier and also pushed a Bitrix24 deal status
 * update + post-payment task/notification (tasks.task.add/im.notify/
 * crm.timeline.comment.add) — all removed. The manager-facing CRM is
 * Platon/Progression (App\Services\AmoOrder\SendOrderToAmoCrm, called
 * separately at order creation), which this class never touched.
 */
class OneCOrderIntegrationNotifier implements OrderIntegrationNotifier
{
    public function __construct(
        private readonly OneCOrderGateway $oneC,
    ) {
    }

    public function notifyPaymentStatusChanged(Orders $order, PaymentStatus $status): bool
    {
        $mapping = IntegrationIdMapping::where('orders_id', $order->id)->first();

        if (!$mapping || !$mapping->isFullySynced()) {
            Log::error('OneCOrderIntegrationNotifier: order has no fully-synced mapping — cannot push payment status', [
                'orders_id' => $order->id,
                'mapping_exists' => (bool) $mapping,
            ]);
            return false;
        }

        try {
            $paymentId = (string) ($order->payments()->latest()->first()->id ?? '');
            $this->oneC->markPaid($mapping->onec_document_id, (float) $order->ordersData->total_price, $paymentId);
        } catch (\Throwable $e) {
            Log::error('OneCOrderIntegrationNotifier: 1С status push failed', ['orders_id' => $order->id, 'error' => $e->getMessage()]);
            return false;
        }

        return true;
    }
}
