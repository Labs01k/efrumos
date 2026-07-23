<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsColors extends Model
{
    use HasFactory;

    protected $table = 'goods_colors';

    protected $fillable = [

        'goods_colors_id', 'lang_id', 'name'

    ];

	public function getGoodsColorsId() {
		return $this->hasOne('App\Models\GoodsColorsId', 'id', 'goods_colors_id');
	}
}
