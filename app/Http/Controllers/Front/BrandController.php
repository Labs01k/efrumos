<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\BrandId;
use App\Models\GoodsItemId;
use App\Models\GoodsParametrId;
use App\Models\GoodsParametrItemRsc;
use App\Models\GoodsParametrValueId;
use App\Models\InfoItemId;
use App\Models\InfoLineId;
use App\Services\GA4\GoogleEcommerce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class BrandController extends Controller
{
    public function index(Request $request, $item = null)
    {
        if ($item) {

            $view = 'front.pages.brand.detail-item';

            $brand_item = BrandId::where('active', 1)
                ->where('deleted', 0)
                ->where('alias', $item)
                ->has('itemByLang')
                ->with('itemByLang', 'oImage')
                ->first();

            if (!$brand_item)
                return abort(404, 'Unauthorized action.');

            $filters_elements = $request->except(['_token', 'page']);
            $filters_elements = array_filter($filters_elements);
            if (!$filters_elements)
                $filters_elements['in_stoc'] = 'yes';
            $filters_elements['brand'] = '[' . $brand_item->id . ']';

            $sorting = Cookie::get('sorting');
            $count_per_page = config('custom.front.products_per_page');

            if (!empty($filters_elements) && count($filters_elements) > 0) {

                $new_filters_elem = [];

                foreach ($filters_elements as $key => $one_filter_elem) {
                    $new_filters_elem[$key] = $one_filter_elem;
                    if (strpos($one_filter_elem, '[') !== false || strpos($one_filter_elem, ']' !== false)) {
                        $new_filters_elem[$key] = explode(',', substr($filters_elements[$key], 1, -1));
                    }
                }

                $filters_elements = $new_filters_elem;

            }

            $search = @$filters_elements['s'];


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

            //Get the main list of parameters
            $parameters = [];
            $parameters = GoodsParametrId::where('active', 1)
                ->where('deleted', 0)
                ->where('goods_subject_id', getMainCatalogId())
                ->has('itemByLang')
                ->with('itemByLang')
                ->orderBy('position', 'asc')
                ->get();

            $parameter_values = [];
            if (!empty($parameters) && count($parameters)) {
                foreach ($parameters as $one_parameter) {
                    $parameter_values[$one_parameter->id] = GoodsParametrValueId::where('active', 1)
                        ->where('goods_parametr_id', $one_parameter->id)
                        ->whereRaw('goods_parametr_value_id.id IN(SELECT DISTINCT goods_parametr_value_id FROM goods_parametr_item_rsc WHERE goods_parametr_item_id IN (SELECT id FROM goods_parametr_item_id WHERE goods_parametr_id=' . $one_parameter->id . ' AND goods_item_id IN(SELECT id FROM goods_item_id WHERE brand_id =' . $brand_item->id . ')))')
                        ->has('itemByLang')
                        ->with('itemByLang')
                        ->orderBy('position', 'asc')
                        ->get();
                }
            }

            if ($filters_elements || $sorting)
                $goods_items = GetItemsPodborList(LANG_ID, $sorting, $count_per_page, null, $filters_elements);
                //$goods_items = GetItemsPodborList(LANG_ID, $sorting, $count_per_page, $goods_subject_id_parent, $filters_elements, $subjects_array);

            if (!empty($goods_items) && count($goods_items)) {
                $goods_items_list = $goods_items['goods_items_paginate'];
                $goods_items_list_ids = $goods_items['goods_items_ids'];
            }

            $goods_parameter_values_ids = [];

            if (!empty($goods_items_list_ids) && count($goods_items_list_ids)) {

                $goods_items_list_ids = implode(',', $goods_items_list_ids);

                $goods_parameter_values_ids = GoodsParametrItemRsc::whereRaw('goods_parametr_item_id IN(SELECT id FROM goods_parametr_item_id WHERE goods_item_id IN(' . $goods_items_list_ids . '))')
                    ->pluck('goods_parametr_value_id')
                    ->toArray();

                if(!empty($goods_parameter_values_ids) && count($goods_parameter_values_ids))
                    $goods_parameter_values_ids = array_unique($goods_parameter_values_ids);
            }

            $get_max_price = null;
            $min_price = null;
            $max_price = null;

            if ($request->get('min_price'))
                $min_price = $request->input('min_price');

            if ($request->input('max_price'))
                $max_price = $request->input('max_price');

            /*$get_max_price = GoodsItemId::where('brand_id', $brand_item->id)
                ->where('active', 1)
                ->where('deleted', 0)
                ->max('price');*/

            $brands_ids_array = [];
            if ($brand_item && $brand_item->children->isNotEmpty()) {
                $brands_ids_array = $brand_item->children->pluck('id')->toArray();
            }

            $get_max_price = GoodsItemId::where('active', 1)
                ->where('deleted', 0)
                ->when($brands_ids_array, function ($q) use ($brands_ids_array) {
                    $q->whereIn('brand_id', $brands_ids_array);
                }, function ($q) use ($brand_item) {
                    $q->where('brand_id', $brand_item->id);
                })
                ->max('price');

            if (is_null($get_max_price))
                $get_max_price = config('custom.front.price_range_max_price');

            //For GA4
            $goods_objects = GoogleEcommerce::goodsCollectionsToObjects($goods_items_list,null, ['item_list_name' => 'Brand - ' . $brand_item->itemByLang->name]);

            $meta = collect([]);
            $meta = $brand_item ?? collect([]);
            $meta->current_meta_img = $meta->oImage && $meta->oImage->img ? asset('upfiles/brand/' . $meta->oImage->img) : '';

        } else {

            $view = 'front.pages.brand.list';

            $segment_2 = $request->segment(2);
            $menu_id = getItemByAlias($segment_2, 'MenuId');

            $brands_l1 = BrandId::where('active', 1)
                ->where('deleted', 0)
                ->where('p_id', 0)
                ->has('itemByLang')
                ->with('itemByLang', 'oImage', 'children')
                ->orderBy('position')
                ->get();

            $meta = collect([]);
            $meta = $menu_id ?? collect([]);

        }

        return view($view, get_defined_vars());
    }
}

