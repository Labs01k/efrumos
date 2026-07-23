<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoLineId extends Model
{
    use HasFactory;

    protected $table = 'info_line_id';

    protected $fillable = [
        'alias', 'active', 'deleted', 'img', 'position'
    ];

    public function itemByLang()
    {
        return $this->hasOne('App\Models\InfoLine', 'info_line_id', 'id')->where('lang_id', LANG_ID);
    }

    public function infoItems(){
        return $this->hasmany('App\Models\InfoItemId', 'info_line_id', 'id')
            ->where('active', 1)
            ->where('deleted', 0)
            ->with('itemByLang','oImage')
            ->orderBy(config('custom.sorting.sort_info_items_slider')[0], config('custom.sorting.sort_info_items_slider')[1]);
    }
}
