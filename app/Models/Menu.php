<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menu';

    protected $fillable = [
        'menu_id', 'lang_id', 'name', 'short_descr', 'body', 'link', 'page_title', 'h1_title', 'meta_title', 'meta_keywords', 'meta_description', 'body_two'
    ];

    public function menuId(){
        return $this->hasOne('App\Models\MenuId', 'id', 'menu_id');
    }


}
