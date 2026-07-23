<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryItemId extends Model
{
    use HasFactory;

    protected $table = 'gallery_item_id';

    protected $fillable = [
        'gallery_subject_id', 'alias', 'active', 'deleted', 'position', 'show_on_main', 'img', 'youtube_id', 'youtube_link', 'type'
    ];

    public function getSubjectId() {
        return $this->hasOne('App\Models\GallerySubjectId', 'id', 'gallery_subject_id');
    }
    public function itemByLang()
    {
        return $this->hasOne('App\Models\GalleryItem', 'gallery_item_id', 'id')->where('lang_id', LANG_ID);
    }
}

