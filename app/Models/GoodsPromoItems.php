<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GoodsPromoItems extends Model
{
    use HasFactory;

    protected $table = 'goods_promo_items';

    protected $fillable = [
        'goods_promo_id', 'goods_item_id', 'one_c_id', 'is_produs', 'is_cadou'
    ];

    public function getPromoId() {
        return $this->hasOne('App\Models\GoodsPromo', 'id', 'goods_promo_id');
    }

    public function getGoodsItemId() {
        return $this->hasOne('App\Models\GoodsItemId', 'id', 'goods_item_id')->where('active', 1)->where('deleted', 0);
    }

    public function getGoodsItem()
    {
        return $this->hasOne('App\Models\GoodsItem', 'goods_item_id', 'goods_item_id');
    }


}

