<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsFrequentlyBoughtWith extends Model
{
    protected $table = 'goods_frequently_bought_with';

    protected $fillable = [
        'goods_item_id', 'related_goods_item_id', 'pair_count',
    ];
}
