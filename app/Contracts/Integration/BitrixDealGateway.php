<?php

namespace App\Contracts\Integration;

use App\Models\Orders;

/**
 * Epic 0 / 0.1, 0.5 — the boundary to Bitrix24. No Bitrix24 webhook
 * URL/access token exists in this project yet — nothing in the codebase
 * talks to Bitrix24 today. Implement a real REST/webhook client here once
 * credentials exist; LoggingBitrixDealGateway stands in until then.
 */
interface BitrixDealGateway
{
    /** @return string Bitrix24 deal id */
    public function createDeal(Orders $order): string;

    /**
     * Epic 0 / 0.5 — mirror the payment status onto the deal (e.g. "Оплачен"),
     * plus whatever task/notification scenario is configured on the Bitrix24 side.
     */
    public function updateDealStatus(string $dealId, string $status): void;

    /**
     * Epic 1 / 1.5 — after payment: task for the responsible employee
     * (tasks.task.add, linked to the deal), a bell notification to them
     * (im.notify), and a CRM timeline comment on the deal. Client-facing
     * chat notification is separate and best-effort — we don't have a
     * Bitrix24 user id for the customer, only their site account.
     */
    public function notifyOrderTask(Orders $order, string $dealId): void;
}
