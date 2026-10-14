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
        })
            // правка от 14.10.2026: магазины без остатка в выдаче не показываем
            // вообще (раньше показывали с бейджем «нет в наличии» и кнопкой
            // «показать ещё») — явный запрос клиента, чтобы не вводить в
            // заблуждение количеством магазинов, которые реально нечем закрыть.
            ->filter(fn ($row) => $row['in_stock'])
            ->sortByDesc('qty')
            ->values();
    }

    /**
     * «В наличии для онлайн-заказа» (правка от 14.10.2026) — остаток ГЛАВНОГО
     * склада комплектации, с которого реально уходят интернет-заказы. Раньше
     * этот склад от покупателя скрывался целиком (п.5 старого ТЗ) — теперь
     * клиент явно просит показывать его отдельной строкой под клиентским
     * названием, не как ещё один «магазин» в списке городов.
     *
     * Использует те же in_stoc/products_count, что и остальной сайт (кнопка
     * «в корзину», каталог) — ImportFrom1C пишет их именно с главного склада
     * (config('custom.main_store_rest_id')), отдельный запрос к
     * goods_shop_rests не нужен.
     */
    public static function onlineAvailable(GoodsItemId $goods_item): bool
    {
        return (bool) $goods_item->in_stoc && (int) $goods_item->products_count > 0;
    }

    /**
     * Есть ли товар хоть где-то — в любом магазине или на главном складе
     * (правка от 14.10.2026: «если товара нет нигде, скрыть с сайта»).
     * false здесь означает «скрыть товар с сайта» — используется там, где
     * решается видимость карточки, а не просто бейдж наличия.
     */
    public static function isAvailableAnywhere(GoodsItemId $goods_item): bool
    {
        if (self::onlineAvailable($goods_item)) {
            return true;
        }

        return self::byShops($goods_item)->contains('in_stock', true);
    }

    /** Готова ли интеграция: аварийный выключатель блока. */
    public static function isAvailable(): bool
    {
        return (bool) config('custom.front.stock_by_shops_enabled', false);
    }

    /**
     * Ближайший к покупателю магазин, где товар реально есть в наличии
     * (п.5 ТЗ — Epic 5). Переиспользует ту же формулу расстояния (haversine),
     * что и клиентский `distanceKm()` в shops-page.js (Epic 2), только на
     * стороне сервера — здесь нужен именно расчёт, а не отображение на карте.
     * Магазины без наличия (`in_stock=false`) или без координат не
     * учитываются. Пустой `byShops()` (см. его докблок — 1С ещё не шлёт
     * остатки по складам) означает null здесь тоже: "ближайшего с наличием"
     * не определить без данных о наличии.
     */
    public static function nearestWithStock(GoodsItemId $goods_item, float $lat, float $lng): ?array
    {
        $nearest = self::byShops($goods_item)
            ->filter(fn ($row) => $row['in_stock'] && $row['lat'] && $row['lng'])
            ->map(function ($row) use ($lat, $lng) {
                $row['distance_km'] = self::haversineKm($lat, $lng, (float) $row['lat'], (float) $row['lng']);

                return $row;
            })
            ->sortBy('distance_km')
            ->first();

        return $nearest ?: null;
    }

    private static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $rad = M_PI / 180;
        $h = sin(($lat2 - $lat1) * $rad / 2) ** 2
            + cos($lat1 * $rad) * cos($lat2 * $rad) * sin(($lng2 - $lng1) * $rad / 2) ** 2;

        return 6371 * 2 * atan2(sqrt($h), sqrt(1 - $h));
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
