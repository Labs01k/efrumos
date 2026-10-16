<?php

namespace App\Services\Product;

use App\Models\BrandId;
use App\Models\GoodsItemId;
use Illuminate\Support\Collection;

/**
 * Подбор совместимых окислителей/активаторов для краски (п.3 тикета 14.10.2026).
 *
 * Связь задаётся вручную в CMS (goods_brand_id.oxidizer_brand_id) — бренд
 * краски указывает, в каком бренде лежат её окислители. Проверено вживую:
 * жёсткая привязка по иерархии брендов (родитель/ребёнок) не держится —
 * у части линеек (DEMIRA Professional, jNOWA Professional, Elea Max Size,
 * Artisto Color) окислитель лежит в бренде, вообще не связанном по p_id.
 * Поэтому источник правды — явное поле, не угадывание по дереву брендов.
 *
 * Если у бренда краски своё поле не заполнено, проверяется родительский
 * бренд (одна линейка — родитель, у конкретного суб-бренда может не быть
 * смысла дублировать то же значение).
 */
class OxidizerMatcher
{
    /**
     * Тип товара «Оксигент» в справочнике goods_type. У части линеек (De
     * Luxe Sensation, Elea Max Size, Artisto Color) окислитель лежит в ТОМ
     * ЖЕ brand_id, что и сама краска — без фильтра по типу товара в подбор
     * попали бы и сами оттенки краски, не только окислители.
     */
    private const OXIDIZER_GOODS_TYPE_ID = 113;

    /** Краска вообще требует окислитель — есть ли что подбирать. */
    public static function isRequired(GoodsItemId $goods_item): bool
    {
        return self::oxidizerBrandId($goods_item) !== null;
    }

    /** Сам товар — окислитель/активатор (не краска, которой он нужен). */
    public static function isOxidizer(GoodsItemId $goods_item): bool
    {
        return (int) $goods_item->goods_type_id === self::OXIDIZER_GOODS_TYPE_ID;
    }

    /** Активные окислители/активаторы для линейки этой краски. */
    public static function compatibleProducts(GoodsItemId $goods_item): Collection
    {
        $oxidizer_brand_id = self::oxidizerBrandId($goods_item);

        if (!$oxidizer_brand_id) {
            return collect();
        }

        return GoodsItemId::where('brand_id', $oxidizer_brand_id)
            ->where('goods_type_id', self::OXIDIZER_GOODS_TYPE_ID)
            ->where('active', 1)
            ->where('deleted', 0)
            ->where('in_stoc', 1)
            ->where('products_count', '>', 0)
            ->has('itemByLang')
            ->with('itemByLang', 'oImage')
            ->get()
            ->map(function ($one) {
                $name = $one->itemByLang->name ?? '';
                $one->oxidizer_percent = self::percentFromName($name);
                $one->oxidizer_volume_label = self::volumeLabel($name);
                $one->oxidizer_volume_value = self::volumeValue($name);

                return $one;
            })
            // сортировка в два уровня (процент, затем объём внутри процента) —
            // строкой, чтобы не зависеть от сравнения PHP-массивов с null
            ->sortBy(fn ($one) => sprintf('%08.2f-%010.2f', $one->oxidizer_percent ?? 0, $one->oxidizer_volume_value ?? 0))
            ->values();
    }

