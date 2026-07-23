<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeedForm;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeedFormController extends Controller
{

    public function index()
    {
        $view = 'admin.feedform.feedform-list';
        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $feedform = FeedForm::orderBy('created_at', 'desc')
            ->paginate(config('custom.back.feedforms_items_per_page'));

        $new_feedform = FeedForm::where('seen', 0)->count();
        if ($new_feedform > 0) {
            FeedForm::where('seen', 0)->update(['seen' => 1]);
        }

        return view($view, get_defined_vars());
    }

    // ajax response for active
    public function changeActive(Request $request)
    {
        $active = $request->input('active');
        $id = $request->input('id');

        $element_id = FeedForm::findOrFail($id);

        if (!is_null($element_id))
            $element_name = FeedForm::where('id', $id)->first()->name;
        else
            return response()->json([
                'status' => false,
                'type' => 'error',
                'messages' => [controllerTrans('variables.something_wrong', LANG)]
            ]);

        if ($active == 1) {
            $change_active = 0;
            $msg = controllerTrans('variables.element_is_inactive', LANG, ['name' => $element_name]);
        } else {
            $change_active = 1;
            $msg = controllerTrans('variables.element_is_active', LANG, ['name' => $element_name]);
        }

        FeedForm::where('id', $id)->update(['active' => $change_active]);

        return response()->json([
            'status' => true,
            'type' => 'info',
            'messages' => [$msg]
        ]);

    }

    public function destroyFeedFormFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $goods_item_elems_id = FeedForm::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$goods_item_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($goods_item_elems_id as $one_goods_item_elems_id) {
                    $del_message .= $one_goods_item_elems_id->name . ', ';

                    FeedForm::destroy($one_goods_item_elems_id->id);
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

    public function editItem($id)
    {
        $view = 'admin.feedform.edit-feedform';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $feedform = FeedForm::where('id', $id)->first();

        if (is_null($feedform)) {
            return App::abort(503, 'Unauthorized action.');
        }

        return view($view, get_defined_vars());
    }

    public function save(Request $request, $id)
    {

        $item = Validator::make($request->all(), [
            'comment' => 'required',
        ]);

        if ($item->fails()) {
            return response()->json([
                'status' => false,
                'type' => 'error',
                'messages' => $item->messages(),
            ]);
        }

        if (is_null($id))
            return response()->json([
                'status' => false,
                'type' => 'warning',
                'messages' => [controllerTrans('variables.something_wrong', LANG)],
            ]);

        $feedform = FeedForm::where('id', $id)->first();

        if (is_null($feedform))
            return response()->json([
                'status' => false,
                'type' => 'warning',
                'messages' => [controllerTrans('variables.something_wrong', LANG)],
            ]);

        $data = [
            'comment' => !is_null($request->input('comment')) ? $request->input('comment') : ''
        ];

        FeedForm::where('id', $feedform->id)->update($data);

        return response()->json([
            'status' => true,
            'type' => 'success',
            'messages' => [controllerTrans('variables.updated_text', LANG)],
            'redirect' => urlForLanguage(LANG, 'edititem/' . $id)
        ]);
    }

    /**
     * return to another url, if method membersList does not exist
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function membersList()
    {
        return redirect(urlForFunctionLanguage(LANG, ''));
    }
}
