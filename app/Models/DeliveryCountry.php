<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class DeliveryCountry extends Model
{
    protected $table = 'delivery_countries';

    protected $fillable = [
        'name', 'iso_code_2', 'iso_code_3', 'address_format', 'postcode_required', 'active'
    ];
}