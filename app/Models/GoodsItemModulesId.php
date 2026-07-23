<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsItemModulesId extends Model
{
    use HasFactory;

    protected $table = 'goods_item_modules_id';

    protected $fillable = [
        'goods_item_id', 'position'
    ];

    public function goodsItemId(){
        return $this->hasOne('App\Models\GoodsItemId', 'id', 'goods_item_id');
    }

    public function itemByLang(){
        return $this->hasOne('App\Models\GoodsItemModules', 'goods_item_modules_id', 'id')->where('lang_id', LANG_ID);
    }
}
