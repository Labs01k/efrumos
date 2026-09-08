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
                $one_shade->shade_label = self::shadeLabel($one_shade);

                return $one_shade;
            })
            // членство в линейке не зависит от того, распознался ли код: у части
            // линеек кода нет в принципе, и раньше они оставались вовсе без палитры
            ->pipe(fn ($all) => self::dropDuplicateCodes($all))
            ->sortBy(fn ($one_shade) => self::sortKey($one_shade->shade_code, $one_shade->shade_name))
            ->values();

        return $shades->count() >= self::MIN_SHADES ? $shades : collect();
    }

    /**
     * Один и тот же оттенок иногда заведён двумя SKU — старый и новый артикул
     * из 1С (Acme Avena: 12226.014 и 31210.014). В палитре это два одинаковых
     * свотча подряд, поэтому оставляем один: текущий товар, если дубль — он,
     * иначе тот, которого больше на складе.
     */
    private static function dropDuplicateCodes(Collection $shades): Collection
    {
        return $shades
            ->groupBy(fn ($one_shade) => $one_shade->shade_code ?? 'no-code-' . $one_shade->id)
            ->map(function ($group) {
                if ($group->count() === 1) {
                    return $group->first();
                }

                return $group->firstWhere('is_current', true)
                    ?? $group->sortByDesc('products_count')->first();
            })
            ->values();
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
        // «, 135 мл» — это объём, а не оттенок, поэтому единицы измерения исключаем.
        // Разделителем бывает и тире («, 1 - Черный»), и просто пробел.
        $units = '(?:мл|ml|г|гр|g|gr|л|l|шт|buc)';

        if ($goods_name && preg_match('~,\s*([\d]+(?:/[\dA-Za-zА-Яа-я]+)?)\s*[-–—]?\s*(?!' . $units . '\b)\p{L}~u', $goods_name, $match)) {
            return $match[1];
        }

        if ($articol) {
            $articol = trim($articol);
            $code = preg_replace('/^[A-Za-zА-Яа-я.\-\s]+/u', '', $articol);

            if ($code !== '' && preg_match('~^[\d/]+$~', $code)) {
                return $code;
            }

            // артикулы вида 31210.042 — код оттенка после точки; сам префикс
            // это номер линейки в 1С и к оттенку отношения не имеет
            if (preg_match('~\.([\d]{2,}(?:/[\d]+)?)$~', $articol, $match)) {
                return $match[1];
            }
        }

        return null;
    }

    /**
     * Подпись оттенка в палитре: «9/76, Блондин коричнево-фиолетовый».
     * У части линеек кода нет вовсе (ENIGMA — только «Графит», «Черный»),
     * тогда остаётся одно название.
     */
    public static function shadeLabel(GoodsItemId $one_shade): string
    {
        $name = trim((string) $one_shade->shade_name);

        if (!$one_shade->shade_code) {
            return $name;
        }

        return $name === '' ? $one_shade->shade_code : $one_shade->shade_code . ', ' . $name;
    }

    /**
     * Название оттенка из названия товара: «… DE LUXE, 9/76 Блондин коричнево-фиолетовый, 60 мл»
     * → «Блондин коричнево-фиолетовый».
     */
    public static function shadeName(string $goods_name, ?string $code): string
    {
        $volume_tail = '~,?\s*\d+[.,]?\d*\s*(?:мл|ml|г|гр|g|gr|л|l|шт|buc)\b.*$~ui';

        if (!$code) {
            return trim(preg_replace($volume_tail, '', $goods_name), " ,-–—\t\n");
        }

        $quoted = preg_quote($code, '~');

        // сначала ищем код там, где он и должен стоять — после запятой; иначе
        // короткий код («1») может совпасть с числом из названия товара
        foreach (['~,\s*' . $quoted . '\s*[-–—]?\s*(.*)$~ui', '~' . $quoted . '\s*[-–—]?\s*(.*)$~ui'] as $pattern) {
            if (preg_match($pattern, $goods_name, $match)) {
                $tail = trim(preg_replace($volume_tail, '', $match[1]), " ,-–—\t\n");

                // у части линеек название оттенка отсутствует, есть только номер
                // («… Avena Shine Color 042, 135 мл») — тогда подписью служит код
                return $tail;
            }
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

    /**
     * Приводит поисковый запрос к тому виду, в котором код оттенка хранится
     * в названии товара и в артикуле: «9-76», «9_76», «9 76» → «9/76».
     * null означает «запрос не похож на код оттенка» — обычный поиск.
     *
     * Единая точка нормализации для витрины: подсказки в шапке и выдача
     * каталога обязаны находить одно и то же, иначе покупатель видит
     * подсказку, жмёт Enter и получает пустой каталог.
     */
    public static function normalizeShadeQuery(?string $query): ?string
    {
        $query = trim((string) $query);

        if (!preg_match('~^\d+[\s\-_/]\d+$~', $query)) {
            return null;
        }

        return preg_replace('~[\s\-_]~', '/', $query);
    }

    /**
     * SQL-выражение, приводящее колонку к тому же формату, что и
     * normalizeShadeQuery() — артикулы у брендов пишутся вразнобой
     * («NDL9-76», «DLS 9/76»).
     */
    public static function normalizedColumnSql(string $column): string
    {
        return "REPLACE(REPLACE(REPLACE($column, '-', '/'), '_', '/'), ' ', '/')";
    }

    /**
     * Сортировка палитры по уровню тона, затем по нюансу: 1/0, 3/11, 9/76, 10/1.
     * Оттенки без кода уходят в конец списка и там сортируются по названию.
     */
    private static function sortKey(?string $code, ?string $name = null): array
    {
        if ($code === null) {
            return [1, 0, 0, (string) $name];
        }

        $parts = explode('/', $code);
        $level = is_numeric($parts[0]) ? (int) $parts[0] : 999;
        $tone = isset($parts[1]) && is_numeric($parts[1]) ? (int) $parts[1] : -1;

        return [0, $level, $tone, (string) $name];
    }
}
