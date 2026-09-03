<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Epic 0 / 0.3 — WEB ID ↔ 1C ID ↔ Bitrix ID ↔ Payment ID. By any one of the
 * four, the other three are one lookup away, and a retried/duplicate event
 * (webhook replay, job retry) finds this row instead of creating a duplicate
 * downstream record — see the unique index on orders_id in the migration.
 */
class IntegrationIdMapping extends Model
{
    protected $table = 'integration_id_mappings';

    protected $fillable = [
        'orders_id', 'onec_document_id', 'bitrix_deal_id', 'order_payments_id',
        'onec_status', 'bitrix_status', 'onec_attempts', 'bitrix_attempts', 'last_error',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'orders_id', 'id');
    }

    public function payment()
    {
        return $this->belongsTo(OrderPayment::class, 'order_payments_id', 'id');
    }

    public function isFullySynced(): bool
    {
        return $this->onec_status === 'synced' && $this->bitrix_status === 'synced';
    }
}
