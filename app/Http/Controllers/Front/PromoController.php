<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\GoodsItemId;
use App\Models\GoodsSubjectId;
use App\Models\InfoItemId;
use App\Models\InfoLineId;
use App\Models\MenuId;
use App\Services\GA4\GoogleEcommerce;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index(Request $request, $item = null)
    {
        if ($item) {

            $view = 'front.pages.promo.detail-item';

            $promo_item = InfoItemId::where('active', 1)
                ->where('deleted', 0)
                ->where('alias', $item)
                ->has('itemByLang')
                ->with('itemByLang', 'oImage', 'getGoodsPromoId')
                ->first();

            if (!$promo_item)
                return abort(404, 'Unauthorized action.');

            $promo_goods_ids_array = explode(',', $promo_item->goods_list);

            $promo_goods = GoodsItemId::where('active', 1)
                ->where('deleted', 0)
                ->whereIn('goods_item_id.id', $promo_goods_ids_array)
                ->has('itemByLang')
                ->with('itemByLang', 'oImage', 'getBrand', 'getBrand.itemByLang')
                ->orderBy('in_stoc', 'desc')
                ->orderBy('position', 'asc')
                ->limit(config('custom.front.products_in_slider'))
                ->get();

            //For GA4
            $goods_objects = GoogleEcommerce::goodsCollectionsToObjects($promo_goods,null, ['promotion_name' => $promo_item->itemByLang->name, 'promotion_id' => $promo_item->id]);
            $promo_goods_objects = GoogleEcommerce::goodsCollectionsToObjects($promo_goods,null, ['item_list_name' => 'List of promo products on promo page - ' . $promo_item->itemByLang->name]);

            $view_goods = ViewGoods();

            $meta = collect([]);
            $meta = $promo_item ?? collect([]);
            if ($meta && $meta->oImage && $meta->oImage->img)
                $meta->current_meta_img = asset('upfiles/info-items/' . $meta->oImage->img);

        } else {

            $view = 'front.pages.promo.list';

            $segment_2 = $request->segment(2);
            $menu_id = getItemByAlias($segment_2, 'MenuId');

            $info_line_id = InfoLineId::where('active', 1)
                ->where('deleted', 0)
                ->where('alias', $segment_2)
                ->value('id');

            $promo_list = [];
            if($info_line_id){
                $promo_list = InfoItemId::where('active', 1)
                    ->where('deleted', 0)
                    ->where('info_line_id', $info_line_id)
                    ->has('itemByLang')
                    ->with('itemByLang', 'oImage', 'getGoodsPromoId')
                    ->orderBy('position', 'asc')
                    ->paginate(config('custom.front.promo_items_per_page'));
            }

            $goods_subject_l1 = GoodsSubjectId::where('active', 1)
                ->where('deleted', 0)
                ->where('p_id', 1)
                ->has('itemByLang')
                ->has('promoGoodsItems')
                ->with('itemByLang', 'promoGoodsItems')
                ->orderBy('position_promo', 'asc')
                ->get();

            $advantages = MenuId::where('active', 1)
                ->where('deleted', 0)
                ->where('alias', 'advantages-under-about-shop')
                ->with(['children' => function ($q) {
                    $q->limit(4);
                }])
                ->first();

            $view_goods = ViewGoods();


            $meta = collect([]);
            $meta = $menu_id ?? collect([]);
        }

        return view($view, get_defined_vars());
    }

    public function ga4PromoClick(Request $request)
    {
        $promo_id = intval($request->input('promo_id'));

        if (!$promo_id)
            return response()->json([
                'status' => false,
                'text' => 'Promo not found',
            ]);

        $promo_item = InfoItemId::where('active', 1)
            ->where('deleted', 0)
            ->where('id', $promo_id)
            ->has('itemByLang')
            ->with('itemByLang')
            ->first();

        if(!$promo_item)
            return response()->json([
                'status' => false,
                'text' => 'Promo item not found',
            ]);

        $promo_goods_ids_array = explode(',', $promo_item->goods_list);

        $promo_goods = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->whereIn('goods_item_id.id', $promo_goods_ids_array)
            ->has('itemByLang')
            ->with('itemByLang')
            ->orderBy('position', 'asc')
            ->get();

        //For GA4
        $promo_goods_objects = GoogleEcommerce::goodsCollectionsToObjects($promo_goods,null, ['promotion_name' => $promo_item->itemByLang->name, 'promotion_id' => $promo_item->id]);

        return response()->json([
            'status' => true,
            'promo_goods_objects' => json_decode($promo_goods_objects),
        ]);
    }
}

