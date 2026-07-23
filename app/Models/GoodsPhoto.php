<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsPhoto extends Model
{
    use HasFactory;

    protected $table = 'goods_foto';

    protected $fillable = [
        'goods_item_id', 'img', 'position', 'active', 'add_date', 'photo_url'
    ];

    public function goodsItemId(){
        return $this->hasOne('App\Models\GoodsItemId', 'id', 'goods_item_id');
    }
}

