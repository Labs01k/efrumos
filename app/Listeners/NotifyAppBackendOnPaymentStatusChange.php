<?php

namespace App\Listeners;

use App\Events\OrderPaymentStatusChanged;
use App\Jobs\AppApi\NotifyAppBackendOrderStatusJob;

/**
 * Смена статуса оплаты заказа из приложения → уведомление бэкенду приложения
 * (push «оплачен» / «ошибка оплаты»). Заказы сайта не затрагиваются.
 */
class NotifyAppBackendOnPaymentStatusChange
{
    public function handle(OrderPaymentStatusChanged $event): void
    {
        NotifyAppBackendOrderStatusJob::dispatchFor($event->order, 'payment');
    }
}
