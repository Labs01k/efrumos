<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [BE] Сумма к оплате от 1С — AdditionalInfo в ответе CreateOrder, живьём
 * подтверждено 2026-10-01: {"orderSum":825,"deliverySum":0,"total":825}.
 * Пока поле не читалось (SoapOneCOrderGateway::reserveOrder()).
 *
 * onec_delivery_sum — nullable отдельно от «не пришло»: 1С сама шлёт
 * deliverySum:null, когда доставку обслуживает сторонняя курьерская служба
 * и её стоимость неизвестна заранее (клиент платит курьеру сам, не онлайн).
 * onec_total_sum — то, что реально списывать с покупателя: 1С уже вычла
 * неизвестную доставку из total в этом случае, пересчитывать не нужно.
 *
 * Эти колонки пустые для заказов, по которым CreateOrder не вызывался
 * (оплата при получении, Приднестровье) — для них сайт считает сумму сам,
 * как и раньше (total_price + delivery_cost).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders_data', 'onec_total_sum')) {
            return;
        }

        Schema::table('orders_data', function (Blueprint $table) {
            $table->decimal('onec_order_sum', 10, 2)->nullable()->after('delivery_cost');
            $table->decimal('onec_delivery_sum', 10, 2)->nullable()->after('onec_order_sum');
            $table->decimal('onec_total_sum', 10, 2)->nullable()->after('onec_delivery_sum');
        });
    }

    public function down(): void
    {
        Schema::table('orders_data', function (Blueprint $table) {
            $table->dropColumn(['onec_order_sum', 'onec_delivery_sum', 'onec_total_sum']);
        });
    }
};
