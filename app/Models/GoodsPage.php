<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsPage extends Model
{
    use HasFactory;

    protected $table = 'goods_page';

    protected $fillable = [
        'goods_page_id', 'lang_id', 'name', 'short_descr', 'body', 'page_title', 'h1_title', 'meta_title', 'meta_keywords', 'meta_description'
    ];

    public function goodsPageId(){
        return $this->hasOne('App\Models\GoodsPageId', 'id', 'goods_page_id');
    }


}
