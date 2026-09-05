<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\BannerId;
use App\Models\Brand;
use App\Models\BrandId;
use App\Models\GallerySubjectId;
use App\Models\GoodsItem;
use App\Models\GoodsItemId;
use App\Models\GoodsPageId;
use App\Models\GoodsParametrId;
use App\Models\GoodsParametrItemId;
use App\Models\GoodsParametrItemRsc;
use App\Models\GoodsParametrValueId;
use App\Models\GoodsPromo;
use App\Models\GoodsSubjectId;
use App\Models\GoodsType;
use App\Models\GoodsTypeId;
use App\Models\InfoItemId;
use App\Models\InfoLineId;
use App\Models\MenuId;
use App\Services\FacebookAds\FacebookPixelConversion;
use App\Services\Product\ProductRecommendations;
use App\Services\Product\ProductStock;
use App\Services\Product\ProductVariants;
use App\Services\Product\ShadePalette;
use App\Services\GA4\GoogleEcommerce;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{

    public function index(Request $request, $category = 'catalog', $item = null)
    {
        $goods_subject = null;
        $goods_subject_id = null;
        $catalog_param = null;

        if (is_null($item))
            $goods_subject = GoodsSubjectId::where('alias', $category)
                ->where('active', 1)
                ->where('deleted', 0)
                ->has('itemByLang')
                ->with('itemByLang')
                ->firstOrFail();

        if ($request->routeIs('catalog-product'))
            return $item ? $this->itemPage($item) : $this->productsList($goods_subject);
        elseif ($request->routeIs('category'))
            return $this->productsList($goods_subject);
        else
            abort(404, 'Unauthorized action.');

        /*if ($category && $category != 'product') {
            $goods_subject = GoodsSubjectId::where('alias', $category)
                ->where('active', 1)
                ->where('deleted', 0)
                ->has('itemByLang')
                ->with('itemByLang')
                ->first();

            if (!$goods_subject)
                return abort(404, 'Unauthorized action.');
        }

        if (!$category && !$item) {
            $goods_subject = GoodsSubjectId::where('alias', 'catalog')
                ->where('active', 1)
                ->where('deleted', 0)
                ->has('itemByLang')
                ->with('itemByLang')
                ->first();

            if (!$goods_subject)
                return abort(404, 'Unauthorized action.');

        }

        if (!empty($item))
            return $this->itemPage($goods_subject, $item);
         else
            return $this->productsList($request, $goods_subject);*/

    }

    public function categorySeoPage($alias)
    {
        $goods_meta_page = GoodsPageId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', $alias)
            ->has('itemByLang')
            ->with('itemByLang')
            ->first();

        if (!$goods_meta_page)
            return abort(404, 'Unauthorized action.');

        $goods_subject_alias = $goods_meta_page->link ? parse_url($goods_meta_page->link)['path'] : null;

        if ($goods_subject_alias) {
            $goods_subject = GoodsSubjectId::where('active', 1)
                ->where('deleted', 0)
                ->where('alias', $goods_subject_alias)
                ->has('itemByLang')
                ->with('itemByLang')
                ->firstOrFail();

            return $this->productsList($goods_subject, $goods_meta_page);
        }

        return abort(404, 'Unauthorized action.');
    }

    public function itemPage($item)
    {
        $view = 'front.pages.catalog.detail-item';

        $goods_item = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', $item)
            ->has('itemByLang')
            ->with('itemByLang', 'oImages', 'goodsItemReviews', 'goodsItemReviews.frontUserId', 'checkIfWishItemExist', 'goodsPromoTags')
            ->firstOrFail();

        $goods_subject = $goods_item->goods_subject_id;
        $goods_price_collect = getGoodsPrice($goods_item);
        $promo_color = CheckIfItemIsHasPromoColor($goods_item->id);

        $goods_parameters = ParametrDisplay(getMainCatalogId(), $goods_item->id, LANG_ID);
        //For GA4
        $goods_object = GoogleEcommerce::oneGoodsCollectionToObjects($goods_item);
        //For FB Pixel Api Conversions
        $goods_collect = collect();
        $goods_collect->goods_price = $goods_price_collect->price;
        $goods_collect->goods_item = $goods_item;
        FacebookPixelConversion::pixelEvent('ViewContent', $goods_collect);

        $reviews_count = $goods_item->goodsItemReviews->count();

        //For goods status
        $color_by_status = goodsColorByStatus($goods_item, $goods_price_collect);

        $curr_date = Carbon::now()->format('Y-m-d H:i:s');

        $promo_list = GoodsPromo::where('data_start', '<=', \Carbon\Carbon::now())
            ->where('data_end', '>=', \Carbon\Carbon::now())
            //->whereIn('promo_type', [4,5])
            ->join('goods_promo_items', 'goods_promo_items.goods_promo_id', '=', 'goods_promo.id')
            ->where('goods_promo_items.one_c_id', $goods_item->one_c_code)
            ->select('*', 'goods_promo.id as id')
            ->get();

        $info_line_id_promo = InfoLineId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'promo')
            ->first();


        //dd($info_line_id_promo,!empty($promo_list) && count($promo_list));

        $promo_info_list = [];
        if ($info_line_id_promo && !empty($promo_list) && count($promo_list)) {
            foreach ($promo_list as $one_promo) {

                $promo_info_list[$one_promo->id] = InfoItemId::where('active', 1)
                    ->where('deleted', 0)
                    ->where('is_public', 1)
                    ->where('info_line_id', $info_line_id_promo->id)
                    ->where('goods_promo_id', 'LIKE', '%' . $one_promo->id . '%')
                    ->orderBy('info_item_id.created_at', 'asc')
                    ->first();
            }
        }


        $similare_goods = [];
        if ($goods_item->produse_similare) {
            $similar_products_array = explode(',', $goods_item->produse_similare);
            $similare_goods = GoodsItemId::where('active', 1)
                ->where('deleted', 0)
                ->where('in_stoc', 1)
                ->where('products_count', '>', 0)
                ->whereIn('id', $similar_products_array)
                ->with('itemByLang', 'oImage', 'goodsItemReviews', 'goodsItemReviews.frontUserId', 'checkIfWishItemExist', 'getBrand', 'goodsPromoTags')
                ->orderBy('in_stoc', 'desc')
                ->orderBy(config('custom.sorting.sort_similar_goods_slider')[0], config('custom.sorting.sort_similar_goods_slider')[1])
                ->limit(config('custom.front.products_in_slider'))
                ->get();
        }

        $compatibile_goods = [];
        if ($goods_item->produse_compatibile) {
            $compatibile_products_array = explode(',', $goods_item->produse_compatibile);
            $compatibile_goods = GoodsItemId::where('active', 1)
                ->where('deleted', 0)
                ->where('in_stoc', 1)
                ->where('products_count', '>', 0)
                ->whereIn('id', $compatibile_products_array)
                ->has('itemByLang')
                ->with('itemByLang', 'oImage', 'goodsItemReviews', 'goodsItemReviews.frontUserId', 'checkIfWishItemExist', 'getBrand', 'goodsPromoTags')
                ->orderBy('in_stoc', 'desc')
                ->orderBy(config('custom.sorting.sort_similar_goods_slider')[0], config('custom.sorting.sort_similar_goods_slider')[1])
                ->limit(config('custom.front.products_in_slider'))
                ->get();
        }

        $bestseller_goods = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->where('popular_element', 1)
            ->has('itemByLang')
            ->with('itemByLang', 'oImage', 'goodsItemReviews', 'goodsItemReviews.frontUserId', 'checkIfWishItemExist', 'getBrand', 'getBrand', 'goodsPromoTags')
            ->orderBy('in_stoc', 'desc')
            ->orderBy(config('custom.sorting.sort_bestseller_goods_slider')[0], config('custom.sorting.sort_bestseller_goods_slider')[1])
            ->limit(config('custom.front.products_in_slider'))
            ->get();

        $three_banners_for_goods_page = BannerId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'three-banners-for-goods-page')
            ->with(['children' => function ($q) {
                $q->limit(3);
            }])
            ->first();

        $view_goods = ViewGoods($goods_item->id);

        //For GA4
        $goods_objects_similar = GoogleEcommerce::goodsCollectionsToObjects($similare_goods, null, ['item_list_name' => 'List of similar products on the product page']);
        $goods_objects_compatibile = GoogleEcommerce::goodsCollectionsToObjects($compatibile_goods, null, ['item_list_name' => 'List of related products on the product page']);
        $goods_objects_bestseller = GoogleEcommerce::goodsCollectionsToObjects($bestseller_goods, null, ['item_list_name' => 'List of recommended products on the product page']);

        $blog = InfoLineId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'blog')
            ->has('itemByLang')
            ->with(['itemByLang', 'infoItems' => function ($q) {
                $q->limit(3);
            }])
            ->first();

        $advantages = MenuId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'advantages-under-about-shop')
            ->with(['children' => function ($q) {
                $q->limit(4);
            }])
            ->first();

        $faq_links = MenuId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'faq-goods-page')
            ->with(['children' => function ($q) {
                $q->where('active', 1)
                    ->where('deleted', 0)
                    ->orderBy('position', 'asc')
                    ->has('itemByLang')
                    ->with('itemByLang');
            }])
            ->first();

        //Parameters colors and sizes
        $parameters = GoodsParametrId::where('active', 1)
            ->where('deleted', 0)
            ->with('itemByLang')
            ->has('itemByLang')
            ->where('goods_subject_id', getMainCatalogId())
            ->orderBy('position', 'asc')
            ->get();

        //Get parameter values
        $parameters_value = [];
        if ($parameters) {
            foreach ($parameters as $one_parameter) {
                $parameters_value[$one_parameter->id] = GoodsParametrItemId::where('goods_parametr_id', $one_parameter->id)
                    ->where('goods_item_id', $goods_item->id)
                    ->join('goods_parametr_item_rsc', 'goods_parametr_item_id.id', '=', 'goods_parametr_item_rsc.goods_parametr_item_id')
                    ->get();
            }
        }

        $brand_image_palette = null;
        if ($goods_item->getBrand && $goods_item->getBrand->img_palette) {
            $brand_image_palette = $goods_item->getBrand->img_palette;
        } else {
            if ($goods_item->getBrand && $goods_item->getBrand->parent && $goods_item->getBrand->parent->img_palette) {
                $brand_image_palette = $goods_item->getBrand->parent->img_palette;
            }
        }

        // Блоки страницы товара по новому макету:
        // п.3 «С этим товаром покупают», п.4 «Похожие товары», п.6 палитра оттенков
        $set_goods = ProductRecommendations::boughtTogether($goods_item);
        $similar_goods = ProductRecommendations::similar($goods_item);
        $shades = ShadePalette::for($goods_item);
        // Разметка варианта товара (isVariantOf → ProductGroup) осмысленна только
        // там, где линейка оттенков реально есть: у одиночной краски без палитры
        // группы вариантов не существует, и поисковику её обещать нечего.
        $shade_structured_data = $shades->isNotEmpty()
            ? ShadePalette::structuredData($goods_item, $goods_price_collect)
            : null;
        $volumes = ProductVariants::volumes($goods_item);
        // п.5: пока 1С не отдаёт остатки по складам, коллекция пустая и блок скрыт
        $shops_stock = ProductStock::byShops($goods_item);

        // Превью незавершённых блоков: ?preview=pending подставляет демо-данные
        // в те блоки, под которые ещё нет источника (остатки по магазинам, вкладка
        // «Применение»). На проде флаг не работает.
        $preview_pending = !app()->environment('production') && request('preview') === 'pending';
        $preview_usage = null;
        if ($preview_pending) {
            $shops_stock = ProductStock::demo($goods_item);
            $preview_usage = trans('variables.product_usage_demo');
        }

        //For meta tags
        $meta = $goods_item ?? collect([]);
        if ($meta && $meta->oImage && $meta->oImage->img)
            $meta->current_meta_img = asset('upfiles/goods-items/' . $meta->oImage->img);


        return view($view, get_defined_vars());
    }

    public function productsList($goods_subject, $goods_meta_page = null)
    {
        $view = 'front.pages.catalog.list';
        $request = request();

        //For meta tags
        if ($goods_meta_page) {
            $meta = $goods_meta_page ?? collect([]);
            if ($meta->oImage && $meta->oImage->img)
                $meta->current_meta_img = asset('upfiles/goods-pages/' . $meta->oImage->img);
        } else {
            $meta = $goods_subject ?? collect([]);
            if ($meta && $meta->img_two)
                $meta->current_meta_img = asset('upfiles/goods-subject-meta/' . $meta->img_two);
        }


        $filters_elements = $request->except(['_token', 'page']);
        $filters_elements = array_filter($filters_elements);

        if (!$filters_elements && !$goods_meta_page)
            $filters_elements['in_stoc'] = 'yes';

        $sorting = Cookie::get('sorting');
        $count_per_page = Cookie::get('goods_per_page');

        if (is_null($count_per_page))
            $count_per_page = config('custom.front.products_per_page');

        //For Goods SEO Pages
        if ($goods_meta_page && $goods_meta_page->link && !$filters_elements) {
            $new_url = '?' . parse_url($goods_meta_page->link)['query'];

            $new_url_to_array = explode('&', parse_url($goods_meta_page->link)['query']);

            if (!empty($new_url_to_array) && count($new_url_to_array)) {
                foreach ($new_url_to_array as $one_url_item) {
                    $url_params_to_array = explode('=', $one_url_item);
                    if (!empty($url_params_to_array) && count($url_params_to_array))
                        $filters_elements[$url_params_to_array[0]] = $url_params_to_array[1];
                }
            }
        }

        if (!empty($filters_elements) && count($filters_elements) > 0) {

            $new_filters_elem = [];

            foreach ($filters_elements as $key => $one_filter_elem) {
                $new_filters_elem[$key] = $one_filter_elem;
                if (strpos($one_filter_elem, '[') !== false || strpos($one_filter_elem, ']') !== false) {
                    $new_filters_elem[$key] = explode(',', substr($filters_elements[$key], 1, -1));
                }
            }
            $filters_elements = $new_filters_elem;
        }

        $search = $filters_elements['s'] ?? null;

        $new_url = '';

        if (!empty($filters_elements)) {
            foreach ($filters_elements as $key => $one_filter_el) {

                if (is_array($one_filter_el)) {
                    $new_url_arr = '';
                    foreach ($one_filter_el as $k => $filter_el) {
                        $new_url_arr .= $filter_el . ',';
                    }

                    $new_url .= $key . '=[' . substr($new_url_arr, 0, -1) . ']&';
                } else {
                    $new_url .= $key . '=' . $one_filter_el . '&';
                }
            }

            $new_url = '?' . substr($new_url, 0, -1);
        }

        //For Goods SEO Pages
        if ($goods_meta_page && $goods_meta_page->link) {
            $new_url = '?' . parse_url($goods_meta_page->link)['query'];
        }

        $goods_subject_id = $goods_subject->id;

        $subjects_array = [];
        $all_goods_subjects = [];
        if (!is_null($goods_subject)) {
            GetEndSubjectsList('goods_subject_id', $goods_subject_id, LANG_ID, $all_goods_subjects, 1, 0);
            if (!empty($all_goods_subjects) && count($all_goods_subjects)) {
                foreach ($all_goods_subjects as $one_subject) {
                    array_push($subjects_array, $one_subject->id);
                }

                $goods_subject_list = GoodsSubjectId::where('active', 1)
                    ->where('deleted', 0)
                    ->whereIn('id', $subjects_array)
                    ->has('itemByLang')
                    ->with('itemByLang')
                    ->orderBy('position', 'asc')
                    ->get();
            }
            array_push($subjects_array, $goods_subject_id);
        }

        $goods_items = [];
        $goods_items_list = [];
        $goods_items_list_ids = [];
        $goods_brand_ids = [];
        $goods_subject_ids = [];
        $goods_type_ids = [];
        $goods_parameter_values_ids = [];

        /*if (!is_null($goods_subject))
            $goods_items_list = GetItemsPodborList(LANG_ID, $sorting, $count_per_page, $goods_subject_id ?? null, $filters_elements, $subjects_array);
        else
            $goods_items_list = GetItemsPodborList(LANG_ID, $sorting, $count_per_page, null, $filters_elements);*/

        $goods_items = GetItemsPodborList(LANG_ID, $sorting, $count_per_page, $goods_subject_id ?? null, $filters_elements, $subjects_array);

        if (!empty($goods_items) && count($goods_items)) {
            $goods_items_list = $goods_items['goods_items_paginate'];
            $goods_items_list_ids = $goods_items['goods_items_ids'];
            $goods_brand_ids = $goods_items['goods_brand_ids'];
            $goods_subject_ids = $goods_items['goods_subject_ids'];
            $goods_type_ids = $goods_items['goods_type_ids'];
        }

        //dd($goods_items_list, $goods_items_list_ids);

        if (!empty($goods_items_list_ids) && count($goods_items_list_ids)) {

            $goods_items_list_ids = implode(',', $goods_items_list_ids);

            $goods_parameter_values_ids = GoodsParametrItemRsc::whereRaw('goods_parametr_item_id IN(SELECT id FROM goods_parametr_item_id WHERE goods_item_id IN(' . $goods_items_list_ids . '))')
                ->pluck('goods_parametr_value_id')
                ->toArray();

            if(!empty($goods_parameter_values_ids) && count($goods_parameter_values_ids))
                $goods_parameter_values_ids = array_unique($goods_parameter_values_ids);

            if(!empty($goods_brand_ids) && count($goods_brand_ids))
                $goods_brand_ids = array_unique($goods_brand_ids);

            if(!empty($goods_subject_ids) && count($goods_subject_ids))
                $goods_subject_ids = array_unique($goods_subject_ids);

            if(!empty($goods_type_ids) && count($goods_type_ids))
                $goods_type_ids = array_unique($goods_type_ids);
        }

        //For GA4
        $goods_search_ids_array = [];
        if ($request->filled('s')) {
            $goods_objects = GoogleEcommerce::goodsCollectionsToObjects($goods_items_list, null, ['item_list_name' => 'Search results']);
            //For FB Pixel Api Conversions
            $goods_search_ids_array = json_encode($goods_items_list->pluck('one_c_code')->toArray());
            $goods_collect = collect();
            $goods_collect->search_string = $request->input('s');
            $goods_collect->content_category = 'Product Search';
            $goods_collect->content_ids = $goods_search_ids_array;
            FacebookPixelConversion::pixelEvent('Search', $goods_collect);
        } elseif ($goods_subject && $goods_subject->alias != 'catalog') {
            $goods_objects = GoogleEcommerce::goodsCollectionsToObjects($goods_items_list, null, ['item_list_name' => 'Category - ' . $goods_subject->itemByLang->name]);
        } else {
            $goods_objects = GoogleEcommerce::goodsCollectionsToObjects($goods_items_list, null, ['item_list_name' => 'Catalog all items']);
        }

        //Get the main list of parameters
        $parameters = [];
        $parameters = GoodsParametrId::where('active', 1)
            ->where('deleted', 0)
            ->where('goods_subject_id', getMainCatalogId())
            ->has('itemByLang')
            ->with('itemByLang')
            ->orderBy('position', 'asc')
            ->get();

        if ($parameters) {
            foreach ($parameters as $one_parameter) {
                if (!empty($subjects_array))
                    $parameter_and = 'SELECT DISTINCT `goods_parametr_value_id` FROM `goods_parametr_item_rsc` WHERE `goods_parametr_item_id` IN (SELECT `id` FROM `goods_parametr_item_id` WHERE`goods_parametr_id`=' . $one_parameter->id . ' AND `goods_item_id` IN(SELECT `id` FROM `goods_item_id` WHERE `goods_subject_id` IN(' . implode(',', $subjects_array) . ')))';
                else
                    $parameter_and = 'SELECT DISTINCT `goods_parametr_value_id` FROM `goods_parametr_item_rsc` WHERE `goods_parametr_item_id` IN (SELECT `id` FROM `goods_parametr_item_id` WHERE`goods_parametr_id`=' . $one_parameter->id . ' AND `goods_item_id` IN(SELECT `id` FROM `goods_item_id` WHERE `goods_subject_id`=' . $goods_subject->id . '))';

                $parameter_values[$one_parameter->id] = GoodsParametrValueId::where('active', 1)
                    ->where('goods_parametr_id', $one_parameter->id)
                    ->whereRaw('goods_parametr_value_id.id IN(' . $parameter_and . ')')
                    ->has('itemByLang')
                    ->with('itemByLang')
                    ->orderBy('position', 'asc')
                    ->get();
            }
        }

        //For Brands
        $goods_items_brands_array = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->when($subjects_array, function ($q) use ($subjects_array) {
                return $q->whereIn('goods_subject_id', $subjects_array);
            }, function ($q) use ($goods_subject_id) {
                return $q->where('goods_subject_id', $goods_subject_id);
            })
            ->pluck('brand_id')
            ->toArray();

        $goods_brands_ids_to_str = implode(',', $goods_items_brands_array);

        $goods_brands_l1 = BrandId::where('active', 1)
            ->where('deleted', 0)
            ->whereRaw('(p_id=0 AND (id IN(' . $goods_brands_ids_to_str . ') OR id IN (SELECT p_id FROM goods_brand_id WHERE id IN(' . $goods_brands_ids_to_str . '))))')
            ->has('itemByLang')
            ->with('itemByLang', 'childrenSortByName.itemByLang')
            ->with(['childrenSortByName' => function ($q) use ($goods_items_brands_array) {
                $q->whereIn('id', $goods_items_brands_array);
            }])
            ->orderBy(
                Brand::select('name')
                    ->whereColumn('id', 'goods_brand_id.id')
                    ->orderBy('name', 'asc')
            )
            ->get();
        //End for brand

        //For Goods type
        $goods_items_types_array = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->when($subjects_array, function ($q) use ($subjects_array) {
                return $q->whereIn('goods_subject_id', $subjects_array);
            }, function ($q) use ($goods_subject_id) {
                return $q->where('goods_subject_id', $goods_subject_id);
            })
            ->pluck('goods_type_id')
            ->toArray();

        //$goods_types_ids_to_str = implode(',', $goods_items_types_array);
        $goods_types = GoodsTypeId::whereIn('id', $goods_items_types_array)
            ->has('itemByLang')
            ->with('itemByLang')
            ->orderBy(
                GoodsType::select('name')
                    ->whereColumn('id', 'goods_type_id.id')
                    ->orderBy('name', 'asc')
            )
            ->get();
        //End for goods type

        $search_goods_subject = GoodsSubjectId::where('active', 1)
            ->where('deleted', 0)
            ->with('itemByLang')
            ->whereHas('itemByLang', function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%');
            })
            ->orderBy('position', 'asc')
            ->get();

        $get_max_price = null;
        $min_price = null;
        $max_price = null;

        if ($request->input('min_price'))
            $min_price = $request->input('min_price');

        if ($request->input('max_price'))
            $max_price = $request->input('max_price');

        $goods_subject_ids_array = [];
        if ($goods_subject && $goods_subject->children->isNotEmpty()) {
            $goods_subject_ids_array = $goods_subject->children->pluck('id')->toArray();
        }

        $get_max_price = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->when($goods_subject_ids_array, function ($q) use ($goods_subject_ids_array) {
                $q->whereIn('goods_subject_id', $goods_subject_ids_array);
            }, function ($q) use ($goods_subject) {
                $q->where('goods_subject_id', $goods_subject->id);
            })
            ->max('price');

        if (is_null($get_max_price))
            $get_max_price = config('custom.front.price_range_max_price');

        return view($view, get_defined_vars());

    }

    public function ajaxFilterResults(Request $request)
    {
        $view_ajax = 'front.pages.ajax.products-list';
        $filters_elements = $request->except(['_token', 'data-parent']);
        $filters_elements = array_filter($filters_elements, 'arrayMergeFilter');

        $sorting = Cookie::get('sorting');
        $count_per_page = config('custom.front.products_per_page');

        $new_url = '';

        if (!empty($filters_elements)) {
            foreach ($filters_elements as $key => $one_filter_el) {
                if (is_array($one_filter_el)) {
                    $new_url_arr = '';
                    foreach ($one_filter_el as $k => $filter_el) {
                        $new_url_arr .= $filter_el . ',';
                    }

                    $new_url .= $key . '=[' . substr($new_url_arr, 0, -1) . ']&';
                } else {
                    if ($key != 'order')
                        $new_url .= $key . '=' . $one_filter_el . '&';
                }
            }
            $new_url = '?' . substr($new_url, 0, -1);
        }

        $goods_subject_id_parent = null;

        $parent_subject = $request->input('data-parent');

        if (!is_null($parent_subject)) {
            $goods_subject_id_parent = GoodsSubjectId::where('alias', $parent_subject)
                ->where('active', 1)
                ->where('deleted', 0)
                ->first();

            $goods_subject_id_parent = $goods_subject_id_parent->id;
        } else
            $goods_subject_id_parent = null;


        $goods_items_list = [];
        $goods_items_list_ids = [];
        $goods_brand_ids = [];
        $goods_subject_ids = [];
        $goods_type_ids = [];
        $goods_parameter_values_ids = [];

        $subjects_array = [];
        $all_goods_subjects = [];
        if (!is_null($goods_subject_id_parent)) {
            GetEndSubjectsList('goods_subject_id', $goods_subject_id_parent, LANG_ID, $all_goods_subjects, 1, 0);
            if (!empty($all_goods_subjects) && count($all_goods_subjects)) {
                foreach ($all_goods_subjects as $one_subject) {
                    array_push($subjects_array, $one_subject->id);
                }
            }
        }

        $goods_items = GetItemsPodborList(LANG_ID, $sorting, $count_per_page, $goods_subject_id_parent, $filters_elements, $subjects_array);

        if (!empty($goods_items) && count($goods_items)) {
            $goods_items_list = $goods_items['goods_items_paginate'];
            $goods_items_list_ids = $goods_items['goods_items_ids'];
            $goods_brand_ids = $goods_items['goods_brand_ids'];
            $goods_subject_ids = $goods_items['goods_subject_ids'];
            $goods_type_ids = $goods_items['goods_type_ids'];
        }

        if (!empty($goods_items_list_ids) && count($goods_items_list_ids)) {

            $goods_items_list_ids = implode(',', $goods_items_list_ids);

            $goods_parameter_values_ids = GoodsParametrItemRsc::whereRaw('goods_parametr_item_id IN(SELECT id FROM goods_parametr_item_id WHERE goods_item_id IN(' . $goods_items_list_ids . '))')
                ->pluck('goods_parametr_value_id')
                ->toArray();
        }

        if (!empty($goods_items_list))
            return response()->json([
                'status' => true,
                'messages' => $new_url,
                'goods_items_count' => !empty($goods_items_list) && count($goods_items_list) ? $goods_items_list->total() . ' ' . strtolower(trans_choice('variables.goods', $goods_items_list->total())) : 0,
                'view' => view($view_ajax, compact('goods_items_list', 'new_url'))->render(),
                'goods_parameter_values_ids' => array_unique($goods_parameter_values_ids),
                'goods_brand_ids' => array_unique($goods_brand_ids),
                'goods_subject_ids' => array_unique($goods_subject_ids),
                'goods_type_ids' => array_unique($goods_type_ids),
            ]);

        return response()->json([
            'status' => false
        ]);
    }

    public function ajaxSortPage(Request $request)
    {
        if ($request->input('sort_type') && $request->input('sort_type') == 'sorting') {
            if ($request->input('sort_value')) {
                if (!is_null(Cookie::get('sorting'))) {
                    Cookie::queue(Cookie::forget('sorting'));
                }

                Cookie::queue('sorting', $request->input('sort_value'), config('custom.front.cookie_user_remember_time'));
            } else {
                if (!is_null(Cookie::get('sorting'))) {
                    Cookie::queue(Cookie::forget('sorting'));
                }
            }

            return response()->json([
                'status' => true
            ]);
        }

        if ($request->input('sort_type') && $request->input('sort_type') == 'goods_per_page') {
            if ($request->input('sort_value')) {
                if (!is_null(Cookie::get('goods_per_page'))) {
                    Cookie::queue(Cookie::forget('goods_per_page'));
                }

                Cookie::queue('goods_per_page', $request->input('sort_value'), config('custom.front.cookie_user_remember_time'));
            } else {
                if (!is_null(Cookie::get('goods_per_page'))) {
                    Cookie::queue(Cookie::forget('goods_per_page'));
                }
            }

            return response()->json([
                'status' => true
            ]);
        }

        return response()->json([
            'status' => true
        ]);
    }

    public function ajaxGoodsSearch(Request $request)
    {
        $search_value = $request->input('search_value');

        $search_array_values = explode(' ', $search_value);

        // строка поиска только через биндинги — конкатенация ломалась кавычкой
        $multi_query = '';
        $multi_bindings = [];
        if ($search_array_values) {
            foreach ($search_array_values as $one_value) {
                $multi_query .= ' AND name LIKE ?';
                $multi_bindings[] = '%' . $one_value . '%';
            }

            $multi_query = mb_substr($multi_query, 5);
            $multi_query = '(' . $multi_query . ')';
        }

        $search_goods_subject = GoodsSubjectId::where('active', 1)
            ->where('deleted', 0)
            ->with('itemByLang')
            ->whereHas('itemByLang', function ($q) use ($search_value) {
                $q->where('name', 'LIKE', '%' . $search_value . '%');
            })
            ->orderBy('position', 'asc')
            ->limit(5)
            ->get();

        // п.6 ТЗ — поиск по номеру/коду оттенка: покупатель может набрать
        // номер с «-»/«_»/пробелом вместо «/». Нормализация общая с выдачей
        // каталога (GetItemsPodborList), иначе подсказка и результат
        // по Enter расходятся.
        $shade_code_query = ShadePalette::normalizeShadeQuery($search_value);

        $search_goods_items = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->with('itemByLang')
            ->where(function ($query) use ($multi_query, $multi_bindings, $search_value, $shade_code_query) {
                $query->whereHas('itemByLang', function ($q) use ($multi_query, $multi_bindings) {
                    $q->whereRaw($multi_query, $multi_bindings);
                })
                    ->orWhere('one_c_code', 'like', '%' . $search_value . '%')
                    ->orWhere('articol', 'like', '%' . $search_value . '%');

                if ($shade_code_query) {
                    // код оттенка живёт и в названии («…, 9/76 Блондин…»),
                    // и в артикуле — покрытие по названию заметно выше
                    $query->orWhereHas('itemByLang', function ($q) use ($shade_code_query) {
                        $q->where('name', 'like', '%' . $shade_code_query . '%');
                    })->orWhereRaw(
                        ShadePalette::normalizedColumnSql('articol') . ' LIKE ?',
                        ['%' . $shade_code_query . '%']
                    );
                }
            })
            ->whereRaw('goods_subject_id IN(SELECT id FROM goods_subject_id WHERE active = 1 AND deleted = 0)')
            ->orderBy('position', 'asc')
            ->limit(2)
            ->get();

        $keywords_search_menu_id = MenuId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'search-keywords')
            ->value('id');

        $keywords_search = [];
        if ($keywords_search_menu_id)
            $keywords_search = MenuId::where('active', 1)
                ->where('deleted', 0)
                ->where('p_id', $keywords_search_menu_id)
                ->has('itemByLang')
                ->with('itemByLang')
                ->whereHas('itemByLang', function ($q) use ($search_value) {
                    $q->where('name', 'LIKE', '%' . $search_value . '%');
                })
                ->orderBy('position', 'asc')
                ->get();

        $search_items_view = view('front.pages.ajax.search-list', compact('search_goods_subject', 'search_goods_items', 'keywords_search', 'search_value'))->render();

        return response()->json([
            'status' => true,
            'search_items_view' => $search_items_view
        ]);
    }

    public function ajaxQuickViewGoods(Request $request)
    {
        $goods_item_id = intval($request->input('goods_item_id'));

        $goods_item = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->where('id', $goods_item_id)
            ->has('itemByLang')
            ->with('itemByLang', 'oImages', 'getBrand', 'getBrand.itemByLang', 'getType', 'getType.itemByLang')
            ->first();

        if (!$goods_item_id)
            return response()->json([
                'status' => false,
                'text' => 'Product not found'
            ]);

        $view_ajax = 'front.pages.ajax.goods-quick-view';
        $goods_modal_view = view($view_ajax, ['goods_item' => $goods_item])->render();

        return response()->json([
            'status' => true,
            'goods_modal_view' => $goods_modal_view,
        ]);
    }

    /**
     * Epic 5 — ближайший магазин с наличием товара. Принимает координаты
     * покупателя (браузерная геолокация, как на странице «Магазины»,
     * Epic 2) — считать их на сервере, не доверяя присланному расстоянию
     * с клиента.
     */
    public function ajaxNearestShopWithStock(Request $request)
    {
        $goods_item = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->where('id', (int) $request->input('goods_item_id'))
            ->first();

        $lat = $request->input('lat');
        $lng = $request->input('lng');

        if (!$goods_item || !is_numeric($lat) || !is_numeric($lng)) {
            return response()->json(['status' => false]);
        }

        $nearest = ProductStock::nearestWithStock($goods_item, (float) $lat, (float) $lng);

        if (!$nearest) {
            return response()->json(['status' => false]);
        }

        return response()->json([
            'status' => true,
            'shop_id' => $nearest['shop']->id,
            'name' => $nearest['name'],
            'address' => $nearest['address'],
            'distance_km' => round($nearest['distance_km'], 1),
            'qty' => $nearest['qty'],
        ]);
    }

    /**
     * Epic 6 — оттенки линии как варианты (ТЗ §6.2). Читает кеш
     * product_variants (см. RebuildProductVariants) — не собирает вживую.
     * «Код» — уникальный артикул из 1С; «номер» — тон, разобранный из
     * названия, может повторяться между линиями (см. докблок миграции
     * product_variants и open-decisions.md).
     *
     * Не создаёт новых URL и не заменяет существующие товарные страницы —
     * `url` в ответе указывает на уже существующую страницу того же товара.
     */
    public function ajaxProductVariants(Request $request)
    {
        $goods_item = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->where('id', (int) $request->input('goods_item_id'))
            ->first();

        if (!$goods_item || !$goods_item->brand_id) {
            return response()->json(['status' => false]);
        }

        $variants = DB::table('product_variants')
            ->join('goods_item_id', 'goods_item_id.id', '=', 'product_variants.goods_item_id')
            ->where('product_variants.line_brand_id', $goods_item->brand_id)
            ->orderBy('product_variants.shade_number')
            ->select(
                'product_variants.goods_item_id',
                'product_variants.shade_code',
                'product_variants.shade_number',
                'product_variants.shade_name',
                'product_variants.price',
                'product_variants.products_count',
                'product_variants.in_stoc',
                'goods_item_id.alias'
            )
            ->get()
            ->map(fn ($row) => [
                'goods_item_id' => $row->goods_item_id,
                'shade_code' => $row->shade_code,
                'shade_number' => $row->shade_number,
                'shade_name' => $row->shade_name,
                'price' => $row->price,
                'products_count' => $row->products_count,
                'in_stoc' => (bool) $row->in_stoc,
                'url' => route('catalog-product', ['product', $row->alias]),
            ]);

        return response()->json([
            'status' => true,
            'line_brand_id' => $goods_item->brand_id,
            'variants' => $variants,
        ]);
    }
}

