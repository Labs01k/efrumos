<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Labels;
use App\Models\LabelsId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class LabelsController extends Controller
{
    public function index()
    {
        $view = 'admin.labels.labels-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $labels_list_id = LabelsId::where('p_id', 0)
            ->orderBy('id', 'desc')
            ->paginate(config('custom.back.labels_per_page'));

        $labels_list = [];
        foreach($labels_list_id as $key => $one_label_id){
            $labels_list[$key] = Labels::where('labels_id' ,$one_label_id->id)
                ->first();
        }
        //Remove all null values --start
        $labels_list = array_filter( $labels_list, 'strlen' );
        //Remove all null values --end


        return view($view, get_defined_vars());
    }

    public function createItem()
    {
        $view = 'admin.labels.create-label';

        $label_p_id = intval(request()->segment(4)) ?? 0;

        $curr_page_label_id = LabelsId::where('id', $label_p_id)
            ->first();

        return view($view, get_defined_vars());
    }

    public function editItem($id, $lang_id)
    {
        $view = 'admin.labels.edit-label';

        $labels_without_lang = Labels::where('labels_id', $id)
            ->first();

        if(is_null($labels_without_lang)){
            return App::abort(503, 'Unauthorized action.');
        }

        $parent_label_id = LabelsId::whereRaw('id IN(SELECT p_id FROM labels_id WHERE id = ' . $id . ')')
            ->first();

        $labels = Labels::where('labels_id', $labels_without_lang->labels_id)
            ->where('lang_id', $lang_id)
            ->first();

        return view($view, get_defined_vars());
    }

    public function save(Request $request,$id, $lang_id)
    {
        $item = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if($item->fails()){
            return response()->json([
                'status' => false,
                'messages' => $item->messages(),
            ]);
        }

        //Check if lang exist
        if(checkIfLangExist($request->input('lang')) == false)
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.lang_not_exist', LANG)],
            ]);

        $labels_id = LabelsId::updateOrCreate([
            'id'=> $id
        ],[
            'p_id' => $request->input('p_id')
        ]);

        Labels::updateOrCreate([
        'labels_id' => $labels_id->id,
        'lang_id' => $request->input('lang'),
        ],[
        'name' => $request->input('name'),
        ]);

        $labels_id->push();

       /* if(is_null($id)){
            return response()->json([
                'status' => true,
                'messages' => [controllerTrans('variables.save', LANG)],
                'redirect' => urlForFunctionLanguage(LANG, '')
            ]);
        }*/

        if (is_null($id)) {
            if ($labels_id->p_id == 0) {
                return response()->json([
                    'status' => true,
                    'messages' => [controllerTrans('variables.save', LANG)],
                    'redirect' => urlForFunctionLanguage(LANG, '')
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'messages' => [controllerTrans('variables.save', LANG)],
                    'redirect' => urlForFunctionLanguage(LANG, $labels_id->p_id . '/memberslist')
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'messages' => [controllerTrans('variables.updated_text', LANG)],
            'redirect' => $request->input('current_url')
        ]);

    }

    public function memberslist()
    {
        $view = 'admin.labels.child-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $labels_id = LabelsId::where('id', request()->segment(4))
            ->first();

        if (is_null($labels_id)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $child_labels_list_id = LabelsId::where('p_id', $labels_id->id)
            ->orderBy('id', 'desc')
            ->with('itemByLang')
            ->paginate(config('custom.back.labels_per_page'));

        return view($view, get_defined_vars());
    }

    public function destroyLabelFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $label_elems_id = LabelsId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$label_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($label_elems_id as $one_label_elems_id) {

                    $label_elems = Labels::where('labels_id', $one_label_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if(is_null($label_elems)){
                        $label_elems = Labels::where('labels_id', $one_label_elems_id->id)
                            ->first();
                    }

                    $del_message .= $label_elems->name . ', ';

                    LabelsId::destroy($one_label_elems_id->id);
                    Labels::where('labels_id', $one_label_elems_id->id)->delete();
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

    public function searchObjects(Request $request)
    {
        $view = 'admin.labels.search-object';
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

                $labels_list = Labels::leftjoin('labels_id', 'labels_id.id', '=', 'labels.labels_id')
                    ->where('labels.lang_id', LANG_ID)
                    ->where('labels_id.p_id', '!=', 0)
                    ->where(function ($q) use ($concrete_search_key) {
                        $q->orWhere('labels_id.id', 'like', '%' . $concrete_search_key . '%');
                        $q->orWhere('labels.name', 'like', '%' . $concrete_search_key . '%');
                    })
                    ->paginate(config('custom.back.labels_per_page'));

                $labels_list->setPath(url(LANG, ['back', 'labels', 'search', 'searchObjects']) . '?search-key=' . $concrete_search_key);

                if ($labels_list->isEmpty()) {
                    $labels_list = [];
                }
            }
        }

        return view($view, get_defined_vars());
    }


    /**
     * return to another url, if method membersList does not exist
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
   /* public function membersList()
    {
        return redirect(urlForFunctionLanguage(LANG, ''));
    }*/


}
