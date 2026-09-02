<?php

namespace App\Services\Product;

use App\Models\GoodsItemId;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Подбор товаров для блоков «С этим товаром покупают» (п.3 ТЗ)
 * и «Похожие товары» (п.4 ТЗ) на странице товара.
 *
 * Оба блока отдают от 4 до 8 позиций, без дублей, без текущего товара
 * и без позиций с нулевым остатком. Если кандидатов меньше четырёх —
 * показываем сколько нашлось, так требует ТЗ.
 */
class ProductRecommendations
{
    private const MIN_ITEMS = 4;
    private const MAX_ITEMS = 8;

    /** За какой период считаем совместные покупки (п.3 ТЗ). */
    private const CO_PURCHASE_MONTHS = 12;

    /**
     * «С этим товаром покупают». Источники строго по приоритету из ТЗ:
     * закреплённые вручную в CMS → чаще всего покупают вместе за 12 месяцев →
     * та же линейка → тот же бренд → бестселлеры категории.
     */
    public static function boughtTogether(GoodsItemId $goods_item): Collection
    {
        $picked = collect();
        $exclude = [$goods_item->id];

        // Краску не покупают вместе с другой краской: к ней берут оксид, шампунь,
        // маску. Поэтому у красок другие краски из кандидатов убираем совсем —
        // приоритет источников из ТЗ при этом сохраняется, меняется только пул.
        $is_dye = ShadePalette::isDye($goods_item);

        $sources = $is_dye
            ? [
                fn () => self::manualList($goods_item->produse_compatibile),
                fn () => self::coPurchased($goods_item, true),
                fn () => self::bestsellersExceptDyes(),
            ]
            : [
                fn () => self::manualList($goods_item->produse_compatibile),
                fn () => self::coPurchased($goods_item),
                fn () => self::sameLine($goods_item),
                fn () => self::sameBrand($goods_item),
                fn () => self::categoryBestsellers($goods_item),
            ];

        foreach ($sources as $source) {
            if ($picked->count() >= self::MAX_ITEMS) {
                break;
            }
            foreach ($source() as $one_candidate) {
                if ($picked->count() >= self::MAX_ITEMS || in_array($one_candidate->id, $exclude)) {
                    continue;
                }
                $exclude[] = $one_candidate->id;
                $picked->push($one_candidate);
            }
        }

        return $picked;
    }

    /**
     * «Похожие товары». Сначала закреплённые вручную, затем автоподбор каскадом:
     * категория + назначение + объём + цена ±30% → без объёма → цена ±50% →
     * только категория и назначение. Категория и назначение не исключаются никогда.
     */
    public static function similar(GoodsItemId $goods_item): Collection
    {
        $picked = self::manualList($goods_item->produse_similare)
            ->reject(fn ($one) => $one->id === $goods_item->id)
            ->take(self::MAX_ITEMS);

        if ($picked->count() >= self::MIN_ITEMS) {
            return $picked;
        }

        $purpose = self::purposeValueIds($goods_item);
        $price = (float) $goods_item->price;

        $steps = [
            ['volume' => true, 'price' => 0.3],
            ['volume' => false, 'price' => 0.3],
            ['volume' => false, 'price' => 0.5],
            ['volume' => false, 'price' => null],
        ];

        foreach ($steps as $one_step) {
            $found = self::similarByStep($goods_item, $purpose, $price, $one_step, $picked->pluck('id')->all());
            $merged = $picked->concat($found)->unique('id')->take(self::MAX_ITEMS);

            if ($merged->count() >= self::MIN_ITEMS) {
                return $merged;
            }
            $picked = $merged;
        }

        return $picked;
    }

    /* ------------------------------------------------------------ источники */

