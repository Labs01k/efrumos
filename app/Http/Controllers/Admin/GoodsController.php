<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GoodsExport;
use App\Http\Controllers\Controller;
use App\Models\BrandId;
use App\Models\GoodsColors;
use App\Models\GoodsItem;
use App\Models\GoodsItemId;
use App\Models\GoodsItemVideo;
use App\Models\GoodsMeasure;
use App\Models\GoodsMeasureId;
use App\Models\GoodsMeasureList;
use App\Models\GoodsParametr;
use App\Models\GoodsParametrId;
use App\Models\GoodsParametrItemId;
use App\Models\GoodsParametrItemMeasure;
use App\Models\GoodsParametrItemRsc;
use App\Models\GoodsParametrItemSimple;
use App\Models\GoodsParametrValue;
use App\Models\GoodsParametrValueId;
use App\Models\GoodsPhoto;
use App\Models\GoodsSubject;
use App\Models\GoodsSubjectId;
use App\Models\GoodsSubjectImages;
use App\Models\GoodsTypeId;
use App\Services\GoodsRequest1C\GoodsRequest1C;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class GoodsController extends Controller
{

    public function exportExcel()
    {
        return Excel::download(new GoodsExport(), 'efrumos_export_'. date('d_m_Y_H_i') . '.xlsx');
    }

    public function index(Request $request)
    {
        $view = 'admin.goods.goods-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;


        $goods_subject_id_list = GoodsSubjectId::where('deleted', 0)
            ->where('p_id', 0)
            ->orderBy('position', 'asc')
            ->paginate(config('custom.back.goods_subjects_per_page'));

        $goods_subject_list = [];
        foreach ($goods_subject_id_list as $key => $one_goods_subject_id_list) {
            $goods_subject_list[$key] = GoodsSubject::where('goods_subject_id', $one_goods_subject_id_list->id)
                ->first();
        }

        $goods_subject_list = array_filter($goods_subject_list, 'strlen');

        return view($view, get_defined_vars());
    }

    public function changePosition(Request $request)
    {
        $positionItems = [];
        switch ($request->input('action')) {
            case 'subject':
                $positionItems = GoodsSubjectId::get();
                break;
            case 'item':
                $positionItems = GoodsItemId::get();
                break;
            case 'gallery':
                $positionItems = GoodsPhoto::get();
                break;
            case 'parameter':
                $positionItems = GoodsParametrId::get();
                break;
            case 'parameter_value':
                $positionItems = GoodsParametrValueId::get();
                break;
            case 'goods-subject-gallery':
                $positionItems = GoodsSubjectImages::get();
                break;
            case 'item-video':
                $positionItems = GoodsItemVideo::get();
                break;
            default:
                break;
        }

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


    public function changeActive(Request $request)
    {
        $active = $request->input('active');
        $id = $request->input('id');
        $action = $request->input('action');

        if ($action == 'item')
            $element_id = GoodsItemId::findOrFail($id);
        elseif ($action == 'subject')
            $element_id = GoodsSubjectId::findOrFail($id);
        elseif ($action == 'gallery')
            $element_id = GoodsPhoto::findOrFail($id);
        elseif ($action == 'parameter')
            $element_id = GoodsParametrId::findOrFail($id);
        elseif ($action == 'goods-subject-gallery')
            $element_id = GoodsSubjectImages::findOrFail($id);
        elseif ($action == 'item-video')
            $element_id = GoodsItemVideo::findOrFail($id);
        else
            $element_id = '';

        if (!is_null($element_id)) {
            if ($action == 'item')
                $element_name = GetNameByLang($element_id->id, LANG_ID, 'GoodsItem', 'goods_item_id');
            elseif ($action == 'subject')
                $element_name = GetNameByLang($element_id->id, LANG_ID, 'GoodsSubject', 'goods_subject_id');
            elseif ($action == 'parameter')
                $element_name = GetNameByLang($element_id->id, LANG_ID, 'GoodsParametr', 'goods_parametr_id');
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

        if ($action == 'item')
            GoodsItemId::where('id', $id)->update(['active' => $change_active]);
        elseif ($action == 'subject')
            GoodsSubjectId::where('id', $id)->update(['active' => $change_active]);
        elseif ($action == 'gallery')
            GoodsPhoto::where('id', $id)->update(['active' => $change_active]);
        elseif ($action == 'parameter')
            GoodsParametrId::where('id', $id)->update(['active' => $change_active]);
        elseif ($action == 'goods-subject-gallery')
            GoodsSubjectImages::where('id', $id)->update(['active' => $change_active]);
        elseif ($action == 'item-video')
            GoodsItemVideo::where('id', $id)->update(['active' => $change_active]);

        return response()->json([
            'status' => true,
            'type' => 'info',
            'messages' => [$msg]
        ]);
    }

    public function createGoodsSubject()
    {
        $view = 'admin.goods.create-goods-subject';

        $modules_name = $this->menu()['modules_name'];

        $curr_page_id = GoodsSubjectId::where('alias', request()->segment(4))
            ->first();

        if (!is_null($curr_page_id)) {
            $curr_page_id = $curr_page_id->id;
        } else {
            $curr_page_id = null;
        }

        return view($view, get_defined_vars());

    }

    public function editGoodsSubject(Request $request, $id, $lang_id)
    {
        $view = 'admin.goods.edit-goods-subject';
        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $goods_without_lang = GoodsSubject::where('goods_subject_id', $id)->first();

        if (is_null($goods_without_lang)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $goods_elems = GoodsSubject::where('lang_id', $lang_id)
            ->where('goods_subject_id', $goods_without_lang->goods_subject_id)
            ->first();

        $goods_subject_id = '';

        if (!is_null($goods_without_lang)) {
            $goods_subject_id = GoodsSubjectId::where('id', $goods_without_lang->goods_subject_id)
                ->first();
        } elseif (!is_null($goods_elems)) {
            $goods_subject_id = GoodsSubjectId::where('id', $goods_elems->goods_subject_id)
                ->first();
        }

        if (!empty($goods_subject_id->img))
            $category_list = explode(';', $goods_subject_id->img);
        else
            $category_list = '';

        $goods_subject_photo = [];
        if ($goods_elems)
            $goods_subject_photo = GoodsSubjectImages::where('goods_subject_id', $goods_elems->goods_subject_id)
                ->where('lang_id', $lang_id)
                ->orderBy('position', 'asc')
                ->get();

        return view($view, get_defined_vars());
    }

    function ajaxSaveSliderLink(Request $request)
    {
        $goods_subject_id = intval($request->input('goods_subject_id'));
        $goods_subject_image_id = intval($request->input('goods_subject_image_id'));
        $current_lang_id = intval($request->input('current_lang_id'));
        $link = $request->input('link');

        if (!$goods_subject_id && !$goods_subject_image_id && !$current_lang_id)
            return response()->json([
                'status' => false,
                'type' => 'error',
                'messages' => [controllerTrans('variables.something_wrong', LANG)]
            ]);

        GoodsSubjectImages::where('id', $goods_subject_image_id)
            ->where('goods_subject_id', $goods_subject_id)
            ->where('lang_id', $current_lang_id)
            ->update([
                'link' => $link
            ]);

        return response()->json([
            'status' => true,
            'message' => [controllerTrans('variables.save', LANG)],
        ]);
    }

    public function saveSubject(Request $request, $id, $lang_id)
    {
        if (is_null($id)) {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:goods_subject_id',
                'uploaded_files' => 'nullable|max:10',
                'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'upload_files.*.mimes' => __('variables.custom_image_mime'),
                'upload_files.*.max' => __('variables.custom_image_size'),
            ]);
        } else {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:goods_subject_id,alias,' . $id,
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

        $maxPosition = GetMinPosition('goods_subject_id');
        $level = GetLevel($request->input('p_id'), 'goods_subject_id');

        if ($id) {
            $currentPosition = GetPosition('goods_subject_id', $id);
            $position = $currentPosition;
        } else {
            $position = $maxPosition - 1;
        }

        //Check if lang exist
        if (checkIfLangExist($request->input('lang')) == false)
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.lang_not_exist', LANG)],
            ]);

        $goods_subject_id = GoodsSubjectId::updateOrCreate(['id' => $id], [
            'p_id' => $request->input('p_id'),
            'level' => $level + 1,
            'alias' => $request->input('alias'),
            'section' => $request->input('section'),
            'position' => $position,
            'icon_name' => $request->input('icon_name'),
            'position_promo' => $request->input('position_promo'),
            'img' => $request->file('upload_files') ? uploadMultipleFiles($request->file('upload_files'), $request->input('uploaded_files'), 'goods-subject', 0) : getImageById($id, 'GoodsSubjectId'),
            'img_two' => $request->file('img_two') ? uploadOneFile($request->file('img_two'), 'goods-subject-meta') : getImageNameByColumnName($id, 'img_two', 'GoodsSubjectId')
        ]);

        GoodsSubject::updateOrCreate([
            'goods_subject_id' => $goods_subject_id->id,
            'lang_id' => $request->input('lang'),
        ], [
            'name' => $request->input('name'),
            'short_descr' => $request->input('short_descr'),
            'body' => $request->input('body'),
            'link_banner' => $request->input('link_banner'),
            'page_title' => $request->input('page_title'),
            'h1_title' => $request->input('h1_title'),
            'meta_title' => $request->input('meta_title'),
            'meta_keywords' => $request->input('meta_keywords'),
            'meta_description' => $request->input('meta_description'),
        ]);

        $goods_subject_id->push();

        if (is_null($id)) {
            if ($goods_subject_id->level == 1) {
                return response()->json([
                    'status' => true,
                    'messages' => [controllerTrans('variables.save', LANG)],
                    'redirect' => urlForFunctionLanguage(LANG, '')
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'messages' => [controllerTrans('variables.save', LANG)],
                    'redirect' => urlForFunctionLanguage(LANG, GetParentAlias($goods_subject_id->id, 'goods_subject_id') . '/memberslist')
                ]);
            }
        }
        return response()->json([
            'status' => true,
            'messages' => [controllerTrans('variables.updated_text', LANG)],
            'redirect' => urlForLanguage(LANG, 'editgoodssubject/' . $id . '/' . $lang_id)
        ]);
    }

    public function goodsSubjectCart()
    {
        $view = 'admin.goods.subject-cart';

        $deleted_elems_by_alias = GoodsSubjectId::where('alias', request()->segment(4))
            ->first();

        if (is_null($deleted_elems_by_alias)) {
            $deleted_subject_id_elems = GoodsSubjectId::where('deleted', 1)
                ->where('active', 0)
                ->where('p_id', 0)
                ->get();
        } else {
            $deleted_subject_id_elems = GoodsSubjectId::where('deleted', 1)
                ->where('active', 0)
                ->where('p_id', $deleted_elems_by_alias->id)
                ->get();
        }

        $deleted_subject_elems = [];
        foreach ($deleted_subject_id_elems as $key => $one_deleted_subject_elem) {
            $deleted_subject_elems[$key] = GoodsSubject::where('goods_subject_id', $one_deleted_subject_elem->id)
                ->first();
        }

        $deleted_subject_elems = array_filter($deleted_subject_elems, 'strlen');

        return view($view, get_defined_vars());

    }

    public function destroyGoodsSubjectFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $subject_elems_id = GoodsSubjectId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$subject_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($subject_elems_id as $one_subject_elems_id) {

                    $subject_elems = GoodsSubject::where('goods_subject_id', $one_subject_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($subject_elems)) {
                        $subject_elems = GoodsSubject::where('goods_subject_id', $one_subject_elems_id->id)
                            ->first();
                    }

                    if ($one_subject_elems_id->deleted == 1 && $one_subject_elems_id->active == 0) {

                        $goods_images = $one_subject_elems_id->moduleMultipleImg;

                        if (!is_null($goods_images) && !$goods_images->isEmpty()) {
                            foreach ($goods_images as $goods_image) {
                                if (File::exists('upfiles/goods-items/s/' . showImg($goods_image->img)))
                                    File::delete('upfiles/goods-items/s/' . showImg($goods_image->img));

                                if (File::exists('upfiles/goods-items/m/' . showImg($goods_image->img)))
                                    File::delete('upfiles/goods-items/m/' . showImg($goods_image->img));

                                if (File::exists('upfiles/goods-items/' . $goods_image->img))
                                    File::delete('upfiles/goods-items/' . $goods_image->img);
                            }
                        }

                        if (File::exists('upfiles/goods-subject/s/' . showImg($one_subject_elems_id->img)))
                            File::delete('upfiles/goods-subject/s/' . showImg($one_subject_elems_id->img));

                        if (File::exists('upfiles/goods-subject/m/' . showImg($one_subject_elems_id->img)))
                            File::delete('upfiles/goods-subject/m/' . showImg($one_subject_elems_id->img));

                        if (File::exists('upfiles/goods-subject/' . $one_subject_elems_id->img))
                            File::delete('upfiles/goods-subject/' . $one_subject_elems_id->img);

                        //For meta images
                        if (File::exists('upfiles/goods-subject-meta/s/' . showImg($one_subject_elems_id->img_two)))
                            File::delete('upfiles/goods-subject-meta/s/' . showImg($one_subject_elems_id->img_two));

                        if (File::exists('upfiles/goods-subject-meta/m/' . showImg($one_subject_elems_id->img_two)))
                            File::delete('upfiles/goods-subject-meta/m/' . showImg($one_subject_elems_id->img_two));

                        if (File::exists('upfiles/goods-subject-meta/' . $one_subject_elems_id->img_two))
                            File::delete('upfiles/goods-subject-meta/' . $one_subject_elems_id->img_two);

                        $del_message .= $subject_elems->name . ', ';

                        GoodsSubjectId::destroy($one_subject_elems_id->id);
                        GoodsSubject::where('goods_subject_id', $one_subject_elems_id->id)->delete();

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

    public function destroyGoodsSubjectToCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $subject_elems_id = GoodsSubjectId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$subject_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($subject_elems_id as $one_subject_elems_id) {

                    $subject_elems = GoodsSubject::where('goods_subject_id', $one_subject_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($subject_elems)) {
                        $subject_elems = GoodsSubject::where('goods_subject_id', $one_subject_elems_id->id)
                            ->first();

                    }

                    if ($one_subject_elems_id->deleted == 0) {

                        $cart_message .= $subject_elems->name . ', ';

                        GoodsSubjectId::where('id', $one_subject_elems_id->id)
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

    public function restoreGoodsSubject(Request $request)
    {
        $restored_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($restored_elements_id)) {
            $restored_elements_id_arr = explode(',', $restored_elements_id);

            $goods_item_elems_id = GoodsSubjectId::whereIn('id', $restored_elements_id_arr)->get();

            if (!$goods_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($goods_item_elems_id as $one_goods_item_elems_id) {

                    $goods_name = GetNameByLang($one_goods_item_elems_id->id, LANG_ID, 'GoodsSubject', 'goods_subject_id');

                    if ($one_goods_item_elems_id->restored == 0) {

                        $cart_message .= $goods_name . ', ';

                        GoodsSubjectId::where('id', $one_goods_item_elems_id->id)
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

    public function membersList(Request $request)
    {
        $view = 'admin.goods.child-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $goods_list_id = GoodsSubjectId::where('alias', request()->segment(4))
            ->first();

        if (is_null($goods_list_id)) {
            return App::abort(503, 'Unauthorized action.');
        }

        if (CheckIfSubjectHasOtherItems('goods', $goods_list_id->id)->isEmpty()) {
            $child_goods_list_id = GoodsSubjectId::where('p_id', $goods_list_id->id)
                ->where('deleted', 0)
                ->orderBy('position', 'asc')
                ->paginate(config('custom.back.goods_subjects_per_page'));

            $child_goods_list = [];
            foreach ($child_goods_list_id as $key => $one_goods_elem) {
                $child_goods_list[$key] = GoodsSubject::where('goods_subject_id', $one_goods_elem->id)
                    ->first();
            }

            $child_goods_list = array_filter($child_goods_list, 'strlen');
            $child_goods_item_list = [];

        } else {
            $child_goods_item_list_id = GoodsItemId::where('goods_subject_id', $goods_list_id->id)
                ->where('deleted', 0)
                ->orderBy('position', 'asc')
                ->paginate(config('custom.back.goods_items_per_page'));

            $child_goods_item_list = [];
            foreach ($child_goods_item_list_id as $key => $one_goods_elem) {
                $child_goods_item_list[$key] = GoodsItem::where('goods_item_id', $one_goods_elem->id)
                    ->first();
            }

            $child_goods_item_list = array_filter($child_goods_item_list, 'strlen');

            $child_goods_list = [];
        }

        $color_from_subject = GoodsColors::join('goods_colors_id', 'goods_colors.goods_colors_id', '=', 'goods_colors_id.id')
            ->whereRaw('goods_colors_id IN(SELECT DISTINCT goods_colors_id FROM goods_item_colors WHERE goods_item_id IN(SELECT id FROM goods_item_id WHERE goods_subject_id=' . $goods_list_id->id . '))')
            ->where('p_id', '>', 0)
            ->get();

        $parameter_value_id = GoodsParametrItemRsc::join('goods_parametr_item_id', 'goods_parametr_item_rsc.goods_parametr_item_id', '=', 'goods_parametr_item_id.id')
            ->WhereRaw('goods_item_id IN(Select id from goods_item_id where goods_subject_id=' . $goods_list_id->id . ')')
            ->get();

        return view($view, get_defined_vars());

    }

    public function goodsVideoList($id)
    {
        $view = 'admin.goods.goods-video';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $goods_item_id = GoodsItemId::where('id', $id)
            ->has('itemByLang')
            ->with('itemByLang', 'goodsVideosForBack')
            ->first();

        return view($view, get_defined_vars());

    }

    public function saveGoodsItemVideo(Request $request)
    {
        $item = Validator::make($request->all(), [
            'youtube_link' => 'required',
            'goods_item_id' => 'required',
        ]);

        if ($item->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $item->messages(),
            ]);
        }

        $youtube_id = $this->youtubeId($request->input('youtube_link'));

        //https://www.youtube.com/shorts/L-z4Rfm7H9g

        $maxPosition = GetMinPosition('goods_item_video');
        $position = $maxPosition - 1;

        //Save and update goods item

        $goods_item_video = new GoodsItemVideo();
        $goods_item_video->goods_item_id = $request->input('goods_item_id');
        $goods_item_video->youtube_link = $request->input('youtube_link');
        $goods_item_video->youtube_id = $youtube_id;
        $goods_item_video->position = $position;
        $goods_item_video->save();

        return response()->json([
            'status' => true,
            'messages' => [controllerTrans('variables.save', LANG)],
            'redirect' => urlForLanguage(LANG, 'goodsVideoList/' . $goods_item_video->goods_item_id)
        ]);
    }

    public function destroyGoodsItemVideo(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $subject_elems_id = GoodsItemVideo::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$subject_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($subject_elems_id as $one_subject_elems_id) {

                    $del_message .= 'Video , ';

                    GoodsItemVideo::destroy($one_subject_elems_id->id);
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

    public function createGoodsItem()
    {
        $view = 'admin.goods.create-goods-item';

        $goods_subject_id = GoodsSubjectId::where('alias', request()->segment(4))
            ->first();

        $goods_subject_protection_id = GoodsSubjectId::where('id', $goods_subject_id->p_id)
            ->where('active', 1)
            ->where('deleted', 0)
            ->first();

        $goods_parameters = [];

        if (!is_null($goods_subject_id)) {
            $curr_page_id = $goods_subject_id->id;

            $goods_parameters = GoodsParametrId::where('goods_subject_id', getMainCatalogId())
                ->where('deleted', 0)
                ->where('active', 1)
                ->join('goods_parametr', 'goods_parametr.goods_parametr_id', '=', 'goods_parametr_id.id')
                ->where('lang_id', LANG_ID)
                ->select('*', 'goods_parametr_id.id as id')
                ->orderBy('position', 'asc')
                ->get();

        } else {
            $curr_page_id = null;
            $goods_parameters = [];
        }

        $all_goods_subjects = [];
        GetEndSubjectsList('goods_subject_id', 0, LANG_ID, $all_goods_subjects, 1, 0);

        $goods_types = GoodsTypeId::with('itemByLang')
            ->get()->transform(function ($item) {
                $item->name = $item->itemByLang?->name;
                return $item;
            })->sortBy('name');

        $goods_list = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->with('itemByLang')
            ->get()->transform(function ($item) {
                $item->name = $item->itemByLang?->name;
                return $item;
            })->sortBy('name');

        return view($view, get_defined_vars());
    }

    public function editGoodsItem($id, $lang_id)
    {
        $view = 'admin.goods.edit-goods-item';

        $modules_name = $this->menu()['modules_name'];
        $current_url = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $goods_without_lang = GoodsItem::where('goods_item_id', $id)->first();

        if (is_null($goods_without_lang)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $goods_elems = GoodsItem::where('lang_id', $lang_id)
            ->where('goods_item_id', $goods_without_lang->goods_item_id)
            ->first();

        if (!is_null($goods_without_lang)) {
            $goods_item_id = GoodsItemId::where('id', $goods_without_lang->goods_item_id)
                ->first();
        } elseif (!is_null($goods_elems)) {
            $goods_item_id = GoodsItemId::where('id', $goods_elems->goods_item_id)
                ->first();
        }

        $goods_subject_id = GoodsSubjectId::where('id', $goods_item_id->goods_subject_id)->where('alias', request()->segment(4))->first();

        if (is_null($goods_subject_id)) {
            $array_subjects = explode(',', $goods_item_id->other_goods_subject_id);

            $goods_subject_id = GoodsSubjectId::whereIN('id', $array_subjects)->where('alias', request()->segment(4))->first();
        }

        $goods_parameters = [];

        if (!is_null($goods_subject_id)) {
            $goods_subject_id = $goods_subject_id->id;

            $goods_parameters = GoodsParametrId::where('goods_subject_id', getMainCatalogId())
                ->where('deleted', 0)
                ->where('active', 1)
                ->join('goods_parametr', 'goods_parametr.goods_parametr_id', '=', 'goods_parametr_id.id')
                ->where('lang_id', $lang_id)
                ->select('*', 'goods_parametr_id.id as id')
                ->orderBy('position', 'asc')
                ->get();

        } else {
            $goods_subject_id = null;
            $goods_parameters = [];
        }

        $all_goods_subjects = [];
        GetEndSubjectsList('goods_subject_id', 0, $lang_id, $all_goods_subjects, 1, 0);

        $used_subjects = [];
        if (!empty($goods_item_id)) {
            $used_subjects = explode(',', $goods_item_id->other_goods_subject_id);
        }

        $current_subject_id = GoodsSubjectId::where('alias', request()->segment(4))->first();

        $goods_photo = [];
        if ($goods_elems)
            $goods_photo = GoodsPhoto::where('goods_item_id', $goods_elems->goods_item_id)
                ->orderBy('position', 'asc')
                ->get();

        $goods_types = GoodsTypeId::with('itemByLang')
            ->get()->transform(function ($item) {
                $item->name = $item->itemByLang?->name;
                return $item;
            })->sortBy('name');

        $goods_list = GoodsItemId::where('active', 1)
            ->where('deleted', 0)
            ->with('itemByLang')
            ->get()->transform(function ($item) {
                $item->name = $item->itemByLang?->name;
                return $item;
            })->sortBy('name');

        return view($view, get_defined_vars());
    }

    public function saveItem(Request $request, $id, $lang_id)
    {
        if (is_null($id)) {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:goods_item_id',
                'uploaded_files' => 'nullable|max:10',
                'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'upload_files.*.mimes' => __('variables.custom_image_mime'),
                'upload_files.*.max' => __('variables.custom_image_size'),
            ]);
        } else {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:goods_item_id,alias,' . $id,
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

        if (!empty($request->input('add_date'))) {
            $add_date = date('Y-m-d', strtotime($request->input('add_date')));
        } else {
            $add_date = date('Y-m-d');
        }

        if (checkIfLangExist($request->input('lang')) == false)
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.lang_not_exist', LANG)],
            ]);

        /*if (!is_null($request->input('youtube_id')))
            $youtube_id = $request->input('youtube_id');
        elseif (is_null($request->input('youtube_id')) && !is_null($request->input('youtube_link')))
            $youtube_id = $this->youtubeId($request->input('youtube_link'));
        else
            $youtube_id = null;*/

        $subjects_array = [];
        if (!empty($request->input('subject'))) {
            foreach ($request->input('subject') as $one_subject)
                array_push($subjects_array, $one_subject);
        }

        $subjects_array = implode(',', $subjects_array);

        if ($id) {
            $currentPosition = GetPosition('goods_item_id', $id);
            $position = $currentPosition;
        } else {
            $maxPosition = GetMinPosition('goods_item_id');
            $position = $maxPosition - 1;
        }

        //Save and update goods item

        $goods_item_id = GoodsItemId::updateOrCreate(['id' => $id], [
            'goods_subject_id' => $request->input('p_id'),
            'brand_id' => $request->input('brand_id'),
            'position' => $position,
            'other_goods_subject_id' => $subjects_array,
            'alias' => $request->input('alias'),
            'one_c_code' => $request->input('one_c_code'),
            'price' => $request->input('price'),
            'price_promo' => $request->input('price_promo'),
            'youtube_link' => $request->input('youtube_link'),
            'products_count' => $request->input('products_count') ?? 0,
            'articol' => $request->input('articol'),
            'barcode' => $request->input('barcode'),
            'popular_element' => $request->input('popular_element') == 'on' ? 1 : 0,
            'new_element' => $request->input('new_element') == 'on' ? 1 : 0,
            'in_stoc' => $request->input('in_stoc') == 'on' ? 1 : 0,
            'show_in_search' => $request->input('show_in_search') == 'on' ? 1 : 0,
            'add_date' => $add_date,
            'produse_compatibile' => $request->input('produse_compatibile') ? implode(',', $request->input('produse_compatibile')) : null,
            'produse_similare' => $request->input('produse_similare') ? implode(',', $request->input('produse_similare')) : null,
            'goods_type_id' => $request->input('goods_type_id') ? $request->input('goods_type_id') : NULL,
            'b2b_type' => $request->input('b2b_type'),
            //'active' => 1,
            //'deleted' => 0,
        ]);

        GoodsItem::updateOrCreate([
            'goods_item_id' => $goods_item_id->id,
            'lang_id' => $request->input('lang'),
        ], [
            'name' => $request->input('name'),
            'short_descr' => $request->input('short_descr'),
            'body' => $request->input('body'),
            'body_two' => $request->input('body_two'),
            'page_title' => $request->input('page_title'),
            'h1_title' => $request->input('h1_title'),
            'meta_title' => $request->input('meta_title'),
            'meta_keywords' => $request->input('meta_keywords'),
            'meta_description' => $request->input('meta_description'),
        ]);

        $goods_item_id->push();

        if ($request->file('upload_files') && $goods_item_id)
            uploadMultipleFiles($request->file('upload_files'), $request->input('uploaded_files'), 'goods-items', $goods_item_id);

        $goods_parameter_id = GoodsParametrId::where('goods_subject_id', getMainCatalogId())
            ->where('deleted', 0)
            ->where('active', 1)
            ->orderBy('position', 'asc')
            ->get();

        //Save and update goods parameters
        if (!empty($goods_parameter_id)) {

            foreach ($goods_parameter_id as $one_parameter_id) {

                $goods_parametr_item_id = GoodsParametrItemId::updateOrCreate([
                    'goods_item_id' => $goods_item_id->id,
                    'goods_parametr_id' => $one_parameter_id->id
                ], []);

                switch ($one_parameter_id->parametr_type) {
                    case 'textarea':

                        GoodsParametrItemSimple::updateOrCreate([
                            'goods_parametr_item_id' => $goods_parametr_item_id->id,
                            'lang_id' => $request->input('lang'),
                        ], [
                            'parametr_value' => $request->input('parametr_' . $one_parameter_id->id)['parametr_value'] ?? null
                        ]);

                        break;

                    case 'input':
                        switch ($one_parameter_id->measure_type) {
                            case 'no_measure':

                                GoodsParametrItemSimple::updateOrCreate([
                                    'goods_parametr_item_id' => $goods_parametr_item_id->id,
                                    'lang_id' => $request->input('lang'),
                                ], [
                                    'parametr_value' => $request->input('parametr_' . $one_parameter_id->id)['parametr_value'] ?? null
                                ]);

                                break;

                            case 'with_measure':

                                GoodsParametrItemMeasure::updateOrCreate([
                                    'goods_parametr_item_id' => $goods_parametr_item_id->id,
                                ], [
                                    'goods_measure_id' => 0,
                                    'parametr_value' => $request->input('parametr_' . $one_parameter_id->id)['parametr_value'] ?? null
                                ]);

                                break;

                            case 'measure_list':

                                GoodsParametrItemMeasure::updateOrCreate([
                                    'goods_parametr_item_id' => $goods_parametr_item_id->id,
                                ], [
                                    'goods_measure_id' => $request->input('parametr_' . $one_parameter_id->id)['goods_measure_id'] ?? null,
                                    'parametr_value' => $request->input('parametr_' . $one_parameter_id->id)['parametr_value'] ?? null
                                ]);

                                break;

                            default:
                                break;
                        }
                        break;

                    case 'radio':
                    case 'select':

                        GoodsParametrItemRsc::where('goods_parametr_item_id', $goods_parametr_item_id->id)->delete();

                        if (!empty($request->input('parametr_' . $one_parameter_id->id)['goods_parametr_value_id'])) {
                            GoodsParametrItemRsc::updateOrCreate([
                                'goods_parametr_item_id' => $goods_parametr_item_id->id,
                                'goods_parametr_value_id' => $request->input('parametr_' . $one_parameter_id->id)['goods_parametr_value_id'],
                            ], []);
                        }

                        break;

                    case 'checkbox':

                        GoodsParametrItemRsc::where('goods_parametr_item_id', $goods_parametr_item_id->id)->delete();

                        if (!empty($request->input('parametr_' . $one_parameter_id->id)['goods_parametr_value_id'])) {
                            foreach ($request->input('parametr_' . $one_parameter_id->id)['goods_parametr_value_id'] as $pv) {

                                GoodsParametrItemRsc::updateOrCreate([
                                    'goods_parametr_item_id' => $goods_parametr_item_id->id,
                                    'goods_parametr_value_id' => $pv,
                                ], []);
                            }
                        }
                        break;
                    default:
                        break;
                }
            }
        }

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
            'redirect' => urlForLanguage(LANG, 'editgoodsitem/' . $id . '/' . $lang_id)
        ]);
    }

    public function goodsItemCart()
    {
        $view = 'admin.goods.item-cart';

        $deleted_elems_by_alias = GoodsSubjectId::where('alias', request()->segment(4))
            ->first();

        $deleted_item_id_elems = [];

        if (!is_null($deleted_elems_by_alias))
            $deleted_item_id_elems = GoodsItemId::where('deleted', 1)
                ->where('active', 0)
                ->where('goods_subject_id', $deleted_elems_by_alias->id)
                ->get();

        $deleted_item_elems = [];
        if (!empty($deleted_item_id_elems)) {
            foreach ($deleted_item_id_elems as $key => $one_deleted_item_elem) {
                $deleted_item_elems[$key] = GoodsItem::where('goods_item_id', $one_deleted_item_elem->id)
                    ->first();
            }
        }

        $deleted_item_elems = array_filter($deleted_item_elems, 'strlen');

        return view($view, get_defined_vars());
    }

    public function destroyGoodsItemFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $goods_item_elems_id = GoodsItemId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$goods_item_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($goods_item_elems_id as $one_goods_item_elems_id) {

                    if ($one_goods_item_elems_id->deleted == 1 && $one_goods_item_elems_id->active == 0) {

                        $goods_photo = GoodsPhoto::where('goods_item_id', $one_goods_item_elems_id->id)->get();

                        if (!is_null($goods_photo)) {

                            foreach ($goods_photo as $one_goods_photo) {

                                if (File::exists('upfiles/goods-items/s/' . showImg($one_goods_photo->img)))
                                    File::delete('upfiles/goods-items/s/' . showImg($one_goods_photo->img));

                                if (File::exists('upfiles/goods-items/m/' . showImg($one_goods_photo->img)))
                                    File::delete('upfiles/goods-items/m/' . showImg($one_goods_photo->img));

                                if (File::exists('upfiles/goods-items/' . $one_goods_photo->img))
                                    File::delete('upfiles/goods-items/' . $one_goods_photo->img);
                            }
                        }

                        $goods_item_elems = GoodsItem::where('goods_item_id', $one_goods_item_elems_id->id)
                            ->first();

                        $del_message .= $goods_item_elems->name . ', ';

                        GoodsItemId::destroy($one_goods_item_elems_id->id);

                        GoodsItem::where('goods_item_id', $one_goods_item_elems_id->id)->delete();
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

    public function destroyGoodsItemToCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {

            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $goods_item_elems_id = GoodsItemId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$goods_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($goods_item_elems_id as $one_goods_item_elems_id) {

                    if ($one_goods_item_elems_id->deleted == 0) {

                        $goods_item_elems = GoodsItem::where('goods_item_id', $one_goods_item_elems_id->id)
                            ->where('lang_id', LANG_ID)
                            ->first();

                        $cart_message .= $goods_item_elems->name . ', ';

                        GoodsItemId::where('id', $one_goods_item_elems_id->id)
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

    public function restoreGoodsItem(Request $request)
    {
        $restored_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($restored_elements_id)) {

            $restored_elements_id_arr = explode(',', $restored_elements_id);

            $goods_item_elems_id = GoodsItemId::whereIn('id', $restored_elements_id_arr)->get();

            if (!$goods_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($goods_item_elems_id as $one_goods_item_elems_id) {

                    $goods_name = GetNameByLang($one_goods_item_elems_id->id, LANG_ID, 'GoodsItem', 'goods_item_id');

                    if ($one_goods_item_elems_id->restored == 0) {

                        $cart_message .= $goods_name . ', ';

                        GoodsItemId::where('id', $one_goods_item_elems_id->id)
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

    public function destroyGoodsPhoto(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');
        $upload_path_optional = $request->input('upload_path_optional');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            if ($upload_path_optional == 'goods-subject-gallery')
                $images = GoodsSubjectImages::whereIn('id', $deleted_elements_id_arr)
                    ->get();
            elseif ($upload_path_optional == 'goods-items')
                $images = GoodsPhoto::whereIn('id', $deleted_elements_id_arr)
                    ->get();

            if (!$images->isEmpty()) {
                foreach ($images as $one_image) {
                    if (File::exists('upfiles/' . $upload_path_optional . '/s/' . showImg($one_image->img)))
                        File::delete('upfiles/' . $upload_path_optional . '/s/' . showImg($one_image->img));

                    if (File::exists('upfiles/' . $upload_path_optional . '/m/' . showImg($one_image->img)))
                        File::delete('upfiles/' . $upload_path_optional . '/m/' . showImg($one_image->img));

                    if (File::exists('upfiles/' . $upload_path_optional . '/' . $one_image->img))
                        File::delete('upfiles/' . $upload_path_optional . '/' . $one_image->img);

                    if ($upload_path_optional == 'goods-subject-gallery')
                        GoodsSubjectImages::destroy($one_image->id);
                    elseif ($upload_path_optional == 'goods-items')
                        GoodsPhoto::destroy($one_image->id);
                }

                $del_message = controllerTrans('variables.the_photos', LANG) . ' ' . controllerTrans('variables.success_deleted', LANG);

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
        }

        return response()->json([
            'status' => false
        ]);
    }

    public function requestGoodsFrom1C()
    {
        $view = 'admin.goods.goods-request-1C';

        return view($view, get_defined_vars());
    }

    /**
     * @param Request $request
     * @param GoodsRequest1C $goodsRequest1C
     * @return \Illuminate\Http\JsonResponse
     * @throws \SoapFault
     */
    public function actionRequestGoodsFrom1C(Request $request, GoodsRequest1C $goodsRequest1C)
    {
        $item = Validator::make($request->all(), [
            'one_c_code' => 'required',
            'goods_action' => [
                'required',
                Rule::in(['update_goods', 'show_goods']),
            ]
        ]);

        if ($item->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $item->messages(),
            ]);
        }

        $goods_one_c_code = $request->input('one_c_code');
        $goods_one_c_code_array = explode(',', $goods_one_c_code);
        $goods_action = $request->input('goods_action');
        $goods_items = [];
        $goods_guid_array = [];
        $render_goods_request_1C = null;

        if ($goods_one_c_code_array && is_array($goods_one_c_code_array)) {

            $goods_guid_array = GoodsItemId::where('active', 1)
                ->where('deleted', 0)
                ->whereIn('one_c_code', $goods_one_c_code_array)
                ->pluck('one_c_code_guid')
                ->toArray();

            if (!empty($goods_guid_array) && count($goods_guid_array)){
                $goods_items = $goodsRequest1C->actionRequestGoodsFrom1C($goods_guid_array);

                switch ($goods_action) {
                    case 'update_goods':

                        foreach ($goods_items as $item) {

                            $goods_item_id = GoodsItemId::where('one_c_code', $item['Code1C'])->first();

                            if (!is_null($goods_item_id)) {

                                $total_rest_sum = 0;
                                if(isset($item['Rests']) && !empty($item['Rests']) && count($item['Rests'])){
                                    foreach ($item['Rests'] as $one_store_rest){
                                        if($one_store_rest['StoreId'] == config('custom.main_store_rest_id')){
                                            $total_rest_sum = $one_store_rest['Rest'];
                                        }
                                    }
                                    //$total_rest_sum = collect($item['Rests'])->sum('Rest'); // for multiple stores
                                }

                                $data = [
                                    'one_c_code_guid' => $item['GUID'],
                                    'articol' => $item['Articul'],
                                    'barcode' => $item['BarCode'],
                                    'price' => $item['MainPrice'],
                                    'price_promo' => $item['Price'] < $item['MainPrice'] ? $item['Price'] : null,
                                    'products_count' => $total_rest_sum,
                                    'in_stoc' => $total_rest_sum > 0 ? 1 : 0,
                                    'gramaj' => $item['Vol'],
                                ];

                                $goods_item_id->update($data);

                                /*$data_ro = [
                                    'name' => $item['DescriptionRom'],
                                ];

                                $data_ru = [
                                    'name' => $item['DescriptionRu'],
                                ];

                                GoodsItem::where('lang_id', 2)->where('goods_item_id', $goods_item_id->id)->update($data_ro);
                                GoodsItem::where('lang_id', 3)->where('goods_item_id', $goods_item_id->id)->update($data_ru);*/

                            }

                        }

                        break;
                    case 'show_goods':
                        $render_goods_request_1C = view('admin.goods.templates.goods-request-1C-render', ['goods_items' => $goods_items])->render();
                        break;
                    default:
                        break;
                }
            }
        }

        return response()->json([
            'status' => true,
            'messages' => [controllerTrans('variables.update_success', LANG)],
            'render_goods_request' => $render_goods_request_1C,
        ]);
    }

    public function goodsParameters($id)
    {
        $view = 'admin.parameters.parameters-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $goods_subject_id = GoodsSubjectId::find($id);

        if (is_null($goods_subject_id)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $goods_parameter_id = GoodsParametrId::where('goods_subject_id', $id)
            ->where('deleted', 0)
            ->orderBy('position', 'asc')
            ->paginate(config('custom.back.goods_parameters_per_page'));

        if (!empty($goods_parameter_id)) {
            $goods_parameter = [];

            foreach ($goods_parameter_id as $key => $one_goods_parameter_id) {
                $goods_parameter[$key] = GoodsParametr::where('goods_parametr_id', $one_goods_parameter_id->id)
                    ->first();
            }

            $goods_parameter = array_filter($goods_parameter);
        } else {
            $goods_parameter = [];
        }

        return view($view, get_defined_vars());
    }

    public function createGoodsParameter()
    {
        $view = 'admin.parameters.create-parameter';

        $subject_id = (int)request()->segment(6);
        $goods_subject_id = GoodsSubjectId::findOrFail($subject_id);

        $measure_id = GoodsMeasureId::where('active', 1)
            ->orderBy('position', 'asc')
            ->get();

        $measure = [];
        foreach ($measure_id as $key => $one_measure_id) {
            $measure[$key] = GoodsMeasure::where('goods_measure_id', $one_measure_id->id)
                ->where('lang_id', LANG_ID)
                ->first();
        }

        $measure = array_filter($measure);

        return view($view, get_defined_vars());
    }

    public function editGoodsParameter($id, $lang_id)
    {
        $view = 'admin.parameters.edit-parameter';
        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $goods_parameter_without_lang = GoodsParametr::where('goods_parametr_id', $id)->first();

        if (is_null($goods_parameter_without_lang)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $goods_parametr = GoodsParametr::where('lang_id', $lang_id)
            ->where('goods_parametr_id', $goods_parameter_without_lang->goods_parametr_id)
            ->first();

        if (!is_null($goods_parametr)) {
            $goods_parametr_id = GoodsParametrId::where('id', $goods_parametr->goods_parametr_id)
                ->first();
        } else {
            $goods_parametr_id = GoodsParametrId::where('id', $goods_parameter_without_lang->goods_parametr_id)
                ->first();
        }

        $goods_subject_id = GoodsSubjectId::where('id', $goods_parametr_id->goods_subject_id)->first();

        $measure_id = GoodsMeasureId::where('active', 1)
            ->orderBy('position', 'asc')
            ->get();
        $measure = [];

        foreach ($measure_id as $key => $one_measure_id) {
            $measure[$key] = GoodsMeasure::where('goods_measure_id', $one_measure_id->id)
                ->where('lang_id', $lang_id)
                ->first();
        }

        $measure = array_filter($measure);

        $measure_list = GoodsMeasureList::where('goods_parametr_id', $id)
            ->orderBy('position', 'asc')
            ->get();

        $goods_parameter_value_id = GoodsParametrValueId::where('goods_parametr_id', $id)
            ->orderBy('position', 'asc')
            ->get();

        if (!empty($goods_parameter_value_id)) {
            $goods_parameter_value = [];

            foreach ($goods_parameter_value_id as $key => $one_parameter_value_id) {
                $goods_parameter_value[$key] = GoodsParametrValue::where('goods_parametr_value_id', $one_parameter_value_id->id)
                    ->first();
            }

            $goods_parameter_value = array_filter($goods_parameter_value);
        } else {
            $goods_parameter_value = [];
        }

        return view($view, get_defined_vars());
    }

    public function saveGoodsParameter(Request $request, $id, $lang_id)
    {
        if (is_null($id)) {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required',
            ]);
        } else {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required',
            ]);
        }

        if ($item->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $item->messages(),
            ]);
        }

        if ($id) {
            $currentPosition = GetPosition('goods_parametr_id', $id);
            $position = $currentPosition;
        } else {
            $maxPosition = GetMinPosition('goods_parametr_id');
            $position = $maxPosition - 1;
        }


        $parametr_type_value = $request->input('parametr_type_value');
        $goods_measure_list = $request->input('goods_measure_list');

        if (checkIfLangExist($request->input('lang')) == false)
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.lang_not_exist', LANG)],
            ]);

        if ($request->input('measure_type') == 'with_measure') {
            $goods_measure_id = $request->input('goods_measure_id');
        } else {
            $goods_measure_id = null;
        }

        if ($request->input('parametr_type') == 'textarea' || $request->input('parametr_type') == 'select' || $request->input('parametr_type') == 'radio' || $request->input('parametr_type') == 'checkbox') {
            $goods_measure_id = null;
            $measure_type = 'no_measure';
        } else {
            $measure_type = $request->input('measure_type');
        }

        //Save Parameters
        $parameter_id = GoodsParametrId::updateOrCreate(['id' => $id], [
            'goods_subject_id' => $request->input('goods_subject_id'),
            'measure_type' => $measure_type,
            'goods_measure_id' => $goods_measure_id,
            'parametr_type' => $request->input('parametr_type'),
            'alias' => $request->input('alias'),
            'position' => $position,
            //'active' => 1,
            //'deleted' => 0,
            'show_in_list' => $request->input('show_in_list') == 'on' ? 1 : 0,
            'font_for_list' => $request->input('font_for_list') ?? null,
            'display_on_list_page' => $request->input('display_on_list_page') == 'on' ? 1 : 0,
            'start_open' => $request->input('start_open') == 'on' ? 1 : 0,
            'display_in_line' => $request->input('display_in_line') == 'on' ? 1 : 0,
            'for_basket' => $request->input('for_basket') == 'on' ? 1 : 0,
            //'is_color' => $request->input('is_color') == 'on' ? 1 : 0,
        ]);

        GoodsParametr::updateOrCreate([
            'goods_parametr_id' => $parameter_id->id,
            'lang_id' => $request->input('lang'),
        ], [
            'name' => $request->input('name'),
            'body' => $request->input('body'),
        ]);

        $parameter_id->push();

        //Save Parameter Values
        if ($request->input('parametr_type') == 'select' || $request->input('parametr_type') == 'radio' || $request->input('parametr_type') == 'checkbox') {

            $i = 0;
            foreach ($parametr_type_value as $one_parameter_id => $one_parameter_val) {

                $goods_parameter_value_id = GoodsParametrValueId::updateOrCreate([
                    'id' => $id ? $one_parameter_id : 0
                ], [
                    'goods_parametr_id' => $parameter_id->id,
                    'position' => $i,
                    'active' => 1
                ]);

                $i++;

                GoodsParametrValue::updateOrCreate([
                    'goods_parametr_value_id' => $goods_parameter_value_id->id,
                    'lang_id' => $request->input('lang'),
                ], [
                    'name' => $one_parameter_val,
                ]);

                $goods_parameter_value_id->push();
            }
        }

        if ($request->input('measure_type') == 'measure_list') {

            GoodsMeasureList::where('goods_parametr_id', $parameter_id->id)->delete();

            $i = 0;
            foreach ($goods_measure_list as $one_goods_measure_id => $one_goods_measure_val) {
                GoodsMeasureList::updateOrCreate([
                    'id' => $one_goods_measure_id,
                ], [
                    'goods_parametr_id' => $parameter_id->id,
                    'goods_measure_id' => $one_goods_measure_val,
                    'position' => $i
                ]);

                $i++;
            }
        }

        if (is_null($id)) {
            return response()->json([
                'status' => true,
                'messages' => [controllerTrans('variables.save', LANG)],
                'redirect' => urlForLanguage(LANG, 'goodsparameters/' . $request->input('goods_subject_id'))
            ]);
        }

        return response()->json([
            'status' => true,
            'messages' => [controllerTrans('variables.updated_text', LANG)],
            'redirect' => urlForLanguage(LANG, 'editgoodsparameter/' . $id . '/' . $lang_id)
        ]);

    }

    public function goodsParameterCart()
    {
        $view = 'admin.parameters.parameter-cart';

        $parameter_subject_id = (int)request()->segment(6);

        $deleted_parameters = GoodsParametrId::where('deleted', 1)
            ->where('active', 0)
            ->where('goods_subject_id', $parameter_subject_id)
            ->get();

        return view($view, get_defined_vars());
    }

    public function destroyGoodsParameterFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $parameter_elems_id = GoodsParametrId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$parameter_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($parameter_elems_id as $one_banner_item_elems_id) {

                    $parameter_elems = GoodsParametr::where('goods_parametr_id', $one_banner_item_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($parameter_elems)) {
                        $parameter_elems = GoodsParametr::where('goods_parametr_id', $one_banner_item_elems_id->id)
                            ->first();
                    }

                    if ($one_banner_item_elems_id->deleted == 1 && $one_banner_item_elems_id->active == 0) {

                        $del_message .= $parameter_elems->name . ', ';

                        GoodsParametrId::destroy($one_banner_item_elems_id->id);
                        GoodsParametr::where('goods_parametr_id', $one_banner_item_elems_id->id)->delete();
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

    public function destroyGoodsParameterToCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $parameter_elems_id = GoodsParametrId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$parameter_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($parameter_elems_id as $one_parameter_elems_id) {

                    $parameter_elems = GoodsParametr::where('goods_parametr_id', $one_parameter_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($parameter_elems)) {
                        $parameter_elems = GoodsParametr::where('goods_parametr_id', $one_parameter_elems_id->id)
                            ->first();
                    }

                    if ($one_parameter_elems_id->deleted == 0) {

                        $cart_message .= $parameter_elems->name . ', ';

                        GoodsParametrId::where('id', $one_parameter_elems_id->id)
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

    public function restoreGoodsParameter(Request $request)
    {
        $restored_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($restored_elements_id)) {

            $restored_elements_id_arr = explode(',', $restored_elements_id);
            $parameter_item_elems_id = GoodsParametrId::whereIn('id', $restored_elements_id_arr)->get();

            if (!$parameter_item_elems_id->isEmpty()) {

                $cart_message = '';
                foreach ($parameter_item_elems_id as $one_parameter_item_elems_id) {

                    $parameter_name = GetNameByLang($one_parameter_item_elems_id->id, LANG_ID, 'GoodsParametr', 'goods_parametr_id');
                    if ($one_parameter_item_elems_id->restored == 0) {

                        if (!empty($parameter_name))
                            $cart_message .= $parameter_name . ', ';

                        GoodsParametrId::where('id', $one_parameter_item_elems_id->id)
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
                'status' => false,
                'mes' => 'tut'
            ]);
        }
    }

    public function removeParameter(Request $request)
    {
        $parameter_val_id = $request->input('action');

        $count_goods_parameter_value_id = GoodsParametrValueId::where('goods_parametr_id', $request->input('param_id'))->count();

        if ($count_goods_parameter_value_id > 1) {

            GoodsParametrValue::where('goods_parametr_value_id', $parameter_val_id)->delete();
            GoodsParametrValueId::where('id', $parameter_val_id)->delete();

            return response()->json([
                'status' => true,
                'messages' => controllerTrans('variables.removed', LANG),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'messages' => controllerTrans('variables.min_one_elem', LANG),
            ]);
        }
    }

    public function searchObjects(Request $request)
    {
        $view = 'admin.goods.search-object';
        $search_key = $request->except('_token', 'goods_subject');
        $child_goods_item_list = [];
        $concrete_search_key = trim($request->input('search-key'));
        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;
        $new_url = "";

        $goods_subject_id = null;
        if (!is_null(request()->segment(4)))
            $goods_subject_id = GoodsSubjectId::where('alias', request()->segment(4))
                ->first();

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

                $child_goods_item_list = GoodsItem::leftjoin('goods_item_id', 'goods_item_id.id', '=', 'goods_item.goods_item_id')
                    ->where('goods_item.lang_id', LANG_ID)
                    ->with('goodsItemId', 'goodsItemId.getSubjectId')
                    ->where(function ($q) use ($concrete_search_key) {
                        $q->orWhere('goods_item.name', 'like', '%' . $concrete_search_key . '%');
                        $q->orWhere('goods_item_id.one_c_code', 'like', '%' . $concrete_search_key . '%');
                        $q->orWhere('goods_item_id.articol', 'like', '%' . $concrete_search_key . '%');
                        $q->orWhere('goods_item_id.barcode', 'like', '%' . $concrete_search_key . '%');
                    })
                    ->orderBy('position', 'asc')
                    ->paginate(config('custom.back.goods_items_per_page'));

                $child_goods_item_list->setPath(url(LANG, ['back', 'goods', 'search', 'searchObjects']) . '?search-key=' . $concrete_search_key);

                if ($child_goods_item_list->isEmpty()) {
                    $child_goods_item_list = [];
                }
            }
        }

        return view($view, get_defined_vars());
    }

    public function youtubeId($youtube_link = null)
    {
        if (is_null($youtube_link))
            $code = request()->input('code');
        else
            $code = $youtube_link;

        if (!empty($code)) {
            if (FindYoutubeImg($code)) {
                $youtube_img = FindYoutubeImg($code);
            } else {
                $youtube_img = '';
            }
        } else {
            $youtube_img = '';
        }

        return $youtube_img;

    }

    public function changeGoodItemCount(Request $request){

        $goods_item_id = (int)$request->get('goods_item_id');
        $products_count = (int)$request->get('products_count');

        if(is_null($goods_item_id))
            return response()->json([
                'status' => false,
                'messages' => controllerTrans('variables._wrong_message', LANG)
            ]);

        GoodsItemId::where('deleted', 0)
            ->where('id', $goods_item_id)
            ->update([
                'products_count' => $products_count,
            ]);

        return response()->json([
            'status' => true,
            'products_count' => $products_count,
            'messages' => controllerTrans('variables.save', LANG),
        ]);
    }

}

