<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GoodsImages extends Model
{
    use HasFactory;

    protected $table = 'goods_images';

    protected $fillable = [
        'goods_subject_id', 'img', 'active', 'position'
    ];

}

