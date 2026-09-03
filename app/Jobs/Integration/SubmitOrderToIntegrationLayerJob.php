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
 * Epic 0 / 0.4 — the retry half of "не допускать рассинхронизации": 1С or
 * Bitrix24 being down at order-creation time doesn't lose the order, it
 * retries with backoff. failed() fires only once tries are exhausted, and
 * is the "ответственный сотрудник получает уведомление" acceptance criterion.
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
        Log::critical('SubmitOrderToIntegrationLayerJob: exhausted retries, order not synced to 1С/Bitrix24', [
            'orders_id' => $this->ordersId,
            'error' => $exception?->getMessage(),
        ]);

        $alertTo = config('services.integration.alert_email');
        if ($alertTo) {
            Mail::to($alertTo)->send(new IntegrationSyncFailedMail($this->ordersId, $exception?->getMessage() ?? 'unknown error'));
        }
    }
}
