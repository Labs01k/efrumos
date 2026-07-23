<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandImages extends Model
{
    use HasFactory;

    protected $table = 'goods_brand_images';

    protected $fillable = [
        'goods_brand_id', 'img', 'active', 'position'
    ];


}
