<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * EFA-43 / E1-BES-05 — «режим входа сайта для связки из приложения»: после
 * логина (email/Google/Facebook) на этой странице сайт выдаёт приложению
 * одноразовое подтверждение, что ИМЕННО ЭТОТ front_user только что вошёл.
 * Сам токен — непрозрачный для приложения; погашает его бэкенд приложения
 * через /api/app/v1/account-link/redeem (EFA-35, тот же защищённый канал
 * E0-BE-04). Короткоживущий и одноразовый — как OTP, но это не OTP (здесь
 * нет кода, который вводит пользователь, только серверный redirect).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('account_link_tokens')) {
            return;
        }

        Schema::create('account_link_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->unsignedInteger('front_user_id');
            $table->string('app_state', 255)->nullable(); // непрозрачная строка от приложения, просто возвращается ему обратно
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index('front_user_id');
            $table->foreign('front_user_id')->references('id')->on('front_user')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_link_tokens');
    }
};
