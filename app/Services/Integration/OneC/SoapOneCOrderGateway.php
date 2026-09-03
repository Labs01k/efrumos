<?php

namespace App\Services\Integration\OneC;

use App\Contracts\Integration\OneCOrderGateway;
use App\Models\GoodsItemId;
use App\Models\Orders;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * checkStock() reads GoodsItemId.products_count — real stock figures
 * sourced from 1С, kept fresh by the existing catalog exchange
 * (App\Http\Controllers\Exchange\ImportFrom1C, GetSKU 'Rests' per
 * config('custom.main_store_rest_id'), same field the legacy sync writes).
 * It intentionally does NOT call 1С live per order:
 *   - GetSKU(FullExchange) downloads the *entire* catalog and was directly
 *     timed at 25+ minutes against the test endpoint — unusable inline in
 *     a checkout request.
 *   - GetSKUArray (the per-SKU lookup) was tried directly against the real
 *     test endpoint too. Its WSDL type (ObjectArray.Goods) requires each
 *     item to carry Barcode/Description/DescriptionRu/Qty/price as
 *     *required* fields with only the GUID optional — the opposite of what
 *     a "look up stock by id" call needs — and it rejected the natural
 *     GUID-array payload the existing legacy code sends. That strongly
 *     suggests this operation isn't actually wired for this use case (or
 *     is dead code) on the 1С side, not something to paper over with
 *     guessed placeholder values.
 * So this reads the same real, 1С-sourced number the site already trusts
 * elsewhere (catalog pages, "in stock" badges) instead of adding an
 * unreliable/slow live round trip. reserveOrder()/releaseReservation()/
 * markPaid() stay logging-only: the WSDL only binds GetSKU/GetSKUArray —
 * there is no order/reservation operation to call at all yet.
 */
class SoapOneCOrderGateway implements OneCOrderGateway
{
    public function checkStock(array $skuCodes): array
    {
        if ($skuCodes === []) {
            return [];
        }

        // TEMPORARY — ONEC_FORCE_STOCK_OK. 1С has no real reserve/create
        // endpoint yet (see class docblock), so testing the rest of the
        // integration chain (submitOrder -> synced -> markPaid ->
        // updateDealStatus) currently depends on whatever real stock number
        // happens to be seeded for the test order's SKU. If it's genuinely
        // low/zero, submitOrder() correctly throws InsufficientStockException
        // and the job retries forever without ever reaching 'synced' — which
        // blocks testing everything downstream, not just the stock check.
        // With the flag on, every requested SKU is reported as having stock,
        // regardless of the real number — remove this once 1С exposes a real
        // reserve endpoint and this whole gateway stops being a stub.
        if (config('services.onec.force_stock_ok')) {
            Log::warning('[1С TEMP OVERRIDE] ONEC_FORCE_STOCK_OK is on — checkStock() ignoring real stock, reporting all requested SKUs as available', [
                'skus' => $skuCodes,
            ]);

            return array_fill_keys($skuCodes, PHP_INT_MAX);
        }

        $stockByCode = GoodsItemId::whereIn('one_c_code', $skuCodes)
            ->pluck('products_count', 'one_c_code');

        $result = [];
        foreach ($skuCodes as $code) {
            $result[$code] = (int) ($stockByCode[$code] ?? 0);
        }

        return $result;
    }

    public function reserveOrder(Orders $order): string
    {
        $fakeId = '1C-NOENDPOINT-' . $order->id . '-' . strtoupper(Str::random(6));

        Log::info('[1С — no order/reservation operation on the WSDL] reserveOrder — minted a placeholder document id', [
            'orders_id' => $order->id,
            'onec_document_id' => $fakeId,
        ]);

        return $fakeId;
    }

    public function releaseReservation(string $onecDocumentId): void
    {
        Log::info('[1С — no order/reservation operation on the WSDL] releaseReservation', ['onec_document_id' => $onecDocumentId]);
    }

    public function markPaid(string $onecDocumentId, float $amount, string $paymentId): void
    {
        Log::info('[1С — no order/reservation operation on the WSDL] markPaid', [
            'onec_document_id' => $onecDocumentId,
            'amount' => $amount,
            'payment_id' => $paymentId,
        ]);
    }
}
