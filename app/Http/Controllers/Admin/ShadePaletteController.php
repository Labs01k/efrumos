<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandId;
use App\Models\GoodsItemId;
use App\Services\Product\ShadePalette;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * CMS-раздел «Палитра оттенков» (п.6 ТЗ): управление фотографиями оттенков.
 * Только фото — цены, остатки, названия и серии ведутся в 1С.
 *
 * URL раздела: /{lang}/back/goods/shades (подраздел модуля «Товары»,
 * modules_id id=23; права наследуются от модуля goods).
 */
class ShadePaletteController extends Controller
{
    private const UPLOAD_DIR = 'upfiles/goods-shades';

    /** Лимит на одно фото оттенка, КБ. */
    private const MAX_PHOTO_KB = 4096;

    /** Больше — картинка не поместится в memory_limit при создании миниатюр. */
    private const MAX_PHOTO_PIXELS = 25000000;

    private const PHOTO_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /** Список красок-оттенков: поиск по коду, названию или артикулу. */
    public function index(Request $request)
    {
        $view = 'admin.shade-palette.list';

        $modules_name = $this->menu()['modules_name'];

        $q = trim((string) $request->input('q'));
        $brand_filter = (int) $request->input('brand');
        $only_without = (bool) $request->input('without_photo');

        $shades_query = $this->dyesQuery()
            ->with('itemByLang', 'getBrand')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->whereHas('itemByLang', function ($lang_q) use ($q) {
                        $lang_q->where('name', 'like', '%' . $q . '%');
                    })
                        ->orWhere('articol', 'like', '%' . $q . '%')
                        ->orWhere('one_c_code', 'like', '%' . $q . '%');
                });
            })
            ->when($brand_filter, fn ($query) => $query->where('brand_id', $brand_filter))
            ->when($only_without, fn ($query) => $query->whereNull('shade_img'))
            ->orderBy('brand_id')
            ->orderBy('position');

        $shades_list = $shades_query->paginate(50)->withQueryString();

        $shades_list->getCollection()->transform(function ($one_item) {
            $one_item->shade_code = ShadePalette::shadeCode($one_item->itemByLang->name ?? '', $one_item->articol);

            return $one_item;
        });

        $brand_list = $this->dyeBrands();


        return view($view, get_defined_vars());
    }

    /** Загрузка или замена фотографии одного оттенка. */
    public function saveImg(Request $request, $id)
    {
        $goods_item = GoodsItemId::findOrFail($id);
        $file = $request->file('shade_photo');

        $problem = $file ? $this->photoProblem($file) : __('variables.shades_error_no_files');

        if ($problem === null && !$this->storePhoto($goods_item, $file)) {
            $problem = __('variables.shades_error_corrupt');
        }

        if ($problem !== null) {
            return redirect()->back()->with('shade_error', $problem);
        }

        return redirect()->back()->with('shade_saved', $goods_item->id);
    }

    /** Удаление фотографии оттенка. */
    public function deleteImg(Request $request, $id)
    {
        $goods_item = GoodsItemId::findOrFail($id);

        $this->removePhotoFiles($goods_item->shade_img);
        $goods_item->update(['shade_img' => null]);

        return redirect()->back()->with('shade_saved', $goods_item->id);
    }

    /** Форма массовой загрузки фотографий. */
    public function massUpload()
    {
        $view = 'admin.shade-palette.mass-upload';

        $modules_name = $this->menu()['modules_name'];
        $brand_list = $this->dyeBrands();

        return view($view, get_defined_vars());
    }

    /**
     * Массовая загрузка: имя файла — код оттенка (7-47.jpg → 7/47) или артикул
     * (NDL9-76.jpg → NDL9/76). Артикул уникален и матчится по всему каталогу,
     * код оттенка повторяется между линейками, поэтому матчится только внутри
     * выбранной линейки. Всё несопоставленное попадает в отчёт.
     */
    public function saveMass(Request $request)
    {
        $view = 'admin.shade-palette.mass-report';

        $modules_name = $this->menu()['modules_name'];

        $files = array_filter((array) $request->file('shade_photos'));

        if (!$files) {
            return redirect()->back()->with('shade_error', __('variables.shades_error_no_files'));
        }

        $brand_id = (int) $request->input('brand');

        // карты соответствия: артикул → товар (весь каталог красок),
        // код оттенка → товар (только выбранная линейка)
        $by_articol = [];
        $by_code = [];

        $dyes = $this->dyesQuery()->with('itemByLang')->get();

        foreach ($dyes as $one_dye) {
            if ($one_dye->articol) {
                $by_articol[$this->normalizeCode($one_dye->articol)][] = $one_dye;
            }

            if ($brand_id && (int) $one_dye->brand_id === $brand_id) {
                $code = ShadePalette::shadeCode($one_dye->itemByLang->name ?? '', $one_dye->articol);

                if ($code !== null) {
                    $by_code[$this->normalizeCode($code)][] = $one_dye;
                }
            }
        }

        $report = [
            'saved' => [],       // [имя файла, товар]
            'replaced' => [],    // фото уже было и заменено
            'unmatched' => [],   // имя файла ни с чем не совпало
            'ambiguous' => [],   // совпало с несколькими товарами
            'rejected' => [],    // файл не принят: размер, формат, битый — [имя файла, причина]
        ];

        foreach ($files as $one_file) {
            // битый или слишком большой файл не должен ни к чему привязаться
            // и тем более затереть уже загруженное фото
            $problem = $this->photoProblem($one_file);

            if ($problem !== null) {
                $report['rejected'][] = ['file' => $one_file->getClientOriginalName(), 'reason' => $problem];
                continue;
            }

            $base = pathinfo($one_file->getClientOriginalName(), PATHINFO_FILENAME);
            $key = $this->normalizeCode($base);

            $candidates = $by_articol[$key] ?? $by_code[$key] ?? [];

            if (count($candidates) === 0) {
                $report['unmatched'][] = $one_file->getClientOriginalName();
                continue;
            }

            if (count($candidates) > 1) {
                $report['ambiguous'][] = [
                    'file' => $one_file->getClientOriginalName(),
                    'items' => $candidates,
                ];
                continue;
            }

            $goods_item = $candidates[0];
            $had_photo = (bool) $goods_item->shade_img;

            if (!$this->storePhoto($goods_item, $one_file)) {
                $report['rejected'][] = [
                    'file' => $one_file->getClientOriginalName(),
                    'reason' => __('variables.shades_error_corrupt'),
                ];
                continue;
            }

            $report[$had_photo ? 'replaced' : 'saved'][] = [
                'file' => $one_file->getClientOriginalName(),
                'item' => $goods_item,
            ];
        }

        $brand_name = $brand_id
            ? (BrandId::with('itemByLang')->find($brand_id)->itemByLang->name ?? '')
            : null;

        return view($view, get_defined_vars());
    }

    /** Базовая выборка красок: типы товара из настройки палитры. */
    private function dyesQuery()
    {
        return GoodsItemId::whereIn('goods_type_id', config('custom.front.dye_goods_type_ids', []))
            ->where('deleted', 0)
            ->has('itemByLang');
    }

    /** Линейки, в которых есть краски, — для фильтра и массовой загрузки. */
    private function dyeBrands()
    {
        $brand_ids = $this->dyesQuery()->distinct()->pluck('brand_id')->filter();

        return BrandId::whereIn('id', $brand_ids)
            ->has('itemByLang')
            ->with('itemByLang')
            ->get()
            ->sortBy(fn ($one_brand) => mb_strtolower($one_brand->itemByLang->name ?? ''))
            ->values();
    }

    /** «NDL9-76», «9_76», «9/76» → «9/76» без префикса регистра. */
    private function normalizeCode(string $value): string
    {
        return mb_strtolower(str_replace(['-', '_', ' '], '/', trim($value)));
    }

    /**
     * Лимиты загрузки для формы: JS проверяет выбор файлов до отправки, потому
     * что PHP за пределами лимитов молча выкидывает лишние файлы или весь POST.
     */
    public static function uploadLimits(): array
    {
        return [
            'file_bytes' => self::MAX_PHOTO_KB * 1024,
            'file_mb' => self::MAX_PHOTO_KB / 1024,
            'post_bytes' => self::iniBytes(ini_get('post_max_size')),
            'post_mb' => round(self::iniBytes(ini_get('post_max_size')) / 1048576),
            'max_files' => (int) ini_get('max_file_uploads'),
        ];
    }

    /** «8M» → 8388608. 0 — лимита нет. */
    private static function iniBytes($value): int
    {
        $value = trim((string) $value);
        $number = (int) $value;

        switch (strtolower(substr($value, -1))) {
            case 'g': return $number * 1073741824;
            case 'm': return $number * 1048576;
            case 'k': return $number * 1024;
            default: return $number;
        }
    }

    /**
     * Почему файл нельзя принять как фото оттенка; null — можно. Текст причины
     * показывается администратору как есть. Декодируемость проверяет
     * storePhoto(): картинку всё равно разбирать ради миниатюр.
     */
    private function photoProblem(UploadedFile $file): ?string
    {
        if (!$file->isValid()) {
            // файл больше upload_max_filesize PHP отрезает ещё до нас
            return in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? __('variables.shades_error_size', ['max' => self::MAX_PHOTO_KB / 1024])
                : __('variables.shades_error_upload');
        }

        if ($file->getSize() > self::MAX_PHOTO_KB * 1024) {
            return __('variables.shades_error_size', ['max' => self::MAX_PHOTO_KB / 1024]);
        }

        if (!in_array($file->getMimeType(), self::PHOTO_MIMES, true)) {
            return __('variables.shades_error_type');
        }

        $size = @getimagesize($file->getRealPath());

        if (!$size || !in_array($size['mime'] ?? null, self::PHOTO_MIMES, true)) {
            return __('variables.shades_error_corrupt');
        }

        // картинка разбирается в память целиком: 50 Мп — это ~200 МБ,
        // больше memory_limit, и PHP упал бы фатальной ошибкой
        if ($size[0] * $size[1] > self::MAX_PHOTO_PIXELS) {
            return __('variables.shades_error_dimensions', ['width' => $size[0], 'height' => $size[1]]);
        }

        return null;
    }

    /**
     * Сохраняет фото оттенка: файл, миниатюры, shade_img — и только потом
     * удаляет старое фото. Если картинку не удалось разобрать (битый файл),
     * новое убирается, старое фото остаётся, возвращается false.
     */
    private function storePhoto(GoodsItemId $goods_item, UploadedFile $file): bool
    {
        $dir = public_path(self::UPLOAD_DIR);

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // случайный хвост: один оттенок может прийти в пакете дважды за секунду
        $file_name = $goods_item->id . '-' . time() . '-' . Str::lower(Str::random(6))
            . '.' . strtolower($file->getClientOriginalExtension());
        $file->move($dir, $file_name);

        // по умолчанию GD молча дорисовывает серым обрезанный JPEG — нам нужна ошибка
        $ignore_jpeg_warnings = ini_set('gd.jpeg_ignore_warning', '0');

        try {
            // миниатюры (.webp): s — свотч в палитре, m — карточка в каталоге и рекомендациях
            foreach (array_keys(ShadePalette::SHADE_THUMB_SIZES) as $size) {
                ShadePalette::makeShadeThumb($file_name, $size);

                if (!File::exists($dir . '/' . $size . '/' . showImg($file_name))) {
                    throw new \RuntimeException('Не удалось создать миниатюру ' . $size . ' для ' . $file_name);
                }
            }
        } catch (\Throwable $e) {
            // битый JPEG: GD не читает его вовсе или ругается на обрыв данных
            $this->removePhotoFiles($file_name);

            return false;
        } finally {
            if ($ignore_jpeg_warnings !== false) {
                ini_set('gd.jpeg_ignore_warning', $ignore_jpeg_warnings);
            }
        }

        $old_file_name = $goods_item->shade_img;
        $goods_item->update(['shade_img' => $file_name]);
        $this->removePhotoFiles($old_file_name);

        return true;
    }

    /** Удаляет оригинал и миниатюры фото оттенка. */
    private function removePhotoFiles(?string $file_name): void
    {
        if (!$file_name) {
            return;
        }

        $dir = public_path(self::UPLOAD_DIR);

        File::delete($dir . '/' . $file_name);

        foreach (array_keys(ShadePalette::SHADE_THUMB_SIZES) as $size) {
            File::delete($dir . '/' . $size . '/' . showImg($file_name));
        }
    }
}
