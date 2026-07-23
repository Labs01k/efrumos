<?php

namespace App\Services\GA4;

use App\Models\GoodsItemId;

class GoogleEcommerce
{
    public static function goodsCollectionsToObjects($goods_collections, $if_basket = 0, $add_fields = []): bool|string
    {
        $goods_items_array = [];

        if ($if_basket) {
            foreach ($goods_collections as $one_basket_item) {
                $goods_item = GoodsItemId::where('id', $one_basket_item->goods_item_id)->first();

                $goods_items_array[] = self::itemsArray($goods_item, $one_basket_item);
            }
        } else {
            foreach ($goods_collections as $one_item) {
                $goods_items_array[] = self::itemsArray($one_item,null, $add_fields);
            }
        }

        return json_encode($goods_items_array, JSON_UNESCAPED_UNICODE);

    }

    public static function oneGoodsCollectionToObjects($goods_item): bool|string
    {
        $goods_item_array = self::itemsArray($goods_item);

        return json_encode($goods_item_array, JSON_UNESCAPED_UNICODE);

    }

    private static function itemsArray($goods_item,  $basket_item = null, $add_fields = []): array
    {
        $price_collect = getGoodsPrice($goods_item);

        $main_arr = [
            'item_name' => $goods_item && $goods_item->itemByLang ? $goods_item->itemByLang->name : '',
            'item_id' => $goods_item && $goods_item->one_c_code ? $goods_item->one_c_code : '',
            'item_category' => $goods_item && $goods_item->itemByLang ? $goods_item->itemByLang->subject_nav_name : '',
            'item_brand' => $goods_item && $goods_item->itemByLang ? $goods_item->itemByLang->brand_nav_name : '',
            //'price' => !is_null($basket_item) ? $basket_item->goods_price : ($price_collect ? $price_collect->price : ''),
            'price' => $price_collect ? priceFormatForGA4($price_collect->price) : '',
            'quantity' => !is_null($basket_item) ? $basket_item->items_count : 1,
        ];

        if(!empty($add_fields))
            $main_arr += $add_fields;

        return $main_arr;

    }
}
