<?php

namespace App\Services\AppApi;

use App\Services\Product\ShadePalette;

/** Результат расчёта заказа (OrderPricing::quote). */
final class Quote
{
    /**
     * @param QuoteLine[] $lines
     * @param array<int, array<string, mixed>> $problems
     */
    public function __construct(
        public readonly array $lines,
        public readonly array $problems,
        public readonly int $itemsTotal,
        public readonly int $discount,
        public readonly int $delivery,
    ) {
    }

    public function isOrderable(): bool
    {
        return $this->problems === [] && $this->lines !== [];
    }

    public function totalQty(): int
    {
        return array_sum(array_map(fn (QuoteLine $line) => $line->qty, $this->lines));
    }

    /** К оплате: товары − скидка + доставка. */
    public function totalDue(): int
    {
        return $this->itemsTotal - $this->discount + $this->delivery;
    }

    public function toArray(): array
    {
        return [
            'lines' => array_map(fn (QuoteLine $line) => [
                'product_id' => $line->goods->id,
                'name' => $line->goods->itemByLang->name ?? null,
                'image' => ShadePalette::cardImageUrl($line->goods),
                'qty' => $line->qty,
                'unit_price' => $line->unitPrice,
                'line_total' => $line->total(),
            ], $this->lines),
            'problems' => $this->problems,
            'totals' => self::totals($this->itemsTotal, $this->discount, $this->delivery),
        ];
    }

    public static function totals(int $itemsTotal, int $discount, int $delivery): array
    {
        return [
            'items_total' => $itemsTotal,
            'discount' => $discount,
            'delivery' => $delivery,
            'total_due' => $itemsTotal - $discount + $delivery,
            'currency' => ProductPresenter::CURRENCY,
        ];
    }
}
