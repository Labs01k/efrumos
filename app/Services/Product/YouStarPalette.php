<?php

namespace App\Services\Product;

use App\Models\GoodsItemId;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Переключатель оттенков для палеток/помад YouStar (п.3 тикета 16.10.2026,
 * Site_3.docx — тот же NUANȚĂ-дропдаун со свотчами, что у красок, только для
 * линейки YouStar). В отличие от красок (ShadePalette — группировка по
 * brand_id+goods_type_id), у YouStar все товары одного бренда вперемешку:
 * палетки теней, карандаши, тушь и т.д. — собственного type_id у них нет
 * (goods_type_id = NULL), поэтому группируем по уже проставленному фильтру
 * «Коллекция YouStar» (goods_parametr_id=51) — он и так точно размечает, что
 * относится к одной линейке (Senseyetions, Boho Glam, Browxpert, …).
 *
 * Названия в 1С разнобойные — код оттенка то до тире, то после
 * («Boho Glam 02 - Gipsy Soul» и «Boho Glam - 01 Hippie Chick» в одной
 * линейке), а у части коллекций (Liquidlips/Matte, Intense Colour) название
 * линейки стоит ПОСЛЕ названия оттенка, не до него. Поэтому код/название
 * разбираются двумя попытками регулярки, а название коллекции (уже известное
 * из самого параметра) обрезается с хвоста отдельно.
 */
class YouStarPalette
{
    private const PARAMETR_ID = 51;
    private const MIN_ITEMS = 2;

    /** Оттенки линии YouStar, к которой привязан товар — пусто, если он не размечен фильтром или в линии меньше 2 штук. */
    public static function for(GoodsItemId $goods_item): Collection
    {
        $value_id = self::valueIdFor($goods_item->id);

        if (!$value_id) {
            return collect();
        }

        $collection_name = DB::table('goods_parametr_value')
            ->where('goods_parametr_value_id', $value_id)
            ->where('lang_id', LANG_ID)
            ->value('name') ?? '';

        // ВАЖНО: goods_parametr_value_id не гарантированно уникален только в
        // рамках параметра 51 — обязательно фильтровать ещё и pivot.goods_parametr_id,
        // иначе если то же числовое значение id существует у другого параметра
        // (а id растут из общей последовательности на всю таблицу), в палитру
        // попадут совершенно чужие товары. Поймано живьём на локальном стенде.
        $sibling_ids = DB::table('goods_parametr_item_rsc as rsc')
            ->join('goods_parametr_item_id as pivot', 'pivot.id', '=', 'rsc.goods_parametr_item_id')
            ->where('rsc.goods_parametr_value_id', $value_id)
            ->where('pivot.goods_parametr_id', self::PARAMETR_ID)
            ->pluck('pivot.goods_item_id');

        $shades = GoodsItemId::whereIn('id', $sibling_ids)
            ->where('active', 1)
            ->where('deleted', 0)
            ->has('itemByLang')
            ->with('itemByLang', 'oImage')
            ->get()
            ->map(function ($one) use ($goods_item, $collection_name) {
                $name = $one->itemByLang->name ?? '';
                [$code, $shade_name] = self::parseCodeAndName($name, $collection_name);

                $one->shade_code = $code;
                $one->shade_name = $shade_name;
                $one->is_current = $one->id === $goods_item->id;
                $one->shade_swatch = ShadePalette::swatchUrl($one);
                $one->shade_label = $code ? ($shade_name === '' ? $code : $code . ', ' . $shade_name) : $shade_name;

                return $one;
            })
            ->sortBy(fn ($one) => [is_numeric($one->shade_code) ? (int) $one->shade_code : 999, $one->shade_name])
            ->values();

        return $shades->count() >= self::MIN_ITEMS ? $shades : collect();
    }

    /** id значения параметра «Коллекция YouStar» для товара, если он размечен — иначе null. */
    private static function valueIdFor(int $goods_item_id): ?int
    {
        $pivot = DB::table('goods_parametr_item_id')
            ->where('goods_item_id', $goods_item_id)
            ->where('goods_parametr_id', self::PARAMETR_ID)
            ->value('id');

        if (!$pivot) {
            return null;
        }

        return DB::table('goods_parametr_item_rsc')
            ->where('goods_parametr_item_id', $pivot)
            ->value('goods_parametr_value_id');
    }

    /**
     * «Палетка теней Senseyetions 05 - Glorious Days YouStar» → ['05', 'Glorious Days'].
     * «Палетка теней Boho Glam - 01 Hippie Chick YouStar» → ['01', 'Hippie Chick'] —
     * в этой же линии код у части товаров идёт ПЕРЕД тире, у части ПОСЛЕ, пробуем оба.
     * «Жидкая матовая помада 06 - Burgundy Liquidlips / Matte YouStar» →
     * ['06', 'Burgundy'] — название коллекции после оттенка обрезается отдельно.
     *
     * @return array{0: ?string, 1: string}
     */
    public static function parseCodeAndName(string $goods_name, string $collection_name): array
    {
        $code = null;
        $tail = null;

        if (preg_match('~(\d{1,2})\s*[-–—]\s*(.+)$~u', $goods_name, $match)) {
            $code = $match[1];
            $tail = $match[2];
        } elseif (preg_match('~[-–—]\s*(\d{1,2})\s+(.+)$~u', $goods_name, $match)) {
            $code = $match[1];
            $tail = $match[2];
        }

        if ($tail === null) {
            return [null, trim($goods_name)];
        }

        // хвост вида «Burgundy Liquidlips / Matte YouStar, 15 мл» — по очереди
        // срезаем объём, бренд-суффикс и (если стоит в конце) название коллекции
        $tail = preg_replace('~,?\s*\d+[.,]?\d*\s*(?:мл|ml|г|гр|g|gr)\b.*$~ui', '', $tail);
        $tail = preg_replace('~\s*YouStar\s*$~ui', '', $tail);

        if ($collection_name !== '') {
            $tail = preg_replace('~\s*' . preg_quote($collection_name, '~') . '\s*$~ui', '', $tail);
        }

        return [$code, trim($tail, " ,-–—\t\n")];
    }
}
