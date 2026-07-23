<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GoodsParametr extends Model
{
    use HasFactory;

    protected $table = 'goods_parametr';

    protected $fillable = [
        'goods_parametr_id', 'lang_id', 'name', 'body'
    ];

    public function parametrId()
    {
        return $this->hasOne('App\Models\GoodsParametrId', 'id', 'goods_parametr_id');
    }

}


