<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $table = 'banner';

    protected $fillable = [
        'banner_id', 'lang_id', 'name', 'body', 'link', 'short_descr', 'link_name'
    ];

    public function bannerId()
    {
        return $this->hasOne('App\Models\BannerId', 'id', 'banner_id');
    }
}
