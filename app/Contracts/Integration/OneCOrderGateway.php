<?php

namespace App\Contracts\Integration;

use App\Models\Orders;

/**
 * Epic 0 / 0.2 — the boundary to 1С for the order flow (separate from the
 * existing catalog-sync SOAP client in App\Services\GoodsRequest1C, which
 * only reads product data). No real 1C endpoint exists yet for creating an
 * order or reserving stock — see the blocker already raised with the client
 * ("1С physically doesn't see orders from the site today"). Implement a
 * real gateway here once 1С exposes one; until then LoggingOneCOrderGateway
 * stands in so the rest of Epic 0 can be built and tested against it.
 */
interface OneCOrderGateway
{
    /**
     * @param array<string> $skuCodes 1C SKU codes (Basket.goods_one_c_code)
     * @return array<string,int> sku => available quantity
     */
    public function checkStock(array $skuCodes): array;

    /**
     * Reserve stock and create the order document in 1С.
     * @return string 1C document id/GUID
     */
    public function reserveOrder(Orders $order): string;

    public function releaseReservation(string $onecDocumentId): void;

    /**
     * Epic 0 / 0.5 — push the confirmed payment onto the 1C document:
     * status, sum, payment id, and the transition into sborka/processing.
     */
    public function markPaid(string $onecDocumentId, float $amount, string $paymentId): void;
}
