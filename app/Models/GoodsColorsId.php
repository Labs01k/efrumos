<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsColorsId extends Model
{
    use HasFactory;

    protected $table = 'goods_colors_id';

    protected $fillable = [
        'p_id','img'
    ];

	public function getGoodsItemColors() {
		return $this->hasOne('App\Models\GoodsItemColors', 'goods_colors_id', 'id');
	}

	public function GoodsColors() {
		return $this->hasOne('App\Models\GoodsColors', 'goods_colors_id', 'id');
	}

	public function Basket()
	{
		return $this->hasOne('App\Models\Basket', 'goods_colors_id', 'id');
	}

}
