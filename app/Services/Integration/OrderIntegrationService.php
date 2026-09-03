<?php

namespace App\Services\Integration;

use App\Contracts\Integration\BitrixDealGateway;
use App\Contracts\Integration\OneCOrderGateway;
use App\Exceptions\Integration\InsufficientStockException;
use App\Exceptions\Integration\IntegrationGatewayException;
use App\Models\IntegrationIdMapping;
use App\Models\Orders;
use Illuminate\Support\Facades\Log;

/**
 * Epic 0 / 0.1 — accepts an order from the site and drives it through
 * check+reserve+create in 1С and create-deal in Bitrix24, as one logical
 * operation from the caller's point of view.
 *
 * Idempotent by construction (0.3): submitOrder() always starts from the
 * order's IntegrationIdMapping row (unique per orders_id) and skips any leg
 * already marked synced, so a retried/duplicate call never creates a second
 * 1С document or Bitrix24 deal. Each leg's failure is isolated (0.4) — a 1С
 * success with a Bitrix24 failure leaves the 1С side alone on retry.
 */
class OrderIntegrationService
{
    public function __construct(
        private readonly OneCOrderGateway $oneC,
        private readonly BitrixDealGateway $bitrix,
    ) {
    }

    /**
     * @throws InsufficientStockException|IntegrationGatewayException
     */
    public function submitOrder(Orders $order): IntegrationIdMapping
    {
        $mapping = IntegrationIdMapping::firstOrCreate(['orders_id' => $order->id]);

        if ($mapping->onec_status !== 'synced') {
            $this->syncToOneC($order, $mapping);
        }

        if ($mapping->bitrix_status !== 'synced') {
            $this->syncToBitrix($order, $mapping);
        }

        return $mapping->fresh();
    }

    public function releaseReservation(Orders $order): void
    {
        $mapping = IntegrationIdMapping::where('orders_id', $order->id)->first();

        if ($mapping?->onec_document_id) {
            $this->oneC->releaseReservation($mapping->onec_document_id);
        }
    }

    private function syncToOneC(Orders $order, IntegrationIdMapping $mapping): void
    {
        try {
            $quantities = $this->skuQuantities($order);

            if ($quantities !== []) {
                $stock = $this->oneC->checkStock(array_keys($quantities));

                foreach ($quantities as $sku => $requested) {
                    $available = $stock[$sku] ?? 0;
                    if ($available < $requested) {
                        throw new InsufficientStockException($sku, $requested, $available);
                    }
                }
            }

            $onecId = $this->oneC->reserveOrder($order);

            $mapping->update([
                'onec_document_id' => $onecId,
                'onec_status' => 'synced',
                'last_error' => null,
            ]);
        } catch (InsufficientStockException $e) {
            // Not a transient failure — retrying won't create stock. Record
            // and let it propagate; the caller decides how to tell the customer.
            $mapping->update(['onec_status' => 'failed', 'last_error' => $e->getMessage()]);
            throw $e;
        } catch (\Throwable $e) {
            $mapping->increment('onec_attempts');
            $mapping->update(['onec_status' => 'failed', 'last_error' => $e->getMessage()]);
            Log::error('OrderIntegrationService: 1С sync failed', ['orders_id' => $order->id, 'error' => $e->getMessage()]);
            throw new IntegrationGatewayException("1С sync failed for order {$order->id}: {$e->getMessage()}", previous: $e);
        }
    }

    private function syncToBitrix(Orders $order, IntegrationIdMapping $mapping): void
    {
        try {
            $dealId = $this->bitrix->createDeal($order);

            $mapping->update([
                'bitrix_deal_id' => $dealId,
                'bitrix_status' => 'synced',
                'last_error' => null,
            ]);
        } catch (\Throwable $e) {
            $mapping->increment('bitrix_attempts');
            $mapping->update(['bitrix_status' => 'failed', 'last_error' => $e->getMessage()]);
            Log::error('OrderIntegrationService: Bitrix24 sync failed', ['orders_id' => $order->id, 'error' => $e->getMessage()]);
            throw new IntegrationGatewayException("Bitrix24 sync failed for order {$order->id}: {$e->getMessage()}", previous: $e);
        }
    }

    /** @return array<string,int> 1C SKU code => quantity ordered */
    private function skuQuantities(Orders $order): array
    {
        $quantities = [];

        foreach ($order->basket as $item) {
            $sku = $item->goods_one_c_code;
            if (!$sku) {
                continue; // no 1C code on this line — nothing to check/reserve against
            }
            $quantities[$sku] = ($quantities[$sku] ?? 0) + (int) $item->items_count;
        }

        return $quantities;
    }
}
