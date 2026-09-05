<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Схема этого проекта исторически живёт в дампе и в патчах
        // database/sql, а не в миграциях: дефолтные ларавелевские миграции
        // никогда не прогонялись, и таблица уже существует на всех стендах.
        // Без этой проверки первый же `artisan migrate` падает на ней и не
        // доходит до миграций оплаты и рекомендаций.
        if (Schema::hasTable('password_resets')) {
            return;
        }

        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('password_resets');
    }
};
