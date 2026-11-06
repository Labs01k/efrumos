<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // VictoriaBank compliance — e-mail receipt must carry the
        // authorization code and masked card number (bank's own
        // "Electronic Receipt — Required Fields" list). APPROVAL already
        // arrives on the capture/reversal server callback we store; CARD
        // only arrives on the browser BACKREF step, never on that callback
        // (confirmed empirically, see blockers.md), so it needs its own
        // column rather than being read out of raw_callback_payload.
        Schema::table('order_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('order_payments', 'approval_code')) {
                $table->string('approval_code', 20)->nullable()->after('provider_status');
            }
            if (!Schema::hasColumn('order_payments', 'card_masked')) {
                $table->string('card_masked', 32)->nullable()->after('approval_code');
            }
            // Карта не несёт язык оформления заказа (orders сам его тоже не
            // хранит — LANG только транзитный query-параметр на вызовах
            // initiate()/backref()). Письмо по картовому заказу теперь шлётся
            // отложенно, из вебхука банка, где локали покупателя взять уже
            // неоткуда — чтобы чек не уехал на дефолтном языке сервера,
            // сохраняем язык здесь же, в момент старта оплаты.
            if (!Schema::hasColumn('order_payments', 'lang')) {
                $table->string('lang', 5)->nullable()->after('card_masked');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_payments', function (Blueprint $table) {
            $table->dropColumn(['approval_code', 'card_masked', 'lang']);
        });
    }
};