    /** Товары, закреплённые администратором в CMS (поля produse_compatibile / produse_similare). */
    private static function manualList(?string $ids): Collection
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $ids)));

        if (!$ids) {
            return collect();
        }

        return self::baseQuery()
            ->whereIn('id', $ids)
            ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
            ->limit(self::MAX_ITEMS)
            ->get();
    }

    /**
     * Товары, которые чаще всего заказывали вместе с этим за последние 12 месяцев.
     * Позиции заказа лежат в basket, заказ ссылается на корзину через orders.basket_id.
     */
    private static function coPurchased(GoodsItemId $goods_item, bool $without_dyes = false): Collection
    {
        $pairs = DB::table('orders')
            ->join('basket as current_item', 'current_item.basket_id', '=', 'orders.basket_id')
            ->join('basket as other_item', function ($join) {
                $join->on('other_item.basket_id', '=', 'orders.basket_id')
                    ->whereColumn('other_item.goods_item_id', '<>', 'current_item.goods_item_id');
            })
            ->where('current_item.goods_item_id', $goods_item->id)
            ->where('orders.deleted', 0)
            ->where('orders.created_at', '>=', now()->subMonths(self::CO_PURCHASE_MONTHS))
            ->groupBy('other_item.goods_item_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(self::MAX_ITEMS * 2)
            ->pluck('other_item.goods_item_id')
            ->all();

        if (!$pairs) {
            return collect();
        }

        $query = self::baseQuery()->whereIn('id', $pairs);

        if ($without_dyes) {
            $query->whereNotIn('goods_type_id', self::dyeTypeIds());
        }

        return $query
            ->orderByRaw('FIELD(id, ' . implode(',', $pairs) . ')')
            ->limit(self::MAX_ITEMS)
            ->get();
    }

    /** Заполнитель для красок: популярные товары из всех категорий, кроме самих красок. */
    private static function bestsellersExceptDyes(): Collection
    {
        return self::baseQuery()
            ->whereNotIn('goods_type_id', self::dyeTypeIds())
            ->orderBy('popular_element', 'desc')
            ->orderBy('rating', 'desc')
            ->limit(self::MAX_ITEMS)
            ->get();
    }

    private static function dyeTypeIds(): array
    {
        return array_map('intval', config('custom.front.dye_goods_type_ids', []));
    }

    /** Та же линейка продукции: в базе линейка — это brand_id товара. */
    private static function sameLine(GoodsItemId $goods_item): Collection
    {
        if (!$goods_item->brand_id) {
            return collect();
        }

        return self::baseQuery()
            ->where('brand_id', $goods_item->brand_id)
            ->limit(self::MAX_ITEMS)
            ->get();
    }

    /** Тот же бренд: линейки сгруппированы под родительским брендом. */
    private static function sameBrand(GoodsItemId $goods_item): Collection
    {
        $parent_id = $goods_item->getBrand->p_id ?? null;

        if (!$parent_id) {
            return collect();
        }

        $line_ids = DB::table('goods_brand_id')->where('p_id', $parent_id)->pluck('id')->all();

        if (!$line_ids) {
            return collect();
        }

        return self::baseQuery()
            ->whereIn('brand_id', $line_ids)
            ->limit(self::MAX_ITEMS)
            ->get();
    }

    /** Заполнитель: самые продаваемые товары той же категории. */
    private static function categoryBestsellers(GoodsItemId $goods_item): Collection
    {
        return self::baseQuery()
            ->where('goods_type_id', $goods_item->goods_type_id)
            ->orderBy('popular_element', 'desc')
            ->orderBy('rating', 'desc')
            ->limit(self::MAX_ITEMS)
            ->get();
    }

    /* ------------------------------------------------ каскад похожих товаров */

    private static function similarByStep(GoodsItemId $goods_item, array $purpose, float $price, array $step, array $exclude): Collection
    {
        $query = self::baseQuery()
            ->where('id', '<>', $goods_item->id)
            // категория и назначение из подбора не исключаются никогда
            ->where('goods_type_id', $goods_item->goods_type_id);

        if ($exclude) {
            $query->whereNotIn('id', $exclude);
        }

        if ($purpose) {
            $query->whereIn('id', function ($sub) use ($purpose) {
                $sub->select('goods_parametr_item_id.goods_item_id')
                    ->from('goods_parametr_item_id')
                    ->join('goods_parametr_item_rsc', 'goods_parametr_item_rsc.goods_parametr_item_id', '=', 'goods_parametr_item_id.id')
                    ->whereIn('goods_parametr_item_rsc.goods_parametr_value_id', $purpose);
            });
        }

        if ($step['volume'] && $goods_item->gramaj) {
            $query->where('gramaj', $goods_item->gramaj);
        }

        if ($step['price'] !== null && $price > 0) {
            $query->whereBetween('price', [$price * (1 - $step['price']), $price * (1 + $step['price'])]);
        }

        return $query->limit(self::MAX_ITEMS)->get();
    }

    /** Значения параметра «назначение» (Pentru) у товара. */
    private static function purposeValueIds(GoodsItemId $goods_item): array
    {
        $purpose_parametr_id = (int) config('custom.front.purpose_parametr_id', 1);

        return DB::table('goods_parametr_item_id')
            ->join('goods_parametr_item_rsc', 'goods_parametr_item_rsc.goods_parametr_item_id', '=', 'goods_parametr_item_id.id')
            ->where('goods_parametr_item_id.goods_item_id', $goods_item->id)
            ->where('goods_parametr_item_id.goods_parametr_id', $purpose_parametr_id)
            ->pluck('goods_parametr_item_rsc.goods_parametr_value_id')
            ->all();
    }

    /* ------------------------------------------------------------ общий запрос */

    /** Активные товары в наличии — то, что вообще может попасть в блоки. */
    private static function baseQuery()
    {
        return GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->where('in_stoc', 1)
            ->where('products_count', '>', 0)
            ->has('itemByLang')
            ->with('itemByLang', 'oImage', 'getBrand', 'goodsPromoTags', 'checkIfWishItemExist');
    }
}
