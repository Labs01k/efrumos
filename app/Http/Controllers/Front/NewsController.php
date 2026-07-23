<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\GoodsItemId;
use App\Models\InfoItemId;
use App\Models\InfoLineId;
use App\Services\GA4\GoogleEcommerce;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request, $item = null)
    {

        if ($item) {

            $view = 'front.pages.news.detail-item';
            $segment_2 = $request->segment(2);

            $news_item = InfoItemId::where('active', 1)
                ->where('deleted', 0)
                ->where('alias', $item)
                ->with('itemByLang', 'oImage')
                ->first();

            if (!$news_item)
                return abort(404, 'Unauthorized action.');

            $bestseller_goods = GoodsItemId::where('active', 1)
                ->where('deleted', 0)
                ->where('popular_element', 1)
                ->with('itemByLang', 'oImage', 'getBrand.itemByLang')
                ->orderBy('in_stoc', 'desc')
                ->orderBy(config('custom.sorting.sort_bestseller_goods_slider')[0], config('custom.sorting.sort_bestseller_goods_slider')[1])
                ->limit(config('custom.front.products_in_slider'))
                ->get();

            $related_products_ids = $news_item->goods_list ? explode(',', $news_item->goods_list) : [];
            $related_products = [];
            if (!empty($related_products_ids) && count($related_products_ids))
                $related_products = GoodsItemId::where('active', 1)
                    ->where('deleted', 0)
                    ->whereIn('id', $related_products_ids)
                    ->with('itemByLang', 'oImage', 'getBrand.itemByLang')
                    ->orderBy('in_stoc', 'desc')
                    ->orderBy(config('custom.sorting.sort_bestseller_goods_slider')[0], config('custom.sorting.sort_bestseller_goods_slider')[1])
                    ->limit(config('custom.front.products_in_slider'))
                    ->get();

            $similar_news_list = InfoLineId::where('active', 1)
                ->where('deleted', 0)
                ->where('alias', $segment_2)
                ->has('itemByLang')
                ->with(['itemByLang', 'infoItems' => function ($q) use ($news_item) {
                    $q->where('id', '!=', $news_item->id)
                        ->limit(config('custom.front.info_items_in_slider'));
                }])
                ->first();

            $goods_objects_bestseller = GoogleEcommerce::goodsCollectionsToObjects($bestseller_goods, null,['item_list_name' => 'List of recommended products on news page - ' . $news_item->itemByLang->name]);

            $meta = collect([]);
            $meta = $news_item ?? collect([]);
            $meta->current_meta_img = asset('upfiles/info-items/' . $meta->oImage->img);

        } else {

            $view = 'front.pages.news.list';

            $segment_2 = $request->segment(2);
            $menu_id = getItemByAlias($segment_2, 'MenuId');

            $info_line_id = InfoLineId::where('active', 1)
                ->where('deleted', 0)
                ->where('alias', $segment_2)
                ->value('id');

            if (!$info_line_id)
                return abort(404, 'Unauthorized action.');

            $news_list = [];
            $news_list = InfoItemId::where('active', 1)
                ->where('deleted', 0)
                ->where('info_line_id', $info_line_id)
                ->with('itemByLang', 'oImage')
                ->orderBy('add_date', 'desc')
                ->paginate(config('custom.front.news_items_per_page'));

            $meta = collect([]);
            $meta = $menu_id ?? collect([]);

        }

        return view($view, get_defined_vars());
    }
}

