<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Basket;
use App\Models\BasketId;
use App\Models\GoodsItemId;
use App\Models\Wish;
use App\Models\WishId;
use App\Services\FacebookAds\FacebookPixelConversion;
use App\Services\GA4\GoogleEcommerce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class WishController extends Controller
{
    //By cookies
    /*public function index(Request $request)
    {
        $view = 'front.pages.product-action.wish';

        $cookie_wish = $request->cookie('wish');
        $wish_items_ids = [];
        $goods_items_list = [];

        if ($cookie_wish) {
            $wish_id = WishId::where('id', $cookie_wish)->first();

            if ($wish_id)
                $wish_items_ids = Wish::where('wish_id', $wish_id->id)
                    ->orderBy('created_at', 'desc')
                    ->pluck('goods_item_id')->toArray();

            $goods_items_list = GoodsItemId::where('active', 1)
                ->where('deleted', 0)
                ->whereIn('id', $wish_items_ids)
                ->with('itemByLang')
                ->with('oImage')
                ->paginate(config('custom.front.products_per_page'));
        }

        $meta = collect([]);
        $meta->meta_static = 'Lista de dorințe' . ' - ' . env('APP_NAME') ?? env('APP_NAME');

        return view($view, get_defined_vars());
    }*/

    /*public function ajaxAddToWish(Request $request)
    {
        $goods_id = intval($request->input('goods_item_id'));
        $cookie_wish = Cookie::get('wish');

        $goods_item_id = GoodsItemId::where('id', $goods_id)
            ->where('active', 1)
            ->where('deleted', 0)
            ->first();

        if (is_null($goods_item_id))
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ]);

        $wish_id = WishId::updateOrCreate(['id' => $cookie_wish], [
            'user_ip' => $request->ip()
        ]);

        if ($wish_id) {
            $wish = Wish::where('goods_item_id', $goods_item_id->id)
                ->where('wish_id', $wish_id->id)
                ->first();

            $if_has_wish_id = $wish ? $wish->id : 0;

            Wish::updateOrCreate(['id' => $if_has_wish_id], [
                'wish_id' => $wish_id->id,
                'goods_item_id' => $goods_item_id->id,
            ]);

            Cookie::queue('wish', $wish_id->id, config('custom.front.cookie_user_remember_time'));
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Wish not found'
            ]);
        }


        return response()->json([
            'status' => true,
            'message' => 'Podusul a fost adăugat cu succes la favorite',
        ]);
    }*/

    /*public function ajaxDestroyWish(Request $request)
    {
        $goods_item_id = $request->input('goods_id');
        $cookie_wish = $request->cookie('wish');

        $wish = Wish::where('goods_item_id', $goods_item_id)
            ->where('wish_id', $cookie_wish)
            ->first();

        if (is_null($wish) || is_null($cookie_wish))
            return response()->json([
                'status' => false,
                'message' => 'Wish item is empty'
            ]);

        Wish::where('goods_item_id', $goods_item_id)
            ->where('wish_id', $cookie_wish)
            ->delete();

        //$count_all_goods = Wish::where('wish_id', $cookie_basket)->sum('items_count');
        $count_all_goods = Wish::where('wish_id', $cookie_wish)->count('id');

        return response()->json([
            'status' => true,
            'wish_count' => $count_all_goods,
            'message' => 'Produsul a fost eliminat cu succes din favorite',
        ]);
    }*/

    //By user
    /*public function wish()
    {
        $view = 'front.pages.wish-list';

        $user_collect = getAuthorizedUser();
        if (!$user_collect && empty($user_collect->user))
            return redirect(LANG . '/login');
        $wish_items_ids = [];
        $goods_items_list = [];

        $wish_id = WishId::where('front_user_id', $user_collect->user->id)->first();
        if ($wish_id)
            $wish_items_ids = Wish::where('wish_id', $wish_id->id)
                ->orderBy('created_at', 'desc')
                ->pluck('goods_item_id')->toArray();

        if ($wish_items_ids)
            $goods_items_list = GoodsItemId::where('active', 1)
                ->where('deleted', 0)
                ->whereIn('goods_item_id.id', $wish_items_ids)
                ->with('itemByLang')
                ->with('oImage')
                ->has('itemByLang')
                ->paginate(config('custom.front.products_per_page'));

        $meta = collect([]);
        $meta->meta_static = 'Wish' . ' - ' . env('APP_NAME') ?? env('APP_NAME');

        return view($view, get_defined_vars());
    }*/

    public function ajaxAddToWish(Request $request)
    {
        $user = app('global_user');
        $goods_item_id = intval($request->input('goods_item_id'));

        if (!$user || !$goods_item_id) {
            return response()->json([
                'status' => false,
                'message' => ShowLabelById(62),
            ]);
        }

        $wish_id = null;
        $wish_id = WishId::where('front_user_id', $user->id)->first();

        if (!$wish_id) {
            $wish_id = new WishId();
            $wish_id->user_ip = request()->ip();
            $wish_id->front_user_id = $user->id;
            $wish_id->save();
        }

        $check_item = Wish::where('wish_id', $wish_id->id)->where('goods_item_id', $goods_item_id)->first();

        $maxPosition = GetMaxPosition('wish');

        if (!$check_item) {
            $new_wish_item = new Wish();
            $new_wish_item->wish_id = $wish_id->id;
            $new_wish_item->goods_item_id = $goods_item_id;
            $new_wish_item->position = $maxPosition + 1;
            $new_wish_item->save();

            $count_wish_items = Wish::where('wish_id', $wish_id->id)->count();

            $goods_object = null;
            if ($new_wish_item->goodsItemId) {
                $goods_price_collect = getGoodsPrice($new_wish_item->goodsItemId);
                //For GA4
                $goods_object = GoogleEcommerce::oneGoodsCollectionToObjects($new_wish_item->goodsItemId);
                //For FB Pixel
                $goods_collect = collect();
                $goods_collect->goods_price = $goods_price_collect->price;
                $goods_collect->goods_item = $new_wish_item->goodsItemId;
                FacebookPixelConversion::pixelEvent('AddToWishlist', $goods_collect);
            }

            return response()->json([
                'status' => true,
                'wish_count' => $count_wish_items,
                'message' => ShowLabelById(60),
                //For GA4
                'goods_object' => json_decode($goods_object)
            ]);
        } else {
            Wish::where('wish_id', $wish_id->id)->where('goods_item_id', $goods_item_id)->delete();

            $count_wish_items = Wish::where('wish_id', $wish_id->id)->count();

            return response()->json([
                'status' => true,
                'wish_count' => $count_wish_items,
                'message' => ShowLabelById(61)
            ]);
        }

    }

    public function ajaxDestroyWish(Request $request)
    {
        $user = app('global_user');
        $goods_item_id = intval($request->input('goods_item_id'));

        if (!$user || !$goods_item_id) {
            return response()->json([
                'status' => false,
                'message' => 'User or goods not found',
            ]);
        }

        $wish_id = WishId::where('front_user_id', $user->id)->first();

        $goods_items_list = [];
        $wish_total_price = 0;
        $wish_total_promo_price = 0;

        if ($wish_id) {
            Wish::where('wish_id', $wish_id->id)
                ->where('goods_item_id', $goods_item_id)
                ->delete();

            $count_wish_items = Wish::where('wish_id', $wish_id->id)
                ->count();

            //Calculate total price
            $wish_items_ids = Wish::where('wish_id', $wish_id->id)
                ->orderBy('created_at', 'desc')
                ->pluck('goods_item_id')->toArray();

            if ($wish_items_ids)
                $goods_items_list = GoodsItemId::where('active', 1)
                    ->where('deleted', 0)
                    ->whereIn('id', $wish_items_ids)
                    ->has('itemByLang')
                    ->with('itemByLang', 'oImage')
                    ->get();

            if (!empty($goods_items_list) && count($goods_items_list)) {
                foreach ($goods_items_list as $one_goods) {
                    $goods_price_collect = getGoodsPrice($one_goods);

                    $wish_total_price += $goods_price_collect->price_default;
                    $wish_total_promo_price += $goods_price_collect->price_promo;
                }
            }
        }

        return response()->json([
            'status' => true,
            'wish_count' => $count_wish_items,
            'wish_total_price' => $wish_total_price,
            'wish_total_promo_price' => $wish_total_promo_price,
            'message' => ShowLabelById(61)
        ]);
    }

    public function ajaxAddAllWishToBasket(Request $request)
    {
        $user = app('global_user');

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User or goods not found',
            ]);
        }

        $wish_id = WishId::where('front_user_id', $user->id)->first();

        $goods_items_list = [];
        $wish_total_price = 0;
        $wish_total_promo_price = 0;

        if ($wish_id) {
            $wish_items_ids = Wish::where('wish_id', $wish_id->id)
                ->orderBy('created_at', 'desc')
                ->pluck('goods_item_id')->toArray();

            if ($wish_items_ids)
                $goods_items_list = GoodsItemId::where('active', 1)
                    ->where('deleted', 0)
                    ->whereIn('id', $wish_items_ids)
                    ->has('itemByLang')
                    ->with('itemByLang', 'oImage')
                    ->get();

            if (!Cookie::get('basket')) {
                $basket_id = new BasketId();
                $basket_id->user_ip = request()->ip();
                $basket_id->save();

                if ($basket_id)
                    Cookie::queue('basket', $basket_id->id, config('custom.front.cookie_user_remember_time'));
            } else
                $basket_id = BasketId::where('id', Cookie::get('basket'))->first();

            if (!empty($goods_items_list) && count($goods_items_list)) {
                foreach ($goods_items_list as $one_goods) {

                    $check_basket_item = Basket::where('basket_id', $basket_id->id)
                        ->where('goods_item_id', $one_goods->id)
                        ->first();

                    if (!$check_basket_item) {
                        $basket = new Basket();
                        $basket->basket_id = $basket_id->id;
                        $basket->goods_item_id = $one_goods->id;
                        $basket->items_count = 1;
                        $basket->goods_name = $one_goods->name;
                        $basket->goods_price = $one_goods->price;
                        $basket->goods_one_c_code = $one_goods->one_c_code;
                        $basket->save();
                    }
                }

                return response()->json([
                    'status' => true,
                    'redirect' => route('cart'),
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'wish_total_price' => $wish_total_price,
            'wish_total_promo_price' => $wish_total_promo_price,
            'message' => ShowLabelById(61)
        ]);
    }

}

