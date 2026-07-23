<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Basket extends Model
{
    use HasFactory;

    protected $table = 'basket';

	protected $fillable = [

		'basket_id', 'goods_item_id', 'items_count', 'goods_name', 'goods_price', 'goods_one_c_code', 'goods_model', 'promo_one_c_id', 'has_cadou', 'related_one_c_id', 'discount_procent', 'discount_summa'
 	];

	public function basketId()
	{
		return $this->hasOne('App\Models\BasketId', 'id', 'basket_id');
	}

	public function goodsItemId()
	{
		return $this->hasOne('App\Models\GoodsItemId', 'id', 'goods_item_id');
	}

	public function goodsItem()
	{
		return $this->hasOne('App\Models\GoodsItem', 'goods_item_id', 'goods_item_id');
	}

	public function oImage() {
		return $this->hasOne('App\Models\GoodsPhoto', 'goods_item_id', 'goods_item_id')->where('active', '1')->orderBy('position', 'asc');
	}

}
