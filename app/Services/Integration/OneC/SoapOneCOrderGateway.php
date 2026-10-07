<?php

namespace App\Services\Integration\OneC;

use App\Contracts\Integration\OneCOrderGateway;
use App\Contracts\Integration\OneCOrderResult;
use App\Exceptions\Integration\IntegrationGatewayException;
use App\Models\FrontUser;
use App\Models\GoodsItemId;
use App\Models\Orders;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * checkStock() reads GoodsItemId.products_count — real stock figures
 * sourced from 1С, kept fresh by the existing catalog exchange
 * (App\Http\Controllers\Exchange\ImportFrom1C, GetSKU 'Rests' per
 * config('custom.main_store_rest_id'), same field the legacy sync writes).
 * It intentionally does NOT call 1С live per order — see git history for
 * the earlier GetSKU(FullExchange)/GetSKUArray timing/shape findings on
 * ws_ef.1cws. A live per-SKU alternative now exists on ws_amo.1cws's own
 * GetSKU (fast, ~0.3-0.7s, confirmed 2026-09-09) but switching to it is a
 * separate decision (freshness vs. an extra live round trip per checkout),
 * not made here.
 *
 * reserveOrder()/markPaid() call the real ws_amo.1cws WSDL — the same
 * service amoCRM/Bitrix24 already use, adapted (per 1С's 2026-09-09 email)
 * to accept orders straight from the site via CreateOrder's isWebSite flag.
 * Two of its inputs (Owner, City) are reasoned best guesses, not confirmed
 * facts — see config('services.onec') comments and
 * efrumos-docs/open-decisions.md п.7 — logged loudly wherever a default is
 * used so a wrong guess is easy to spot and fix later without a code change.
 *
 * releaseReservation() has no real operation on this WSDL either (nothing
 * resembling "cancel/release" in CreateOrder/CreateClientPoint/CreatePayment) —
 * stays mocked regardless of ONEC_MOCK_MODE, same reasoning as before.
 */
class SoapOneCOrderGateway implements OneCOrderGateway
{
    public function __construct(private readonly OneCCityMapper $cityMapper)
    {
    }

    public function checkStock(array $skuCodes): array
    {
        if ($skuCodes === []) {
            return [];
        }

        if (config('services.integration.onec_mock_mode')) {
            Log::warning('[1С MOCK] checkStock — ONEC_MOCK_MODE is on, ignoring real stock, reporting all requested SKUs as available', [
                'skus' => $skuCodes,
            ]);

            return array_fill_keys($skuCodes, PHP_INT_MAX);
        }

        $stockByCode = GoodsItemId::whereIn('one_c_code', $skuCodes)
            ->pluck('products_count', 'one_c_code');

        $result = [];
        foreach ($skuCodes as $code) {
            $result[$code] = (int) ($stockByCode[$code] ?? 0);
        }

        return $result;
    }

    public function reserveOrder(Orders $order): OneCOrderResult
    {
        if (config('services.integration.onec_mock_mode')) {
            $fakeId = '1C-MOCK-' . $order->id . '-' . strtoupper(Str::random(6));

            Log::info('[1С MOCK] reserveOrder — ONEC_MOCK_MODE is on, minted a placeholder document id', [
                'orders_id' => $order->id,
                'onec_document_id' => $fakeId,
            ]);

            return new OneCOrderResult($fakeId);
        }

        try {
            $client = $this->client();
            $clientPointCode = $this->resolveClientPointCode($client, $order);

            $goods = [];
            foreach ($order->basket as $item) {
                if (!$item->goods_one_c_code) {
                    continue; // no 1С SKU on this line — nothing to report to 1С
                }
                $goods[] = [
                    'SKU' => (int) $item->goods_one_c_code,
                    'SKUName' => (string) $item->goods_name,
                    'QTYOrder' => (float) $item->items_count,
                    'Price' => (float) $item->goods_price,
                ];
            }

            $response = $client->CreateOrder([
                'OrderData' => [
                    'ClientPointCode' => $clientPointCode,
                    'ShippingDate' => gmdate('Y-m-d\TH:i:s'),
                    'Goods' => ['items' => $goods],
                    'Comment' => 'efrumos.md order #' . $order->id,
                    'AgentId' => (string) config('services.onec.agent_id'),
                    'isCurierat' => $order->delivery_method === 'delivery',
                    'PromoCode' => $this->promoCode($order),
                    'isWebSite' => true,
                    // Необязательное поле (ответ 1С, 2026-10-01): сайт сюда
                    // попадает только для оплаты картой в момент оформления
                    // (гейт pay_method==='card' в SubmitOrderToIntegrationLayerJob),
                    // отсрочки оплаты у нас нет и не планируется — значение
                    // всегда одно и то же, менять не на что.
                    'PaymentType' => 'ПриСозданииЗаказа',
                ],
            ]);

            $result = self::toArray($response->return);

            if (!empty($result['isError'])) {
                throw new IntegrationGatewayException(
                    "1С CreateOrder вернул ошибку для заказа #{$order->id}: " . ($result['ErrorDescription'] ?? 'без описания')
                );
            }

            Log::info('1С CreateOrder: заказ создан', [
                'orders_id' => $order->id,
                'onec_document_id' => $result['ObjectId'] ?? null,
                'onec_object_code' => $result['ObjectCode'] ?? null,
            ]);

            [$orderSum, $deliverySum, $total] = self::parseAdditionalInfo($result['AdditionalInfo'] ?? null, $order->id);

            // ObjectCode — номер заказа в 1С; вебхук статуса шлёт его как xml_id.
            return new OneCOrderResult(
                (string) $result['ObjectId'],
                isset($result['ObjectCode']) ? (int) $result['ObjectCode'] : null,
                $orderSum,
                $deliverySum,
                $total,
            );
        } catch (IntegrationGatewayException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new IntegrationGatewayException(
                "1С reserveOrder упал для заказа #{$order->id}: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    public function releaseReservation(string $onecDocumentId): void
    {
        // Ни в ws_ef.1cws, ни в ws_amo.1cws нет операции отмены/освобождения
        // резерва — письмо 1С от 2026-09-09 описывает только CreateClientPoint/
        // CreateOrder/CreatePayment. Остаётся мок независимо от
        // ONEC_MOCK_MODE, пока 1С не пришлёт такую операцию.
        Log::info('[1С MOCK] releaseReservation — операции отмены нет в WSDL ни на одном известном эндпоинте', [
            'onec_document_id' => $onecDocumentId,
        ]);
    }

    public function markPaid(string $onecDocumentId, float $amount, string $paymentId): void
    {
        if (config('services.integration.onec_mock_mode')) {
            Log::info('[1С MOCK] markPaid — ONEC_MOCK_MODE is on', [
                'onec_document_id' => $onecDocumentId,
                'amount' => $amount,
                'payment_id' => $paymentId,
            ]);

            return;
        }

        try {
            $client = $this->client();

            $response = $client->CreatePayment([
                'OrderId' => $onecDocumentId,
                'PaymentAmount' => $amount,
                'PaymentDate' => gmdate('Y-m-d\TH:i:s'),
                'PaymentID' => $paymentId,
            ]);

            $result = self::toArray($response->return);

            if (!empty($result['isError'])) {
                throw new IntegrationGatewayException(
                    "1С CreatePayment вернул ошибку для документа {$onecDocumentId}: " . ($result['ErrorDescription'] ?? 'без описания')
                );
            }

            Log::info('1С CreatePayment: оплата записана', [
                'onec_document_id' => $onecDocumentId,
                'onec_payment_object_id' => $result['ObjectId'] ?? null,
            ]);
        } catch (IntegrationGatewayException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new IntegrationGatewayException(
                "1С markPaid упал для документа {$onecDocumentId}: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * Зарегистрированный покупатель — код точки продаж кэшируется на
     * FrontUser (повторные заказы переиспользуют её). Гость — новая точка
     * продаж на каждый заказ: у гостя нет постоянного идентификатора, на
     * который можно было бы это закэшировать, как и в остальной части сайта.
     */
    private function resolveClientPointCode(\SoapClient $client, Orders $order): int
    {
        $frontUser = $order->front_user_id ? FrontUser::find($order->front_user_id) : null;

        if ($frontUser && $frontUser->onec_client_point_code) {
            return (int) $frontUser->onec_client_point_code;
        }

        $ordersUsers = $order->ordersUsers;
        $fullName = trim(($ordersUsers->name ?? '') . ' ' . ($ordersUsers->last_name ?? '')) ?: 'efrumos.md customer';
        $cityCode = $this->cityMapper->cityCodeFor($ordersUsers->district_id ? (int) $ordersUsers->district_id : null);

        // Самовывоз — у ordersUsers обычно нет адреса (клиент забирает сам),
        // 1С требует непустой Adress ("Не заполнен адрес торговой точки" —
        // живьём проверено). Берём адрес выбранного магазина самовывоза.
        $address = $ordersUsers->address ?: '';
        if ($address === '' && $order->delivery_method !== 'delivery' && $order->pickupShop?->itemByLang) {
            $address = trim(($order->pickupShop->itemByLang->name ?? '') . ', ' . ($order->pickupShop->itemByLang->address ?? ''), ', ');
        }

        // Owner зависит от способа доставки — см. комментарий у
        // config('services.onec') про owner_code_*. Ветка nova_courier/
        // nova_terminal — не в задачах текущего этапа (не наша разработка,
        // дремлющий код из первого коммита); валидация в OrderController
        // сейчас не пускает эти значения дальше формы, пока не включат флаг
        // custom.front.nova_poshta_enabled — эта ветка match() практически
        // не выполняется.
        $ownerCode = match ($order->delivery_method) {
            'delivery' => config('services.onec.owner_code_delivery'),
            'nova_courier', 'nova_terminal' => config('services.onec.owner_code_courier_service'),
            default => config('services.onec.owner_code_pickup'), // pickup и всё неизвестное
        };

        $response = $client->CreateClientPoint([
            'ClientPointInData' => [
                'Description' => $fullName,
                'Owner' => (int) $ownerCode,
                'City' => $cityCode,
                'Adress' => $address,
                'OrderRoute' => (int) config('services.onec.order_route'),
                'FullDescription' => 'efrumos.md, заказ #' . $order->id,
                'Contacts' => [
                    'items' => [[
                        'PhoneNumber' => (string) ($ordersUsers->phone ?? ''),
                        'ContactPerson' => $fullName,
                    ]],
                ],
                'ClientPointCode' => 0, // при создании — 0, per письмо 1С
            ],
        ]);

        $result = self::toArray($response->return);

        if (!empty($result['isError'])) {
            throw new IntegrationGatewayException(
                "1С CreateClientPoint вернул ошибку для заказа #{$order->id}: " . ($result['ErrorDescription'] ?? 'без описания')
            );
        }

        $clientPointCode = (int) $result['ObjectCode'];

        if ($frontUser) {
            $frontUser->update(['onec_client_point_code' => $clientPointCode]);
        }

        return $clientPointCode;
    }

    private function promoCode(Orders $order): string
    {
        $promoId = $order->basket->first(fn ($item) => $item->promo_one_c_id > 0)?->promo_one_c_id;

        return $promoId
            ? (string) (\App\Models\GoodsPromo::where('id', $promoId)->value('promocod') ?? '')
            : '';
    }

    private function client(): \SoapClient
    {
        $wsdl = config('services.onec.order_wsdl_url');

        if (!$wsdl) {
            throw new IntegrationGatewayException('1С order WSDL URL не настроен (services.onec.order_wsdl_url) — см. SOAP_1C_ORDER_API_LIVE_URL для прода.');
        }

        return new \SoapClient($wsdl, [
            'trace' => true,
            'connection_timeout' => 30,
            'cache_wsdl' => WSDL_CACHE_NONE,
        ]);
    }

    private static function toArray(mixed $value): array
    {
        return json_decode(json_encode($value), true) ?: [];
    }

    /**
     * AdditionalInfo — JSON-строка внутри ответа CreateOrder (не вложенный
     * объект), например '{"orderSum":825, "deliverySum":0, "total": 825}'.
     * Живьём подтверждено 2026-10-01. Поля нет вовсе на старых ответах/моке
     * — тогда все три null, и сумму к оплате считает сайт
     * (OrdersData::amountDue()). Падать из-за кривого AdditionalInfo нельзя:
     * заказ в 1С уже создан, терять его из-за необязательного поля — хуже,
     * чем остаться без суммы от 1С на этот раз.
     *
     * @return array{0: ?float, 1: ?float, 2: ?float} orderSum, deliverySum, total
     */
    private static function parseAdditionalInfo(?string $raw, int $ordersId): array
    {
        if (!$raw) {
            return [null, null, null];
        }

        $info = json_decode($raw, true);

        if (!is_array($info)) {
            // 1С генерирует JSON/XML с литералами в Pascal-case (сами видели
            // isWebSite:True в SOAP-запросе, deliverySum:Null в их же
            // примере) — невалидно для json_decode, который требует
            // строчные true/false/null. Та же защита, что и в ImportFrom1C
            // для их битого JSON каталога: один аварийный проход вместо
            // падения на необязательном поле.
            $normalized = preg_replace_callback('/\b(True|False|Null)\b/', fn ($m) => strtolower($m[1]), $raw);
            $info = json_decode($normalized, true);
        }

        if (!is_array($info) || !array_key_exists('total', $info)) {
            Log::warning('1С CreateOrder: AdditionalInfo не разобрался', [
                'orders_id' => $ordersId,
                'raw' => $raw,
            ]);

            return [null, null, null];
        }

        return [
            isset($info['orderSum']) ? (float) $info['orderSum'] : null,
            // deliverySum: null в самом JSON — валидное значение ("доставку
            // обслуживает сторонняя служба, стоимость неизвестна"), не ошибка
            array_key_exists('deliverySum', $info) && $info['deliverySum'] !== null
                ? (float) $info['deliverySum']
                : null,
            (float) $info['total'],
        ];
    }
}
