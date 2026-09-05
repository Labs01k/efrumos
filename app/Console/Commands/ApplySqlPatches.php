<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Применяет патчи схемы и данных из database/sql/*.sql.
 *
 * Структура сайта живёт в дампе, а не в миграциях, поэтому часть изменений
 * (колонки, служебные таблицы, разделы CMS, координаты магазинов) оформлена
 * идемпотентным сырым SQL. bin/local-up.sh накатывает эти файлы клиентом
 * mysql внутри compose; на сервере такого клиента и compose нет — там патчи
 * применяет эта команда, через то же подключение, что и приложение.
 *
 * Файлы обязаны быть идемпотентными (IF NOT EXISTS / WHERE): команда
 * выполняется на каждом деплое.
 */
class ApplySqlPatches extends Command
{
    protected $signature = 'db:apply-sql-patches {--pretend : Только показать список файлов, ничего не выполнять}';

    protected $description = 'Применяет идемпотентные SQL-патчи из database/sql/*.sql';

    public function handle(): int
    {
        $files = glob(database_path('sql/*.sql')) ?: [];
        sort($files);

        if (!$files) {
            $this->info('database/sql: патчей нет.');

            return self::SUCCESS;
        }

        foreach ($files as $file) {
            $name = basename($file);

            if ($this->option('pretend')) {
                $this->line('  будет применён: ' . $name);
                continue;
            }

            try {
                // Патчи содержат по несколько операторов; unprepared отдаёт их
                // драйверу как есть, без биндингов и без разбиения по ';'.
                DB::unprepared(file_get_contents($file));
                $this->info('  применён: ' . $name);
            } catch (Throwable $e) {
                // Останавливаемся на первой ошибке: патчи бывают связаны
                // (колонка → данные), продолжать по сломанной базе опаснее.
                $this->error('  ОШИБКА в ' . $name . ': ' . $e->getMessage());
                $this->error('  Остальные патчи не применялись — база в промежуточном состоянии.');

                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
