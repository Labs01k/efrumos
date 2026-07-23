<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GoodsParametrValue extends Model
{
    use HasFactory;

    protected $table = 'goods_parametr_value';

    protected $fillable = [
        'goods_parametr_value_id', 'lang_id', 'name'
    ];

    public function parametrValueId()
    {
        return $this->hasOne('App\Models\GoodsParametrValueId', 'id', 'goods_parametr_value_id');
    }

}
