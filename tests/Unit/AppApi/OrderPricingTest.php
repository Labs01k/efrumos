<?php

namespace Tests\Unit\AppApi;

use App\Services\AppApi\OrderPricing;
use App\Services\AppApi\Quote;
use Tests\TestCase;

/**
 * Правила доставки API приложения должны совпадать с сайтом
 * (Front\OrderController::ajaxNewOrder): самовывоз и заказ от порога — 0,
 * Кишинёв (код CU или район не найден) — тариф Кишинёва, остальные — по стране.
 */
class OrderPricingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'custom.front.until_free_delivery' => 600,
            'custom.front.delivery_price_chisinau' => 50,
            'custom.front.delivery_price_moldova' => 100,
        ]);
    }

    public function test_pickup_is_always_free(): void
    {
        $this->assertSame(0, OrderPricing::deliveryCost(100, 'pickup', 'CU'));
        $this->assertSame(0, OrderPricing::deliveryCost(100, 'pickup', null));
    }

    public function test_order_at_or_above_threshold_is_free(): void
    {
        $this->assertSame(0, OrderPricing::deliveryCost(600, 'delivery', 'CU'));
        $this->assertSame(0, OrderPricing::deliveryCost(1500, 'delivery', 'BL'));
    }

    public function test_below_threshold_chisinau_tariff(): void
    {
        $this->assertSame(50, OrderPricing::deliveryCost(599, 'delivery', 'CU'));
        // район не найден в справочнике — как на сайте, тариф Кишинёва
        $this->assertSame(50, OrderPricing::deliveryCost(599, 'delivery', null));
    }

    public function test_below_threshold_rest_of_country_tariff(): void
    {
        $this->assertSame(100, OrderPricing::deliveryCost(599, 'delivery', 'BL'));
    }

    public function test_totals_sum_items_minus_discount_plus_delivery(): void
    {
        $this->assertSame(
            ['items_total' => 550, 'discount' => 0, 'delivery' => 50, 'total_due' => 600, 'currency' => 'MDL'],
            Quote::totals(550, 0, 50),
        );
    }
}
