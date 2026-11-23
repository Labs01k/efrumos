<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Договор с банком (SOLVEX LUX SRL, п.2.7): капчур (TRTYPE=21) должен
        // уходить в день доставки, не позже 6 дней с положительного ответа на
        // авторизацию — а не сразу при авторизации, как было раньше. Нужна
        // отдельная метка момента успешной АВТОРИЗАЦИИ (ACTION=0 RC=00),
        // отличная от confirmed_at (момент реального капчура) — иначе нечем
        // отличить «авторизован, капчур отложен до доставки» от «вообще не
        // платил» ни в AutoCancelUnpaidOrders, ни в защитной job на 6 дней.
        // Идемпотентность: деплой применяет миграции на стендах с разной
        // историей, повторный прогон не должен падать.
        if (Schema::hasColumn('order_payments', 'authorized_at')) {
            return;
        }

        Schema::table('order_payments', function (Blueprint $table) {
            $table->timestamp('authorized_at')->nullable()->after('provider_status');
        });
    }

    public function down(): void
    {
        Schema::table('order_payments', function (Blueprint $table) {
            $table->dropColumn('authorized_at');
        });
    }
};
