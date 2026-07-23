<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Subscribers extends Model
{
    use HasFactory;

    protected $table = 'subscribers';

    protected $fillable = [
        'email', 'active'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d, H:i',
    ];

}
