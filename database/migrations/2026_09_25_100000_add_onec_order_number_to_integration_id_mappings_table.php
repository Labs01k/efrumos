<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Номер заказа в 1С (`ObjectCode` из ответа CreateOrder). Вебхук статуса от
 * 1С идентифицирует заказ именно им — `xml_id` в теле запроса (ответ 1С,
 * 2026-09-25), а не GUID `ObjectId`, который лежит в onec_document_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('integration_id_mappings', 'onec_order_number')) {
            return;
        }

        Schema::table('integration_id_mappings', function (Blueprint $table) {
            $table->unsignedBigInteger('onec_order_number')->nullable()->index()->after('onec_document_id');
        });
    }

    public function down(): void
    {
        Schema::table('integration_id_mappings', function (Blueprint $table) {
            $table->dropColumn('onec_order_number');
        });
    }
};
