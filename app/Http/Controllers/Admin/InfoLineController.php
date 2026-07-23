<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoodsItem;
use App\Models\GoodsItemId;
use App\Models\GoodsPromo;
use App\Models\InfoItem;
use App\Models\InfoItemId;
use App\Models\InfoLine;
use App\Models\InfoLineId;
use App\Models\InfoLineImages;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class InfoLineController extends Controller
{

    public function index()
    {
        $view = 'admin.infoline.infoline-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $info_line_id = InfoLineId::where('deleted', 0)
            ->orderBy('position', 'asc')
            ->paginate(config('custom.back.info_line_per_page'));

        $info_line = [];
        foreach ($info_line_id as $key => $one_info_line_id) {
            $info_line[$key] = InfoLine::where('info_line_id', $one_info_line_id->id)
                ->first();
        }

        //Remove all null values --start
        $info_line = array_filter($info_line, 'strlen');
        //Remove all null values --end

        return view($view, get_defined_vars());
    }

    public function changePosition(Request $request)
    {
        if ($request->input('action') == 'promo-list') {
            $positionItems = [];
            $info_line_id = InfoLineId::where('active', 1)->where('alias', 'promo')->value('id');
            if ($info_line_id)
                $positionItems = InfoItemId::where('info_line_id', $info_line_id)->get();
        } else
            $positionItems = InfoLineId::get();

        $requestPositions = $request->input('position');

        if (!empty($positionItems) && count($positionItems) && !empty($requestPositions)) {
            foreach ($positionItems as $onePositionItem) {
                $onePositionItem->timestamps = false; // To disable update_at field update

                foreach ($requestPositions as $position) {
                    if ($position['id'] && $position['id'] == $onePositionItem->id) {
                        $onePositionItem->update(['position' => $position['position']]);
                    }
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => __('variables.change_position')
        ]);
    }

    public function changeImgPosition(Request $request)
    {
        $imagePositions = $request->input('position');

        if ($imagePositions && !empty($imagePositions) && count($imagePositions)) {
            foreach ($imagePositions as $oneImagePosition) {
                if ($oneImagePosition['id'])
                    InfoLineImages::find($oneImagePosition['id'])->update(['position' => $oneImagePosition['position']]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => __('variables.change_position')
        ]);
    }

    // ajax response for active
    public function changeActive(Request $request)
    {
        $active = $request->input('active');
        $id = $request->input('id');
        $action = $request->input('action');

        if ($action != '')
            $element_id = InfoItemId::findOrFail($id);
        else
            $element_id = InfoLineId::findOrFail($id);

        if (!is_null($element_id)) {
            if ($action == 'info_item')
                $element_name = GetNameByLang($element_id->id, LANG_ID, 'InfoItem', 'info_item_id');
            else
                $element_name = GetNameByLang($element_id->id, LANG_ID, 'InfoLine', 'info_line_id');
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

        if ($action == 'info_item') {
            InfoItemId::where('id', $id)->update(['active' => $change_active]);
        } else {
            InfoLineId::where('id', $id)->update(['active' => $change_active]);
        }

        return response()->json([
            'status' => true,
            'type' => 'info',
            'messages' => [$msg]
        ]);
    }

    public function createInfoLine()
    {
        $view = 'admin.infoline.create-infoline';

        return view($view, get_defined_vars());
    }

    public function editInfoLine($id, $lang_id)
    {
        $view = 'admin.infoline.edit-infoline';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $info_line_without_lang = InfoLine::where('info_line_id', $id)->first();

        if (is_null($info_line_without_lang)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $info_line = InfoLine::where('lang_id', $lang_id)
            ->where('info_line_id', $info_line_without_lang->info_line_id)
            ->first();

        if (!is_null($info_line_without_lang)) {
            $info_line_id = InfoLineId::where('id', $info_line_without_lang->info_line_id)
                ->first();
        } elseif (!is_null($info_line)) {
            $info_line_id = InfoLineId::where('id', $info_line->info_line_id)
                ->first();
        }

        return view($view, get_defined_vars());
    }

    public function infoLineCart()
    {
        $view = 'admin.infoline.infoline-cart';

        $deleted_info_line_id = InfoLineId::where('deleted', 1)
            ->where('active', 0)
            ->get();

        $deleted_info_line = [];

        foreach ($deleted_info_line_id as $key => $one_deleted_info_line_id) {
            $deleted_info_line[$key] = InfoLine::where('info_line_id', $one_deleted_info_line_id->id)
                ->first();
        }

        $deleted_info_line = array_filter($deleted_info_line, 'strlen');

        return view($view, get_defined_vars());
    }

    public function saveInfoLine(Request $request, $id, $lang_id)
    {
        if (is_null($id)) {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:info_line_id',
                'uploaded_files' => 'nullable|max:10',
                'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'upload_files.*.mimes' => __('variables.custom_image_mime'),
                'upload_files.*.max' => __('variables.custom_image_size'),
            ]);
        } else {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:info_line_id,alias,' . $id,
                'uploaded_files' => 'nullable|max:10',
                'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'upload_files.*.mimes' => __('variables.custom_image_mime'),
                'upload_files.*.max' => __('variables.custom_image_size'),
            ]);
        }

        if ($item->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $item->messages(),
            ]);
        }

        $maxPosition = GetMinPosition('info_line_id');
        if ($id) {
            $currentPosition = GetPosition('info_line_id', $id);
            $position = $currentPosition;
        } else {
            $position = $maxPosition - 1;
        }

