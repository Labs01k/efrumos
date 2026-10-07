<?php

namespace App\Services\AppApi;

use App\Models\DeliveryState;
use App\Models\GoodsItemId;

/**
 * Расчёт заказа для приложения: цены строк и стоимость доставки по тем же
 * правилам, что в Front\OrderController::ajaxNewOrder (сайт не трогаем —
 * логика продублирована сознательно, чтобы не менять живой чекаут).
 *
 * НЕ реализовано в v1 (на сайте это считает корзина при добавлении товара):
 * промокоды и скидки по акциям (basket.promo_one_c_id / discount_*),
 * подарки (cadou). Строки считаются по цене товара с учётом его промо-цены;
 * скидка заказа всегда 0.
 *
 * Стоимость доставки — тариф сайта (config custom.front.delivery_price_*).
 * По ответу 1С (25.09.2026) итоговую сумму с доставкой будет отдавать 1С в
 * AdditionalInfo ответа CreateOrder — после этого сумма здесь заменяется.
 */
final class OrderPricing
{
    public const MAX_LINES = 50;
    public const MAX_QTY = 99;

    /**
     * @param array<int, array{product_id: int, qty: int}> $items
     */
    public static function quote(array $items, string $deliveryMethod, ?int $districtId = null): Quote
    {
        // одинаковые товары в запросе складываем в одну строку
        $wanted = [];
        foreach ($items as $item) {
            $id = (int) $item['product_id'];
            $wanted[$id] = ($wanted[$id] ?? 0) + (int) $item['qty'];
        }

        $goods = GoodsItemId::whereIn('id', array_keys($wanted))
            ->where('active', 1)
            ->where('deleted', 0)
            ->has('itemByLang')
            ->with('itemByLang', 'oImage')
            ->get()
            ->keyBy('id');

        $lines = [];
        $problems = [];
        $itemsTotal = 0;

        foreach ($wanted as $id => $qty) {
            $one = $goods->get($id);
            $price = $one ? getGoodsPrice($one) : null;

            if (!$one || !$price) {
                $problems[] = ['product_id' => $id, 'reason' => 'not_found'];
                continue;
            }

            if ((int) $one->in_stoc !== 1) {
                $problems[] = ['product_id' => $id, 'reason' => 'out_of_stock'];
                continue;
            }

            // 1С проверяет заказ по остатку основного склада (products_count) —
            // заказ больше остатка сайт создал бы, а 1С отклонила
            if ($qty > (int) $one->products_count) {
                $problems[] = ['product_id' => $id, 'reason' => 'insufficient_stock', 'available' => (int) $one->products_count];
                continue;
            }

            $unit = (int) $price->price;
            $lines[] = new QuoteLine($one, $qty, $unit);
            $itemsTotal += $unit * $qty;
        }

        $districtCode = $districtId
            ? DeliveryState::where('active', 1)->where('id', $districtId)->value('code')
            : null;

        $delivery = self::deliveryCost($itemsTotal, $deliveryMethod, $districtCode);

        return new Quote($lines, $problems, $itemsTotal, 0, $delivery);
    }

    /**
     * Самовывоз и заказ от порога бесплатной доставки — 0; иначе Кишинёв
     * (код района CU или район не найден) — тариф Кишинёва, остальные — по стране.
     */
    public static function deliveryCost(int $itemsTotalAfterDiscount, string $deliveryMethod, ?string $districtCode): int
    {
        if ($deliveryMethod === 'pickup' || $itemsTotalAfterDiscount >= (int) config('custom.front.until_free_delivery')) {
            return 0;
        }

        return $districtCode !== null && $districtCode !== 'CU'
            ? (int) config('custom.front.delivery_price_moldova')
            : (int) config('custom.front.delivery_price_chisinau');
    }
}
