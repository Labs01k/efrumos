<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Exceptions\Payment\InvalidPaymentStatusTransitionException;
use App\Models\Orders;
use App\Services\Payment\OrderPaymentStatusService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * E7-BE-02 — БП-8 формулирует это фактом ТЗ, не открытым вопросом:
 * «Неоплаченный картой заказ автоматически отменяется через 24 часа»
 * (`efrumos-docs/mob/tasks-status.md`, ОВ-3). Ничьего внешнего ответа не
 * ждёт — весь E7 разблокирован 26.10, единственная зависимость (связка
 * профилей E0/E1) закрыта 10.11.2026.
 *
 * Отмена идёт через тот же OrderPaymentStatusService::transition(), что и
 * любая другая смена статуса — событие OrderPaymentStatusChanged сработает
 * само, и для app-заказов push «Заказ отменён» уйдёт без единой строчки
 * нового кода здесь (NotifyAppBackendOnPaymentStatusChange →
 * ProcessOrderStatusJob → SendUserPushJob, уже весь путь готов).
 *
 * Напоминание за 2ч до отмены — НЕ реализовано здесь: это не смена
 * статуса (заказ остаётся Pending), значит через существующий канал
 * «статус изменился» его не отправить. Нужен новый тип события в
 * NotifyAppBackendOrderStatusJob — отдельная, более крупная задача.
 */
class AutoCancelUnpaidOrders extends Command
{
    protected $signature = 'orders:auto-cancel-unpaid {--hours=24 : Через сколько часов неоплаченный картой заказ отменяется}';

    protected $description = 'Автоматически отменяет неоплаченные картой заказы старше N часов (БП-8)';

    public function handle(OrderPaymentStatusService $paymentStatusService): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);
        $cancelled = 0;
        $skipped = 0;

        Orders::query()
            ->where('pay_method', 'card')
            ->where('payment_status', PaymentStatus::Pending->value)
            ->where('created_at', '<=', $cutoff)
            ->chunkById(100, function ($orders) use (&$cancelled, &$skipped, $paymentStatusService, $hours) {
                foreach ($orders as $order) {
                    // Договор с банком, п.2.7 — капчур теперь откладывается
                    // до доставки (или до защитной job на 6-дневный
                    // потолок), поэтому авторизованный заказ может сидеть в
                    // Pending днями — это НЕ «неоплаченный», банк уже
                    // заблокировал деньги на карте клиента. Раньше здесь
                    // проверялся только capture_requested_at (заявка на
                    // капчур уже отправлена) — этого недостаточно: теперь
                    // между authorized_at и capture_requested_at может
                    // пройти несколько дней, и всё это время заказ не
                    // должен попадать под автоотмену.
                    $authorizedByBank = $order->payments()
                        ->where('provider', 'victoriabank')
                        ->whereNotNull('authorized_at')
                        ->exists();

                    if ($authorizedByBank) {
                        $skipped++;
                        Log::info('AutoCancelUnpaidOrders: пропущен — платёж уже авторизован банком', ['orders_id' => $order->id]);

                        continue;
                    }

                    try {
                        $paymentStatusService->transition(
                            order: $order,
                            to: PaymentStatus::Cancelled,
                            source: 'auto_cancel_24h',
                            comment: "Автоматическая отмена — картой не оплачен более {$hours}ч (БП-8).",
                        );
                        $cancelled++;
                    } catch (InvalidPaymentStatusTransitionException $e) {
                        // Статус успел измениться между выборкой и этим
                        // циклом (например, бан успел подтвердить оплату) —
                        // не баг, просто гонка, безопасно пропускаем.
                        $skipped++;
                        Log::info('AutoCancelUnpaidOrders: пропущен — статус уже изменился', [
                            'orders_id' => $order->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        $this->info("Отменено: {$cancelled}, пропущено: {$skipped}.");
        Log::info('AutoCancelUnpaidOrders: прогон завершён', ['cancelled' => $cancelled, 'skipped' => $skipped]);

        return self::SUCCESS;
    }
}
