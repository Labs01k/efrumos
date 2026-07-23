<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsItemColors extends Model
{
    use HasFactory;

    protected $table = 'goods_item_colors';

    protected $fillable = [
        'id', 'goods_item_id', 'goods_colors_id', 'position'
    ];

    public function getGoodsColorsId() {
      return $this->hasOne('App\Models\GoodsColorsId', 'id', 'goods_colors_id');
    }

    public function getGoodsItemId() {
        return $this->hasOne('App\Models\GoodsItemId', 'id', 'goods_item_id');
    }

}

