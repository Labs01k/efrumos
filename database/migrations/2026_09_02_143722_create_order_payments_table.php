<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Epic 1 / 1.1 — one row per payment attempt against a provider
        // (VictoriaBank today, kept provider-agnostic for later gateways).
        // Deliberately separate from `orders_data.maib_trans_id/maib_status`,
        // which is unused legacy from a different, never-finished MAIB
        // integration attempt — not reused here to avoid mixing the two.
        // Идемпотентность: деплой применяет миграции и патчи database/sql
        // на стендах с разной историей, повторный прогон не должен падать.
        if (Schema::hasTable('order_payments')) {
            return;
        }

        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('orders_id'); // matches orders.id (int unsigned) in the legacy schema
            $table->string('provider', 40)->default('victoriabank');
            $table->string('external_payment_id')->nullable()->index();
            $table->unsignedInteger('amount_bani'); // amount in bani (MDL minor unit)
            $table->string('currency', 3)->default('MDL');
            // raw status string as reported by the bank, before mapping onto
            // our own PaymentStatus enum
            $table->string('provider_status', 40)->nullable();
            $table->boolean('signature_verified')->default(false);
            $table->json('raw_callback_payload')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index('orders_id');
            $table->foreign('orders_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
