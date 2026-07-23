<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsSubjectId extends Model
{
    use HasFactory;

    protected $table = 'goods_subject_id';

    protected $fillable = [
        'p_id', 'alias', 'active', 'deleted', 'level', 'position', 'img', 'one_c_code', 'one_c_id', 'section', 'menurow', 'img', 'oldid', 'icon_name', 'position_promo', 'img_two'
    ];

    public function parent()
    {
        return $this->belongsTo('App\Models\GoodsSubjectId', 'p_id', 'id');
    }

    public function children()
    {
        return $this->hasMany('App\Models\GoodsSubjectId', 'p_id', 'id')
            ->where('active', 1)
            ->where('deleted', 0)
            ->has('itemByLang')
            ->with('itemByLang')
            ->orderBy('position', 'asc');
    }

    public function itemByLang()
    {
        return $this->hasOne('App\Models\GoodsSubject', 'goods_subject_id', 'id')->where('lang_id', LANG_ID);
    }

    public function goodsItemId(){
        return $this->hasOne('App\Models\GoodsItemId', 'goods_subject_id', 'id');
    }

	public function goodsSubject(){
		return $this->hasOne('App\Models\GoodsSubject', 'goods_subject_id', 'id');
	}

    public function oImages(){
        return $this->hasMany('App\Models\GoodsSubjectImages', 'goods_subject_id', 'id')->where('lang_id', LANG_ID)->where('active', 1)->orderBy('position', 'asc');
    }

    public function promoGoodsItems(){
        return $this->hasMany('App\Models\GoodsItemId','goods_subject_pid','id')
            ->where('active', 1)
            ->where('deleted', 0)
            ->where('price_promo','>',0)
            ->where('in_stoc', 1)
            ->has('itemByLang')
            ->with('itemByLang', 'oImage', 'getBrand', 'getBrand.itemByLang')
            ->orderBy('updated_at', 'desc')
            ->limit(config('custom.front.products_in_slider'));
    }

    public function getNameAttribute()
    {
        return $this->itemByLang->name ?? null;
    }

    public function goodsSeoPages(){
        return $this->hasMany('App\Models\GoodsPageId', 'goods_subject_id', 'id')
            ->where('active', 1)
            ->where('deleted', 0)
            ->has('itemByLang')
            ->with('itemByLang', 'children')
            ->orderBy('position', 'asc');
    }

}
