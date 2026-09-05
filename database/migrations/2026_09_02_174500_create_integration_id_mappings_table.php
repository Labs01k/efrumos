<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Epic 0 / 0.3 — one row per order, linking it across every system.
        // The unique index on orders_id is what makes "приём заказа" idempotent:
        // a retried/duplicate submission finds the existing row instead of
        // creating a second 1C document / Bitrix24 deal.
        // Идемпотентность: деплой применяет миграции и патчи database/sql
        // на стендах с разной историей, повторный прогон не должен падать.
        if (Schema::hasTable('integration_id_mappings')) {
            return;
        }

        Schema::create('integration_id_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('orders_id')->unique(); // WEB ID — matches orders.id (int unsigned)
            $table->string('onec_document_id')->nullable()->index();
            $table->string('bitrix_deal_id')->nullable()->index();
            $table->unsignedBigInteger('order_payments_id')->nullable()->index();

            // per-leg sync state — lets 0.4 know exactly what still needs a retry
            // without re-deriving it from timestamps.
            $table->string('onec_status', 20)->default('pending'); // pending|synced|failed
            $table->string('bitrix_status', 20)->default('pending');
            $table->unsignedTinyInteger('onec_attempts')->default(0);
            $table->unsignedTinyInteger('bitrix_attempts')->default(0);
            $table->text('last_error')->nullable();

            $table->timestamps();

            $table->foreign('orders_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('order_payments_id')->references('id')->on('order_payments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_id_mappings');
    }
};
