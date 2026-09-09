<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoodsItemId;
use App\Models\GoodsParametrItemId;
use App\Models\GoodsParametrItemRsc;
use App\Models\GoodsParametrValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * CMS-раздел «Импорт назначения» (modules_id id=44, подраздел «Товары»,
 * см. database/sql/2026-09-09-goods-purpose-import-module.sql).
 *
 * Отвечает на вопрос заказчика 3.4 (part1, 07.09.2026): «можно как-то через
 * импорт файла заполнить [поле «Назначение»]?» — да, теперь можно. До этого
 * такого импорта в CMS не было вообще (только экспорт, GoodsController::exportExcel()).
 *
 * config('custom.front.purpose_parametr_id') = 5 («Beneficii»/«Решение», чекбоксы
 * с фиксированным списком значений) — участвует в подборе похожих товаров
 * (п.4 ТЗ) как второй по важности признак после категории.
 *
 * Формат CSV: 2 колонки — артикул;назначение (значения через запятую или
 * точку с запятой). Матчинг по артикулу — тот же ключ, что уже используется
 * везде в проекте (фото оттенков, поиск и т.д.), уникален по всему каталогу.
 * Значения назначения сверяются со СУЩЕСТВУЮЩИМ списком чекбоксов — новые
 * варианты из файла не создаются самовольно (опечатка/незнакомое значение
 * попадает в отчёт как нераспознанное, а не тихо плодит мусор в справочнике).
 * Для товара, найденного в файле, набор назначения заменяется полностью тем,
 * что указано в строке (не добавляется к прежнему) — это и есть «заполнить»,
 * а не «дополнить», раз задача явно про заполнение пустого поля.
 */
class GoodsPurposeImportController extends Controller
{
    public function index()
    {
        $view = 'admin.goods-purpose-import.form';

        $modules_name = $this->menu()['modules_name'];

        $purpose_parametr_id = (int) config('custom.front.purpose_parametr_id');
        $known_values = $this->knownValues($purpose_parametr_id);

        return view($view, get_defined_vars());
    }

