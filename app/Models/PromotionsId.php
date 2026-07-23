<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionsId extends Model
{
    use HasFactory;

    protected $table = 'promotions_id';

    protected $fillable = [
        'alias', 'img', 'active', 'deleted', 'position'
    ];

    public function moduleMultipleImg() {
        return $this->hasMany('App\Models\PromotionsImages', 'promotions_id', 'id')->orderBy('position', 'asc');
    }

}
