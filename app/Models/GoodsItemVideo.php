<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsItemVideo extends Model
{
    use HasFactory;

    protected $table = 'goods_item_video';

    protected $fillable = [
        'goods_item_id', 'youtube_id', 'youtube_link', 'active', 'position'
    ];

    public function goodsItemId(){
        return $this->hasOne('App\Models\GoodsItemId', 'id', 'goods_item_id');
    }
}

