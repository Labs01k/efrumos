<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabelsId extends Model
{
    use HasFactory;

    protected $table = 'labels_id';

    protected $fillable = [
        'id', 'p_id'
    ];

    public function itemByLang()
    {
        return $this->hasOne('App\Models\Labels', 'labels_id', 'id')->where('lang_id', LANG_ID);
    }

    public function children()
    {
        return $this->hasMany('App\Models\LabelsId', 'p_id', 'id');
    }
}
