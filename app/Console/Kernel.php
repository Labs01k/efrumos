<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // Обмен каталогом/остатками с 1С (замена public/cron/cron.php на
        // проде: тот скрипт раз в час дёргал curl на собственный публичный
        // URL через ХОСТОВЫЙ `php` — 28.09.2026 хостовый php по умолчанию
        // переключился на 8.5 без модуля curl, и обмен молча не работал
        // 10+ дней, пока не заметили по жалобе клиента на акции/новые
        // товары. Здесь — тот же onec:sync-stock, что и так уже есть,
        // вызывается планировщиком Laravel изнутри контейнера: не зависит
        // от хостового php, не ходит через веб (нет таймаута nginx на
        // полный обмен — он может идти до ~16 минут), и виден в
        // `php artisan schedule:list`/логах, а не падает молча.
        //
        // Полный обмен (замена /fullExchange/download + /fullExchange/update,
        // раньше это было два раздельных часа в cron.php) — одной атомарной
        // командой: если скачивание не удалось, применение не запустится
        // на старом файле (в отличие от прежней схемы).
        $schedule->command('onec:sync-stock')->dailyAt('02:10')->withoutOverlapping();

        // Только изменённые позиции (замена onlyChangedExchange — раньше
        // шла каждый час). Смещена от полного обмена, чтобы не дёргать 1С
        // двумя запросами почти одновременно.
        $schedule->command('onec:sync-stock --changed')->hourlyAt(35)->withoutOverlapping();

        // Epic 0 / 0.4 — catch any 1С desync that slipped past the
        // per-job retry alert.
        $schedule->command('integration:check-desync')->daily();

        // Epic 3 — «часто покупают вместе» (ТЗ.md п.3.2): фоновый пересчёт
        // раз в сутки, не на лету на каждой странице товара.
        $schedule->command('recommendations:recalc-bought-together')->daily();

        // Epic 6 — витрина оттенков-вариантов (см. RebuildProductVariants).
        // Чаще, чем co-purchase кеш выше — цена/остаток ближе к покупателю.
        // Идеально было бы дёргать это в конце ImportFrom1C::getExchange(),
        // сразу после каждого реального обмена — не стали трогать этот
        // контроллер без отдельной просьбы (живая точка входа для 1С).
        $schedule->command('shades:rebuild-variants')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
