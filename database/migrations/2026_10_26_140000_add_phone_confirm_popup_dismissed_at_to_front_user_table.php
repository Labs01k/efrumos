<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * E1-BES-03 — «Поп-ап после входа не чаще раза в 7 дней после «Позже»»
 * (xlsx, ТЗ §4 М1). Таймер отсчитывается от последнего клика «Позже», не от
 * первого показа — поэтому нужна именно отметка времени отказа, а не счётчик
 * показов. Баннер в ЛК отдельным полем не нужен — он «постоянно до
 * подтверждения», то есть выводится по уже существующему phone_confirmed_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('front_user', 'phone_confirm_popup_dismissed_at')) {
            return;
        }

        Schema::table('front_user', function (Blueprint $table) {
            $table->timestamp('phone_confirm_popup_dismissed_at')->nullable()->after('phone_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('front_user', function (Blueprint $table) {
            $table->dropColumn('phone_confirm_popup_dismissed_at');
        });
    }
};
