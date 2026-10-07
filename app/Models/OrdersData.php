<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdersData extends Model
{
    use HasFactory;

    protected $table = 'orders_data';

    protected $fillable = [
        'orders_id', 'total_price', 'total_count', 'total_discount', 'delivery_cost', 'onec_order_sum', 'onec_delivery_sum', 'onec_total_sum', 'gift_card_id', 'gift_card_code', 'gift_card_sum', 'maib_trans_id', 'maib_status', 'lang_id', 'money_were_returned', 'email_sent'
    ];

    /** Сумма к оплате: 1С (AdditionalInfo), если прислала, иначе — расчёт сайта. */
    public function amountDue(): float
    {
        return $this->onec_total_sum !== null
            ? (float) $this->onec_total_sum
            : (float) $this->total_price + (float) $this->delivery_cost;
    }
}
