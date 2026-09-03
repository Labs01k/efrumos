<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    protected $table = 'order_payments';

    protected $fillable = [
        'orders_id', 'provider', 'external_payment_id', 'rrn', 'int_ref', 'amount_bani', 'currency',
        'provider_status', 'signature_verified', 'raw_callback_payload', 'confirmed_at',
    ];

    protected $casts = [
        'signature_verified' => 'boolean',
        'raw_callback_payload' => 'array',
        'confirmed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'orders_id', 'id');
    }
}
