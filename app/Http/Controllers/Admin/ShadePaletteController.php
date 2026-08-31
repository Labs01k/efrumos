<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandId;
use App\Models\GoodsItemId;
use App\Services\Product\ShadePalette;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

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
        $item = Validator::make($request->all(), [
            'shade_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
        ], [
            'shade_photo.mimes' => __('variables.custom_image_mime'),
            'shade_photo.max' => __('variables.custom_image_size'),
        ]);

        if ($item->fails()) {
            return redirect()->back()->with('shade_error', implode(' ', $item->messages()->all()));
        }

        $goods_item = GoodsItemId::findOrFail($id);

        $file_name = $this->storePhoto($goods_item, $request->file('shade_photo'));

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

        $item = Validator::make($request->all(), [
            'shade_photos' => 'required',
            'shade_photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
        ], [
            'shade_photos.*.mimes' => __('variables.custom_image_mime'),
            'shade_photos.*.max' => __('variables.custom_image_size'),
        ]);

        if ($item->fails()) {
            return redirect()->back()->with('shade_error', implode(' ', $item->messages()->all()));
        }

        $brand_id = (int) $request->input('brand');

        // карты соответствия: артикул → товар (весь каталог красок),
        // код оттенка → товар (только выбранная линейка)
        $by_articol = [];
        $by_code = [];
        $code_conflicts = [];

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
        ];

        foreach ((array) $request->file('shade_photos') as $one_file) {
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

            $this->storePhoto($goods_item, $one_file);

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

    /** Сохраняет файл и миниатюру, удаляет старое фото, пишет shade_img. */
    private function storePhoto(GoodsItemId $goods_item, $file): string
    {
        if (!File::exists(self::UPLOAD_DIR)) {
            File::makeDirectory(self::UPLOAD_DIR, 0755, true);
        }
        if (!File::exists(self::UPLOAD_DIR . '/s')) {
            File::makeDirectory(self::UPLOAD_DIR . '/s', 0755, true);
        }

        $this->removePhotoFiles($goods_item->shade_img);

        $file_name = $goods_item->id . '-' . time() . '.' . strtolower($file->getClientOriginalExtension());
        $file->move(self::UPLOAD_DIR, $file_name);

        // миниатюра под свотч в палитре (сохранится как .webp)
        CreateImageManipulator('goods-shades', self::UPLOAD_DIR . '/s/', $file_name, 100, 100);

        $goods_item->update(['shade_img' => $file_name]);

        return $file_name;
    }

    /** Удаляет оригинал и миниатюру фото оттенка. */
    private function removePhotoFiles(?string $file_name): void
    {
        if (!$file_name) {
            return;
        }

        if (File::exists(self::UPLOAD_DIR . '/' . $file_name)) {
            File::delete(self::UPLOAD_DIR . '/' . $file_name);
        }

        if (File::exists(self::UPLOAD_DIR . '/s/' . showImg($file_name))) {
            File::delete(self::UPLOAD_DIR . '/s/' . showImg($file_name));
        }
    }
}
