<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopsId extends Model
{
    use HasFactory;

    protected $table = 'shops_id';

    protected $fillable = [
        'alias', 'active', 'phone', 'city_id', 'latitude', 'longitude', 'img', 'position', 'map_iframe'
    ];

    public function itemByLang()
    {
        return $this->hasOne('App\Models\Shops', 'shops_id', 'id')->where('lang_id', LANG_ID);
    }

    public function moduleMultipleImg() {
        return $this->hasMany('App\Models\ShopsImages', 'shops_id', 'id')->orderBy('position', 'asc');
    }

    public function oImage()
    {
        return $this->hasOne('App\Models\ShopsImages', 'shops_id', 'id')->orderBy('position', 'asc');
    }

    public function cityId()
    {
        return $this->belongsTo('App\Models\City', 'city_id', 'city_id');
    }

}
