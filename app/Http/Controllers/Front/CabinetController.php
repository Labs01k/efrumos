<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Basket;
use App\Models\BasketId;
use App\Models\DeliveryState;
use App\Models\GoodsItemId;
use App\Models\Orders;
use App\Models\Wish;
use App\Models\WishId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CabinetController extends Controller
{
    public function cabinetOrders()
    {
        $view = 'front.pages.cabinet.orders';

        $user = app('global_user');

        $user_orders = Orders::where('active', 1)
            ->where('front_user_id', $user->id)
            ->with('ordersData')
            ->with('basket.oImage')
            ->with('basket.goodsItemId.itemByLang')
            ->with('basket.goodsItemId.getSubjectId')
            ->orderBy('created_at', 'desc')
            ->get();

        $cabinet_menu_links = getItemWithChildrenByAlias('for-cabinet', 'MenuId');

        $meta = collect([]);
        $meta->meta_static = ShowLabelById(153) . ' - ' . env('APP_NAME') ?? env('APP_NAME');

        return view($view, get_defined_vars());
    }

    public function ajaxShowOrderDetails(Request $request)
    {
        $order_id = intval($request->input('order_id'));

        if (!$order_id)
            return response()->json([
                'status' => false,
                'text' => 'Order not found',
            ]);

        $order = Orders::where('active', 1)
            ->where('id', $order_id)
            ->with('ordersData', 'ordersUsers', 'basket', 'ordersFrontUser')
            ->first();

        if ($order && $order->ordersFrontUser)
            $user = $order->ordersFrontUser;
        else
            $user = $order->ordersUsers;

        $user_district = $user ? getItemByIdSimple($user->district_id, 'DeliveryState') : ($order->ordersUsers ? getItemByIdSimple($order->ordersUsers->district_id, 'DeliveryState') : '');

        $view_ajax = 'front.pages.ajax.show-order-details';
        $goods_order_details_view = view($view_ajax, ['order' => $order, 'user' => $user, 'user_district' => $user_district])->render();

        return response()->json([
            'status' => true,
            'goods_order_details_view' => $goods_order_details_view,
        ]);
    }

    public function ajaxRepeatOrder(Request $request)
    {
        $order_id = intval($request->input('order_id'));

        if (!$order_id)
            return response()->json([
                'status' => false,
                'text' => 'Order not found',
            ]);

        $order = Orders::where('active', 1)
            ->where('id', $order_id)
            ->with('ordersData', 'ordersUsers', 'basket', 'basket.goodsItemId.ItemByLang', 'ordersFrontUser')
            ->first();

        if ($order && $order->basket->isNotEmpty()) {

            $basket_id = new BasketId();
            $basket_id->user_ip = $request->ip();
            $basket_id->save();

            Cookie::queue('basket', $basket_id->id, config('custom.front.cookie_user_remember_time'));

            foreach ($order->basket as $one_order_item) {

                if ($one_order_item->goodsItemId) {
                    $save_basket = new Basket();
                    $save_basket->basket_id = $basket_id->id;
                    $save_basket->goods_item_id = $one_order_item->goodsItemId->id;
                    $save_basket->items_count = $one_order_item->items_count;
                    $save_basket->goods_price = $one_order_item->goodsItemId->price;
                    $save_basket->goods_name = $one_order_item->goodsItemId->itemByLang->name;
                    $save_basket->goods_one_c_code = $one_order_item->goodsItemId->one_c_code;
                    $save_basket->save();
                } else
                    return response()->json([
                        'status' => false,
                        'text' => 'Goods item not found'
                    ]);
            }

            return response()->json([
                'status' => true,
                'redirect' => route('cart')
            ]);

        } else
            return response()->json([
                'status' => false,
                'text' => 'Goods not found',
            ]);
    }

    public function cabinetWish()
    {
        $view = 'front.pages.cabinet.wish';

        $user = app('global_user');

        $wish_id = WishId::where('front_user_id', $user->id)->first();

        $wish_items_ids = [];
        $goods_items_list = [];

        if ($wish_id)
            $wish_items_ids = Wish::where('wish_id', $wish_id->id)
                ->orderBy('created_at', 'desc')
                ->pluck('goods_item_id')->toArray();

        if ($wish_items_ids)
            $goods_items_list = GoodsItemId::where('active', 1)
                ->where('deleted', 0)
                ->whereIn('id', $wish_items_ids)
                ->has('itemByLang')
                ->with('itemByLang', 'oImage')
                ->orderBy('in_stoc', 'desc')
                ->orderBy('position', 'asc')
                ->get();

        //Calculate total price
        $wish_total_price = 0;
        $wish_total_promo_price = 0;
        if (!empty($goods_items_list) && count($goods_items_list)) {
            foreach ($goods_items_list as $one_goods) {
                $goods_price_collect = getGoodsPrice($one_goods);

                $wish_total_price += $goods_price_collect->price_default;
                $wish_total_promo_price += $goods_price_collect->price_promo;
            }
        }

        $meta = collect([]);
        $meta->meta_static = ShowLabelById(154) . ' - ' . env('APP_NAME') ?? env('APP_NAME');

        return view($view, get_defined_vars());

    }

    public function cabinetProfile()
    {
        $view = 'front.pages.cabinet.profile';

        $month_list = showSettingBodyByAlias('month-list') ? explode(';', showSettingBodyByAlias('month-list')) : [];

        $districts = DeliveryState::where('active', 1)
            ->where('country_id', 140) //140 Moldova
            ->orderBy('name', 'asc')
            ->get();

        $meta = collect([]);
        $meta->meta_static = ShowLabelById(151) . ' - ' . env('APP_NAME') ?? env('APP_NAME');

        return view($view, get_defined_vars());

    }

    public function ajaxUpdateProfile(Request $request)
    {
        $user = app('global_user');

        $rules = [
            'name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'phone' => 'required|min:6|max:50',
            'gender' => 'required',
            'district_id' => 'required',
            'city' => 'required',
            'address' => 'required|min:2|max:100',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json([
                'status' => false,
                'messages' => $validator->messages(),
            ]);

        $birth_day = $request->input('birth_day') ?? null;
        $birth_month = $request->input('birth_month') ?? null;
        $birth_year = $request->input('birth_year') ?? null;
        $birth = $birth_day && $birth_month && $birth_year ? $birth_day . '-' . $birth_month . '-' . $birth_year : null;

        $user->name = $request->input('name');
        $user->last_name = $request->input('last_name');
        $user->phone = $request->input('phone');
        $user->gender = $request->input('gender');
        $user->district_id = $request->input('district_id');
        $user->city = $request->input('city');
        $user->address = $request->input('address');
        $user->birth = $user->birth ? $user->birth : ($birth ? $birth : null);
        $user->save();

        return response()->json([
            'status' => true,
            'remove_inputs_value' => 0,
            'message' => ShowLabelById(88),
            'redirect' => route('cabinet-profile')
        ]);
    }

    public function cabinetPassword()
    {
        $view = 'front.pages.cabinet.password';

        $user = app('global_user');

        if($user->facebook_id || $user->google_id)
            return redirect(route('cabinet-profile'));


        $meta = collect([]);
        $meta->meta_static = ShowLabelById(155) . ' - ' . env('APP_NAME') ?? env('APP_NAME');

        return view($view, get_defined_vars());

    }

    public function ajaxUpdatePassword(Request $request)
    {
        $user = app('global_user');

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            //'new_pass' => 'required|confirmed|min:6',
            'password' => [
                'required',
                'string',
                Password::min(6) //Require at least 8 characters
                ->mixedCase() //Require at least one uppercase and one lowercase letter
                ->numbers() // Require at least one number
                ->letters(), //Require at least one letter
                //->symbols() //Require at least one symbol
                //->uncompromised(),
                'confirmed'
            ],
        ]);

        if ($validator->fails())
            return response()->json([
                'status' => false,
                'messages' => $validator->messages(),
            ]);

        if (!empty($request->input('current_password'))) {

            if ($user && Hash::check($request->input('current_password'), $user->password) == false) {
                $user = null;
            }

            if ($user == null)
                return response()->json([
                    'status' => false,
                    'message' => ShowLabelById(96),
                ]);

            $user->password = bcrypt($request->input('password'));
            $user->save();

            return response()->json([
                'status' => true,
                'message' => ShowLabelById(97),
                'remove_inputs_value' => 1,
                'redirect' => route('cabinet-password')
            ]);
        }
    }

}

