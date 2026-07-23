<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GalleryItem extends Model
{
    use HasFactory;

    protected $table = 'gallery_item';

    protected $fillable = [
        'gallery_item_id', 'lang_id', 'name', 'body'
    ];

    public function galleryItemId() {
        return $this->hasOne('App\Models\GalleryItemId', 'id', 'gallery_item_id');
    }

}

