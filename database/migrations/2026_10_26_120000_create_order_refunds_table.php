<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Задача с рефандом (тикет SK, 16.10.2026) — журнал операций возврата,
 * как и просили: «права доступа, подтверждение, журнал операций»
 * (комментарий на месте удалённого 07.09.2026 refundPayment()). Одна
 * строка на попытку возврата — банк разрешает только один reversal на
 * транзакцию (RC=95 на повторный), поэтому повторная попытка — это новая
 * строка со status=failed, а не апдейт старой.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_refunds')) {
            return;
        }

        Schema::create('order_refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('orders_id');
            $table->unsignedBigInteger('order_payments_id');
            $table->unsignedInteger('amount_bani'); // сумма возврата, в баних (может быть меньше полной оплаты — частичный возврат)
            $table->text('reason');
            $table->unsignedInteger('initiated_by_admin_id');
            $table->string('status', 20)->default('pending'); // pending | success | failed
            $table->string('rrn', 32)->nullable();
            $table->string('int_ref', 32)->nullable();
            $table->string('provider_response', 255)->nullable(); // например "RC=00" или "RC=95 (already reversed)"
            $table->timestamp('onec_notified_at')->nullable(); // null = 1С ещё не уведомили об этом возврате (не блокирует сам возврат)
            $table->timestamps();

            $table->index('orders_id');
            $table->foreign('orders_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('order_payments_id')->references('id')->on('order_payments')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_refunds');
    }
};
