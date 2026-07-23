<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GoodsItemTagsId extends Model
{
    use HasFactory;

    protected $table = 'goods_item_tags_id';

    protected $fillable = [
        'active'
    ];

    public function itemByLang()
    {
        return $this->hasOne('App\Models\GoodsItemTags', 'goods_item_tags_id', 'id')->where('lang_id', LANG_ID);
    }

    public function getNameAttribute()
    {
        return $this->itemByLang->name ?? null;
    }
}
