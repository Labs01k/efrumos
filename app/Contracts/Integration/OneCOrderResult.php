<?php

namespace App\Contracts\Integration;

/**
 * Что 1С вернула на CreateOrder: GUID документа (ObjectId), номер заказа
 * (ObjectCode) и — с 2026-10-01, живьём подтверждено — AdditionalInfo
 * (orderSum/deliverySum/total). AdditionalInfo может отсутствовать (старый
 * формат ответа, либо мок/стаб-шлюз) — тогда все три суммы null, и сумму
 * к оплате сайт считает сам (см. OrdersData::amountDue()).
 */
final class OneCOrderResult
{
    public function __construct(
        public readonly string $documentId,
        public readonly ?int $number = null,
        public readonly ?float $orderSum = null,
        /** null — доставку обслуживает сторонняя курьерская служба, её
         *  стоимость 1С заранее не знает; клиент платит курьеру сам, не
         *  онлайн (orderSum/total при этом не null — AdditionalInfo пришла,
         *  известна только доставка). 0.0 — доставка бесплатна. */
        public readonly ?float $deliverySum = null,
        public readonly ?float $total = null,
    ) {
    }
}
