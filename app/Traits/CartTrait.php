<?php
namespace App\Traits;

trait CartTrait
{
    public function calculate_basket($basket/*, $change_delivery = 'chisinau'*/)
    {
        $ret = [];
        $ret['total_price'] = 0;
        $ret['total_item_price'] = [];

        if (!empty($basket)) {

            foreach ($basket as $one_item) {
                $goods_item_price = $one_item->goodsItemId->price;
                $ret['total_price'] += $goods_item_price * $one_item->items_count;
                $ret['total_item_price'][$one_item->id] = $goods_item_price * $one_item->items_count;
            }
        }

        return $ret;
    }
}
