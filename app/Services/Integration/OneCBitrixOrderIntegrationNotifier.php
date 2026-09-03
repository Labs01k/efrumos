<?php

namespace App\Services\Integration;

use App\Contracts\Integration\BitrixDealGateway;
use App\Contracts\Integration\OneCOrderGateway;
use App\Contracts\OrderIntegrationNotifier;
use App\Enums\PaymentStatus;
use App\Models\IntegrationIdMapping;
use App\Models\Orders;
use Illuminate\Support\Facades\Log;

/**
 * Epic 0 / 0.5 — the real implementation of the Epic 1 boundary interface.
 * Called by Epic 1's ForwardPaymentStatusToIntegrationLayer listener once a
 * payment is confirmed; updates 1С (status, sum, payment id) and Bitrix24
 * (deal status) "одновременно" — synchronously, one call after the other,
 * each failure isolated so one system's outage doesn't block the other.
 *
 * Requires the order to already have a synced IntegrationIdMapping (i.e.
 * OrderIntegrationService::submitOrder() ran at order-creation time) — if
 * not, there's no 1С document / Bitrix24 deal to update yet, which is
 * itself a desync worth surfacing rather than silently no-op-ing.
 */
class OneCBitrixOrderIntegrationNotifier implements OrderIntegrationNotifier
{
    public function __construct(
        private readonly OneCOrderGateway $oneC,
        private readonly BitrixDealGateway $bitrix,
    ) {
    }

    public function notifyPaymentStatusChanged(Orders $order, PaymentStatus $status): bool
    {
        $mapping = IntegrationIdMapping::where('orders_id', $order->id)->first();

        if (!$mapping || !$mapping->isFullySynced()) {
            Log::error('OneCBitrixOrderIntegrationNotifier: order has no fully-synced mapping — cannot push payment status', [
                'orders_id' => $order->id,
                'mapping_exists' => (bool) $mapping,
            ]);
            return false;
        }

        $ok = true;

        try {
            $paymentId = (string) ($order->payments()->latest()->first()->id ?? '');
            $this->oneC->markPaid($mapping->onec_document_id, (float) $order->ordersData->total_price, $paymentId);
        } catch (\Throwable $e) {
            Log::error('OneCBitrixOrderIntegrationNotifier: 1С status push failed', ['orders_id' => $order->id, 'error' => $e->getMessage()]);
            $ok = false;
        }

        try {
            $this->bitrix->updateDealStatus($mapping->bitrix_deal_id, $status->value);
        } catch (\Throwable $e) {
            Log::error('OneCBitrixOrderIntegrationNotifier: Bitrix24 status push failed', ['orders_id' => $order->id, 'error' => $e->getMessage()]);
            $ok = false;
        }

        // Epic 1 / 1.5 — task for the responsible employee + CRM timeline
        // comment. Isolated from updateDealStatus() above: a failure here
        // shouldn't be reported as "the payment status push failed" (it did
        // succeed) — this is a secondary notification step.
        if ($status === PaymentStatus::Paid) {
            try {
                $this->bitrix->notifyOrderTask($order, $mapping->bitrix_deal_id);
            } catch (\Throwable $e) {
                Log::error('OneCBitrixOrderIntegrationNotifier: Bitrix24 task/notification failed', ['orders_id' => $order->id, 'error' => $e->getMessage()]);
                $ok = false;
            }
        }

        return $ok;
    }
}
