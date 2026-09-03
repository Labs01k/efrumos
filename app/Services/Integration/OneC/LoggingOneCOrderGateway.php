<?php

namespace App\Services\Integration\OneC;

use App\Contracts\Integration\OneCOrderGateway;
use App\Models\Orders;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Placeholder for OneCOrderGateway — 1С has no order-creation/reservation
 * endpoint today (only the catalog-sync SOAP method GetSKUArray exists,
 * see App\Services\GoodsRequest1C). This makes Epic 0 buildable and
 * testable end-to-end without it: checkStock() reports everything in
 * stock, reserveOrder() mints a fake-but-stable document id and logs.
 * Swap the AppServiceProvider binding once the real endpoint exists.
 */
class LoggingOneCOrderGateway implements OneCOrderGateway
{
    public function checkStock(array $skuCodes): array
    {
        Log::info('[1C stub] checkStock — no real endpoint, reporting all requested SKUs in stock', [
            'skus' => $skuCodes,
        ]);

        return array_fill_keys($skuCodes, PHP_INT_MAX);
    }

    public function reserveOrder(Orders $order): string
    {
        $fakeId = '1C-STUB-' . $order->id . '-' . strtoupper(Str::random(6));

        Log::info('[1C stub] reserveOrder — no real endpoint, minted a placeholder document id', [
            'orders_id' => $order->id,
            'onec_document_id' => $fakeId,
        ]);

        return $fakeId;
    }

    public function releaseReservation(string $onecDocumentId): void
    {
        Log::info('[1C stub] releaseReservation', ['onec_document_id' => $onecDocumentId]);
    }

    public function markPaid(string $onecDocumentId, float $amount, string $paymentId): void
    {
        Log::info('[1C stub] markPaid', [
            'onec_document_id' => $onecDocumentId,
            'amount' => $amount,
            'payment_id' => $paymentId,
        ]);
    }
}
