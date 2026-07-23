<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoItemId extends Model
{
    use HasFactory;

    protected $table = 'info_item_id';

    protected $fillable = [
        'id', 'info_line_id', 'alias', 'is_public', 'active', 'deleted', 'img', 'show_img', 'add_date', 'category', 'pdffile', 'goods_list', 'goods_promo_id', 'show_text_in_products', 'promo_color', 'position'
    ];

    public function itemByLang()
    {
        return $this->hasOne('App\Models\InfoItem', 'info_item_id', 'id')->where('lang_id', LANG_ID);
    }

    public function infoLineId()
    {
        return $this->hasMany('App\Models\InfoLineId', 'id', 'info_line_id');
    }

    public function parent()
    {
        return $this->hasOne('App\Models\InfoLineId', 'id', 'info_line_id');
    }

    public function infoItem()
    {
        return $this->hasMany('App\Models\InfoItem', 'info_item_id', 'id');
    }

    public function moduleMultipleImg() {
        return $this->hasMany('App\Models\InfoLineImages', 'info_item_id', 'id')->orderBy('position', 'asc');
    }

    public function oImage() {
        return $this->hasOne('App\Models\InfoLineImages', 'info_item_id', 'id')->orderBy('position', 'asc');
    }

    public function oImageDesc() {
        return $this->hasOne('App\Models\InfoLineImages', 'info_item_id', 'id')->orderBy('position', 'desc');
    }

    public function getGoodsPromoId()
    {
        return $this->hasOne('App\Models\GoodsPromo', 'id', 'goods_promo_id');
    }

    public function getImageByLang(){
        if(LANG == 'ro')
            return $this->oImage();
        else
            return $this->oImageDesc();
    }
}
