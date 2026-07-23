<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewMenuId extends Model
{
    use HasFactory;

    protected $table = 'menu_id';

    protected $fillable = [
        'p_id', 'level', 'alias', 'page_type', 'position', 'active', 'deleted', 'img', 'top_menu', 'footer_menu'
    ];

    public function menu() {
        return $this->hasMany('App\Models\Menu', 'menu_id', 'id');
    }

    public function moduleMultipleImg() {
        return $this->hasMany('App\Models\MenuImages', 'menu_id', 'id')->orderBy('position', 'asc');
    }

    public function oImage()
    {
        return $this->hasOne('App\Models\MenuImages', 'menu_id', 'id')->orderBy('position', 'asc');
    }

    public function oImageDesc()
    {
        return $this->hasOne('App\Models\MenuImages', 'menu_id', 'id')->orderBy('position', 'desc');
    }

    public function children()
    {
        return $this->hasMany('App\Models\MenuId', 'p_id', 'id')
            ->where('active', 1)
            ->where('deleted', 0)
            ->has('itemByLang')
            ->with('itemByLang', 'oImage')
            ->orderBy('position', 'asc');
    }

    public function itemByLang()
    {
        return $this->hasOne('App\Models\Menu', 'menu_id', 'id')->where('lang_id', LANG_ID);
    }

    public function getNameAttribute()
    {
        return $this->itemByLang->name ?? null;
    }

    public function getH1TitleAttribute()
    {
        return $this->itemByLang->h1_title ?? null;
    }

    public function getTitlePageAttribute()
    {
        return $this->itemByLang->h1_title ?? ($this->itemByLang->name ?? null);
    }

    public function getShortDescrAttribute()
    {
        return $this->itemByLang->short_descr ?? null;
    }

    public function getBodyAttribute()
    {
        return $this->itemByLang->body ?? null;
    }

    public function getImageAttribute()
    {
        return $this->oImage && $this->oImage->img && file_exists('upfiles/menu/' . $this->oImage->img) ?
            asset('upfiles/menu/'. $this->oImage->img) : asset('front-assets/img/no-image.png');
    }

    public function getSecondImageAttribute()
    {
        return $this->moduleMultipleImg && $this->moduleMultipleImg[1]
        && $this->moduleMultipleImg[1]->img
        && file_exists('upfiles/menu/' . $this->moduleMultipleImg[1]->img) ?
            asset('upfiles/menu/'. $this->moduleMultipleImg[1]->img) : asset('front-assets/img/no-image.png');
    }
}
