<?php

namespace App\Services\Product;

use App\Models\GoodsItemId;
use Illuminate\Support\Collection;

/**
 * Палитра оттенков краски для волос (п.6 ТЗ).
 *
 * В 1С каждый оттенок — отдельный SKU, на сайте это отдельный товар со своим
 * адресом. Оттенки одной линейки связаны полем brand_id (в базе линейка хранится
 * именно там), а код оттенка лежит в артикуле: SE7/47, NDL9/76, PR66/45.
 *
 * Селектор показывается только у красок — список типов товара задаётся в
 * config('custom.front.dye_goods_type_ids').
 */
class ShadePalette
{
    /** Палитру показываем, только если в линейке есть хотя бы столько оттенков. */
    private const MIN_SHADES = 2;

    /** Краска ли это: тип товара входит в список «красок» из конфига. */
    public static function isDye(GoodsItemId $goods_item): bool
    {
        $dye_type_ids = config('custom.front.dye_goods_type_ids', []);

        return in_array((int) $goods_item->goods_type_id, array_map('intval', $dye_type_ids), true);
    }

    /**
     * SEO — schema.org разметка для страницы оттенка (Epic 6). Каждый оттенок
     * остаётся отдельной страницей (см. докблок класса), поэтому используем
     * официальный паттерн Google для ровно этого случая — `Product` с
     * `isVariantOf: ProductGroup`, без перечисления всех сиблингов на
     * странице (их может быть 70+). `productGroupID` — та же линия
     * (`brand_id`), что уже использует product_variants/ProductRecommendations.
     */
    public static function structuredData(GoodsItemId $goods_item, $goods_price): ?array
    {
        if (!self::isDye($goods_item) || !$goods_item->brand_id) {
            return null;
        }

        $shade_number = self::shadeCode($goods_item->itemByLang->name ?? '', $goods_item->articol);
        $in_stock = $goods_item->in_stoc && $goods_item->products_count > 0;

        return [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $goods_item->itemByLang->name ?? '',
            'sku' => $goods_item->articol,
            'isVariantOf' => [
                '@type' => 'ProductGroup',
                'productGroupID' => 'line-' . $goods_item->brand_id,
                'name' => $goods_item->getBrand->itemByLang->name ?? null,
            ],
            'color' => $shade_number,
            'offers' => [
                '@type' => 'Offer',
                'price' => (string) ($goods_price->price ?? ''),
                'priceCurrency' => 'MDL',
                'availability' => $in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            ],
        ];
    }

    /**
     * Оттенки линейки: сам товар и его соседи по brand_id.
     * Пустая коллекция означает «палитру не показываем».
     */
    public static function for(GoodsItemId $goods_item): Collection
    {
        if (!self::isDye($goods_item) || !$goods_item->brand_id) {
            return collect();
        }

        $shades = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->where('brand_id', $goods_item->brand_id)
            // в линейке могут лежать не только оттенки (например оксид) — берём только тот же тип
            ->where('goods_type_id', $goods_item->goods_type_id)
            ->has('itemByLang')
            ->with('itemByLang', 'oImage')
            ->get()
            ->map(function ($one_shade) use ($goods_item) {
                $one_shade->shade_code = self::shadeCode($one_shade->itemByLang->name ?? '', $one_shade->articol);
                $one_shade->shade_name = self::shadeName($one_shade->itemByLang->name ?? '', $one_shade->shade_code);
                $one_shade->is_current = $one_shade->id === $goods_item->id;
                $one_shade->shade_swatch = self::swatchUrl($one_shade);

                return $one_shade;
            })
            ->filter(fn ($one_shade) => $one_shade->shade_code !== null)
            ->sortBy(fn ($one_shade) => self::sortKey($one_shade->shade_code))
            ->values();

        return $shades->count() >= self::MIN_SHADES ? $shades : collect();
    }

    /**
     * Код оттенка. Основной источник — название товара: у красок он идёт после
     * запятой перед названием оттенка («… DE LUXE SENSE, 7/47 Русый медный, 60 мл»).
     * Запасной вариант — артикул, из которого отбрасывается буквенный префикс линейки
     * (NDL9/76 → 9/76). Форматы артикулов у разных брендов расходятся, поэтому
     * артикул только подстраховывает.
     */
    public static function shadeCode(?string $goods_name, ?string $articol = null): ?string
    {
        // код оттенка идёт после запятой, за ним название оттенка;
        // «, 135 мл» — это объём, а не оттенок, поэтому единицы измерения исключаем
        if ($goods_name && preg_match('~,\s*([\d]+(?:/[\dA-Za-zА-Яа-я]+)?)\s+(?!(?:мл|ml|г|гр|g|gr|л|l)\b)\p{L}~u', $goods_name, $match)) {
            return $match[1];
        }

        if ($articol) {
            $code = preg_replace('/^[A-Za-zА-Яа-я.\-\s]+/u', '', trim($articol));

            if ($code !== '' && preg_match('~^[\d/]+$~', $code)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Название оттенка из названия товара: «… DE LUXE, 9/76 Блондин коричнево-фиолетовый, 60 мл»
     * → «Блондин коричнево-фиолетовый».
     */
    public static function shadeName(string $goods_name, ?string $code): string
    {
        if ($code && preg_match('~' . preg_quote($code, '~') . '\s+(.+?)(?:,\s*\d+\s*(?:мл|ml)\b.*)?$~ui', $goods_name, $match)) {
            return trim($match[1], " ,\t\n");
        }

        return $goods_name;
    }

    /**
     * Отдельное фото оттенка из CMS (п.6 ТЗ, раздел «Палитра оттенков»).
     * null — фото не загружено, свотч режется из фотографии товара.
     */
    public static function swatchUrl(GoodsItemId $one_shade): ?string
    {
        if (!$one_shade->shade_img) {
            return null;
        }

        if (file_exists(public_path('upfiles/goods-shades/s/' . showImg($one_shade->shade_img)))) {
            return asset('upfiles/goods-shades/s/' . showImg($one_shade->shade_img));
        }

        if (file_exists(public_path('upfiles/goods-shades/' . $one_shade->shade_img))) {
            return asset('upfiles/goods-shades/' . $one_shade->shade_img);
        }

        return null;
    }

    /** Сортировка палитры по уровню тона, затем по нюансу: 1/0, 3/11, 9/76, 10/1. */
    private static function sortKey(string $code): array
    {
        $parts = explode('/', $code);
        $level = is_numeric($parts[0]) ? (int) $parts[0] : 999;
        $tone = isset($parts[1]) && is_numeric($parts[1]) ? (int) $parts[1] : -1;

        return [$level, $tone];
    }
}
