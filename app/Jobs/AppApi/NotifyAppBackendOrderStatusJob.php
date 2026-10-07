<?php

namespace App\Jobs\AppApi;

use App\Models\Orders;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Сообщает бэкенду приложения (eFrumos-backend) о смене статуса заказа,
 * созданного из приложения — по нему приложение шлёт push (этап 8 брифа).
 *
 * Формат — как у вебхуков 1С в eFrumos-backend (event_type / event_id /
 * occurred_at / Data), авторизация — Bearer. event_id стабилен между
 * повторами одной и той же доставки (задаётся при постановке в очередь),
 * так что приёмник может отбросить дубль. Контракт —
 * efrumos-docs/app-api-contract.md, раздел «События».
 */
class NotifyAppBackendOrderStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /** @return array<int> секунд до следующей попытки */
    public function backoff(): array
    {
        return [30, 120, 600, 1800];
    }

    /**
     * @param string $changed 'payment' | 'onec' — какой статус изменился
     */
    public function __construct(
        public readonly int $ordersId,
        public readonly string $changed,
        public readonly string $eventId,
        public readonly string $occurredAt,
    ) {
    }

    /** Ставит доставку в очередь, только если заказ из приложения и адрес приёмника настроен. Никогда не бросает. */
    public static function dispatchFor(Orders $order, string $changed): void
    {
        try {
            if ($order->source !== 'app' || !config('services.app_api.status_webhook_url')) {
                return;
            }

            $now = now();

            self::dispatch(
                $order->id,
                $changed,
                // время в миллисекундах: два разных изменения одного заказа подряд получают разные event_id
                sprintf('order-%d-%s-%s', $order->id, $changed, $now->format('YmdHisv')),
                $now->toIso8601String(),
            );
        } catch (\Throwable $e) {
            Log::error('AppApi: не удалось поставить в очередь уведомление о статусе заказа', [
                'orders_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function handle(): void
    {
        $url = config('services.app_api.status_webhook_url');
        $order = Orders::find($this->ordersId);

        if (!$url || !$order || $order->source !== 'app') {
            return;
        }

        Http::withToken((string) config('services.app_api.status_webhook_token'))
            ->acceptJson()
            ->timeout(10)
            ->post($url, [
                'event_type' => 'order_status_changed',
                'event_id' => $this->eventId,
                'occurred_at' => $this->occurredAt,
                'Data' => [[
                    'order_id' => $order->id,
                    'client_id' => $order->app_client_id,
                    'changed' => $this->changed,
                    // статусы на момент отправки, а не на момент изменения —
                    // приложению нужен актуальный, а не устаревший
                    'payment_status' => $order->payment_status?->value,
                    'onec_status' => $order->onec_order_status,
                ]],
            ])
            ->throw();
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('AppApi: уведомление о статусе заказа не доставлено после всех повторов', [
            'orders_id' => $this->ordersId,
            'event_id' => $this->eventId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
