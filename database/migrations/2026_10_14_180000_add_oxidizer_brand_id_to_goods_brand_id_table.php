<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Правка от 14.10.2026, раздел 3 тикета — подбор окислителя/активатора.
 *
 * Паттерн «краска → дочерний бренд с окислителями» держится не всегда
 * (проверено вживую на 7 линейках из тикета: у De Luxe/Princess Essex
 * действительно есть дочерний бренд «Оксиданты и Активаторы», а у DEMIRA
 * Professional/jNOWA Professional/Elea Max Size/Artisto Color окислитель
 * лежит в совсем другом, не связанном по иерархии бренде). Жёстко кодировать
 * это по brand_id нельзя — при следующей пересортировке 1С снова сломается.
 *
 * Вместо этого — обычное поле в CMS, админ сам указывает бренд-источник
 * окислителей для линейки один раз, дальше не требует правок кода.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_brand_id', function (Blueprint $table) {
            $table->unsignedInteger('oxidizer_brand_id')->nullable()->after('p_id');
        });
    }

    public function down(): void
    {
        Schema::table('goods_brand_id', function (Blueprint $table) {
            $table->dropColumn('oxidizer_brand_id');
        });
    }
};
