<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsParametrValueId extends Model
{
    use HasFactory;

    protected $table = 'goods_parametr_value_id';

    protected $fillable = [
        'goods_parametr_id', 'position', 'active'
    ];

    public function itemByLang(){
        return $this->hasOne('App\Models\GoodsParametrValue', 'goods_parametr_value_id', 'id')->where('lang_id', LANG_ID);
    }

	public function parametrValue()
	{
		return $this->hasOne('App\Models\GoodsParametrValue', 'goods_parametr_value_id', 'id');
	}

}
