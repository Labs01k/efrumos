<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsPageImages extends Model
{
    use HasFactory;

    protected $table = 'goods_page_images';

    protected $fillable = [
        'goods_page_id', 'img', 'active', 'position'
    ];


}
