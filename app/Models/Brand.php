<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Brand extends Model
{
    use HasFactory;

    protected $table = 'goods_brand';

    protected $fillable = [
        'goods_brand_id', 'lang_id', 'link', 'name', 'body', 'meta_title', 'meta_keywords', 'meta_description'
    ];

    public function brandId(){
        return $this->hasOne('App\Models\BrandId', 'id', 'goods_brand_id');
    }


}
