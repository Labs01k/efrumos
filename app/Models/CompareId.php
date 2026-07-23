<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CompareId extends Model
{
    use HasFactory;

    protected $table = 'compare_id';

    protected $fillable = [
        'user_ip', 'front_user_id'
    ];

}
