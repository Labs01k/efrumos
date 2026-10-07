<?php

namespace App\Http\Controllers\AppApi;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Services\AppApi\AppOrderService;
use App\Services\AppApi\OrderPresenter;
use App\Services\AppApi\OrderPricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Заказы приложения. Клиента определяет приложение: заголовок X-App-Client-Id
 * (client_id лояльности) — по нему создаются и ищутся заказы. Сайт эту
 * личность не проверяет: за неё отвечает eFrumos-backend (OTP-вход).
 */
class OrderController extends Controller
{
    /** Расчёт корзины без создания заказа: цены, проблемы с наличием, доставка, итог. */
    public function quote(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'delivery_method' => ['required', Rule::in(['pickup', 'delivery'])],
            'district_id' => 'nullable|integer',
            ...$this->itemRules(),
        ]);

        if ($validator->fails()) {
            return $this->invalid($validator);
        }

        $quote = OrderPricing::quote(
            $request->input('items'),
            $request->input('delivery_method'),
            $request->filled('district_id') ? (int) $request->input('district_id') : null,
        );

        return response()->json(['success' => true, 'data' => $quote->toArray() + ['orderable' => $quote->isOrderable()]]);
    }

    public function store(Request $request, AppOrderService $service): JsonResponse
    {
        $clientId = $this->clientId($request);
        $requestId = trim((string) $request->header('Idempotency-Key'));

        if ($clientId === null) {
            return response()->json(['success' => false, 'message' => 'X-App-Client-Id header is required.'], 400);
        }

        if ($requestId === '' || strlen($requestId) > 64) {
            return response()->json(['success' => false, 'message' => 'Idempotency-Key header is required (max 64 chars).'], 400);
        }

        $validator = Validator::make($request->all(), [
            'delivery_method' => ['required', Rule::in(['pickup', 'delivery'])],
            'pay_method' => ['required', Rule::in(config('custom.front.online_payment_enabled') ? ['cash', 'card'] : ['cash'])],
            'customer.name' => 'required|string|min:2|max:30',
            'customer.last_name' => 'required|string|min:2|max:30',
            'customer.phone' => 'required|numeric|digits_between:8,15',
            'customer.email' => 'nullable|email|max:255',
            'delivery.district_id' => 'required_if:delivery_method,delivery|nullable|integer|exists:delivery_states,id',
            'delivery.city' => 'required_if:delivery_method,delivery|nullable|string|max:100',
            'delivery.address' => 'required_if:delivery_method,delivery|nullable|string|min:2|max:100',
            'pickup_shop_id' => [
                'nullable',
                'required_if:delivery_method,pickup',
                Rule::exists('shops_id', 'id')->where('pickup_point', 1),
            ],
            'comment' => 'nullable|string|max:1000',
            ...$this->itemRules(),
        ]);

        if ($validator->fails()) {
            return $this->invalid($validator);
        }

        $existing = Orders::where('app_request_id', $requestId)->first();

        if ($existing && $existing->app_client_id !== $clientId) {
            return response()->json(['success' => false, 'message' => 'Idempotency-Key was already used by another client.'], 409);
        }

        $quote = OrderPricing::quote(
            $request->input('items'),
            $request->input('delivery_method'),
            $request->filled('delivery.district_id') ? (int) $request->input('delivery.district_id') : null,
        );

        // повтор уже созданного заказа отдаём как есть, даже если цены/остатки с тех пор изменились
        if (!$existing && !$quote->isOrderable()) {
            return response()->json([
                'success' => false,
                'message' => 'Some items cannot be ordered.',
                'data' => $quote->toArray(),
            ], 422);
        }

        ['order' => $order, 'replay' => $replay] = $service->create(
            $quote,
            $request->only(['delivery_method', 'pay_method', 'customer', 'delivery', 'pickup_shop_id', 'comment']),
            $clientId,
            $requestId,
            (string) $request->ip(),
        );

        $order->refresh();

        return response()->json([
            'success' => true,
            'replay' => $replay,
            'data' => OrderPresenter::detail($order) + [
                'payment' => [
                    'method' => $order->pay_method,
                    // карта: открыть эту ссылку в браузере/WebView — страница банка;
                    // результат оплаты приложение узнаёт по payment_status заказа
                    'url' => $order->pay_method === 'card'
                        ? route('payments.bank.initiate', ['order' => $order->id, 'lang' => LANG])
                        : null,
                ],
            ],
        ], $replay ? 200 : 201);
    }

    public function index(Request $request): JsonResponse
    {
        $clientId = $this->clientId($request);

        if ($clientId === null) {
            return response()->json(['success' => false, 'message' => 'X-App-Client-Id header is required.'], 400);
        }

        $page = Orders::where('app_client_id', $clientId)
            ->where('deleted', 0)
            ->with('ordersData')
            ->orderByDesc('id')
            ->paginate(min(50, max(1, (int) $request->query('per_page', 20))));

        return response()->json([
            'success' => true,
            'data' => $page->getCollection()->map(fn ($order) => OrderPresenter::summary($order))->values(),
            'meta' => [
                'page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, int $order): JsonResponse
    {
        $clientId = $this->clientId($request);

        if ($clientId === null) {
            return response()->json(['success' => false, 'message' => 'X-App-Client-Id header is required.'], 400);
        }

        // чужой заказ и несуществующий — одинаковый 404
        $found = Orders::where('id', $order)
            ->where('app_client_id', $clientId)
            ->where('deleted', 0)
            ->first();

        if (!$found) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => OrderPresenter::detail($found)]);
    }

    private function clientId(Request $request): ?string
    {
        $id = trim((string) $request->header('X-App-Client-Id'));

        return $id !== '' && strlen($id) <= 64 ? $id : null;
    }

    private function itemRules(): array
    {
        return [
            'items' => 'required|array|min:1|max:' . OrderPricing::MAX_LINES,
            'items.*.product_id' => 'required|integer|min:1',
            'items.*.qty' => 'required|integer|min:1|max:' . OrderPricing::MAX_QTY,
        ];
    }

    private function invalid($validator): JsonResponse
    {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }
}
