<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsParametrId extends Model
{
    use HasFactory;

    protected $table = 'goods_parametr_id';

    protected $fillable = [
        'goods_subject_id', 'measure_type', 'goods_measure_id', 'parametr_type', 'position', 'active', 'deleted', 'alias', 'show_in_list', 'font_for_list', 'display_on_list_page', 'start_open', 'display_in_line', /*'is_color'*/
    ];

    public function itemByLang()
    {
        return $this->hasOne('App\Models\GoodsParametr', 'goods_parametr_id', 'id')->where('lang_id', LANG_ID);
    }

    public function GoodsParametr()
    {
        return $this->hasOne('App\Models\GoodsParametr', 'goods_parametr_id', 'id');
    }

    public function goodsParametrValues()
    {
        return $this->hasMany('App\Models\GoodsParametrValueId', 'goods_parametr_id', 'id')
            ->where('active', 1)
            ->with('itemByLang')
            ->has('itemByLang')
            ->orderBy('position', 'asc');
    }


}

