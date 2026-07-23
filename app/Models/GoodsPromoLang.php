<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GoodsPromoLang extends Model
{
    use HasFactory;

    protected $table = 'goods_promo_lang';

    protected $fillable = [
       'goods_promo_id', 'lang_id', 'tag_name'
    ];

}

