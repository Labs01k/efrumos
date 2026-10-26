<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrontUserAddress extends Model
{
    protected $fillable = [
        'front_user_id',
        'label',
        'district_id',
        'city',
        'city_area',
        'address',
        'apartment',
        'zip_code',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function frontUser()
    {
        return $this->belongsTo(FrontUser::class, 'front_user_id');
    }
}
