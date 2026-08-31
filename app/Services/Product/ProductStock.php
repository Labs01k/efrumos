<?php

namespace App\Services\Product;

use App\Models\GoodsItemId;
use App\Models\GoodsShopRest;
use App\Models\ShopsId;
use Illuminate\Support\Collection;

/**
 * Наличие товара по магазинам (п.5 ТЗ).
 *
 * Обмен ImportFrom1C складывает все строки Rests в goods_shop_rests. Магазин
 * привязывается к складу 1С полем shops_id.store_guid (заполняется в админке
 * магазинов, когда 1С начнёт слать склады магазинов — сейчас в обмене только
 * основной склад комплектации). Пока привязанных магазинов с остатками нет,
 * метод возвращает пустую коллекцию и блок на странице товара скрыт.
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

        $rests = GoodsShopRest::where('goods_item_id', $goods_item->id)
            ->where('store_guid', '!=', self::MAIN_WAREHOUSE_GUID)
            ->get()
            ->keyBy('store_guid');

        if ($rests->isEmpty()) {
            return collect();
        }

        $shops = ShopsId::where('active', 1)
            ->whereNotNull('store_guid')
            ->where('store_guid', '!=', '')
            ->has('itemByLang')
            ->with('itemByLang')
            ->orderBy('position', 'asc')
            ->get();

        if ($shops->isEmpty()) {
            return collect();
        }

        return $shops->map(function ($one_shop) use ($rests) {
            $qty = (float) ($rests[$one_shop->store_guid]->qty ?? 0);
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
                'qty' => (int) $qty,
                'in_stock' => $qty > 0,
            ];
        })->sortByDesc('in_stock')->values();
    }

    /** Готова ли интеграция: аварийный выключатель блока. */
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
