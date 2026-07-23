<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerImages extends Model
{
    use HasFactory;

    protected $table = 'banners_images';

    protected $fillable = [
        'banner_id', 'img', 'active', 'position'
    ];


}
