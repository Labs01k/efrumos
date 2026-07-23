<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $table = 'city';

    protected $fillable = [
        'city_id', 'lang_id', 'name'
    ];

    public function cityId()
    {
        return $this->hasOne('App\Models\CityId', 'id', 'city_id');
    }

    public function ShopsId()
    {
        return $this->hasMany('App\Models\ShopsId', 'city_id', 'city_id');
    }

}