    public function saveMass(Request $request)
    {
        $view = 'admin.goods-purpose-import.report';

        $modules_name = $this->menu()['modules_name'];
        $purpose_parametr_id = (int) config('custom.front.purpose_parametr_id');

        $item = Validator::make($request->all(), [
            'purpose_csv' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($item->fails()) {
            return redirect()->back()->with('purpose_error', implode(' ', $item->messages()->all()));
        }

        $known_values = $this->knownValues($purpose_parametr_id); // [value_id => name]
        // сравнение без учёта регистра — оператору проще не следить за ним при
        // заполнении файла. mapWithKeys, не keyBy: нужно сохранить value_id как
        // значение (keyBy заменяет им только ключ, но оставляет значением name)
        $known_by_lower = collect($known_values)
            ->mapWithKeys(fn ($name, $value_id) => [mb_strtolower(trim($name)) => $value_id]);

        $goods_by_articol = GoodsItemId::whereNotNull('articol')
            ->where('articol', '!=', '')
            ->where('deleted', 0)
            ->with('itemByLang')
            ->get()
            ->keyBy(fn ($item) => mb_strtolower(trim($item->articol)));

        $report = [
            'saved' => [],           // назначение проставлено впервые
            'replaced' => [],        // назначение уже было, заменено
            'unmatched_product' => [], // артикул не найден
            'unmatched_value' => [], // хотя бы одно значение не из справочника
        ];

        $handle = fopen($request->file('purpose_csv')->getRealPath(), 'r');
        $delimiter = $this->detectDelimiter($handle);
        $is_first_row = true;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($row) < 2 || trim((string) $row[0]) === '') {
                $is_first_row = false;
                continue; // пустая строка
            }

            $articol = trim((string) $row[0]);
            $articol_key = mb_strtolower($articol);

            // строка-заголовок ("артикул"/"articol" и т.п. в первой колонке
            // первой строки) — товар с таким артикулом всё равно не найдётся,
            // но незачем засорять отчёт фантомным «товар не найден»
            if ($is_first_row) {
                $is_first_row = false;
                if (!$goods_by_articol->has($articol_key)) {
                    continue;
                }
            }

            // разделитель значений внутри ячейки — тот из ; и , который НЕ
            // является разделителем колонок самого CSV (иначе значения через
            // запятую в ячейке-строке с разделителем "," распались бы на
            // отдельные столбцы ещё на fgetcsv, а не остались одной ячейкой)
            $value_separator = $delimiter === ';' ? ',' : ';';
            $rawValues = explode($value_separator, (string) $row[1]);
            $values = array_values(array_filter(array_map('trim', $rawValues), fn ($v) => $v !== ''));

            $goods_item = $goods_by_articol->get($articol_key);

            if (!$goods_item) {
                $report['unmatched_product'][] = ['articol' => $articol, 'values' => $values];
                continue;
            }

            $value_ids = [];
            $unknown = [];
            foreach ($values as $one_value) {
                $known = $known_by_lower->get(mb_strtolower($one_value));
                if ($known === null) {
                    $unknown[] = $one_value;
                } else {
                    $value_ids[] = $known;
                }
            }

            if (!empty($unknown)) {
                $report['unmatched_value'][] = [
                    'articol' => $articol,
                    'item' => $goods_item,
                    'unknown' => $unknown,
                ];
                continue;
            }

            $had_purpose = GoodsParametrItemId::where('goods_item_id', $goods_item->id)
                ->where('goods_parametr_id', $purpose_parametr_id)
                ->exists();

            $this->setPurposeValues($goods_item->id, $purpose_parametr_id, $value_ids);

            $report[$had_purpose ? 'replaced' : 'saved'][] = [
                'articol' => $articol,
                'item' => $goods_item,
                'values' => $values,
            ];
        }

        fclose($handle);

        return view($view, get_defined_vars());
    }

    /** @return array<int,string> value_id (goods_parametr_value_id, языко-независимый) => имя (RU) */
    private function knownValues(int $purpose_parametr_id): array
    {
        return \DB::table('goods_parametr_item_rsc')
            ->join('goods_parametr_item_id', 'goods_parametr_item_id.id', '=', 'goods_parametr_item_rsc.goods_parametr_item_id')
            ->join('goods_parametr_value', 'goods_parametr_value.goods_parametr_value_id', '=', 'goods_parametr_item_rsc.goods_parametr_value_id')
            ->where('goods_parametr_item_id.goods_parametr_id', $purpose_parametr_id)
            ->where('goods_parametr_value.lang_id', LANG_ID)
            ->distinct()
            ->pluck('goods_parametr_value.name', 'goods_parametr_value.goods_parametr_value_id')
            ->toArray();
    }

    /** Полностью заменяет набор значений «Назначение» у товара (create-or-update + sync). */
    private function setPurposeValues(int $goods_item_id, int $purpose_parametr_id, array $value_ids): void
    {
        $item = GoodsParametrItemId::firstOrCreate([
            'goods_item_id' => $goods_item_id,
            'goods_parametr_id' => $purpose_parametr_id,
        ]);

        GoodsParametrItemRsc::where('goods_parametr_item_id', $item->id)->delete();

        foreach (array_unique($value_ids) as $one_value_id) {
            GoodsParametrItemRsc::create([
                'goods_parametr_item_id' => $item->id,
                'goods_parametr_value_id' => $one_value_id,
            ]);
        }
    }

    /** CSV может прийти с ; (Excel в RU/RO локали) или , — определяем по первой строке. */
    private function detectDelimiter($handle): string
    {
        $first_line = fgets($handle);
        rewind($handle);

        return substr_count((string) $first_line, ';') > substr_count((string) $first_line, ',') ? ';' : ',';
    }
}
