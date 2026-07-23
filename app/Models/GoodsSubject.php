<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GoodsSubject extends Model
{
    use HasFactory;

    protected $table = 'goods_subject';

    protected $fillable = [
        'goods_subject_id', 'lang_id', 'name', 'short_descr', 'body', 'page_title', 'h1_title', 'meta_title', 'meta_keywords', 'meta_description', 'link_banner'
    ];

    public function goodsSubjectId(){
        return $this->hasOne('App\Models\GoodsSubjectId', 'id', 'goods_subject_id');
    }

}

