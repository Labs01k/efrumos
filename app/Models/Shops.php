<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shops extends Model
{
    use HasFactory;

    protected $table = 'shops';

    protected $fillable = [
        'shops_id', 'lang_id', 'name', 'address', 'schedule'
    ];

    public function shopsId()
    {
        return $this->hasOne('App\Models\ShopsId', 'id', 'shops_id');
    }

}
