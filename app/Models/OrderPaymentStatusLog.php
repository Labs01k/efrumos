<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPaymentStatusLog extends Model
{
    protected $table = 'order_payment_status_logs';

    protected $fillable = [
        'orders_id', 'from_status', 'to_status', 'source', 'changed_by_admin_id', 'comment',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'orders_id', 'id');
    }

    public function changedByAdmin()
    {
        return $this->belongsTo(User::class, 'changed_by_admin_id', 'id');
    }
}
