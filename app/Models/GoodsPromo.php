<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GoodsPromo extends Model
{
    use HasFactory;

    protected $table = 'goods_promo';

    protected $fillable = [
        'one_c_id', 'promo_type', 'name', 'data_start', 'data_end', 'tip_price', 'discount_procent', 'discount_summa', 'cant_pentru_disc', 'cant_cadou', 'promocod', 'show_tag_in_products', 'tag_color'
    ];

    public function itemByLang()
    {
        return $this->hasOne('App\Models\GoodsPromoLang', 'goods_promo_id', 'id')->where('lang_id', LANG_ID);
    }

    public function goodsPromoLangItems()
    {
        return $this->hasMany('App\Models\GoodsPromoLang', 'goods_promo_id', 'id');
    }

    public function getTagNameAttribute()
    {
        return $this->itemByLang->tag_name ?? null;
    }

}

