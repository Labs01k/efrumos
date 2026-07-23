<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GoodsTypeId extends Model
{
    use HasFactory;

    protected $table = 'goods_type_id';

    protected $fillable = [
        'position'
    ];

    public function itemByLang()
    {
        return $this->hasOne('App\Models\GoodsType', 'goods_type_id', 'id')->where('lang_id', LANG_ID);
    }

    public function getNameAttribute()
    {
        return $this->itemByLang->name ?? null;
    }
}
