<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsSubjectRelated extends Model
{
    use HasFactory;

    protected $table = 'goods_subject_related';

    protected $fillable = [
        'goods_subject_id', 'related_goods_subject_id', 'related_goods_brand_id'
    ];

    public function goodsSubjectId(){
        return $this->hasOne('App\Models\GoodsSubjectId', 'goods_subject_id', 'id');
    }

}
