<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // VictoriaBank capture (TRTYPE=21) must reach the bank exactly once
        // per authorization. handle() can run more than once — the bank
        // retries the server callback, and it may race PollVictoriaBankStatusJob.
        // This column is the atomic one-shot claim: the first handler to set it
        // (UPDATE ... WHERE capture_requested_at IS NULL) is the only one that
        // sends TRTYPE=21.
        // Идемпотентность: деплой применяет миграции на стендах с разной
        // историей, повторный прогон не должен падать.
        if (Schema::hasColumn('order_payments', 'capture_requested_at')) {
            return;
        }

        Schema::table('order_payments', function (Blueprint $table) {
            $table->timestamp('capture_requested_at')->nullable()->after('confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_payments', function (Blueprint $table) {
            $table->dropColumn('capture_requested_at');
        });
    }
};
