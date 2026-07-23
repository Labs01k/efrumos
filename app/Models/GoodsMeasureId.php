<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GoodsMeasureId extends Model
{
    use HasFactory;

    protected $table = 'goods_measure_id';

    protected $fillable = [
        'active', 'position'
    ];

}

