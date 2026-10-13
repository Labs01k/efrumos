<?php

namespace App\Console\Commands;

use App\Http\Controllers\Exchange\ImportFrom1C;
use App\Models\GoodsItemId;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Обмен каталогом с 1С из консоли — то же, что маршруты /fullExchange/download
 * и /fullExchange/update, но без лимита времени веб-запроса и без открытого
 * URL. Логику разбора (в том числе rescue-режим для битого JSON от 1С) даёт
 * ImportFrom1C, здесь только оркестрация.
 *
 * Полный обмен у 1С занимает ~16 минут и весит ~7 МБ, поэтому файл сначала
 * скачивается на диск (storage/app/1c/full_exchange.json), потом читается.
 * Файл можно переиспользовать (--skip-download) — например, чтобы прогнать
 * разбор повторно, не дёргая 1С.
 *
 *   php artisan onec:sync-stock                 полный обмен: цены, остатки, новые товары (active=0)
 *   php artisan onec:sync-stock --rests-only    только goods_shop_rests, товары и цены не трогает
 *   php artisan onec:sync-stock --changed       только изменённые позиции (без файла, напрямую из SOAP)
 *   php artisan onec:sync-stock --skip-download использовать уже скачанный файл
 *
 * Привязка магазинов к складам (shops_id.store_guid) остаётся ручной, в CMS:
 * без неё блок «Наличие по магазинам» на карточке товара не показывается.
 */
class SyncStockFrom1C extends Command
{
    protected $signature = 'onec:sync-stock
        {--changed : Только изменённые позиции (FullExchange=false), файл не используется}
        {--rests-only : Обновить только остатки по складам (goods_shop_rests)}
        {--skip-download : Не скачивать из 1С, взять уже сохранённый файл}
        {--file= : Путь к файлу обмена (по умолчанию storage/app/1c/full_exchange.json)}';

    protected $description = 'Обмен каталогом и остатками с 1С (GetSKU)';

    public function handle(ImportFrom1C $import): int
    {
        // Найдено 13.10.2026 при восстановлении dev после переноса прод-дампа:
        // на реальном каталоге (22 склада вместо 4 тестовых, под 150к строк
        // остатков) команда тихо падала по памяти под дефолтным CLI
        // memory_limit — без единой строки в лог, просто обрыв процесса.
        // Через cron (schedule:run >> /dev/null 2>&1, см. efrumos_jobs) это
        // было бы вообще незаметно — ровно так 10 дней молчал обмен на
        // проде (хоть и по другой причине). Фиксированный предел вместо -1,
        // чтобы не положить весь сервер при реальном зависании.
        ini_set('memory_limit', '1024M');

        try {
            return $this->performSync($import);
        } catch (Throwable $e) {
            Log::error('SyncStockFrom1C: обмен упал с исключением', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error('Упало с исключением: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    private function performSync(ImportFrom1C $import): int
    {
        $changed = (bool) $this->option('changed');
        $rests_only = (bool) $this->option('rests-only');
        $file = $this->option('file') ?: storage_path('app/1c/full_exchange.json');

        if (!$changed && !$this->option('skip-download')) {
            File::ensureDirectoryExists(dirname($file));
            $this->info('Скачиваю полный обмен из 1С (около 16 минут)...');
            $started = microtime(true);
            $bytes = $import->_getProductsArray(true, 'download', $file);
            if (!$bytes) {
                $this->error('1С не вернула данные или файл не записан: ' . $file);
                Log::error('SyncStockFrom1C: 1С не вернула данные или файл не записан', ['file' => $file]);
                return self::FAILURE;
            }
            $this->info(sprintf('Файл сохранён: %s (%.1f МБ, %d с)', $file, $bytes / 1048576, microtime(true) - $started));
        }

        if (!$changed && !is_file($file)) {
            $this->error("Файла обмена нет: {$file} (запустите без --skip-download)");
            Log::error('SyncStockFrom1C: файла обмена нет', ['file' => $file]);
            return self::FAILURE;
        }

        if (!$rests_only) {
            // Записывает цену, промо-цену, остаток главного склада, in_stoc, граммаж
            // и остатки по складам; неизвестные товары создаёт неактивными.
            $import->getExchange(!$changed, $changed ? null : 'update', $file);
            $this->info('Обмен применён.');
            Log::info('SyncStockFrom1C: обмен применён', ['changed_only' => $changed]);
            return self::SUCCESS;
        }

        $items = $import->_getProductsArray(!$changed, $changed ? null : 'update', $file);
        if (empty($items)) {
            $this->error('В обмене не найдено ни одного товара (пустой ответ или файл не разобрался).');
            Log::error('SyncStockFrom1C: пустой ответ при --rests-only');
            return self::FAILURE;
        }

        $ids = GoodsItemId::pluck('id', 'one_c_code');
        $updated = $skipped = 0;

        $bar = $this->output->createProgressBar(count($items));
        foreach ($items as $item) {
            $goods_item_id = $ids[$item['Code1C'] ?? null] ?? null;
            if ($goods_item_id === null) {
                $skipped++;
            } else {
                $import->syncShopRests((int) $goods_item_id, $item['Rests'] ?? []);
                $updated++;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        $this->info("Остатки обновлены у {$updated} товаров; пропущено (нет в базе по Code1C): {$skipped}.");
        Log::info('SyncStockFrom1C: остатки обновлены', ['updated' => $updated, 'skipped' => $skipped]);

        return self::SUCCESS;
    }
}
