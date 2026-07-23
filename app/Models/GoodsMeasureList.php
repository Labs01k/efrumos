<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GoodsMeasureList extends Model
{
    use HasFactory;

    protected $table = 'goods_measure_list';

    protected $fillable = [
        'goods_measure_id', 'goods_parametr_id', 'position',
    ];

    public function measureId()
    {
        return $this->hasOne('App\Models\GoodsMeasureId', 'id', 'goods_measure_id');
    }

}


