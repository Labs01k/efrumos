<?php

namespace App\Services\AppApi;

use App\Jobs\Integration\SubmitOrderToIntegrationLayerJob;
use App\Models\Basket;
use App\Models\BasketId;
use App\Models\DeliveryState;
use App\Models\Orders;
use App\Models\OrdersData;
use App\Models\OrdersUsers;
use App\Services\AmoOrder\SendOrderToAmoCrm;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Создание заказа из приложения. Записывает те же строки, что сайт при
 * гостевом оформлении (basket_id + basket + orders + orders_data +
 * orders_users), поэтому CMS, письма менеджерам, Platon/Progression и
 * интеграционный слой (1С) видят его как обычный заказ; отличается только
 * orders.source = 'app' и привязка к клиенту приложения.
 *
 * НЕ делает (в отличие от сайта): не шлёт письма покупателю и менеджерам
 * (менеджерам заказ приходит через Platon/Progression), не применяет
 * промокоды/подарки (см. OrderPricing).
 */
class AppOrderService
{
    public function __construct(private readonly SendOrderToAmoCrm $amoCrm)
    {
    }

    /**
     * @param array<string, mixed> $data провалидированные поля запроса
     * @return array{order: Orders, replay: bool}
     */
    public function create(Quote $quote, array $data, string $clientId, string $requestId, string $ip): array
    {
        $existing = Orders::where('app_request_id', $requestId)->first();

        if ($existing) {
            return ['order' => $existing, 'replay' => true];
        }

        try {
            $order = DB::transaction(fn () => $this->store($quote, $data, $clientId, $requestId, $ip));
        } catch (QueryException $e) {
            // два одновременных запроса с одним Idempotency-Key: второй упирается в unique
            $existing = Orders::where('app_request_id', $requestId)->first();

            if ($existing) {
                return ['order' => $existing, 'replay' => true];
            }

            throw $e;
        }

        $this->afterCreate($order);

        return ['order' => $order, 'replay' => false];
    }

    private function store(Quote $quote, array $data, string $clientId, string $requestId, string $ip): Orders
    {
        $basketId = BasketId::create(['user_ip' => $ip]);

        foreach ($quote->lines as $line) {
            Basket::create([
                'basket_id' => $basketId->id,
                'goods_item_id' => $line->goods->id,
                'items_count' => $line->qty,
                'goods_name' => $line->goods->itemByLang->name,
                'goods_price' => $line->unitPrice,
                'goods_one_c_code' => $line->goods->one_c_code,
            ]);
        }

        $order = new Orders();
        $order->basket_id = $basketId->id;
        $order->delivery_method = $data['delivery_method'];
        if ($data['delivery_method'] === 'pickup') {
            $order->pickup_shop_id = (int) $data['pickup_shop_id'] ?: null;
        }
        $order->pay_method = $data['pay_method'];
        $order->active = 1;
        $order->deleted = 0;
        $order->source = 'app';
        $order->app_client_id = $clientId;
        $order->app_request_id = $requestId;
        $order->save();

        $ordersData = new OrdersData();
        $ordersData->orders_id = $order->id;
        $ordersData->total_price = $quote->itemsTotal - $quote->discount;
        $ordersData->total_count = $quote->totalQty();
        $ordersData->delivery_cost = $quote->delivery;
        $ordersData->total_discount = $quote->discount;
        $ordersData->save();

        $customer = $data['customer'];
        $delivery = $data['delivery'] ?? [];

        $ordersUsers = new OrdersUsers();
        $ordersUsers->orders_id = $order->id;
        $ordersUsers->user_ip = $ip;
        $ordersUsers->name = $customer['name'];
        $ordersUsers->last_name = $customer['last_name'];
        $ordersUsers->email = $customer['email'] ?? null;
        $ordersUsers->phone = $customer['phone'];
        if ($data['delivery_method'] === 'delivery') {
            $ordersUsers->district_id = $delivery['district_id'] ?? null;
            $ordersUsers->city = $delivery['city'] ?? null;
            $ordersUsers->address = $delivery['address'] ?? null;
        }
        $ordersUsers->descr = $data['comment'] ?? null;
        $ordersUsers->save();

        return $order;
    }

    /**
     * Внешние системы. Заказ к этому моменту уже сохранён, поэтому сбой не
     * должен превращаться в ошибку для покупателя: 1С добирается повтором и
     * ежедневной проверкой рассинхронизации (integration:check-desync).
     */
    private function afterCreate(Orders $order): void
    {
        $ordersData = $order->ordersData;
        $ordersUsers = $order->ordersUsers;
        $district = $ordersUsers->district_id ? DeliveryState::find($ordersUsers->district_id) : null;

        try {
            $this->amoCrm->sendOrderToAmoCrm($order, $ordersData, $ordersUsers, $ordersUsers, $district);
        } catch (\Throwable $e) {
            Log::error('AppApi: заказ не ушёл в Platon/Progression', ['orders_id' => $order->id, 'error' => $e->getMessage()]);
        }

        try {
            SubmitOrderToIntegrationLayerJob::dispatch($order->id);
        } catch (\Throwable $e) {
            Log::error('AppApi: заказ не поставлен в очередь интеграции с 1С', ['orders_id' => $order->id, 'error' => $e->getMessage()]);
        }
    }
}
