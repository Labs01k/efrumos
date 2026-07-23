<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsSubjectImages extends Model
{
    use HasFactory;

    protected $table = 'goods_subject_images';

    protected $fillable = [
        'goods_subject_id', 'img', 'active', 'position', 'lang_id', 'link'
    ];


}
