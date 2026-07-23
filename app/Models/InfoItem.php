<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoItem extends Model
{
    use HasFactory;

    protected $table = 'info_item';

    protected $fillable = [
        'info_item_id', 'lang_id', 'name', 'descr', 'body', 'author', 'page_title', 'h1_title', 'meta_title', 'meta_keywords', 'meta_description', 'text_tag_promo'
    ];

    public function infoItemId()
    {
        return $this->hasOne('App\Models\InfoItemId', 'id', 'info_item_id');
    }
}
