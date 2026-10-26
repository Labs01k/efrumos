<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * E1-BES-02 + архитектурная развилка #2 (tasks-status.md) — персистентная
 * связь профиля сайта с картой лояльности 1С (GUID из eFrumos-backend),
 * отдельно от orders.app_client_id (тот — per-заказ, для списка заказов
 * приложения, не для профиля). phone_confirmed_at — подтверждён ли
 * существующий front_user.phone через SMS (само поле phone уже есть,
 * но раньше ничем не верифицировалось).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('front_user', 'app_loyalty_client_id')) {
            return;
        }

        Schema::table('front_user', function (Blueprint $table) {
            $table->string('app_loyalty_client_id', 64)->nullable()->after('phone')->unique();
            $table->timestamp('phone_confirmed_at')->nullable()->after('app_loyalty_client_id');
        });
    }

    public function down(): void
    {
        Schema::table('front_user', function (Blueprint $table) {
            $table->dropUnique(['app_loyalty_client_id']);
            $table->dropColumn(['app_loyalty_client_id', 'phone_confirmed_at']);
        });
    }
};
