<?php

namespace App\Services\AppApi;

use App\Models\Orders;
use App\Services\Product\ShadePalette;

/** Заказ в ответах API приложения. */
final class OrderPresenter
{
    public static function summary(Orders $order): array
    {
        $data = $order->ordersData;

        return [
            'id' => $order->id,
            'created_at' => $order->created_at?->toIso8601String(),
            'delivery_method' => $order->delivery_method,
            'pay_method' => $order->pay_method,
            // pending | paid | failed | cancelled — решает серверный callback банка
            'payment_status' => $order->payment_status?->value,
            // статус заказа в 1С как пришёл вебхуком (значения OrderStatuses:
            // «Ожидание», «НовыйОплачен», «Расфасована», «ВПути», …); null — 1С ещё
            // не прислала (или заказ в 1С не передавался — оплата при получении)
            'onec_status' => $order->onec_order_status,
            'onec_status_at' => $order->onec_order_status_synced_at?->toIso8601String(),
            'totals' => Quote::totals(
                (int) $data->total_price + (int) $data->total_discount,
                (int) $data->total_discount,
                (int) $data->delivery_cost,
            ),
        ];
    }

    public static function detail(Orders $order): array
    {
        $order->loadMissing('basket.goodsItemId.itemByLang', 'basket.goodsItemId.oImage', 'ordersUsers');

        $users = $order->ordersUsers;

        return self::summary($order) + [
            'items' => $order->basket->map(fn ($line) => [
                'product_id' => $line->goods_item_id,
                'name' => $line->goods_name,
                'image' => ShadePalette::cardImageUrl($line->goodsItemId),
                'qty' => (int) $line->items_count,
                'unit_price' => (int) $line->goods_price,
                'line_total' => (int) $line->goods_price * (int) $line->items_count,
            ])->values()->all(),
            'customer' => [
                'name' => $users->name,
                'last_name' => $users->last_name,
                'phone' => $users->phone,
                'email' => $users->email,
            ],
            'delivery' => $order->delivery_method === 'delivery'
                ? ['district_id' => $users->district_id, 'city' => $users->city, 'address' => $users->address]
                : ['pickup_shop_id' => $order->pickup_shop_id],
            'comment' => $users->descr,
        ];
    }
}
