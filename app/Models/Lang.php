<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lang extends Model
{
    use HasFactory;

    protected $table = 'lang';

    protected $fillable = [
        'lang', 'default_lang', 'descr', 'active', 'position', 'active_admin'
    ];


}
