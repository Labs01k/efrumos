<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Остаток товара на складе 1С (п.5 ТЗ). Строки пишет обмен ImportFrom1C
 * из массива Rests: по одной на пару товар+склад. Основной склад комплектации
 * тоже сохраняется, но покупателю не показывается (ProductStock::MAIN_WAREHOUSE_GUID).
 */
class GoodsShopRest extends Model
{
    protected $table = 'goods_shop_rests';

    protected $fillable = [
        'goods_item_id', 'store_guid', 'store_name', 'qty',
    ];
}
