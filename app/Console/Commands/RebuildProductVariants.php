<?php

namespace App\Console\Commands;

use App\Models\GoodsItemId;
use App\Services\Product\ShadePalette;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Epic 6 — «Отдача оттенков как вариантов товара» (ТЗ §6.2).
 *
 * НЕ мигрирует существующие товары-оттенки в модель «родитель + варианты» —
 * каждый оттенок остаётся тем же самостоятельным товаром со своим URL, что
 * и был (см. миграцию product_variants и `open-decisions.md`, раздел 1:
 * полноценная миграция ~826 товаров требует подтверждения заказчиком риска
 * временной просадки SEO — раунд 2, вопрос №4, без ответа). Это read-only
 * витрина поверх существующих товаров: группирует их по линии (тот же
 * `brand_id`, что уже использует ProductRecommendations/ShadePalette) и
 * кладёт в кеш-таблицу `product_variants`, откуда её читает API.
 *
 * 1С продолжает присылать каждый оттенок отдельной строкой обмена (round 2
 * блокер №1 — группировки по линии из 1С обмен сегодня не передаёт) —
 * поэтому пересчёт запускается заново при каждом обмене
 * (ImportFrom1C::getExchange()), а не единоразово.
 */
class RebuildProductVariants extends Command
{
    protected $signature = 'shades:rebuild-variants';
    protected $description = 'Rebuild the product_variants cache from current dye products, grouped by line (brand_id)';

    public function handle(): int
    {
        // Запускается по расписанию (cron), не через HTTP — SetLocale не
        // отрабатывает, а GoodsItemId::itemByLang() жёстко завязан на
        // глобальную константу LANG_ID. Определяем её так же, как
        // SetLocale — по фолбэк-локали из конфига.
        if (!defined('LANG_ID')) {
            define('LANG_ID', array_search(config('app.fallback_locale'), config('app.locales')));
        }
        if (!defined('LANG')) {
            define('LANG', config('app.fallback_locale'));
        }

        $dye_type_ids = array_map('intval', config('custom.front.dye_goods_type_ids', []));

        $shades = GoodsItemId::whereIn('goods_type_id', $dye_type_ids)
            ->where('active', 1)
            ->where('deleted', 0)
            ->whereNotNull('brand_id')
            ->has('itemByLang')
            ->with('itemByLang')
            ->get();

        $rows = $shades->map(function ($one_shade) {
            $shade_number = ShadePalette::shadeCode($one_shade->itemByLang->name ?? '', $one_shade->articol);

            return [
                'line_brand_id' => $one_shade->brand_id,
                'goods_item_id' => $one_shade->id,
                'shade_code' => $one_shade->articol,
                'shade_number' => $shade_number,
                'shade_name' => $shade_number
                    ? ShadePalette::shadeName($one_shade->itemByLang->name ?? '', $shade_number)
                    : ($one_shade->itemByLang->name ?? null),
                'price' => $one_shade->price,
                'products_count' => $one_shade->products_count,
                'in_stoc' => $one_shade->in_stoc && $one_shade->products_count > 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        DB::table('product_variants')->truncate();

        foreach ($rows->chunk(500) as $chunk) {
            DB::table('product_variants')->insert($chunk->all());
        }

        $this->info("Rebuilt {$rows->count()} variants across {$rows->pluck('line_brand_id')->unique()->count()} lines.");

        return self::SUCCESS;
    }
}
