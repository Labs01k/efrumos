<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsItemModules extends Model
{
    use HasFactory;

    protected $table = 'goods_item_modules';

    protected $fillable = [
        'goods_item_modules_id', 'lang_id', 'name', 'body'
    ];

    public function goodsItemModulesId(){
        return $this->hasOne('App\Models\GoodsItemModulesId', 'id', 'goods_item_modules_id');
    }


}

