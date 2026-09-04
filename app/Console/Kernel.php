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

        // Epic 0 / 0.4 — catch any 1С/Bitrix24 desync that slipped past the
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
