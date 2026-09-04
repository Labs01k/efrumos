<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'line_brand_id', 'goods_item_id', 'shade_code', 'shade_number',
        'shade_name', 'price', 'products_count', 'in_stoc',
    ];

    protected $casts = [
        'in_stoc' => 'boolean',
    ];
}
