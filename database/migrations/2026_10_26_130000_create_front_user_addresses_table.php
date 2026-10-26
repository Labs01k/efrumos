<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * E6-BES-02 — сохранённые адреса доставки. Раньше адреса жили только
 * внутри каждого заказа (OrdersUsers), отдельной сущности не было вообще —
 * покупатель вводил адрес заново на каждый заказ. Поля повторяют те, что
 * уже есть в OrdersUsers (district_id/city/city_area/address/apartment/
 * zip_code), чтобы данные из сохранённого адреса ложились в заказ без
 * переименования полей.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('front_user_addresses')) {
            return;
        }

        Schema::create('front_user_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('front_user_id');
            $table->string('label', 100)->nullable(); // «Дом», «Работа» — для себя, не для 1С/доставки
            $table->unsignedInteger('district_id')->nullable();
            $table->string('city', 191)->nullable();
            $table->string('city_area', 191)->nullable();
            $table->string('address', 255);
            $table->string('apartment', 50)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('front_user_id');
            $table->foreign('front_user_id')->references('id')->on('front_user')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('front_user_addresses');
    }
};
