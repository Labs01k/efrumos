<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsItemTags extends Model
{
    use HasFactory;

    protected $table = 'goods_item_tags';

    protected $fillable = [
        'goods_item_tags_id', 'lang_id', 'name'
    ];

    public function goodsItemTagsId(){
        return $this->hasOne('App\Models\GoodsItemTagsId', 'id', 'goods_item_tags_id');
    }
}
