<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WishId extends Model
{
    use HasFactory;

    protected $table = 'wish_id';

    protected $fillable = [
        'user_ip', 'front_user_id'
    ];

}