    /**
     * Site_3.docx, картинка 1 (правка 16.10.2026) — переключатель вариантов
     * на СОБСТВЕННОЙ странице окислителя (не на странице краски, для которой
     * служит compatibleProducts() выше): все активные окислители той же
     * линейки (тот же brand_id, тот же тип товара), включая сам товар.
     * Пусто — если товар вообще не окислитель или в линейке меньше 2 штук.
     */
    public static function siblingVariants(GoodsItemId $goods_item): Collection
    {
        if (!self::isOxidizer($goods_item)) {
            return collect();
        }

        $siblings = GoodsItemId::where('brand_id', $goods_item->brand_id)
            ->where('goods_type_id', self::OXIDIZER_GOODS_TYPE_ID)
            ->where('active', 1)
            ->where('deleted', 0)
            ->has('itemByLang')
            ->with('itemByLang', 'oImage')
            ->get()
            ->map(function ($one) use ($goods_item) {
                $name = $one->itemByLang->name ?? '';
                $one->oxidizer_percent = self::percentFromName($name);
                $one->oxidizer_volume_label = self::volumeLabel($name);
                $one->oxidizer_volume_value = self::volumeValue($name);
                $one->is_current = $one->id === $goods_item->id;

                return $one;
            })
            // как и у оттенков красок — один и тот же процент/объём иногда
            // заведён двумя SKU (старый/новый артикул из 1С); оставляем один:
            // текущий товар, если дубль — он, иначе тот, которого больше на складе
            ->groupBy(fn ($one) => ($one->oxidizer_percent ?? 'x') . '|' . ($one->oxidizer_volume_value ?? 'x'))
            ->map(fn ($group) => $group->count() === 1
                ? $group->first()
                : ($group->firstWhere('is_current', true) ?? $group->sortByDesc('products_count')->first()))
            ->values()
            ->sortBy(fn ($one) => sprintf('%08.2f-%010.2f', $one->oxidizer_percent ?? 0, $one->oxidizer_volume_value ?? 0))
            ->values();

        return $siblings->count() >= 2 ? $siblings : collect();
    }

    /**
     * Промышленная маркировка перекиси по проценту («VOL.») — фиксированная
     * формула (объём = процент × 10/3), а не поле из CMS: 3%→10, 6%→20,
     * 9%→30, 12%→40 — так её всегда подписывают производители (Site_3.docx,
     * картинка 1). Для нестандартных процентов (1.5/1.9/10% и т.п. — реальный
     * ассортимент каталога) даёт приближённое, но верное по формуле число.
     */
    public static function volFromPercent(float $percent): int
    {
        return (int) round($percent * 10 / 3);
    }

    /** id бренда-источника окислителей: свой или, если не задан, родительского. */
    private static function oxidizerBrandId(GoodsItemId $goods_item): ?int
    {
        if (!$goods_item->brand_id) {
            return null;
        }

        $brand = BrandId::find($goods_item->brand_id);

        if (!$brand) {
            return null;
        }

        if ($brand->oxidizer_brand_id) {
            return (int) $brand->oxidizer_brand_id;
        }

        if ($brand->p_id) {
            $parent = BrandId::find($brand->p_id);

            if ($parent && $parent->oxidizer_brand_id) {
                return (int) $parent->oxidizer_brand_id;
            }
        }

        return null;
    }

    /**
     * Процент окислителя из названия («Оксигент 6% DE LUXE, 1000 мл» → 6.0).
     * Используется и для сортировки в подборе, и для фильтра в категории.
     * null — не нашли (не должно происходить у настоящего окислителя, но
     * возвращать 0 вместо null было бы нечестной подменой).
     */
    public static function percentFromName(string $name): ?float
    {
        if (preg_match('~(\d+(?:[.,]\d+)?)\s*%~u', $name, $match)) {
            return (float) str_replace(',', '.', $match[1]);
        }

        return null;
    }

    /**
     * Подпись объёма из названия («Оксигент 3% DE LUXE, 1000 мл» → «1000 мл»).
     * Поле gramaj у части строк не прислано 1С (0, хотя в названии объём
     * есть) — название надёжнее как единственный источник для показа.
     */
    public static function volumeLabel(string $name): ?string
    {
        if (preg_match('~,\s*(\d+(?:[.,]\d+)?\s*(?:мл|ml|л|l|г|гр|g|gr))\s*$~ui', $name, $match)) {
            return trim($match[1]);
        }

        return null;
    }

    /** Числовое значение объёма — для сортировки вариантов внутри одного процента. */
    public static function volumeValue(string $name): ?float
    {
        if (preg_match('~,\s*(\d+(?:[.,]\d+)?)\s*(?:мл|ml|л|l|г|гр|g|gr)\s*$~ui', $name, $match)) {
            return (float) str_replace(',', '.', $match[1]);
        }

        return null;
    }
}
