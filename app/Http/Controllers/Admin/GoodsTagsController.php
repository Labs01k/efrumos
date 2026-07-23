<?php

namespace App\Http\Controllers\Admin;

use App\Models\GoodsItemTags;
use App\Models\GoodsItemTagsId;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class GoodsTagsController extends Controller
{

    public function index()
    {
        $view = 'admin.goods-tags.goods-tags-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $goods_tags_list = GoodsItemTagsId::whereHas('itemByLang', function ($q) {
            $q->orderBy('name', 'asc');
        })->paginate(config('custom.back.goods_tags_items_per_page'));

        return view($view, get_defined_vars());
    }

    public function createGoodsTag()
    {
        $view = 'admin.goods-tags.create-goods-tag';

        return view($view, get_defined_vars());
    }

    public function editGoodsTag($id, $lang_id)
    {
        $view = 'admin.goods-tags.edit-goods-tag';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $goods_tag_without_lang = GoodsItemTags::where('goods_item_tags_id', $id)
            ->first();

        if (is_null($goods_tag_without_lang)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $goods_tag = GoodsItemTags::where('goods_item_tags_id', $goods_tag_without_lang->goods_item_tags_id)
            ->where('lang_id', $lang_id)
            ->first();

        return view($view, get_defined_vars());
    }

    public function save(Request $request, $id, $lang_id)
    {
        $item = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($item->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $item->messages(),
            ]);
        }

        //Check if lang exist
        if (checkIfLangExist($request->input('lang')) == false)
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.lang_not_exist', LANG)],
            ]);


        $goods_tag_id = GoodsItemTagsId::updateOrCreate([
            'id' => $id
        ]);

        GoodsItemTags::updateOrCreate([
            'goods_item_tags_id' => $goods_tag_id->id,
            'lang_id' => $request->input('lang'),
        ], [
            'name' => $request->input('name'),
        ]);

        $goods_tag_id->push();

        if (is_null($id)) {
            return response()->json([
                'status' => true,
                'messages' => [controllerTrans('variables.save', LANG)],
                'redirect' => urlForFunctionLanguage(LANG, '')
            ]);
        }

        return response()->json([
            'status' => true,
            'messages' => [controllerTrans('variables.updated_text', LANG)],
            'redirect' => urlForLanguage(LANG, 'editGoodsTag/' . $id . '/' . $lang_id)
        ]);
    }

    public function changeActive(Request $request)
    {
        $active = $request->input('active');
        $id = $request->input('id');
        $action = $request->input('action');

        if ($action == 'main-active')
            $element_id = GoodsItemTagsId::findOrFail($id);
        else
            $element_id = '';

        if (!is_null($element_id)) {
            if ($action == 'main-active')
                $element_name = $element_id->itemByLang && $element_id->itemByLang->name ? $element_id->itemByLang->name : '';
            else
                $element_name = '';
        } else
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

        if ($action == 'main-active')
            GoodsItemTagsId::where('id', $id)->update(['active' => $change_active]);

        return response()->json([
            'status' => true,
            'type' => 'info',
            'messages' => [$msg]
        ]);
    }

    public function destroyGoodsTag(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $goods_tags_elems_id = GoodsItemTagsId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$goods_tags_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($goods_tags_elems_id as $one_goods_tag_id) {


                    $del_message .= $one_goods_tag_id->itemByLang->name . ', ';

                    GoodsItemTagsId::destroy($one_goods_tag_id->id);
                    GoodsItemTags::where('goods_item_tags_id', $one_goods_tag_id->id)->delete();
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
