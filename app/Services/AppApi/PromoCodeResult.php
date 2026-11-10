<?php

namespace App\Services\AppApi;

/** EFA-82 — одно из 4 состояний задачи: applied/invalid/expired/not_applicable_to_items. */
final class PromoCodeResult
{
    public const APPLIED = 'applied';
    public const INVALID = 'invalid';
    public const EXPIRED = 'expired';
    public const NOT_APPLICABLE_TO_ITEMS = 'not_applicable_to_items';

    public function __construct(
        public readonly string $status,
        public readonly string $code,
        public readonly int $discount = 0,
        /** @var array<int, int> id товаров (GoodsItemId), к которым применена скидка */
        public readonly array $matchedProductIds = [],
        /** goods_promo.one_c_id — то же значение, что сайт пишет в basket.promo_one_c_id */
        public readonly ?int $promoOneCId = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'status' => $this->status,
            'discount' => $this->discount,
        ];
    }
}
