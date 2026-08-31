<?php

namespace App\Services\Product;

use App\Models\GoodsItemId;
use App\Models\ShopsId;
use Illuminate\Support\Collection;

/**
 * Наличие товара по магазинам (п.5 ТЗ).
 *
 * ТОЧКА ИНТЕГРАЦИИ. В 1С остатки ведутся в разрезе складов, каждый магазин —
 * отдельный склад, плюс основной склад комплектации. Сейчас обмен отдаёт только
 * суммарный остаток (goods_item_id.in_stoc / products_count), разреза по складам
 * в базе ещё нет — поэтому метод возвращает пустую коллекцию, а блок на странице
 * товара не показывается.
 *
 * Когда обмен начнёт передавать остатки по складам, здесь остаётся заменить
 * заглушку на выборку и вернуть коллекцию той же формы:
 *   ['shop' => ShopsId, 'city' => string, 'qty' => int, 'in_stock' => bool]
 * Верстка блока и её состояния уже готовы и подхватят данные без изменений.
 */
class ProductStock
{
    /** Основной склад комплектации покупателю не показывается (п.5 ТЗ). */
    public const MAIN_WAREHOUSE_GUID = '1e1203a2-64db-11e4-a118-bcee7b8b6616';

    /** Остатки по магазинам. Пустая коллекция = блок скрыт. */
    public static function byShops(GoodsItemId $goods_item): Collection
    {
        if (!self::isAvailable()) {
            return collect();
        }

        // сюда встанет выборка остатков по складам, когда её начнёт отдавать 1С
        return collect();
    }

    /** Готова ли интеграция: пока разреза остатков по складам в базе нет. */
    public static function isAvailable(): bool
    {
        return (bool) config('custom.front.stock_by_shops_enabled', false);
    }

    /** Города для селектора над списком магазинов. */
    public static function cities(Collection $stock): Collection
    {
        return $stock->pluck('city')->filter()->unique()->sort()->values();
    }

    /**
     * Демонстрационные данные для превью-роута: реальные магазины из CMS,
     * остатки проставлены детерминированно. В боевой выдаче не используются.
     */
    public static function demo(GoodsItemId $goods_item): Collection
    {
        $shops = ShopsId::where('active', 1)
            ->has('itemByLang')
            ->with('itemByLang')
            ->orderBy('position', 'asc')
            ->get();

        return $shops->map(function ($one_shop, $index) use ($goods_item) {
            $seed = ((int) $goods_item->id + $index * 7) % 10;
            $qty = $seed < 4 ? 0 : $seed * 2;
            $name = $one_shop->itemByLang->name ?? '';
            $parts = array_map('trim', explode(',', $name));

            return [
                'shop' => $one_shop,
                // в названии магазина первым идёт город: «Кишинев, Рышкановка»
                'name' => count($parts) > 1 ? implode(', ', array_slice($parts, 1)) : $name,
                'city' => $parts[0],
                'address' => $one_shop->itemByLang->address ?? '',
                'lat' => $one_shop->latitude,
                'lng' => $one_shop->longitude,
                'qty' => $qty,
                'in_stock' => $qty > 0,
            ];
        })->sortByDesc('in_stock')->values();
    }
}
