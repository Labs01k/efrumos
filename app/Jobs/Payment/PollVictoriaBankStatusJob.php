<?php

namespace App\Jobs\Payment;

use App\Enums\PaymentStatus;
use App\Models\Orders;
use App\Services\Payment\Victoriabank\VictoriaBankClient;
use App\Services\Payment\Victoriabank\VictoriaBankPaymentResultHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Epic 1 / 1.1 — подстраховка на случай, если server-to-server callback банка
 * не дошёл (сеть, падение приложения в момент запроса). Опрашивает статус
 * транзакции через TRTYPE=90 по затухающему расписанию и, дойдя до финального
 * ответа, отдаёт его тому же обработчику, что и callback.
 *
 * Статус двигает только VictoriaBankPaymentResultHandler — здесь нет своей
 * логики переходов. Если callback пришёл первым, заказ уже не в Pending и
 * джоба тихо выходит.
 */
class PollVictoriaBankStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Ретраи самой джобы — на сетевые сбои при обращении к банку. */
    public int $tries = 3;

    /**
     * Расписание опроса от момента ухода покупателя на страницу банка.
     * Первый опрос — после того, как 3-D Secure заведомо закончился;
     * дальше реже, суммарно около двух часов.
     *
     * @var array<int, int> секунды
     */
    private const SCHEDULE = [180, 420, 900, 1800, 3600];

    public function __construct(
        public readonly int $ordersId,
        public readonly int $attempt = 0,
    ) {
    }

    /** @return array<int> секунды перед ретраем при ошибке обращения к банку */
    public function backoff(): array
    {
        return [30, 120];
    }

    /**
     * Ставит первый опрос. На синхронной очереди поллинг бессмысленен и вреден:
     * delay() игнорируется, и вся цепочка отработала бы прямо внутри запроса,
     * задерживая редирект покупателя в банк. Тогда единственным источником
     * статуса остаётся callback — как и задумано банком.
     */
    public static function startFor(Orders $order): void
    {
        if (config('queue.default') === 'sync') {
            Log::info('VictoriaBank: опрос статуса пропущен, очередь sync', ['order' => $order->id]);

            return;
        }

        self::schedule($order->id, 0);
    }

    public function handle(VictoriaBankClient $client, VictoriaBankPaymentResultHandler $handler): void
    {
        $order = Orders::find($this->ordersId);

        if (!$order) {
            return;
        }

        // callback уже решил судьбу платежа — опрашивать нечего
        if ($order->payment_status !== PaymentStatus::Pending) {
            return;
        }

        $payment = $order->payments()->where('provider', 'victoriabank')->first();

        if (!$payment) {
            return;
        }

        $result = $client->checkStatus((string) $order->id);

        if (VictoriaBankPaymentResultHandler::isFinal($result)) {
            Log::info('VictoriaBank: статус получен опросом, callback не пришёл', [
                'order' => $order->id,
                'attempt' => $this->attempt,
                'result' => $result,
            ]);

            $handler->handle($order, $payment, $result, 'status_poll');

            return;
        }

        self::schedule($this->ordersId, $this->attempt + 1);
    }

    /** Ставит следующий опрос, пока расписание не исчерпано. */
    private static function schedule(int $ordersId, int $attempt): void
    {
        if (!isset(self::SCHEDULE[$attempt])) {
            // банк так и не дал финального ответа: заказ остаётся Pending,
            // дальше им занимается менеджер (ручная смена статуса в CMS)
            Log::warning('VictoriaBank: опрос статуса исчерпан, заказ остался в ожидании оплаты', [
                'order' => $ordersId,
            ]);

            return;
        }

        self::dispatch($ordersId, $attempt)->delay(now()->addSeconds(self::SCHEDULE[$attempt]));
    }
}
