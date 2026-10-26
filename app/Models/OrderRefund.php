<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderRefund extends Model
{
    protected $fillable = [
        'orders_id',
        'order_payments_id',
        'amount_bani',
        'reason',
        'initiated_by_admin_id',
        'status',
        'rrn',
        'int_ref',
        'provider_response',
        'onec_notified_at',
    ];

    protected $casts = [
        'onec_notified_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'orders_id');
    }

    public function initiatedByAdmin()
    {
        return $this->belongsTo(User::class, 'initiated_by_admin_id');
    }
}
