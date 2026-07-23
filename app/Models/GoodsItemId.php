<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class GoodsItemId extends Model
{
    use HasFactory;

    protected $table = 'goods_item_id';

    /*protected static function booted(): void
    {
        static::addGlobalScope('active',function (Builder $builder){
            $builder->where('active',1);
        });

    }*/

    private static $user_wish_id;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        self::$user_wish_id = ifUserSessionExists() ? app('global_user')->wishId : null;
    }

    protected $fillable = [
        'goods_subject_id', 'other_goods_subject_id', 'brand_id', 'alias', 'active', 'deleted', 'one_c_id', 'one_c_code', 'position', 'show_on_main', 'add_date', 'popular_element', 'new_element', 'price', 'price_promo', 'in_stoc', 'articol', 'youtube_link', 'youtube_id', 'products_count', 'rating', 'barcode', 'produse_compatibile', 'produse_similare', 'b2b_type', 'goods_type_id', 'show_in_search', 'one_c_code_guid', 'price_promo_date_end'
    ];

    public function oImage()
    {
        return $this->hasOne('App\Models\GoodsPhoto', 'goods_item_id', 'id')->where('active', 1)->orderBy('position', 'asc');
    }

    public function oImages()
    {
        return $this->hasMany('App\Models\GoodsPhoto', 'goods_item_id', 'id')->where('active', 1)->orderBy('position', 'asc');
    }

    public function goodsVideos()
    {
        return $this->hasMany('App\Models\GoodsItemVideo', 'goods_item_id', 'id')->where('active', 1)->orderBy('position', 'asc');
    }

    public function goodsVideosForBack()
    {
        return $this->hasMany('App\Models\GoodsItemVideo', 'goods_item_id', 'id')->orderBy('position', 'asc');
    }

    public function getBrand()
    {
        return $this->hasOne('App\Models\BrandId', 'id', 'brand_id');
    }

    public function getType()
    {
        return $this->hasOne('App\Models\GoodsTypeId', 'id', 'goods_type_id');
    }

    public function oBrandImage()
    {
        return $this->hasOne('App\Models\BrandImages', 'brand_id', 'brand_id')->orderBy('position', 'asc');
    }

    public function goodsItem()
    {
        return $this->hasMany('App\Models\GoodsItem', 'goods_item_id', 'id');
    }

    public function getSubjectId()
    {
        return $this->hasOne('App\Models\GoodsSubjectId', 'id', 'goods_subject_id');
    }

    public function subjectByLang()
    {
        return $this->hasOne('App\Models\GoodsSubject', 'goods_subject_id', 'goods_subject_id')->where('lang_id', LANG_ID);
    }

    public function goodsItemReviews()
    {
        return $this->hasMany('App\Models\GoodsItemReviews', 'goods_item_id', 'id')->where('active', 1)->orderBy('created_at', 'desc');
    }

    public function itemByLang()
    {
        return $this->hasOne('App\Models\GoodsItem', 'goods_item_id', 'id')->where('lang_id', LANG_ID);
    }

    public function itemByDefaultLang()
    {
        return $this->hasOne('App\Models\GoodsItem', 'goods_item_id', 'id')->where('lang_id', app()->getLocale());
    }

    public function checkIfWishItemExist()
    {
        return $this->hasOne('App\Models\Wish', 'goods_item_id', 'id')->where('wish_id', self::$user_wish_id?->id);
    }

    public function goodsPromoTags()
    {
        return $this->belongsToMany(GoodsPromo::class, 'goods_promo_items', 'goods_item_id', 'goods_promo_id')->with('itemByLang')->where('show_tag_in_products', 1);
    }

    public function getNameAttribute()
    {
        return $this->itemByLang->name ?? null;
    }

    public function itemsAllLangs()
    {
        return $this->hasMany('App\Models\GoodsItem', 'goods_item_id', 'id');
    }

}

