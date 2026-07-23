<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopsImages extends Model
{
    use HasFactory;

    protected $table = 'shops_images';

    protected $fillable = [
        'shops_id', 'img', 'active', 'position'
    ];
}
