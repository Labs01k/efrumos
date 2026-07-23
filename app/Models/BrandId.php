<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandId extends Model
{
    use HasFactory;

    protected $table = 'goods_brand_id';

    protected $fillable = [
        'img', 'alias', 'active', 'deleted', 'link', 'position', 'p_id', 'level', 'img_palette', 'img_certificate', 'img_seo'
    ];

    public function parent()
    {
        return $this->belongsTo('App\Models\BrandId', 'p_id', 'id');
    }

    public function itemByLang()
    {
        return $this->hasOne('App\Models\Brand', 'goods_brand_id', 'id')->where('lang_id', LANG_ID);
    }

    /*public function goodsItemId() {
        return $this->hasOne('App\Models\GoodsItemId', 'brand_id', 'id');
    }*/

    public function children()
    {
        return $this->hasMany('App\Models\BrandId', 'p_id', 'id')
            ->where('active', 1)
            ->where('deleted', 0)
            ->has('itemByLang')
            ->with('itemByLang')
            ->orderBy('position', 'asc');
    }

    public function childrenSortByName()
    {
        return $this->hasMany('App\Models\BrandId', 'p_id', 'id')
            ->where('active', 1)
            ->where('deleted', 0)
            ->has('itemByLang')
            ->with('itemByLang')
            ->orderBy(
                Brand::select('name')
                    ->whereColumn('id', 'goods_brand_id.id')
                    ->orderBy('name', 'asc')
            );
            /*->with(['itemByLang' => function($query) {
                $query->orderBy('name', 'asc');
            }]);*/
    }

    public function moduleMultipleImg() {
        return $this->hasMany('App\Models\BrandImages', 'goods_brand_id', 'id')->orderBy('position', 'asc');
    }

	public function oImage() {
		return $this->hasOne('App\Models\BrandImages', 'goods_brand_id', 'id')->orderBy('position', 'asc');
	}


}
