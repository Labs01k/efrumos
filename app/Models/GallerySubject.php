<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GallerySubject extends Model
{
    use HasFactory;

    protected $table = 'gallery_subject';

    protected $fillable = [
        'gallery_subject_id', 'lang_id', 'name', 'body', 'page_title', 'h1_title', 'meta_title', 'meta_keywords', 'meta_description'
    ];

    public function gallerySubjectId(){
        return $this->hasOne('App\Models\GallerySubjectId', 'id', 'gallery_subject_id');
    }

    public function getOneItemPhoto(){
        return $this->hasOne('App\Models\GalleryItemId', 'gallery_subject_id', 'gallery_subject_id')->where('show_on_main', 1)->where('deleted', 0);
    }

}

