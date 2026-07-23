<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerId extends Model
{
    use HasFactory;

    protected $table = 'banner_id';

    protected $fillable = [
        'img', 'p_id', 'alias', 'active', 'deleted', 'position', 'percent', 'color_code', 'color_code_button'
    ];

    public function moduleMultipleImg() {
        return $this->hasMany('App\Models\BannerImages', 'banner_id', 'id')->orderBy('position', 'asc');
    }

    public function oImage() {
        return $this->hasOne('App\Models\BannerImages', 'banner_id', 'id')->orderBy('position', 'asc');
    }

    public function itemByLang()
    {
        return $this->hasOne('App\Models\Banner', 'banner_id', 'id')->where('lang_id', LANG_ID);
    }

    public function children()
    {
        return $this->hasMany('App\Models\BannerId', 'p_id', 'id')
            ->where('active', 1)
            ->where('deleted', 0)
            ->has('itemByLang')
            ->with('itemByLang', 'oImage')
            ->orderBy('position', 'asc');
    }

    public function oImageDesc() {
        return $this->hasOne('App\Models\BannerImages', 'banner_id', 'id')->orderBy('position', 'desc');
    }

    public function getImageByLang(){
        if(LANG == 'ro')
            return $this->oImage();
        else
            return $this->oImageDesc();
    }

}
