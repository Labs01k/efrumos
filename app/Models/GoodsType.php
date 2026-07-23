<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsType extends Model
{
    use HasFactory;

    protected $table = 'goods_type';

    protected $fillable = [
        'goods_type_id', 'lang_id', 'name'
    ];
}
