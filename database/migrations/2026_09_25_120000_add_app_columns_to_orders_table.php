<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * API для приложения лояльности (eFrumos-backend), см.
 * efrumos-docs/app-api-contract.md.
 *
 *  - source          — откуда заказ: 'site' (по умолчанию) или 'app';
 *  - app_client_id   — идентификатор клиента приложения (client_id лояльности,
 *                      GUID), по нему приложение получает список своих заказов.
 *                      Профиль сайта (front_user) с ним пока не связан — это
 *                      отдельное решение по единому входу (этап 6 брифа);
 *  - app_request_id  — ключ идемпотентности от приложения (Idempotency-Key):
 *                      повтор запроса создания заказа из-за обрыва связи
 *                      возвращает уже созданный заказ, а не второй.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'source')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->string('source', 16)->default('site')->after('fast_order');
            $table->string('app_client_id', 64)->nullable()->after('source')->index();
            $table->string('app_request_id', 64)->nullable()->after('app_client_id')->unique();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['app_request_id']);
            $table->dropIndex(['app_client_id']);
            $table->dropColumn(['source', 'app_client_id', 'app_request_id']);
        });
    }
};
