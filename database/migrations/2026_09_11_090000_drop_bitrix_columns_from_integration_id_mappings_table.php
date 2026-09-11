<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bitrix24 integration cancelled by the client 2026-09-11 (was
        // mock-only — LoggingBitrixDealGateway, never had real credentials).
        // 1С remains the only leg in integration_id_mappings.
        // Идемпотентность: деплой применяет миграции на стендах с разной
        // историей, повторный прогон не должен падать.
        if (!Schema::hasColumn('integration_id_mappings', 'bitrix_deal_id')) {
            return;
        }

        Schema::table('integration_id_mappings', function (Blueprint $table) {
            $table->dropIndex(['bitrix_deal_id']);
            $table->dropColumn(['bitrix_deal_id', 'bitrix_status', 'bitrix_attempts']);
        });
    }

    public function down(): void
    {
        Schema::table('integration_id_mappings', function (Blueprint $table) {
            $table->string('bitrix_deal_id')->nullable()->index()->after('onec_document_id');
            $table->string('bitrix_status', 20)->default('pending')->after('onec_status');
            $table->unsignedTinyInteger('bitrix_attempts')->default(0)->after('onec_attempts');
        });
    }
};
