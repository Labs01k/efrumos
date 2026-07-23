<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerTop extends Model
{
    use HasFactory;

    protected $table = 'banner_top';

    protected $fillable = [
        'banner_top_id', 'lang_id', 'img', 'name', 'h1_title','body', 'link', 'link_name', 'img_mobile'
    ];

    public function bannerTopId()
    {
        return $this->hasOne('App\Models\BannerTopId', 'id', 'banner_top_id');
    }

}
