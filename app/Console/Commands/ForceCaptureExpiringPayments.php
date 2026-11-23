<?php

namespace App\Console\Commands;

use App\Models\OrderPayment;
use App\Services\Payment\Victoriabank\VictoriaBankPaymentResultHandler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Договор с банком (SOLVEX LUX SRL, п.2.7) — капчур должен уйти не позже
 * 6 дней со дня положительного ответа на авторизацию, даже если статус
 * «Доставлен» от 1С так и не пришёл (посылка потерялась, 1С не прислала
 * вебхук, заказ забыли закрыть и т.д.). Эта команда — последний рубеж перед
 * нарушением условий банка: за день до дедлайна (5 суток с authorized_at)
 * принудительно списывает такие платежи, независимо от статуса доставки.
 *
 * Обычный путь (капчур по факту доставки) — в
 * OneCOrderStatusWebhookController::captureOnDelivery(). Эта команда ловит
 * только то, что тот путь не поймал вовремя — по дизайну таких случаев
 * должно быть мало, и каждый такой случай логируется как warning, потому
 * что означает: доставка не подтвердилась в разумный срок, нужно разбираться.
 */
class ForceCaptureExpiringPayments extends Command
{
    protected $signature = 'payments:force-capture-expiring {--days=5 : Через сколько дней с момента авторизации принудительно списывать}';

    protected $description = 'Принудительно списывает (TRTYPE=21) авторизованные, но не доставленные платежи VictoriaBank перед 6-дневным потолком банка (БП 2.7)';

    public function handle(VictoriaBankPaymentResultHandler $captureHandler): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);
        $captured = 0;
        $failed = 0;

        OrderPayment::query()
            ->where('provider', 'victoriabank')
            ->whereNotNull('authorized_at')
            ->whereNull('confirmed_at')
            ->whereNull('capture_requested_at')
            ->where('authorized_at', '<=', $cutoff)
            ->with('order')
            ->chunkById(100, function ($payments) use (&$captured, &$failed, $captureHandler, $days) {
                foreach ($payments as $payment) {
                    $order = $payment->order;

                    if (!$order) {
                        continue;
                    }

                    Log::warning('ForceCaptureExpiringPayments: доставка не подтвердилась вовремя — принудительный капчур', [
                        'orders_id' => $order->id,
                        'authorized_at' => $payment->authorized_at,
                        'onec_order_status' => $order->onec_order_status,
                        'days_threshold' => $days,
                    ]);

                    try {
                        $captureHandler->captureAuthorizedPayment($order, $payment, 'force_capture_deadline');
                        $captured++;
                    } catch (\Throwable $e) {
                        $failed++;
                        Log::error('ForceCaptureExpiringPayments: капчур не удался', [
                            'orders_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        $this->info("Принудительно списано: {$captured}, ошибок: {$failed}.");
        Log::info('ForceCaptureExpiringPayments: прогон завершён', ['captured' => $captured, 'failed' => $failed]);

        return self::SUCCESS;
    }
}
