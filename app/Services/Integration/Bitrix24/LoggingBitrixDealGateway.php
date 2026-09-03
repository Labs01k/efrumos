<?php

namespace App\Services\Integration\Bitrix24;

use App\Contracts\Integration\BitrixDealGateway;
use App\Exceptions\Integration\IntegrationGatewayException;
use App\Models\Orders;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * No Bitrix24 webhook URL/access token exists in this project yet.
 * Behaviour is gated by the single INTEGRATION_MOCK_MODE flag
 * (services.integration.mock_mode, default true everywhere): mocked
 * (mints a placeholder deal id, logs, never throws) when on — so the rest
 * of the order flow always reaches 'synced' regardless of Bitrix24 access —
 * or a clear IntegrationGatewayException when off, "as honest as currently
 * possible" rather than a silent no-op. Swap for a real REST client once
 * credentials exist — nothing else needs to change when that happens.
 */
class LoggingBitrixDealGateway implements BitrixDealGateway
{
    public function createDeal(Orders $order): string
    {
        if (!config('services.integration.mock_mode')) {
            throw new IntegrationGatewayException(
                "Bitrix24 createDeal: no webhook/credentials configured for order #{$order->id}."
            );
        }

        $fakeId = 'BX-MOCK-' . $order->id . '-' . strtoupper(Str::random(6));

        Log::info('[Bitrix24 MOCK] createDeal — no real credentials, minted a placeholder deal id', [
            'orders_id' => $order->id,
            'bitrix_deal_id' => $fakeId,
        ]);

        return $fakeId;
    }

    public function updateDealStatus(string $dealId, string $status): void
    {
        if (!config('services.integration.mock_mode')) {
            throw new IntegrationGatewayException(
                "Bitrix24 updateDealStatus: no webhook/credentials configured (deal {$dealId})."
            );
        }

        Log::info('[Bitrix24 MOCK] updateDealStatus', [
            'bitrix_deal_id' => $dealId,
            'status' => $status,
        ]);
    }
}
