<?php

namespace App\Http\Controllers\AppApi;

use App\Http\Controllers\Controller;
use App\Models\BannerId;
use App\Models\GoodsItemId;
use App\Models\GoodsSubjectId;
use App\Services\AppApi\ProductPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

/**
 * Каталог для приложения — только чтение. Тот же набор данных, что и на
 * сайте: активные, не удалённые товары с ценой и названием на выбранном
 * языке (X-App-Lang).
 */
class CatalogController extends Controller
{
    private const MAX_PER_PAGE = 50;

    public function categories(): JsonResponse
    {
        $roots = GoodsSubjectId::where('active', 1)
            ->where('deleted', 0)
            ->where('p_id', 0)
            ->has('itemByLang')
            ->with('itemByLang')
            ->orderBy('position', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $this->tree($roots),
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'category' => 'nullable|string|max:191',
            'q' => 'nullable|string|min:2|max:100',
            'in_stock' => 'nullable|boolean',
            'on_sale' => 'nullable|boolean',
            'sort' => 'nullable|in:popular,new,price_asc,price_desc',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:' . self::MAX_PER_PAGE,
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $query = $this->baseQuery();

        if ($category = $request->query('category')) {
            $ids = $this->categoryIds($category);

            if ($ids->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
            }

            $query->whereIn('goods_subject_id', $ids);
        }

        if ($search = $request->query('q')) {
            $like = '%' . addcslashes($search, '%_\\') . '%';

            // EFA-56 / E2-BES-02 — «по названию, бренду, артикулу»: бренд
            // раньше не участвовал в поиске, хотя товар с совпадением
            // только по названию бренда (не по самому названию) не находился.
            $query->where(function ($q) use ($like) {
                $q->whereHas('itemByLang', fn ($name) => $name->where('name', 'like', $like))
                    ->orWhere('articol', 'like', $like)
                    ->orWhere('barcode', 'like', $like)
                    ->orWhereHas('getBrand.itemByLang', fn ($brand) => $brand->where('name', 'like', $like));
            });
        }

        if ($request->boolean('in_stock')) {
            $query->where('in_stoc', 1);
        }

        if ($request->boolean('on_sale')) {
            $query->where('price_promo', '>', 0);
        }

        match ($request->query('sort', 'popular')) {
            'new' => $query->orderByDesc('id'),
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->orderByDesc('popular_element')->orderBy('position'),
        };

        $page = $query
            ->with('itemByLang', 'oImage', 'getBrand.itemByLang')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $page->getCollection()->map(fn ($goods) => ProductPresenter::summary($goods))->values(),
            'meta' => [
                'page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        ]);
    }

    /**
     * EFA-57 / E2-BES-03 — подборки главной (Акции/Хиты/Новинки), тот же
     * набор и порядок, что на сайте (Front\DefaultController::index()):
     * price_promo>0, popular_element=1, new_element=1, тот же сорт из
     * config('custom.sorting.*') и тот же лимит. Заголовок секции — из
     * того же банера, что на сайте (short_descr), не придуманный текст.
     * Пустую секцию не отдаём — пустой карусели в приложении смысла нет.
     */
    public function homeSections(): JsonResponse
    {
        $limit = (int) config('custom.front.products_in_slider');

        $sections = [
            $this->buildHomeSection('mpb-promo-goods', 'promo', fn () => $this->baseQuery()
                ->where('price_promo', '>', 0)
                ->with('itemByLang', 'oImage', 'getBrand.itemByLang')
                ->orderBy('in_stoc', 'desc')
                ->orderBy(config('custom.sorting.sort_promo_goods_slider')[0], config('custom.sorting.sort_promo_goods_slider')[1])
                ->limit($limit)
                ->get()),
            $this->buildHomeSection('mpb-bestseller-goods', 'bestseller', fn () => $this->baseQuery()
                ->where('popular_element', 1)
                ->with('itemByLang', 'oImage', 'getBrand.itemByLang')
                ->orderBy('in_stoc', 'desc')
                ->orderBy(config('custom.sorting.sort_bestseller_goods_slider')[0], config('custom.sorting.sort_bestseller_goods_slider')[1])
                ->limit($limit)
                ->get()),
            $this->buildHomeSection('mpb-new-goods', 'new', fn () => $this->baseQuery()
                ->where('new_element', 1)
                ->with('itemByLang', 'oImage', 'getBrand.itemByLang')
                ->orderBy('in_stoc', 'desc')
                ->orderBy(config('custom.sorting.sort_new_goods_slider')[0], config('custom.sorting.sort_new_goods_slider')[1])
                ->limit($limit)
                ->get()),
        ];

        return response()->json(['success' => true, 'data' => array_values(array_filter($sections))]);
    }

    private function buildHomeSection(string $bannerAlias, string $key, \Closure $productsResolver): ?array
    {
        $products = $productsResolver();

        if ($products->isEmpty()) {
            return null;
        }

        $banner = BannerId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', $bannerAlias)
            ->has('itemByLang')
            ->with('itemByLang')
            ->first();

        return [
            'key' => $key,
            'title' => $banner->itemByLang->short_descr ?? $banner->itemByLang->name ?? '',
            'products' => $products->map(fn ($g) => ProductPresenter::summary($g))->values(),
        ];
    }

    /**
     * EFA-56 / E2-BES-02 — подсказки по мере ввода: несколько категорий +
     * несколько товаров, не полный постраничный список (это уже умеет
     * products() выше). Та же логика поиска, что у сайта
     * (Front\CatalogController::ajaxGoodsSearch), но JSON, не рендер view.
     */
    public function suggest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'q' => 'required|string|min:2|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $like = '%' . addcslashes($request->query('q'), '%_\\') . '%';

        $categories = GoodsSubjectId::where('active', 1)
            ->where('deleted', 0)
            ->whereHas('itemByLang', fn ($name) => $name->where('name', 'like', $like))
            ->with('itemByLang')
            ->orderBy('position')
            ->limit(5)
            ->get();

        $products = $this->baseQuery()
            ->where(function ($q) use ($like) {
                $q->whereHas('itemByLang', fn ($name) => $name->where('name', 'like', $like))
                    ->orWhere('articol', 'like', $like)
                    ->orWhere('barcode', 'like', $like)
                    ->orWhereHas('getBrand.itemByLang', fn ($brand) => $brand->where('name', 'like', $like));
            })
            ->with('itemByLang', 'oImage', 'getBrand.itemByLang')
            ->orderByDesc('popular_element')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->itemByLang->name ?? '',
                ])->values(),
                'products' => $products->map(fn ($g) => ProductPresenter::summary($g))->values(),
            ],
        ]);
    }

    /** {product} — числовой id или alias, как в адресе товара на сайте. */
    public function product(string $product): JsonResponse
    {
        $goods = $this->baseQuery()
            ->where(fn ($q) => ctype_digit($product) ? $q->where('id', (int) $product) : $q->where('alias', $product))
            ->first();

        if (!$goods) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        return response()->json(['success' => true, 'data' => ProductPresenter::detail($goods)]);
    }

    private function baseQuery()
    {
        return GoodsItemId::query()
            ->where('active', 1)
            ->where('deleted', 0)
            ->where('price', '>', 0)
            ->has('itemByLang');
    }

    /** id категории или alias → она сама и все её потомки (товары лежат в листьях). */
    private function categoryIds(string $category): Collection
    {
        $root = GoodsSubjectId::where('active', 1)
            ->where('deleted', 0)
            ->where(fn ($q) => ctype_digit($category) ? $q->where('id', (int) $category) : $q->where('alias', $category))
            ->first();

        if (!$root) {
            return collect();
        }

        $ids = collect([$root->id]);
        $frontier = [$root->id];

        while ($frontier) {
            $children = GoodsSubjectId::where('active', 1)
                ->where('deleted', 0)
                ->whereIn('p_id', $frontier)
                ->pluck('id')
                ->diff($ids);

            $ids = $ids->merge($children);
            $frontier = $children->all();
        }

        return $ids;
    }

    private function tree(Collection $nodes): array
    {
        return $nodes->map(fn ($node) => [
            'id' => $node->id,
            'alias' => $node->alias,
            'name' => $node->itemByLang->name ?? null,
            'children' => $this->tree($node->children),
        ])->values()->all();
    }
}
