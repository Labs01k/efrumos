<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Epic 3 — «часто покупают вместе» (см. ТЗ.md п.3.2): фоновый
        // ежесуточный пересчёт по оплаченным заказам за 12 месяцев,
        // результат кешируется здесь. RecalculateFrequentlyBoughtTogether
        // truncates + refills this table on every run — it's a derived
        // cache, not a source of truth.
        // Идемпотентность: деплой применяет миграции и патчи database/sql
        // на стендах с разной историей, повторный прогон не должен падать.
        if (Schema::hasTable('goods_frequently_bought_with')) {
            return;
        }

        Schema::create('goods_frequently_bought_with', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('goods_item_id');
            $table->unsignedInteger('related_goods_item_id');
            $table->unsignedInteger('pair_count');
            $table->timestamps();

            $table->index(['goods_item_id', 'pair_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_frequently_bought_with');
    }
};
