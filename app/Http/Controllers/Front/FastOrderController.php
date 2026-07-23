<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Basket;
use App\Models\BasketId;
use App\Models\GoodsItemId;
use App\Models\Orders;
use App\Models\OrdersData;
use App\Models\OrdersUsers;
use App\Services\AmoOrder\SendOrderToAmoCrm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class FastOrderController extends Controller
{
    public function ajaxNewFastOrder(Request $request, SendOrderToAmoCrm $sendOrderToAmoCrm)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'phone' => 'required|min:6|max:18',
            'agree' => 'required'
        ]);

        if ($validator->fails())
            return response()->json([
                'status' => false,
                'messages' => $validator->messages(),
            ]);

        if (reCaptchaVersionThree($request->input('g-recaptcha-response')) == false)
            return response()->json([
                'status' => false,
                'messages' => ['Spam'],
            ]);

        $goods_item_id = intval($request->input('goods_item_id'));
        $user = app('global_user');

        $goods_item = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->where('id', $goods_item_id)
            ->has('itemByLang')
            ->with('itemByLang')
            ->first();

        if (!$goods_item)
            return response()->json([
                'status' => false,
                'text' => 'Product not found',
            ]);

        $goods_price_collect = getGoodsPrice($goods_item);

        $basket_id = new BasketId();
        $basket_id->user_ip = $request->ip();
        $basket_id->save();

        if ($basket_id) {
            $basket = new Basket();
            $basket->basket_id = $basket_id->id;
            $basket->goods_item_id = $goods_item->id;
            $basket->items_count = $request->input('fast_order_item_count');
            $basket->goods_name = $goods_item->itemByLang->name ?? '';
            $basket->goods_price = $goods_price_collect->price;
            $basket->goods_one_c_code = $goods_item->one_c_code;
            $basket->save();

            $order_new = new Orders();
            $order_new->basket_id = $basket_id->id;

            if ($user)
                $order_new->front_user_id = $user->id;

            $order_new->fast_order = 1;
            $order_new->save();

            $orders_data = new OrdersData();
            $orders_data->orders_id = $order_new->id;
            $orders_data->total_price = $goods_price_collect->price * $request->input('fast_order_item_count');
            $orders_data->total_count = $request->input('fast_order_item_count');
            $orders_data->save();

            $orders_users = new OrdersUsers();
            $orders_users->orders_id = $order_new->id;
            $orders_users->user_ip = $basket_id->user_ip;
            if (!$user) {
                $orders_users->name = $request->input('name');
                $orders_users->last_name = $request->input('last_name');
                $orders_users->phone = $request->input('phone');
            }
            $orders_users->save();
        }

        if ($order_new->email_sent == 0) {

            $user_info = $user ?: $orders_users;
            $email_message = getItemByAlias('email-success-message','MenuId');
            $emails_array = explode(',', showSettingBodyByAlias('email-phone'));
            $subject = str_replace('{order_id}', $order_new->id, $email_message->itemByLang->h1_title) ?? ShowLabelById(46);

            $if_admin = 1;
            if (!empty($emails_array) && count($emails_array)) {
                foreach ($emails_array as $one_email) {
                    $one_email = trim($one_email);
                    if (filter_var($one_email, FILTER_VALIDATE_EMAIL)) {
                        Mail::send('front.email.emailNewFastOrder', ['orders_users' => $orders_users, 'orders' => $order_new, 'orders_data' => $orders_data, 'basket' => $basket, 'if_admin' => $if_admin, 'user_info' => $user_info, 'email_message' => $email_message], function ($message) use ($one_email, $subject) {
                            $message->from(showSettingBodyByAlias('send-email-from'), ShowLabelById(46));
                            $message->to($one_email);
                            $message->subject($subject);
                        });
                    }
                }
            }

            OrdersData::where('id', $order_new->id)->update(['email_sent' => 1]);
        }

        //For AMO CRM
        $sendOrderToAmoCrm->sendOrderToAmoCrm($order_new, $orders_data, $orders_users, $user_info);

        return response()->json([
            'status' => true,
            'message' => ShowLabelById(251),
            //'redirect' => $request->input('current_url'),
        ]);
    }
}

