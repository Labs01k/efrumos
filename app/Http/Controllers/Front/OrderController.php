<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Basket;
use App\Models\BasketId;
use App\Models\DeliveryState;
use App\Models\FrontUser;
use App\Models\GoodsItemId;
use App\Models\Orders;
use App\Models\OrdersData;
use App\Models\OrdersUsers;
use App\Jobs\Integration\SubmitOrderToIntegrationLayerJob;
use App\Services\AmoOrder\SendOrderToAmoCrm;
use App\Services\FacebookAds\FacebookPixelConversion;
use App\Services\GA4\GoogleEcommerce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function ajaxNewOrder(Request $request, SendOrderToAmoCrm $sendOrderToAmoCrm)
    {
        $order_type = (string)$request->input('order_type');

        switch ($order_type) {
            case 'new':

                //For confirmation user
                /*$user = FrontUser::where('email', $request->input('email'))
                    ->where('confirmation', 0)
                    ->whereNotNull('confirmation_hash')
                    ->first();

                if ($user)
                    return response()->json([
                        'status' => 'warning',
                        'message' => str_replace('{link_confirmation}', '<a style="color: #ffffff;text-decoration:underline;font-weight:bold;" href="' . route('resend-user-confirmation', $user->confirmation_hash) . '">' . ShowLabelById(201) . '</a>', showLabelById(200))
                    ]);*/

                if ($request->input('delivery_method') && $request->input('delivery_method') == 'delivery') {

                    $item = Validator::make($request->all(), [
                        'name' => 'required|min:2|max:30',
                        'last_name' => 'required|min:2|max:30',
                        'phone' => 'required|numeric|min:8',
                        'email' => 'required|email|unique:front_user,email|max:255',
                        'district_id' => 'required',
                        'city' => 'required',
                        'address' => 'required|min:2|max:100',
                        'pay_method' => [
                            'required',
                            Rule::in(['cash', 'card']),
                        ],
                        'delivery_method' => [
                            'required',
                            Rule::in(['pickup', 'delivery', 'nova_courier', 'nova_terminal']),
                        ],
                        'pickup_shop_id' => [
                            'nullable',
                            'required_if:delivery_method,pickup',
                            'exists:shops_id,id',
                        ],
                        'agree' => 'required'
                    ]);
                } else {
                    $item = Validator::make($request->all(), [
                        'name' => 'required|min:2|max:30',
                        'last_name' => 'required|min:2|max:30',
                        'phone' => 'required|numeric|min:8',
                        'email' => 'required|email|unique:front_user,email|max:255',
                        'pay_method' => [
                            'required',
                            Rule::in(['cash', 'card']),
                        ],
                        'delivery_method' => [
                            'required',
                            Rule::in(['pickup', 'delivery', 'nova_courier', 'nova_terminal']),
                        ],
                        'pickup_shop_id' => [
                            'nullable',
                            'required_if:delivery_method,pickup',
                            'exists:shops_id,id',
                        ],
                        'agree' => 'required'
                    ]);
                }

                if ($item->fails())
                    return response()->json([
                        'status' => false,
                        'messages' => $item->messages(),
                    ]);

                $birth_day = $request->input('birth_day') ?? null;
                $birth_month = $request->input('birth_month') ?? null;
                $birth_year = $request->input('birth_year') ?? null;
                $birth = $birth_day && $birth_month && $birth_year ? $birth_year . '-' . $birth_month . '-' . $birth_day : null;

                $new_user = new FrontUser();
                $new_user->last_name = $request->input('last_name');
                $new_user->name = $request->input('name');
                $new_user->email = $request->input('email');
                $new_user->phone = $request->input('phone');
                $new_user->district_id = $request->input('district_id');
                $new_user->city = $request->input('city');
                $new_user->address = $request->input('address');
                $new_user->birth = $birth ?? null;
                $new_user->password = bcrypt($request->input('password'));
                $new_user->confirmation = 1;
                $new_user->save();
                //Обновить и адресс

                $user = $new_user;
                break;

            case 'already':

                if ($request->input('delivery_method') && $request->input('delivery_method') == 'delivery') {

                    $item = Validator::make($request->all(), [
                        'name' => 'required|min:2|max:30',
                        'last_name' => 'required|min:2|max:30',
                        'phone' => 'required|numeric|min:8',
                        //'email' => 'required|email|unique:front_user,email|max:255',
                        'district_id' => 'required',
                        'city' => 'required',
                        'address' => 'required|min:2|max:100',
                        'pay_method' => [
                            'required',
                            Rule::in(['cash', 'card']),
                        ],
                        'delivery_method' => [
                            'required',
                            Rule::in(['pickup', 'delivery', 'nova_courier', 'nova_terminal']),
                        ],
                        'pickup_shop_id' => [
                            'nullable',
                            'required_if:delivery_method,pickup',
                            'exists:shops_id,id',
                        ],
                        'agree' => 'required'
                    ]);
                } else {

                    $item = Validator::make($request->all(), [
                        'name' => 'required|min:2|max:30',
                        'last_name' => 'required|min:2|max:30',
                        'phone' => 'required|numeric|min:8',
                        //'email' => 'required|email|unique:front_user,email|max:255',
                        'pay_method' => [
                            'required',
                            Rule::in(['cash', 'card']),
                        ],
                        'delivery_method' => [
                            'required',
                            Rule::in(['pickup', 'delivery', 'nova_courier', 'nova_terminal']),
                        ],
                        'pickup_shop_id' => [
                            'nullable',
                            'required_if:delivery_method,pickup',
                            'exists:shops_id,id',
                        ],
                        'agree' => 'required'
                    ]);
                }

                if ($item->fails())
                    return response()->json([
                        'status' => false,
                        'messages' => $item->messages(),
                    ]);

                $user = app('global_user');
                $user->name = $request->input('name');
                $user->last_name = $request->input('last_name');
                $user->phone = $request->input('phone');
                $user->district_id = $request->input('district_id');
                $user->city = $request->input('city');
                $user->address = $request->input('address');
                $user->save();
                //Обновить и адресс
                break;

            case 'without':
                if ($request->input('delivery_method') && $request->input('delivery_method') == 'delivery') {

                    $item = Validator::make($request->all(), [
                        'name' => 'required|min:2|max:30',
                        'last_name' => 'required|min:2|max:30',
                        'phone' => 'required|numeric|min:8',
                        'email' => 'required|email|max:255',
                        'district_id' => 'required',
                        'city' => 'required',
                        'address' => 'required|min:2|max:100',
                        'pay_method' => [
                            'required',
                            Rule::in(['cash', 'card']),
                        ],
                        'delivery_method' => [
                            'required',
                            Rule::in(['pickup', 'delivery', 'nova_courier', 'nova_terminal']),
                        ],
                        'pickup_shop_id' => [
                            'nullable',
                            'required_if:delivery_method,pickup',
                            'exists:shops_id,id',
                        ],
                        'agree' => 'required'
                    ]);
                } else {
                    $item = Validator::make($request->all(), [
                        'name' => 'required|min:2|max:30',
                        'last_name' => 'required|min:2|max:30',
                        'phone' => 'required|numeric|min:8',
                        'email' => 'required|email|max:255',
                        'pay_method' => [
                            'required',
                            Rule::in(['cash', 'card']),
                        ],
                        'delivery_method' => [
                            'required',
                            Rule::in(['pickup', 'delivery', 'nova_courier', 'nova_terminal']),
                        ],
                        'pickup_shop_id' => [
                            'nullable',
                            'required_if:delivery_method,pickup',
                            'exists:shops_id,id',
                        ],
                        'agree' => 'required'
                    ]);
                }

                if ($item->fails())
                    return response()->json([
                        'status' => false,
                        'messages' => $item->messages(),
                    ]);

                $user = null;

                break;
            default:
                break;
        }

        $total_price = 0;
        $total_count = 0;
        $cadou = null;
        $discount_goods_price = 0;
        $pina_livrare = config('custom.front.until_free_delivery');
        $costul_livrarei = config('custom.front.delivery_price_chisinau');

        if (reCaptchaVersionThree($request->input('g-recaptcha-response')) == false)
            return response()->json([
                'status' => false,
                'messages' => ['Spam'],
            ]);

        if ($request->cookie('basket')) {

            $basket_id = BasketId::where('id', $request->cookie('basket'))->first();
            $basket = Basket::where('basket_id', $request->cookie('basket'))->get();

            if (!empty($basket) && count($basket)) {
                foreach ($basket as $one_basket) {

                    if (!$one_basket->goodsItemId || $one_basket->goodsItemId->in_stoc == 0 || $one_basket->goodsItemId->active == 0 || $one_basket->goodsItemId->deleted == 1)
                        Basket::where('id', $one_basket->id)->delete();

                    $goods_price_collect = getGoodsPrice($one_basket->goodsItemId);

                    $total_price += $goods_price_collect->price * $one_basket->items_count;
                    $total_count += $one_basket->items_count;

                    Basket::where('id', $one_basket->id)->update(['goods_price' => $goods_price_collect->price]);

                    if ($one_basket->has_cadou == 1 && $one_basket->related_one_c_id > 0) {
                        $cadou = GoodsItemId::where('one_c_code', $one_basket->related_one_c_id)
                            ->where('active', 1)
                            ->where('deleted', 0)
                            ->first();
                    }

                    if ($one_basket->promo_one_c_id > 0) {
                        if ($one_basket->discount_procent > 0 || $one_basket->discount_summa > 0) {
                            if ($one_basket->discount_procent > 0) {
//                            $discount_goods_price_one = round($item_price * $one_item->discount_procent / 100);
                                $one_item_discount_price = round($goods_price_collect->price * (100 - $one_basket->discount_procent) / 100);
                                $discount_goods_price_one = $goods_price_collect->price - $one_item_discount_price;
                                $discount_goods_price += $discount_goods_price_one * $one_basket->items_count;
                            } elseif ($one_basket->discount_summa > 0) {
                                $discount_goods_price += $one_basket->discount_summa;
                            }
                        }
                    }
                }

                $order_new = new Orders();
                $order_new->basket_id = $basket_id->id;
                $order_new->delivery_method = $request->input('delivery_method');
                // магазин самовывоза: разовый выбор на заказ (п.2 ТЗ)
                if ($order_new->delivery_method == 'pickup') {
                    $order_new->pickup_shop_id = (int) $request->input('pickup_shop_id') ?: null;
                }
                $order_new->pay_method = $request->input('pay_method');

                if ($user)
                    $order_new->front_user_id = $user->id;

                $order_new->active = 1;
                $order_new->deleted = 0;
                $order_new->save();

                //$user_order = Orders::where('id', $order_new->id)->first();

                if (!empty($order_new)) {

                    $discount_goods_price = round($discount_goods_price);
                    $total_price = round($total_price);

                    if (($total_price - $discount_goods_price) <= config('custom.front.until_free_delivery')) {
                        $district_id = intval($request->input('district_id'));

                        $district = DeliveryState::where('active', 1)
                            ->where('id', $district_id)
                            ->first();

                        if ($district) {
                            if ($district->code == 'CU')
                                $costul_livrarei = config('custom.front.delivery_price_chisinau');
                            else
                                $costul_livrarei = config('custom.front.delivery_price_moldova');
                        } else {
                            $costul_livrarei = config('custom.front.delivery_price_chisinau');
                        }
                    }

                    $orders_data = new OrdersData();
                    $orders_data->orders_id = $order_new->id;
                    $orders_data->total_price = $total_price - $discount_goods_price;
                    $orders_data->total_count = $total_count;
                    $orders_data->delivery_cost = $orders_data->total_price >= config('custom.front.until_free_delivery') || $order_new->delivery_method == 'pickup' ? 0 : $costul_livrarei;
                    $orders_data->total_discount = $discount_goods_price;
                    $orders_data->save();

                    $orders_users = new OrdersUsers();
                    $orders_users->orders_id = $order_new->id;
                    $orders_users->user_ip = $request->ip();
                    if ($order_type == 'without') {
                        $orders_users->name = $request->input('name');
                        $orders_users->last_name = $request->input('last_name');
                        $orders_users->email = $request->input('email');
                        $orders_users->phone = $request->input('phone');
                        $orders_users->district_id = $request->input('district_id');
                        $orders_users->city = $request->input('city');
                        $orders_users->address = $request->input('address');

                        $birth_day = $request->input('birth_day') ?? null;
                        $birth_month = $request->input('birth_month') ?? null;
                        $birth_year = $request->input('birth_year') ?? null;
                        $birth = $birth_day && $birth_month && $birth_year ? $birth_year . '-' . $birth_month . '-' . $birth_day : null;

                        $orders_users->birth = $birth ?? null;
                    }
                    $orders_users->descr = $request->input('comment');
                    $orders_users->save();
                }

                Cookie::queue(Cookie::forget('basket'));

                $new_orders_data = OrdersData::where('id', $orders_data->id)->first();

                $user_info = $user ?: $orders_users;
                $user_district = $user ? getItemByIdSimple($user->district_id, 'DeliveryState') : ($orders_users ? getItemByIdSimple($orders_users->district_id, 'DeliveryState') : '');

                if ($new_orders_data->email_sent == 0) {

                    $user_email = $request->input('email');
                    $email_message = getItemByAlias('email-success-message', 'MenuId');
                    $emails_array = explode(',', showSettingBodyByAlias('email-phone'));
                    $subject = str_replace('{order_id}', $order_new->id, $email_message->itemByLang->h1_title) ?? ShowLabelById(46);

                    $if_admin = 0;
                    if (filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
                        Mail::send('front.email.emailNewOrder', ['orders_users' => $orders_users, 'orders' => $order_new, 'orders_data' => $orders_data, 'basket' => $basket, 'cadou' => $cadou, 'discount_goods_price' => $discount_goods_price, 'if_admin' => $if_admin, 'user_info' => $user_info, 'user_district' => $user_district, 'email_message' => $email_message], function ($message) use ($user_email, $subject) {
                            $message->from(showSettingBodyByAlias('send-email-from'), ShowLabelById(46));
                            $message->to($user_email);
                            $message->subject($subject);
                        });
                    }

                    $if_admin = 1;
                    if (!empty($emails_array) && count($emails_array)) {
                        foreach ($emails_array as $one_email) {
                            $one_email = trim($one_email);
                            if (filter_var($one_email, FILTER_VALIDATE_EMAIL)) {
                                Mail::send('front.email.emailNewOrder', ['orders_users' => $orders_users, 'orders' => $order_new, 'orders_data' => $orders_data, 'basket' => $basket, 'cadou' => $cadou, 'discount_goods_price' => $discount_goods_price, 'if_admin' => $if_admin, 'user_info' => $user_info, 'user_district' => $user_district, 'email_message' => $email_message], function ($message) use ($one_email, $subject) {
                                    $message->from(showSettingBodyByAlias('send-email-from'), ShowLabelById(46));
                                    $message->to($one_email);
                                    $message->subject($subject);
                                });
                            }
                        }
                    }

                    OrdersData::where('id', $new_orders_data->id)->update(['email_sent' => 1]);
                }

                //For AMO CRM
                $sendOrderToAmoCrm->sendOrderToAmoCrm($order_new, $orders_data, $orders_users, $user_info, $user_district);

                // Epic 0 / 0.1 — push the order into 1С + Bitrix24. Queued (not
                // called inline) so a slow/unavailable 1С or Bitrix24 doesn't
                // hold up the customer's checkout response; retries/alerting
                // are handled by the job itself (0.4).
                SubmitOrderToIntegrationLayerJob::dispatch($order_new->id);

                Session::put('if-checkout-success', 1);
                Session::put('order-id', $new_orders_data->orders_id);

                // Card orders go to the bank first — checkout-success is only
                // for orders that don't need online payment (cash). The bank
                // callback, not this response, is what ultimately confirms
                // the payment (Epic 1 / 1.1).
                if ($order_new->pay_method === 'card') {
                    // Payment routes live outside the locale-prefixed route group
                    // (bank redirects/webhooks aren't localized pages), so LANG
                    // isn't derivable from their own URL — carry it through
                    // explicitly so backref() can send the customer back to the
                    // language they were actually shopping in.
                    return response()->json([
                        'status' => true,
                        'redirect' => route('payments.bank.initiate', ['order' => $order_new->id, 'lang' => LANG]),
                    ]);
                }

                return response()->json([
                    'status' => true,
                    'redirect' => route('checkout-success'),
                ]);

            } else
                return response()->json([
                    'status' => false,
                    'text' => trans('variables.something_wrong')
                ]);
        } else
            return response()->json([
                'status' => false,
                'text' => trans('variables.empty_cart')
            ]);
    }

    /**
     * [FE+BE] Экран возврата с оплаты — reached two ways:
     * 1. Normal cash checkout (session flags set in ajaxNewOrder above).
     * 2. Bank redirect for a card order (VictoriaBankController::backref),
     *    which passes ?order= instead — no session flags, because the
     *    customer's browser round-tripped through the bank in between.
     *
     * Either way, the actual payment_status shown here always comes from
     * the order itself, never from the bank's redirect query params —
     * those are informational only, per the task's own acceptance criteria.
     */
    public function checkoutSuccess(Request $request)
    {
        $bankOrderId = $request->query('order');

        if ($bankOrderId) {
            $order = Orders::where('id', (int) $bankOrderId)
                ->where('pay_method', 'card')
                ->with('ordersData', 'basket')
                ->first();

            if (!$order) {
                return redirect(url(LANG));
            }

            $view = 'front.pages.checkout.success';
            $orders = $order;
            $payment_outcome = match ($order->payment_status) {
                \App\Enums\PaymentStatus::Paid => 'paid',
                \App\Enums\PaymentStatus::Failed, \App\Enums\PaymentStatus::Cancelled => 'failed',
                default => 'processing',
            };

            if ($payment_outcome === 'paid' && $orders->basket->isNotEmpty()) {
                $goods_objects = GoogleEcommerce::goodsCollectionsToObjects($orders->basket, 1);
                $goods_items_ids = json_encode($orders->basket->pluck('goods_one_c_code')->toArray());
                $goods_objects_fb = $orders->basket->map(fn ($item) => [
                    'id' => $item->goods_one_c_code,
                    'quantity' => $item->items_count,
                    'item_price' => $item->goods_price,
                ])->all();

                $goods_collect = collect();
                $goods_collect->num_items = $orders->ordersData->total_count;
                $goods_collect->content_ids = $goods_items_ids;
                $goods_collect->contents = $goods_objects_fb;
                $goods_collect->value = priceFormatForGA4($orders->ordersData->total_count + $orders->ordersData->delivery_cost);
                FacebookPixelConversion::pixelEvent('Purchase', $goods_collect);
            }

            $checkout_success_message = getItemByAlias('success-order-message', 'MenuId');
            $order_id = $order->id;

            $meta = collect([]);
            $meta->meta_static = ShowLabelById(162) . ' - ' . env('APP_NAME') ?? env('APP_NAME');

            return view($view, get_defined_vars());
        }

        if (Session::get('if-checkout-success') == 1) {
            $view = 'front.pages.checkout.success';
            $payment_outcome = 'paid'; // cash orders — no online payment step to wait on

            $order_id = Session::get('order-id');
            $goods_items_ids = [];
            $goods_objects = [];
            $goods_objects_fb = [];
            if ($order_id) {
                $orders = Orders::where('id', $order_id)
                    ->with('ordersData', 'basket')
                    ->first();

                if ($orders && $orders->basket->isNotEmpty()) {
                    //For GA4
                    $goods_objects = GoogleEcommerce::goodsCollectionsToObjects($orders->basket, 1);
                    //For FB Pixel
                    $goods_items_ids = json_encode($orders->basket->pluck('goods_one_c_code')->toArray());

                    foreach ($orders->basket as $one_basket_item) {
                        $goods_objects_fb[] = [
                            'id' => $one_basket_item->goods_one_c_code,
                            'quantity' => $one_basket_item->items_count,
                            'item_price' => $one_basket_item->goods_price,
                        ];
                    }

                    $goods_collect = collect();
                    $goods_collect->num_items = $orders->ordersData->total_count;
                    $goods_collect->content_ids = $goods_items_ids;
                    $goods_collect->contents = $goods_objects_fb;
                    $goods_collect->value = priceFormatForGA4($orders->ordersData->total_count + $orders->ordersData->delivery_cost);
                    FacebookPixelConversion::pixelEvent('Purchase', $goods_collect);

                }
            }

            $checkout_success_message = getItemByAlias('success-order-message', 'MenuId');

            Session::forget('if-checkout-success');
            Session::forget('order-id');

            $meta = collect([]);
            $meta->meta_static = ShowLabelById(162) . ' - ' . env('APP_NAME') ?? env('APP_NAME');
        } else
            return redirect(url(LANG));

        return view($view, get_defined_vars());

    }
}

