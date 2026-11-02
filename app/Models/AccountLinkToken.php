<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountLinkToken extends Model
{
    protected $fillable = [
        'token', 'front_user_id', 'app_state', 'expires_at', 'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function frontUser()
    {
        return $this->belongsTo(FrontUser::class, 'front_user_id', 'id');
    }
}
