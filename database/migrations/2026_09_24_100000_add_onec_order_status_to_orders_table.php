<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [BE] Синхронное обновление статуса после оплаты — приёмник вебхука 1С
 * (App\Http\Controllers\Integration\OneCOrderStatusWebhookController).
 *
 * Названо onec_order_status, не onec_status — на orders.onec_status было бы
 * легко спутать с integration_id_mappings.onec_status, а это другое: там
 * НАШ статус синхронизации (pending/synced/failed), здесь — бизнес-статус,
 * присланный 1С («в сборке» и т.п., точный словарь значений неизвестен).
 *
 * Формат payload от 1С не подтверждён (эндпоинт `estel.sale.webhook.status`
 * у Platon/Progression — не наш, увидеть реальный пример нельзя, только
 * запросить письмом, см. efrumos-docs/1c-open-items.md п.6) — сохраняем
 * статус как есть, строкой, без своего словаря значений, пока не увидим
 * первый настоящий вызов.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'onec_order_status')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->string('onec_order_status', 100)->nullable()->after('payment_status_changed_at');
            $table->timestamp('onec_order_status_synced_at')->nullable()->after('onec_order_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['onec_order_status', 'onec_order_status_synced_at']);
        });
    }
};
