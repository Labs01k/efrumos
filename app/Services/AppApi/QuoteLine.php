<?php

namespace App\Services\AppApi;

use App\Models\GoodsItemId;

/** Строка расчёта заказа: товар, количество, цена за штуку на момент расчёта. */
final class QuoteLine
{
    public function __construct(
        public readonly GoodsItemId $goods,
        public readonly int $qty,
        public readonly int $unitPrice,
    ) {
    }

    public function total(): int
    {
        return $this->unitPrice * $this->qty;
    }
}
