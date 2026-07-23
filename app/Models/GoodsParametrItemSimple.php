<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GoodsParametrItemSimple extends Model
{
    use HasFactory;

    protected $table = 'goods_parametr_item_simple';

    protected $fillable = [
        'goods_parametr_item_id', 'lang_id', 'parametr_value'
    ];

}
