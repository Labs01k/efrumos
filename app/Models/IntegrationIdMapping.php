<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Epic 0 / 0.3 — WEB ID ↔ 1C ID ↔ Payment ID. By any one of the three, the
 * other two are one lookup away, and a retried/duplicate event (webhook
 * replay, job retry) finds this row instead of creating a duplicate
 * downstream record — see the unique index on orders_id in the migration.
 *
 * 2026-09-11 — dropped the Bitrix ID leg (`bitrix_deal_id`/`bitrix_status`/
 * `bitrix_attempts`): Bitrix24 integration cancelled by the client, see
 * migration 2026_09_11_090000_drop_bitrix_columns_from_integration_id_mappings_table.
 */
class IntegrationIdMapping extends Model
{
    protected $table = 'integration_id_mappings';

    protected $fillable = [
        'orders_id', 'onec_document_id', 'order_payments_id',
        'onec_status', 'onec_attempts', 'last_error',
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
        return $this->onec_status === 'synced';
    }
}
