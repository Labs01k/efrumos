<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Basket;
use App\Models\Orders;
use App\Services\FacebookAds\FacebookPixelConversion;
use App\Services\GA4\GoogleEcommerce;
use App\Services\Integration\OrderIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;


class OrdersController extends Controller
{

    public function index(Request $request)
    {
        $view = 'admin.orders.orders-list';
        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $orders = Orders::orderBy('created_at', 'desc')
            ->where('deleted', 0)
            ->with('ordersUsers', 'ordersFrontUser')
            ->paginate(config('custom.back.orders_items_per_page'));

        $new_orders = Orders::where('seen', 0)->count();
        if ($new_orders > 0) {
            Orders::where('seen', 0)->update(['seen' => 1]);
        }

        return view($view, get_defined_vars());
    }

    // ajax response for active
    public function changeActive(Request $request)
    {
        $active = $request->input('active');
        $action = $request->input('action');
        $id = $request->input('id');

        $element_id = Orders::findOrFail($id);

        if (!is_null($element_id))
            $element_name = $element_id->ordersUsers->name;
        else
            return response()->json([
                'status' => false,
                'type' => 'error',
                'messages' => [controllerTrans('variables.something_wrong', LANG)]
            ]);

        if ($active == 1) {
            $change_active = 0;
            $msg = controllerTrans('variables.element_is_inactive', LANG, ['name' => $element_name]);
            $text = 'Не оплачено';
        } else {
            $change_active = 1;
            $msg = controllerTrans('variables.element_is_active', LANG, ['name' => $element_name]);
            $text = 'Оплачено';
        }

        if ($action == 'paid-order')
            Orders::where('id', $id)->update(['paid' => $change_active]);
        else
            Orders::where('id', $id)->update(['active' => $change_active]);

        return response()->json([
            'status' => true,
            'type' => 'info',
            'text' => $text,
            'messages' => [$msg]
        ]);

    }

    public function editItem($id)
    {
        $view = 'admin.orders.edit-order';
        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $orders = Orders::where('id', $id)->first();

        if (is_null($orders)) {
            return App::abort(503, 'Unauthorized action.');
        }

        if ($orders->ordersFrontUser)
            $user_info = $orders->ordersFrontUser;
        else
            $user_info = $orders->ordersUsers;

        $user_district = $user_info ? getItemByIdSimple($user_info->district_id, 'DeliveryState') : null;


        $orderedItems = $orders->basket;
        $orders_info = Orders::join('orders_data', 'orders.id', '=', 'orders_data.orders_id')
            ->where('orders.id', $id)
            ->get();

        $basket = Basket::whereRaw('basket_id = (SELECT basket_id FROM orders where id=' . $id . ')')
            ->get();

        return view($view, get_defined_vars());
    }


    public function ordersCart()
    {
        $view = 'admin.orders.orders-cart';

        $orders = Orders::where('active', 0)
            ->where('deleted', 1)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view($view, get_defined_vars());
    }

