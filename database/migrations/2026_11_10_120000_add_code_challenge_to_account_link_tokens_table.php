<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * EFA-43/35 — PKCE (RFC 8252): `efrumosapp://` можно перехватить другим
 * приложением на Android, одноразовости и TTL токена недостаточно
 * (перехватчик использует токен раньше владельца). Приложение само
 * генерирует code_verifier, на шаге логина передаёт его хэш — сайт хранит
 * его здесь рядом с токеном и сверяет на redeem().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_link_tokens', function (Blueprint $table) {
            if (!Schema::hasColumn('account_link_tokens', 'code_challenge')) {
                $table->string('code_challenge', 64)->nullable()->after('app_state');
            }
        });
    }

    public function down(): void
    {
        Schema::table('account_link_tokens', function (Blueprint $table) {
            $table->dropColumn(['code_challenge']);
        });
    }
};
