<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CityId extends Model
{
    use HasFactory;

    protected $table = 'city_id';

    protected $fillable = [
        'alias', 'active', 'position'
    ];

    public function itemByLang()
    {
        return $this->hasOne('App\Models\City', 'city_id', 'id')->where('lang_id', LANG_ID);
    }

}
