<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsPageId extends Model
{
    use HasFactory;

    protected $table = 'goods_page_id';

    protected $fillable = [
        'p_id', 'level', 'alias', 'position', 'active', 'deleted', 'goods_subject_id', 'link'
    ];

    public function moduleMultipleImg() {
        return $this->hasMany('App\Models\GoodsPageImages', 'goods_page_id', 'id')->orderBy('position', 'asc');
    }

    public function oImage()
    {
        return $this->hasOne('App\Models\GoodsPageImages', 'goods_page_id', 'id')->orderBy('position', 'asc');
    }

    public function oImageDesc()
    {
        return $this->hasOne('App\Models\GoodsPageImages', 'goods_page_id', 'id')->orderBy('position', 'desc');
    }


    public function children()
    {
        return $this->hasMany('App\Models\GoodsPageId', 'p_id', 'id')
            ->where('active', 1)
            ->where('deleted', 0)
            ->has('itemByLang')
            ->with('itemByLang')
            ->orderBy('position', 'asc');
    }

    public function itemByLang()
    {
        return $this->hasOne('App\Models\GoodsPage', 'goods_page_id', 'id')->where('lang_id', LANG_ID);
    }
}
