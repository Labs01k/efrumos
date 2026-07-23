<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsItemsColors extends Model
{
    use HasFactory;

    protected $table = 'goods_item_colors';

    protected $fillable = [

        'goods_item_id', 'goods_colors_id', 'position'

    ];
}
