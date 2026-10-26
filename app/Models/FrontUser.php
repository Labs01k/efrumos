<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrontUser extends Model
{
    use HasFactory;

    protected $table = 'front_user';

    protected $fillable = [
        'name', 'last_name', 'email', 'password', 'active', 'address', 'remember_token', 'gift_card', 'recovery_hash', 'phone', 'google_id', 'facebook_id', 'img', 'birth', 'gender', 'district_id', 'city', 'address', 'confirmation_hash', 'confirmation_at', 'confirmation',
        // E1-BES-02 — связь с картой лояльности 1С и подтверждение телефона, см. миграцию 2026_10_26.
        'app_loyalty_client_id', 'phone_confirmed_at',
        // E1-BES-03 — когда пользователь последний раз нажал «Позже» на поп-апе подтверждения телефона.
        'phone_confirm_popup_dismissed_at',
    ];

    // Laravel 10 здесь (не 11+) — именно свойство $casts, не метод casts().
    protected $casts = [
        'phone_confirmed_at' => 'datetime',
        'phone_confirm_popup_dismissed_at' => 'datetime',
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
