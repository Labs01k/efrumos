<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // VictoriaBank e-Gateway callback gives us RRN + INT_REF, both
        // required as-is for the later TRTYPE=21 (capture) / TRTYPE=24
        // (refund) calls — kept as their own columns rather than parsed out
        // of raw_callback_payload every time they're needed.
        Schema::table('order_payments', function (Blueprint $table) {
            $table->string('rrn', 32)->nullable()->after('external_payment_id');
            $table->string('int_ref', 32)->nullable()->after('rrn');
        });
    }

    public function down(): void
    {
        Schema::table('order_payments', function (Blueprint $table) {
            $table->dropColumn(['rrn', 'int_ref']);
        });
    }
};
