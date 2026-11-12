<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SoapOneCOrderGateway::resolveClientPointCode() кэширует ClientPointCode
 * на зарегистрированном покупателе, но ничего не знает, для какого Owner
 * (доставка/самовывоз/курьерская служба) этот код был создан — покупатель,
 * заказавший сначала с доставкой, а потом самовывозом, получил бы точку
 * продаж с чужим Owner. Нужна рядом с самим кодом.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('front_user', function (Blueprint $table) {
            if (!Schema::hasColumn('front_user', 'onec_client_point_owner')) {
                $table->unsignedInteger('onec_client_point_owner')->nullable()->after('onec_client_point_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('front_user', function (Blueprint $table) {
            $table->dropColumn(['onec_client_point_owner']);
        });
    }
};
