<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GoodsParametrItemMeasure extends Model
{
    use HasFactory;

    protected $table = 'goods_parametr_item_measure';

    protected $fillable = [
        'goods_parametr_item_id', 'goods_measure_id', 'parametr_value'
    ];

}
