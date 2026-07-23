<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrontUser extends Model
{
    use HasFactory;

    protected $table = 'front_user';

    protected $fillable = [
        'name', 'last_name', 'email', 'password', 'active', 'address', 'remember_token', 'gift_card', 'recovery_hash', 'phone', 'google_id', 'facebook_id', 'img', 'birth', 'gender', 'district_id', 'city', 'address', 'confirmation_hash', 'confirmation_at', 'confirmation'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function wishId()
    {
        return $this->hasOne('App\Models\WishId', 'front_user_id', 'id');
    }

    public function frontUserTotalOrders()
    {
        return $this->hasMany('App\Models\Orders', 'front_user_id', 'id');
    }
}
