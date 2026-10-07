<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Jobs\AppApi\NotifyAppBackendOrderStatusJob;
use App\Models\IntegrationIdMapping;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * [BE] Синхронное обновление статуса после оплаты, вариант «в» из
 * efrumos-docs/call-with-sk.md A3 — вебхук статуса заказа от 1С.
 *
 * Контракт — тот, что 1С сама шлёт в amoCRM (ответ 1С, 2026-09-25), они
 * просили сохранить его как есть:
 *   {"xml_id": "104943", "status": "ВПути", "contact": 55399,
 *    "sum": 1105.01, "sumfact": 1105.01, "token": "...", "account_id": 29770027}
 *   xml_id  — номер заказа в 1С (ObjectCode из ответа CreateOrder, не GUID);
 *   status  — одно из OrderStatuses 1С (Ожидание, НовыйОплачен, Подтверждена,
 *             Расфасована, ВПути, Доставлен, СделкаЗавершена, Аннулирована и
 *             др.), храним строкой как есть, своего словаря не заводим;
 *   token   — наш секрет (services.onec.status_webhook_token): 1С подставит
 *             в это поле значение, которое мы выдадим. Принимаем также
 *             ?token= в URL и заголовок X-OneC-Webhook-Token;
 *   contact, sum, sumfact, account_id — служебные, не разбираем.
 * Даты статуса 1С не передаёт — фиксируем момент получения. Ответ 1С не
 * анализирует, повторов нет.
 *
 * Как и VictoriaBankController::callback() — всегда отвечаем 200; результат
 * виден в логе, сырое тело (без токена) пишем всегда.
 */
class OneCOrderStatusWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('OneCOrderStatusWebhookController: получен вебхук статуса от 1С', [
            // токен — секрет, в лог не пишем
            'payload' => Arr::except($payload, ['token']),
            'ip' => $request->ip(),
        ]);

        if (!$this->tokenValid($request)) {
            Log::warning('OneCOrderStatusWebhookController: неверный или отсутствующий токен', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['status' => 'ignored']);
        }

        $number = $payload['xml_id'] ?? null;
        $status = $payload['status'] ?? null;

        $mapping = is_numeric($number)
            ? IntegrationIdMapping::where('onec_order_number', (int) $number)->first()
            : null;
        $order = $mapping?->order;

        if (!$order || !is_string($status) || $status === '') {
            Log::warning('OneCOrderStatusWebhookController: не удалось сопоставить заказ или статус — см. payload в записи выше', [
                'xml_id' => $number,
                'status_found' => is_string($status) && $status !== '',
                'order_found' => $order !== null,
            ]);

            return response()->json(['status' => 'ignored']);
        }

        $order->update([
            'onec_order_status' => $status,
            'onec_order_status_synced_at' => now(),
        ]);

        // заказ из приложения — сообщаем eFrumos-backend (push по статусу)
        NotifyAppBackendOrderStatusJob::dispatchFor($order, 'onec');

        Log::info('OneCOrderStatusWebhookController: статус сохранён', [
            'orders_id' => $order->id,
            'onec_order_status' => $status,
        ]);

        return response()->json(['status' => 'ok']);
    }

    private function tokenValid(Request $request): bool
    {
        $expected = config('services.onec.status_webhook_token');

        // Пустой сконфигурированный токен — не «всё разрешено», а «эндпоинт
        // не настроен на этом окружении» (ONEC_STATUS_WEBHOOK_TOKEN не задан
        // в .env, деплой .env не синкает).
        if (!$expected) {
            return false;
        }

        // input() — и тело запроса (поле token, как просила 1С), и ?token=
        $given = $request->header('X-OneC-Webhook-Token') ?? $request->input('token');

        return is_string($given) && hash_equals((string) $expected, $given);
    }
}