//        Check if lang exist
        if (checkIfLangExist($request->input('lang')) == false)
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.lang_not_exist', LANG)],
            ]);
//        Check if lang exist

        $info_line_id = InfoLineId::updateOrCreate(['id' => $id], [
            'position' => $position,
            'img' => $request->file('upload_files') ? uploadMultipleFiles($request->file('upload_files'), $request->input('uploaded_files'), 'info-line', 0) : getImageById($id, 'InfoLineId'),
            'alias' => $request->input('alias'),
        ]);

        InfoLine::updateOrCreate([
            'info_line_id' => $info_line_id->id,
            'lang_id' => $request->input('lang'),
        ], [
            'name' => $request->input('name'),
            'descr' => $request->input('body'),
            'page_title' => $request->input('page_title'),
            'h1_title' => $request->input('h1_title'),
            'meta_title' => $request->input('meta_title'),
            'meta_keywords' => $request->input('meta_keywords'),
            'meta_description' => $request->input('meta_description'),
        ]);

        $info_line_id->push();

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
            'redirect' => urlForLanguage(LANG, 'editinfoline/' . $id . '/' . $lang_id)
        ]);

    }

    public function destroyInfoLineFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $info_line_elems_id = InfoLineId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$info_line_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($info_line_elems_id as $one_info_line_elems_id) {

                    $info_line_elems = InfoLine::where('info_line_id', $one_info_line_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($info_line_elems)) {
                        $info_line_elems = InfoLine::where('info_line_id', $one_info_line_elems_id->id)
                            ->first();
                    }

                    if ($one_info_line_elems_id->deleted == 1 && $one_info_line_elems_id->active == 0) {

                        if (File::exists('upfiles/info-line/s/' . showImg($one_info_line_elems_id->img)))
                            File::delete('upfiles/info-line/s/' . showImg($one_info_line_elems_id->img));

                        if (File::exists('upfiles/info-line/m/' . showImg($one_info_line_elems_id->img)))
                            File::delete('upfiles/info-line/m/' . showImg($one_info_line_elems_id->img));

                        if (File::exists('upfiles/info-line/' . $one_info_line_elems_id->img))
                            File::delete('upfiles/info-line/' . $one_info_line_elems_id->img);

                        $del_message .= $info_line_elems->name . ', ';

                        InfoLineId::destroy($one_info_line_elems_id->id);
                        InfoLine::where('info_line_id', $one_info_line_elems_id->id)->delete();

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

    public function destroyInfoLineToCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $info_line_elems_id = InfoLineId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$info_line_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($info_line_elems_id as $one_info_line_elems_id) {

                    $info_line_elems = InfoLine::where('info_line_id', $one_info_line_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($info_line_elems)) {
                        $info_line_elems = InfoLine::where('info_line_id', $one_info_line_elems_id->id)
                            ->first();
                    }


                    if ($one_info_line_elems_id->deleted == 0) {

                        $cart_message .= $info_line_elems->name . ', ';

                        InfoLineId::where('id', $one_info_line_elems_id->id)
                            ->update(['active' => 0, 'deleted' => 1]);
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

    public function restoreInfoLine(Request $request)
    {
        $restored_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($restored_elements_id)) {
            $restored_elements_id_arr = explode(',', $restored_elements_id);

            $info_item_elems_id = InfoLineId::whereIn('id', $restored_elements_id_arr)->get();

            if (!$info_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($info_item_elems_id as $one_info_item_elems_id) {

                    $info_name = GetNameByLang($one_info_item_elems_id->id, LANG_ID, 'InfoLine', 'info_line_id');

                    if ($one_info_item_elems_id->restored == 0) {

                        $cart_message .= $info_name . ', ';

                        InfoLineId::where('id', $one_info_item_elems_id->id)
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

    public function membersList()
    {
        $view = 'admin.infoline.infoitems-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $info_line_id = InfoLineId::where('alias', request()->segment(4))
            ->first();

        if (is_null($info_line_id)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $position_promo = request()->segment(4) == 'promo' ? 1 : 0;

        $info_items_list = InfoItemId::where('deleted', 0)
            ->where('info_line_id', $info_line_id->id)
            ->when($position_promo, function ($q) {
                return $q->orderBy('position', 'asc');
            }, function ($q) {
                return $q->orderBy('add_date', 'desc');
            })
            ->orderBy('add_date', 'desc')
            ->paginate(config('custom.back.info_items_per_page'));

        $info_item = [];
        foreach ($info_items_list as $key => $one_info_tem) {
            $info_item[$key] = InfoItem::where('info_item_id', $one_info_tem->id)
                ->first();
        }

        //Remove all null values --start
        $info_item = array_filter($info_item, 'strlen');
        //Remove all null values --end

        return view($view, get_defined_vars());
    }

    public function createInfoItem()
    {
        $view = 'admin.infoline.create-infoitem';

        $modules_name = $this->menu()['modules_name'];

        $goods_list = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->has('itemByLang')
            ->with('itemByLang')
            ->get()
            ->sortBy('name');

        $curr_date = Carbon::now()->format('Y-m-d');

        $promo_list = GoodsPromo::where('data_end', '>=', $curr_date)
            ->get();

        return view($view, get_defined_vars());
    }

    public function editInfoItem($id, $lang_id)
    {
        $view = 'admin.infoline.edit-infoitem';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $info_item_without_lang = InfoItem::where('info_item_id', $id)->first();

        if (is_null($info_item_without_lang)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $info_item = InfoItem::where('lang_id', $lang_id)
            ->where('info_item_id', $info_item_without_lang->info_item_id)
            ->first();

        if (!is_null($info_item_without_lang)) {
            $info_item_id = InfoItemId::where('id', $info_item_without_lang->info_item_id)
                ->first();
            $info_line_id = InfoLineId::where('id', $info_item_id->info_line_id)->first();
        } elseif (!is_null($info_item)) {
            $info_item_id = InfoItemId::where('id', $info_item->info_item_id)
                ->first();
            $info_line_id = InfoLineId::where('id', $info_item_id->info_line_id)->first();
        }

        $images = InfoLineImages::where('info_item_id', $info_item_id->id)
            ->orderBy('position', 'asc')
            ->get();

        $goods_list = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->has('itemByLang')
            ->with('itemByLang')
            ->get()
            ->sortBy('name');

        $curr_date = Carbon::now()->format('Y-m-d');

        $promo_list = GoodsPromo::where('data_end', '>=', $curr_date)
            ->get();

        return view($view, get_defined_vars());
    }

    public function infoItemsCart()
    {
        $view = 'admin.infoline.infoitems-cart';

        $deleted_info_item_id = InfoItemId::where('deleted', 1)
            ->where('active', 0)
            ->get();

        $deleted_info_item = [];

        foreach ($deleted_info_item_id as $key => $one_deleted_info_item_id) {
            $deleted_info_item[$key] = InfoItem::where('info_item_id', $one_deleted_info_item_id->id)
                ->first();
        }

        $deleted_info_item = array_filter($deleted_info_item, 'strlen');

        return view($view, get_defined_vars());
    }

    public function saveInfoItem(Request $request, $id, $lang_id)
    {
        if (is_null($id)) {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:info_item_id',
                'uploaded_files' => 'nullable|max:10',
                'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'upload_files.*.mimes' => __('variables.custom_image_mime'),
                'upload_files.*.max' => __('variables.custom_image_size'),
            ]);
        } else {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:info_item_id,alias,' . $id,
                'uploaded_files' => 'nullable|max:10',
                'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'upload_files.*.mimes' => __('variables.custom_image_mime'),
                'upload_files.*.max' => __('variables.custom_image_size'),
            ]);
        }

        if ($item->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $item->messages(),
            ]);
        }

        $info_line_id = InfoLineId::where('alias', request()->segment(4))->first()->id;

        if (is_null($info_line_id))
            return App::abort(503, 'Unauthorized action.');

        if (!empty($request->input('add_date'))) {
            $add_date = date('Y-m-d H:i', strtotime($request->input('add_date')));
        } else {
            $add_date = '';
        }

        $maxPosition = GetMaxPosition('info_item_id');

        if ($id) {
            $currentPosition = GetPosition('info_item_id', $id);
            $position = $currentPosition;
        } else {
            $position = $maxPosition - 1;
        }

//        Check if lang exist
        if (checkIfLangExist($request->input('lang')) == false)
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.lang_not_exist', LANG)],
            ]);
//        Check if lang exist

        $info_item_id = InfoItemId::updateOrCreate([
            'id' => $id,
            'info_line_id' => $info_line_id
        ], [
            'alias' => $request->input('alias'),
            'add_date' => $add_date,
            'position' => $info_line_id == 12 ? $position : 0, //info_line_id 12 - promo
            'goods_list' => $request->input('goods_list') ? implode(',', $request->input('goods_list')) : null,
            'goods_promo_id' => $request->input('goods_promo_id') ? implode(',', $request->input('goods_promo_id')) : null,
            'show_text_in_products' => $request->input('show_text_in_products') == 'on' ? 1 : 0,
            'promo_color' => $request->input('promo_color')
        ]);

        InfoItem::updateOrCreate([
            'info_item_id' => $info_item_id->id,
            'lang_id' => $request->input('lang'),
        ], [
            'name' => $request->input('name'),
            'body' => $request->input('body'),
            'descr' => $request->input('descr'),
            'author' => $request->input('author'),
            'text_tag_promo' => $request->input('text_tag_promo'),
            'page_title' => $request->input('page_title'),
            'h1_title' => $request->input('h1_title'),
            'meta_title' => $request->input('meta_title'),
            'meta_keywords' => $request->input('meta_keywords'),
            'meta_description' => $request->input('meta_description'),
        ]);

        $info_item_id->push();

        if ($request->file('upload_files') && $info_item_id)
            uploadMultipleFiles($request->file('upload_files'), $request->input('uploaded_files'), 'info-items', $info_item_id);


        if (is_null($id)) {
            return response()->json([
                'status' => true,
                'messages' => [controllerTrans('variables.save', LANG)],
                'redirect' => urlForLanguage(LANG, 'memberslist')
            ]);
        }
        return response()->json([
            'status' => true,
            'messages' => [controllerTrans('variables.updated_text', LANG)],
            'redirect' => urlForLanguage(LANG, 'editinfoitem/' . $id . '/' . $lang_id)
        ]);
    }

    public function destroyInfoItemFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $info_item_elems_id = InfoItemId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$info_item_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($info_item_elems_id as $one_info_item_elems_id) {

                    $info_item_elems = InfoItem::where('info_item_id', $one_info_item_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($info_item_elems)) {
                        $info_item_elems = InfoItem::where('info_item_id', $one_info_item_elems_id->id)
                            ->first();
                    }

                    if ($one_info_item_elems_id->deleted == 1 && $one_info_item_elems_id->active == 0) {

                        $info_item_images = $one_info_item_elems_id->moduleMultipleImg;

                        if (!is_null($info_item_images) && !$info_item_images->isEmpty()) {
                            foreach ($info_item_images as $info_item_image) {
                                if (File::exists('upfiles/info-items/s/' . showImg($info_item_image->img)))
                                    File::delete('upfiles/info-items/s/' . showImg($info_item_image->img));

                                if (File::exists('upfiles/info-items/m/' . showImg($info_item_image->img)))
                                    File::delete('upfiles/info-items/m/' . showImg($info_item_image->img));

                                if (File::exists('upfiles/info-items/' . $info_item_image->img))
                                    File::delete('upfiles/info-items/' . $info_item_image->img);
                            }
                        }

                        $del_message .= $info_item_elems->name . ', ';

                        InfoItemId::destroy($one_info_item_elems_id->id);
                        InfoItem::where('info_item_id', $one_info_item_elems_id->id)->delete();

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

    public function destroyInfoItemToCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $info_item_elems_id = InfoItemId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$info_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($info_item_elems_id as $one_info_item_elems_id) {

                    $info_item_elems = InfoItem::where('info_item_id', $one_info_item_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($info_item_elems)) {
                        $info_item_elems = InfoItem::where('info_item_id', $one_info_item_elems_id->id)
                            ->first();
                    }

                    if ($one_info_item_elems_id->deleted == 0) {

                        $cart_message .= $info_item_elems->name . ', ';

                        InfoItemId::where('id', $one_info_item_elems_id->id)
                            ->update(['active' => 0, 'deleted' => 1]);
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

    public function restoreInfoItem(Request $request)
    {
        $restored_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($restored_elements_id)) {
            $restored_elements_id_arr = explode(',', $restored_elements_id);

            $info_item_elems_id = InfoItemId::whereIn('id', $restored_elements_id_arr)->get();

            if (!$info_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($info_item_elems_id as $one_info_item_elems_id) {

                    $info_name = GetNameByLang($one_info_item_elems_id->id, LANG_ID, 'InfoItem', 'info_item_id');

                    if ($one_info_item_elems_id->restored == 0) {

                        $cart_message .= $info_name . ', ';

                        InfoItemId::where('id', $one_info_item_elems_id->id)
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

}
