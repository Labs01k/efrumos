<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GoodsParametrItemRsc extends Model
{
    use HasFactory;

    protected $table = 'goods_parametr_item_rsc';

    protected $fillable = [
        'goods_parametr_item_id', 'goods_parametr_value_id'
    ];

}

