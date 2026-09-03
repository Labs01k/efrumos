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

    /**
     * Real Bitrix24 REST shape (tasks.task.add / im.notify /
     * crm.timeline.comment.add), logged as one payload set so wiring the
     * actual HTTP calls later is a copy of these field names, not a
     * redesign. RESPONSIBLE_ID comes from BITRIX24_RESPONSIBLE_ID — not
     * known yet, so the task/bell-notification half is skipped (logged,
     * not silently dropped) until the client says who. The CRM timeline
     * comment doesn't need a responsible employee, so it always "runs".
     * Client-facing chat notification needs a Bitrix24 user id for the
     * customer, which nothing in this project maps to yet — also skipped.
     */
    public function notifyOrderTask(Orders $order, string $dealId): void
    {
        if (!config('services.integration.mock_mode')) {
            throw new IntegrationGatewayException(
                "Bitrix24 notifyOrderTask: no webhook/credentials configured (deal {$dealId})."
            );
        }

        $responsibleId = config('services.integration.bitrix_responsible_id');

        if ($responsibleId) {
            $taskFields = [
                'TITLE' => "Обработать заказ #{$order->id}",
                'DESCRIPTION' => "Клиент оплатил заказ #{$order->id}. Нужно проверить наличие и связаться с клиентом.",
                'RESPONSIBLE_ID' => $responsibleId,
                'PRIORITY' => 1,
                'UF_CRM_TASK' => ["D_{$dealId}"],
            ];
            $fakeTaskId = 'BX-TASK-MOCK-' . $order->id . '-' . strtoupper(Str::random(6));

            Log::info('[Bitrix24 MOCK] tasks.task.add — no real credentials, would create this task', [
                'orders_id' => $order->id,
                'fields' => $taskFields,
                'mock_task_id' => $fakeTaskId,
            ]);

            Log::info('[Bitrix24 MOCK] im.notify (responsible bell) — no real credentials', [
                'orders_id' => $order->id,
                'USER_ID' => $responsibleId,
                'MESSAGE' => "Вам назначена новая задача #{$fakeTaskId}: Обработать заказ #{$order->id}",
                'TYPE' => 'SYSTEM',
            ]);
        } else {
            Log::warning('[Bitrix24 MOCK] notifyOrderTask — BITRIX24_RESPONSIBLE_ID not set, skipping task + bell notification', [
                'orders_id' => $order->id,
                'bitrix_deal_id' => $dealId,
            ]);
        }

        Log::info('[Bitrix24 MOCK] crm.timeline.comment.add — no real credentials, would post this comment', [
            'orders_id' => $order->id,
            'ENTITY_ID' => $dealId,
            'ENTITY_TYPE' => 'deal',
            'COMMENT' => 'Заказ оплачен. Менеджер скоро свяжется с вами.',
        ]);

        Log::warning('[Bitrix24 MOCK] client chat notification (im.notify to customer) skipped — no Bitrix24 user id mapping for site customers exists yet', [
            'orders_id' => $order->id,
        ]);
    }
}
