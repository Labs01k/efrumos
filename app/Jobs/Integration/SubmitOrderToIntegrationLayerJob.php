<?php

namespace App\Jobs\Integration;

use App\Exceptions\Integration\InsufficientStockException;
use App\Mail\IntegrationSyncFailedMail;
use App\Models\Orders;
use App\Services\Integration\OrderIntegrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Epic 0 / 0.4 — the retry half of "не допускать рассинхронизации": 1С
 * being down at order-creation time doesn't lose the order, it retries with
 * backoff. failed() fires only once tries are exhausted, and is the
 * "ответственный сотрудник получает уведомление" acceptance criterion.
 */
class SubmitOrderToIntegrationLayerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public readonly int $ordersId)
    {
    }

    /** @return array<int> seconds to wait before each retry */
    public function backoff(): array
    {
        return [30, 120, 600, 1800, 3600];
    }

    public function handle(OrderIntegrationService $service): void
    {
        $order = Orders::findOrFail($this->ordersId);

        if (!$order->isServedByOneC()) {
            Log::info('SubmitOrderToIntegrationLayerJob: заказ не передаётся в 1С — район не обслуживается этой базой', [
                'orders_id' => $order->id,
            ]);

            return;
        }

        // 1С (ответ 24.09.2026 про «наложенный платёж», см.
        // efrumos-docs/1c-open-items.md): в 1С должны попадать только уже
        // оплаченные на сайте заказы — сайт обязан подтвердить оплату
        // (markPaid/CreatePayment), иначе 1С сама аннулирует заказ или
        // передаёт его в amoCRM. Заказ с оплатой при получении и так уходит
        // в amoCRM отдельно и всегда (App\Services\AmoOrder\SendOrderToAmoCrm,
        // вызывается при оформлении независимо от способа оплаты) — создавать
        // для него ещё и документ в 1С не нужно и, по словам 1С, неправильно.
        if ($order->pay_method !== 'card') {
            Log::info('SubmitOrderToIntegrationLayerJob: заказ не передаётся в 1С — оплата при получении, только amoCRM', [
                'orders_id' => $order->id,
                'pay_method' => $order->pay_method,
            ]);

            return;
        }

        try {
            $service->submitOrder($order);
        } catch (InsufficientStockException $e) {
            // Not transient — retrying won't manufacture stock. Fail the job
            // immediately instead of burning through 5 attempts pointlessly.
            $this->fail($e);
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::critical('SubmitOrderToIntegrationLayerJob: exhausted retries, order not synced to 1С', [
            'orders_id' => $this->ordersId,
            'error' => $exception?->getMessage(),
        ]);

        $alertTo = config('services.integration.alert_email');
        if ($alertTo) {
            Mail::to($alertTo)->send(new IntegrationSyncFailedMail($this->ordersId, $exception?->getMessage() ?? 'unknown error'));
        }
    }
}
