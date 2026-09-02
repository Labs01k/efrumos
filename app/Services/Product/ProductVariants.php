<?php

namespace App\Services\Product;

use App\Models\GoodsItemId;
use Illuminate\Support\Collection;

/**
 * Варианты одного и того же товара, отличающиеся только объёмом (блок «объем, мл» в макете).
 *
 * В 1С разные объёмы — это разные SKU, на сайте разные товары. Связываем их так же,
 * как оттенки: общая линейка (brand_id) плюс совпадающее название без указания объёма.
 */
class ProductVariants
{
    /**
     * Варианты объёма. Пустая коллекция или один элемент означают,
     * что переключать нечего — в макете это состояние «если всего 1 вариант».
     */
    public static function volumes(GoodsItemId $goods_item): Collection
    {
        if (!$goods_item->brand_id || !$goods_item->gramaj) {
            return collect();
        }

        $base_name = self::nameWithoutVolume($goods_item->itemByLang->name ?? '');

        if ($base_name === '') {
            return collect();
        }

        return GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->where('brand_id', $goods_item->brand_id)
            ->whereNotNull('gramaj')
            ->where('gramaj', '<>', '')
            ->has('itemByLang')
            ->with('itemByLang')
            ->get()
            ->filter(fn ($one) => self::nameWithoutVolume($one->itemByLang->name ?? '') === $base_name)
            ->unique('gramaj')
            ->sortBy(fn ($one) => (float) $one->gramaj)
            ->values();
    }

    /** «Шампунь Hydra, 300 мл» → «Шампунь Hydra»: отбрасываем объём, чтобы сравнить товары. */
    public static function nameWithoutVolume(string $name): string
    {
        return trim(preg_replace('/,?\s*\d+[.,]?\d*\s*(мл|ml|г|gr|g|л|l)\b\.?/iu', '', $name), " ,\t\n");
    }
}
