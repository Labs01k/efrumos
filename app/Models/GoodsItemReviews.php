<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsItemReviews extends Model
{
    protected $table = 'goods_item_reviews';

    protected $fillable = [
        'goods_item_id', 'front_user_id', 'active', 'rating', 'review_text'
    ];

    public function frontUserId()
    {
        return $this->hasOne('App\Models\FrontUser', 'id', 'front_user_id');
    }

    /*public function oImages() {
        return $this->hasMany('App\Models\GoodsItemReviewsImages', 'review_id', 'id')->orderBy('position', 'asc');
    }*/
}
