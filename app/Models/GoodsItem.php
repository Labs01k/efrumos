<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsItem extends Model
{
    use HasFactory;

    protected $table = 'goods_item';

    protected $fillable = [
        'goods_item_id', 'lang_id', 'name', 'short_descr', 'body', 'body_two', 'page_title', 'h1_title', 'meta_title', 'meta_keywords', 'meta_description', 'subject_name', 'subject_nav_name', 'brand_nav_name'
    ];

    public function goodsItemId() {
        return $this->hasOne('App\Models\GoodsItemId', 'id', 'goods_item_id');
    }

    public function goodsOnePhoto() {
        return $this->hasOne('App\Models\GoodsPhoto', 'goods_item_id', 'goods_item_id')->where('active', '1')->orderBy('position', 'asc');
    }

    public function oImages(){
        return $this->hasMany('App\Models\GoodsPhoto', 'goods_item_id', 'goods_item_id')->orderBy('position', 'asc');
    }


}

