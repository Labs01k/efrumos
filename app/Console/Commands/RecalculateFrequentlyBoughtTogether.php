<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Epic 3 — «часто покупают вместе» (ТЗ.md п.3.2): периодический фоновый
 * пересчёт пар товаров в оплаченных заказах за 12 месяцев, кешируется в
 * goods_frequently_bought_with. ProductRecommendations::coPurchased() читает
 * из этого кеша, не считает вживую на каждой странице (дорого по
 * производительности на растущей базе заказов — то, чего ТЗ просит избегать).
 *
 * «Оплаченные заказы» по факту данных этого проекта: ни новый payment_status
 * (значим только для карточных заказов из Epic 1 — 2901 из 2905 заказов
 * стоят на дефолте pending, включая все заказы наличными), ни легаси-булево
 * paid (всегда 0, никогда не использовалось) не дают надёжного сигнала
 * "оплачен" для заказов наличными — подавляющего большинства. Поэтому здесь
 * считаем подтверждённым любой неудалённый заказ, кроме тех, что точно
 * провалились/отменились (payment_status failed/cancelled) — это тот же
 * критерий, что был в исходной живой реализации, только теперь явно
 * дополнен исключением заведомо неуспешных карточных попыток.
 */
class RecalculateFrequentlyBoughtTogether extends Command
{
    protected $signature = 'recommendations:recalc-bought-together';
    protected $description = 'Rebuild the goods_frequently_bought_with cache from the last 12 months of orders';

    private const MONTHS = 12;

    public function handle(): int
    {
        $since = now()->subMonths(self::MONTHS);

        DB::table('goods_frequently_bought_with')->truncate();

        DB::statement('
            INSERT INTO goods_frequently_bought_with (goods_item_id, related_goods_item_id, pair_count, created_at, updated_at)
            SELECT
                current_item.goods_item_id AS goods_item_id,
                other_item.goods_item_id AS related_goods_item_id,
                COUNT(*) AS pair_count,
                NOW(), NOW()
            FROM orders
            JOIN basket AS current_item ON current_item.basket_id = orders.basket_id
            JOIN basket AS other_item
                ON other_item.basket_id = orders.basket_id
                AND other_item.goods_item_id <> current_item.goods_item_id
            WHERE orders.deleted = 0
                AND orders.payment_status NOT IN (\'failed\', \'cancelled\')
                AND orders.created_at >= ?
            GROUP BY current_item.goods_item_id, other_item.goods_item_id
        ', [$since]);

        $pairs = DB::table('goods_frequently_bought_with')->count();
        $this->info("Recalculated {$pairs} product pairs from orders since {$since->toDateString()}.");

        return self::SUCCESS;
    }
}
