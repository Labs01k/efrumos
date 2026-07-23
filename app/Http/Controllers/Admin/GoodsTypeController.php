<?php

namespace App\Http\Controllers\Admin;

use App\Models\GoodsType;
use App\Models\GoodsTypeId;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class GoodsTypeController extends Controller
{

    public function index()
    {
        $view = 'admin.goods-type.goods-type-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $goods_type_list = GoodsTypeId::orderBy(GoodsType::select('name')
            ->whereColumn('goods_type_id.id', 'goods_type.goods_type_id')
            ->limit(1)
        )->paginate(config('custom.back.goods_types_items_per_page'));

        return view($view, get_defined_vars());
    }

    public function createGoodsType()
    {
        $view = 'admin.goods-type.create-goods-type';

        return view($view, get_defined_vars());
    }

    public function editGoodsType($id, $lang_id)
    {
        $view = 'admin.goods-type.edit-goods-type';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $goods_type_without_lang = GoodsType::where('goods_type_id', $id)
            ->first();

        if (is_null($goods_type_without_lang)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $goods_type = GoodsType::where('goods_type_id', $goods_type_without_lang->goods_type_id)
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

        $maxPosition = GetMaxPosition('goods_type_id');

        $goods_type_id = GoodsTypeId::updateOrCreate([
            'id' => $id
        ], [
            'position' => $maxPosition + 1,
            'one_c_code' => null
        ]);

        GoodsType::updateOrCreate([
            'goods_type_id' => $goods_type_id->id,
            'lang_id' => $request->input('lang'),
        ], [
            'name' => $request->input('name'),
        ]);

        $goods_type_id->push();

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
            'redirect' => urlForLanguage(LANG, 'editGoodsType/' . $id . '/' . $lang_id)
        ]);
    }

    public function destroyGoodsType(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $goods_type_elems_id = GoodsTypeId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$goods_type_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($goods_type_elems_id as $one_goods_type_id) {


                    $del_message .= $one_goods_type_id->itemByLang->name . ', ';

                    GoodsTypeId::destroy($one_goods_type_id->id);
                    GoodsType::where('goods_type_id', $one_goods_type_id->id)->delete();
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
