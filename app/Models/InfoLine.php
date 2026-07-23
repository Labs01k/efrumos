<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoLine extends Model
{
    use HasFactory;

    protected $table = 'info_line';

    protected $fillable = [
        'info_line_id', 'lang_id', 'name', 'descr', 'img', 'page_title', 'h1_title', 'meta_title', 'meta_keywords', 'meta_description'
    ];

    public function infoLineId()
    {
        return $this->hasOne('App\Models\InfoLineId', 'id', 'info_line_id');
    }
}
