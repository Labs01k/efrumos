<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Jobs\AppApi\NotifyAppBackendOrderStatusJob;
use App\Models\IntegrationIdMapping;
use App\Models\Orders;
use App\Services\Payment\Victoriabank\VictoriaBankPaymentResultHandler;
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
    /**
     * Статусы 1С, которые означают «товар физически передан клиенту» —
     * договор с банком (п.2.7) требует списывать (TRTYPE=21) именно в этот
     * момент, не раньше. «СделкаЗавершена» — на случай, если 1С иногда
     * проскакивает «Доставлен» и сразу закрывает сделку.
     */
    private const DELIVERED_STATUSES = ['Доставлен', 'СделкаЗавершена'];

    public function __invoke(Request $request, VictoriaBankPaymentResultHandler $captureHandler): JsonResponse
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

        if (in_array($status, self::DELIVERED_STATUSES, true)) {
            $this->captureOnDelivery($order, $captureHandler);
        }

        // заказ из приложения — сообщаем eFrumos-backend (push по статусу)
        NotifyAppBackendOrderStatusJob::dispatchFor($order, 'onec');

        Log::info('OneCOrderStatusWebhookController: статус сохранён', [
            'orders_id' => $order->id,
            'onec_order_status' => $status,
        ]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Договор с банком, п.2.7 — капчур в день доставки. Берём авторизованный
     * (authorized_at не пуст), но ещё не списанный (confirmed_at пуст)
     * платёж VictoriaBank по заказу и инициируем реальное списание. Заказ
     * может быть оплачен наличными или вообще не иметь платежа — тогда
     * $payment не найдётся, и метод тихо ничего не делает.
     */
    private function captureOnDelivery(Orders $order, VictoriaBankPaymentResultHandler $captureHandler): void
    {
        $payment = $order->payments()
            ->where('provider', 'victoriabank')
            ->whereNotNull('authorized_at')
            ->whereNull('confirmed_at')
            ->first();

        if (!$payment) {
            return;
        }

        try {
            $captureHandler->captureAuthorizedPayment($order, $payment, 'onec_delivery_status');
        } catch (\Throwable $e) {
            // Сетевой сбой при обращении к банку не должен превратить этот
            // вебхук в 500 — 1С по докблоку класса всегда получает 200.
            // Заявка на капчур уже снята внутри captureAuthorizedPayment();
            // следующий триггер (повторный статус от 1С или защитная job
            // на 6-дневный потолок) повторит попытку.
            Log::error('OneCOrderStatusWebhookController: капчур по доставке не удался', [
                'orders_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
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
