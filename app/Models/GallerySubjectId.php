<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GallerySubjectId extends Model
{
    use HasFactory;

    protected $table = 'gallery_subject_id';

    protected $fillable = [
        'p_id', 'alias', 'active', 'deleted', 'level', 'position', 'leader', 'new', 'img', 'height', 'width'
    ];

    public function galleryItemId(){
        return $this->hasOne('App\Models\GalleryItemId', 'gallery_subject_id', 'id');
    }

    public function children()
    {
        return $this->hasMany('App\Models\GallerySubjectId', 'p_id', 'id');
    }

    public function itemByLang()
    {
        return $this->hasOne('App\Models\GallerySubject', 'gallery_subject_id', 'id')->where('lang_id', LANG_ID);
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\GallerySubjectId', 'p_id', 'id');
    }

    public function galleryMedia(){
        return $this->hasmany('App\Models\GalleryItemId', 'gallery_subject_id', 'id');
    }

    public function galleryMediaVideo(){
        return $this->hasmany('App\Models\GalleryItemId', 'gallery_subject_id', 'id')
            ->where('type', 'video')
            ->where('active', 1)
            ->where('deleted', 0)
            ->with('itemByLang')
            ->orderBy('position');
    }

}
