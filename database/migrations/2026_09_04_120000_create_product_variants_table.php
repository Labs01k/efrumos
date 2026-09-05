<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Epic 6 — «Отдача оттенков как вариантов товара» (ТЗ §6.2). Не
        // мигрирует существующие товары-оттенки в новую модель «родитель +
        // варианты» — это отдельный, гораздо более рискованный шаг (SEO при
        // схлопывании ~826 URL, раунд 2 №4, без ответа заказчика), которым
        // здесь не занимаемся. Это аддитивный кеш поверх уже существующих
        // отдельных товаров: RebuildProductVariants (см. её докблок)
        // перестраивает его при каждой синхронизации с 1С, группируя
        // существующие товары по линии (goods_item_id.brand_id — та же
        // иерархия, что уже используется в ProductRecommendations/ShadePalette).
        // Идемпотентность: деплой применяет миграции и патчи database/sql
        // на стендах с разной историей, повторный прогон не должен падать.
        if (Schema::hasTable('product_variants')) {
            return;
        }

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('line_brand_id');
            $table->unsignedInteger('goods_item_id')->unique();
            // «код оттенка» — уникальный идентификатор (артикул из 1С,
            // например «DLS 9/76»); «номер оттенка» — тон/номер, разобранный
            // из названия (например «9/76») — повторяется между линиями.
            // ТЗ использует оба термина, но нигде явно не разводит их —
            // это наша рабочая интерпретация, см. open-decisions.md.
            $table->string('shade_code', 64)->nullable();
            $table->string('shade_number', 32)->nullable();
            $table->string('shade_name', 255)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedInteger('products_count')->default(0);
            $table->boolean('in_stoc')->default(false);
            $table->timestamps();

            $table->index(['line_brand_id']);
            $table->index(['shade_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
