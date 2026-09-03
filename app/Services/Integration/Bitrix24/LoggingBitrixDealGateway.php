<?php

namespace App\Services\Integration\Bitrix24;

use App\Contracts\Integration\BitrixDealGateway;
use App\Models\Orders;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Placeholder for BitrixDealGateway — no Bitrix24 webhook URL/access token
 * exists in this project yet. Deliberately never throws: it always mints a
 * placeholder deal id and logs, so submitOrder()/updateDealStatus() always
 * mark the Bitrix leg 'synced' and nothing in Epic 0's order flow blocks or
 * retries waiting on Bitrix24. The rest of the mechanism (mapping table,
 * retries, idempotency) is built and tested against this. Swap the
 * AppServiceProvider binding for a real REST client once credentials exist —
 * nothing else needs to change when that happens.
 */
class LoggingBitrixDealGateway implements BitrixDealGateway
{
    public function createDeal(Orders $order): string
    {
        $fakeId = 'BX-STUB-' . $order->id . '-' . strtoupper(Str::random(6));

        Log::info('[Bitrix24 stub] createDeal — no real credentials, minted a placeholder deal id', [
            'orders_id' => $order->id,
            'bitrix_deal_id' => $fakeId,
        ]);

        return $fakeId;
    }

    public function updateDealStatus(string $dealId, string $status): void
    {
        Log::info('[Bitrix24 stub] updateDealStatus', [
            'bitrix_deal_id' => $dealId,
            'status' => $status,
        ]);
    }
}
