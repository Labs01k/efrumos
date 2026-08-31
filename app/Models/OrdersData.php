<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdersData extends Model
{
    use HasFactory;

    protected $table = 'orders_data';

    protected $fillable = [
        'orders_id', 'total_price', 'total_count', 'total_discount', 'delivery_cost', 'gift_card_id', 'gift_card_code', 'gift_card_sum', 'maib_trans_id', 'maib_status', 'lang_id', 'money_were_returned', 'email_sent'
    ];
}
