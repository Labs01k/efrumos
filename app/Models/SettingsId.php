<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingsId extends Model
{
    use HasFactory;

    protected $table = 'settings_id';

    protected $fillable = [
        'alias', 'set_type', 'lang_dependent', 'position', 'body_without_lang'
    ];

    public function itemByLang()
    {
        return $this->hasOne('App\Models\Settings', 'settings_id', 'id')->where('lang_id', LANG_ID);
    }

}
