<?php

namespace App\Services\AppApi;

use App\Models\GoodsItemId;
use App\Services\Product\ProductRecommendations;
use App\Services\Product\ProductStock;
use App\Services\Product\ShadePalette;
use App\Services\Product\YouStarPalette;
use Illuminate\Support\Collection;

/**
 * Товар в ответах API приложения. Цены, фото, оттенки и остатки берутся из
 * тех же сервисов и хелперов, что и на сайте (getGoodsPrice, ShadePalette,
 * ProductStock), чтобы приложение и сайт не расходились.
 */
final class ProductPresenter
{
    public const CURRENCY = 'MDL';

    /** Карточка в списке. Модель должна быть загружена с itemByLang, oImage, getBrand.itemByLang. */
    public static function summary(GoodsItemId $goods): array
    {
        return [
            'id' => $goods->id,
            'code' => $goods->one_c_code,
            'alias' => $goods->alias,
            'name' => $goods->itemByLang->name ?? null,
            'brand' => $goods->getBrand->itemByLang->name ?? null,
            'price' => self::price($goods),
            'in_stock' => (int) $goods->in_stoc === 1,
            'image' => ShadePalette::cardImageUrl($goods),
            'is_popular' => (bool) $goods->popular_element,
            'is_new' => (bool) $goods->new_element,
            'url' => url('/' . LANG . '/catalog/product/' . $goods->alias),
        ];
    }

    /** Полная карточка товара. */
    public static function detail(GoodsItemId $goods): array
    {
        $goods->loadMissing('itemByLang', 'oImage', 'oImages', 'getBrand.itemByLang', 'subjectByLang');

        $shades = ShadePalette::for($goods);

        // не краска (нет goods_type_id в dye_goods_type_ids) — пробуем палитру
        // YouStar (та же логика, что и на сайте, Front\CatalogController::itemPage()):
        // группировка там не по типу товара, а по фильтру «Коллекция YouStar»
        if ($shades->isEmpty()) {
            $shades = YouStarPalette::for($goods);
        }

        return self::summary($goods) + [
            'articul' => $goods->articol,
            'barcode' => $goods->barcode,
            'category' => [
                'id' => $goods->goods_subject_id,
                'name' => $goods->subjectByLang->name ?? null,
            ],
            'short_description' => $goods->itemByLang->short_descr ?? null,
            // HTML из CMS — приложение показывает его как есть (WebView/rich text)
            'description' => $goods->itemByLang->body ?? null,
            'images' => ShadePalette::galleryImages($goods)
                ->map(fn ($image) => ['big' => $image['big'], 'thumb' => $image['thumb']])
                ->values()
                ->all(),
            'stock' => [
                // остаток основного склада — тот же, по которому 1С проверяет заказ
                'available_qty' => (int) $goods->products_count,
                'by_shops' => ProductStock::byShops($goods)
                    ->map(fn ($row) => [
                        'shop_id' => $row['shop']->id,
                        'city' => $row['city'],
                        'name' => $row['name'],
                        'address' => $row['address'],
                        'lat' => $row['lat'],
                        'lng' => $row['lng'],
                        'qty' => $row['qty'],
                        'in_stock' => $row['in_stock'],
                    ])
                    ->values()
                    ->all(),
            ],
            'shades' => $shades
                ->map(fn ($shade) => [
                    'id' => $shade->id,
                    'code' => $shade->shade_code,
                    'name' => $shade->shade_name,
                    'label' => $shade->shade_label,
                    'swatch' => $shade->shade_swatch,
                    'in_stock' => (int) $shade->in_stoc === 1,
                    'is_current' => (bool) $shade->is_current,
                ])
                ->values()
                ->all(),
            // E3-BES-03 — та же логика сайта (ручной список в CMS, совместимые
            // окислители для краски с привязкой, иначе совместные покупки/
            // бестселлеры), не отдельный пересчёт под приложение.
            'recommendations' => [
                'bought_together' => self::summaryList(ProductRecommendations::boughtTogether($goods)),
                'similar' => self::summaryList(ProductRecommendations::similar($goods)),
            ],
        ];
    }

    private static function summaryList(Collection $goodsList): array
    {
        return $goodsList
            ->map(function ($one) {
                $one->loadMissing('itemByLang', 'oImage', 'getBrand.itemByLang');

                return self::summary($one);
            })
            ->values()
            ->all();
    }

    /** @return array{current: int, old: ?int, discount_percent: int, currency: string}|null */
    public static function price(GoodsItemId $goods): ?array
    {
        $price = getGoodsPrice($goods);

        if (!$price) {
            return null;
        }

        $has_promo = $price->price_promo > 0;

        return [
            'current' => (int) $price->price,
            'old' => $has_promo ? (int) $price->price_default : null,
            'discount_percent' => $has_promo ? (int) $price->promo_percent : 0,
            'currency' => self::CURRENCY,
        ];
    }
}
