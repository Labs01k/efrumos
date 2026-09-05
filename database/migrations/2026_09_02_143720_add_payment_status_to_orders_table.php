<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Идемпотентность: деплой применяет миграции и патчи database/sql
        // на стендах с разной историей, повторный прогон не должен падать.
        if (Schema::hasColumn('orders', 'payment_status')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            // Epic 1 / 1.2 — dedicated 4-state payment status, separate from the
            // legacy boolean `paid` column (kept untouched for backward compat
            // with existing admin UI / reports that already read it).
            $table->string('payment_status', 20)->default('pending')->after('paid');
            $table->timestamp('payment_status_changed_at')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_status_changed_at']);
        });
    }
};