    public function destroyOrderFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $orders_id = Orders::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$orders_id->isEmpty()) {

                $del_message = '';

                foreach ($orders_id as $one_orders_id) {

                    if ($one_orders_id->deleted == 1 && $one_orders_id->active == 0) {

                        $del_message .= $one_orders_id->ordersUsers->name . ', ';

                        Orders::destroy($one_orders_id->id);
                    }
                }

                if (!empty($del_message)) {
                    $del_message = substr($del_message, 0, -2) . '<br />' . controllerTrans('variables.success_deleted', LANG);
                }

                return response()->json([
                    'status' => true,
                    'messages' => $del_message,
                    'deleted_elements' => $deleted_elements_id_arr,
                    'redirect' => $data_current_url,
                ]);
            }

            return response()->json([
                'status' => false
            ]);
        } else {
            return response()->json([
                'status' => false
            ]);
        }

    }

    public function destroyOrderToCart(Request $request, OrderIntegrationService $integrationService)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $orders_id = Orders::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$orders_id->isEmpty()) {

                $cart_message = '';

                foreach ($orders_id as $one_orders_id) {


                    if ($one_orders_id->deleted == 0) {

                        if (!empty($one_orders_id->ordersUsers->name))
                            $cart_message .= $one_orders_id->ordersUsers->name . ', ';

                        Orders::where('id', $one_orders_id->id)
                            ->update(['active' => 0, 'deleted' => 1]);

                        // Epic 0 / 0.2 — cancelling an order releases whatever
                        // stock it had reserved in 1С. No-ops cleanly if the
                        // order was never synced (no mapping / no onec_document_id).
                        $integrationService->releaseReservation($one_orders_id);
                    }
                }

                if (!empty($cart_message)) {
                    $cart_message = substr($cart_message, 0, -2) . '<br />' . controllerTrans('variables.success_added_cart', LANG);
                }

                return response()->json([
                    'status' => true,
                    'messages' => $cart_message,
                    'deleted_elements' => $deleted_elements_id_arr,
                    'redirect' => $data_current_url,
                ]);
            }

            return response()->json([
                'status' => false
            ]);
        } else {
            return response()->json([
                'status' => false
            ]);
        }
    }

    public function restoreOrder(Request $request)
    {
        $restored_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($restored_elements_id)) {
            $restored_elements_id_arr = explode(',', $restored_elements_id);

            $promotion_item_elems_id = Orders::whereIn('id', $restored_elements_id_arr)->get();

            if (!$promotion_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($promotion_item_elems_id as $one_promotion_item_elems_id) {

                    $promotion_name = $one_promotion_item_elems_id->ordersUsers->name;

                    if ($one_promotion_item_elems_id->restored == 0) {

                        $cart_message .= $promotion_name . ', ';

                        Orders::where('id', $one_promotion_item_elems_id->id)
                            ->update(['active' => 1, 'deleted' => 0]);
                    }
                }

                if (!empty($cart_message)) {
                    $cart_message = substr($cart_message, 0, -2) . '<br />' . controllerTrans('variables.success_restored', LANG);
                }

                return response()->json([
                    'status' => true,
                    'messages' => $cart_message,
                    'restored_elements' => $restored_elements_id_arr,
                    'redirect' => $data_current_url,
                ]);
            }

            return response()->json([
                'status' => false
            ]);
        } else {
            return response()->json([
                'status' => false
            ]);
        }
    }

    public function searchObjects(Request $request)
    {
        $view = 'admin.orders.search-object';
        $search_key = $request->except('_token');
        $orders = [];
        $concrete_search_key = trim($request->input('search-key'));
        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;
        $new_url = "";

        if (!empty($search_key)) {
            foreach ($search_key as $key => $one_key) {
                if (!empty($one_key)) {
                    if (is_array($one_key)) {
                        $new_url_arr = '';
                        foreach ($one_key as $val) {
                            $new_url_arr .= $val . ',';
                        }
                        $new_url .= $key . '=[' . substr($new_url_arr, 0, -1) . ']&';
                    } else {
                        $new_url .= $key . "=" . $one_key . '&';
                    }
                }
            }

            $new_url = '?' . substr($new_url, 0, -1);

            if (!empty($concrete_search_key)) {

                $orders = Orders::orderBy('created_at', 'desc')
                    ->where('deleted', 0)
                    ->where(function ($query) use ($concrete_search_key) {
                        $query->whereHas('ordersUsers', function ($q) use ($concrete_search_key) {
                            $q->where('name', 'LIKE', '%' . $concrete_search_key . '%')
                                ->orWhere('last_name', 'LIKE', '%' . $concrete_search_key . '%')
                                ->orWhere('phone', 'LIKE', '%' . $concrete_search_key . '%')
                                ->orWhere('email', 'LIKE', '%' . $concrete_search_key . '%');
                        })->orWhereHas('ordersFrontUser', function ($q) use ($concrete_search_key) {
                                $q->where('name', 'LIKE', '%' . $concrete_search_key . '%')
                                    ->orWhere('last_name', 'LIKE', '%' . $concrete_search_key . '%')
                                    ->orWhere('phone', 'LIKE', '%' . $concrete_search_key . '%')
                                    ->orWhere('email', 'LIKE', '%' . $concrete_search_key . '%');
                            })->orWhere('orders.id', 'like', '%' . $concrete_search_key . '%')
                            ->orWhere('orders.pay_method', 'like', '%' . $concrete_search_key . '%')
                            ->orWhere('orders.delivery_method', 'like', '%' . $concrete_search_key . '%');
                    })
                    ->orderBy('orders.created_at', 'desc')
                    ->paginate(config('custom.back.orders_items_per_page'));

                $orders->setPath(url(LANG, ['back', 'orders']) . '?search-key=' . $concrete_search_key);

                if ($orders->isEmpty()) {
                    $orders = [];
                }
            }
        }

        return view($view, get_defined_vars());
    }

    /**
     * return to another url, if method membersList does not exist
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function membersList()
    {
        return redirect(urlForFunctionLanguage(LANG, ''));
    }

    public function ajaxRefundOrderGA4(Request $request){

        $order_id = intval($request->input('order_id'));

        $orders = Orders::where('id', $order_id)
            ->with('ordersData', 'basket')
            ->first();

        if(!$orders)
            return response()->json([
                'status' => false,
                'text' => 'Order not found',
            ]);

        if ($orders->basket->isNotEmpty()) {
            //For GA4
            $goods_objects = GoogleEcommerce::goodsCollectionsToObjects($orders->basket, 1);

            return response()->json([
                'status' => true,
                'order_id' => $orders->id,
                'total_price' => priceFormatForGA4($orders->ordersData->total_price),
                'delivery_cost' => priceFormatForGA4($orders->ordersData->delivery_cost),
                'goods_objects' => $goods_objects,
                'messages' => 'Comanda a fost anulată'
            ]);
        }else{
            return response()->json([
                'status' => false,
                'text' => 'Error',
            ]);
        }
    }

}
