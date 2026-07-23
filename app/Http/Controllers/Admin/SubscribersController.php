<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SubscribersExport;
use App\Http\Controllers\Controller;
use App\Models\Subscribers;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SubscribersController extends Controller
{
    public function index()
    {
        $view = 'admin.subscribers.subscribers-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $subscribers = Subscribers::orderBy('created_at', 'desc')
            ->paginate(config('custom.back.subscribers_items_per_page'));

        return view($view, get_defined_vars());
    }

    public function exportExcel()
    {
        return Excel::download(new SubscribersExport(), 'subscribers' . '.xlsx');
    }

    public function searchObjects(Request $request)
    {
        $view = 'admin.subscribers.search-object';
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

                $subscribers = Subscribers::where('email', 'like', '%' . $concrete_search_key . '%')
                    ->orderBy('created_at', 'desc')
                    ->paginate(config('custom.back.subscribers_items_per_page'));

                $subscribers->setPath(url(LANG, ['back', 'subscribers']) . '?search-key=' . $concrete_search_key);

                if ($subscribers->isEmpty()) {
                    $labels_list = [];
                }
            }
        }

        return view($view, get_defined_vars());
    }

    public function destroySubscriber(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $subscribe_elems_id = Subscribers::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$subscribe_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($subscribe_elems_id as $one_subscribe_elems_id) {


                    $del_message .= $one_subscribe_elems_id->name . ', ';

                    Subscribers::destroy($one_subscribe_elems_id->id);
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

}
