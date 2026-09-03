<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Epic 1 / 1.2 + 1.4 — audit trail for every payment status change,
        // automatic (bank callback) or manual (admin override in the CMS).
        Schema::create('order_payment_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('orders_id'); // matches orders.id (int unsigned) in the legacy schema
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            // 'bank_callback' | 'admin' | 'system'
            $table->string('source', 20);
            // null when changed by the bank callback / system, set to the
            // admin user id for a manual change (1.4's "кто менял")
            $table->unsignedBigInteger('changed_by_admin_id')->nullable();
            $table->string('comment', 500)->nullable();
            $table->timestamps();

            $table->index('orders_id');
            $table->foreign('orders_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payment_status_logs');
    }
};
