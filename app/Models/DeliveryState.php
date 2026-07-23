<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class DeliveryState extends Model
{
    protected $table = 'delivery_states';

    protected $fillable = [
        'country_id', 'name', 'code', 'active', 'is_suburbs', 'is_transnistria'
    ];

    public function country() {
        return $this->hasOne('App\Models\DeliveryCountry', 'id', 'country_id');
    }

    public static function getCitiesByCountryId($country_id) {
        return self::where('country_id', $country_id)->where('active', 1)->orderBy('name', 'asc')->get();
    }
}
