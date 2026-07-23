<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannerTop;
use App\Models\BannerTopId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{
    public function index()
    {
        $view = 'admin.slider.sliders-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $slider_list_ids = BannerTopId::where('deleted', 0)
            ->orderBy('position', 'asc')
            ->paginate(config('custom.back.slider_items_per_page'));

        $banner_list = [];
        foreach ($slider_list_ids as $key => $one_slider_ids) {
            $banner_list[$key] = BannerTop::where('banner_top_id', $one_slider_ids->id)
                ->first();
        }
        //Remove all null values --start
        $banner_list = array_filter($banner_list, 'strlen');
        //Remove all null values --end

        return view($view, get_defined_vars());
    }

    public function cartItems()
    {
        $view = 'admin.slider.slider-cart';

        $deleted_banner_id = BannerTopId::where('deleted', 1)
            ->where('active', 0)
            ->get();

        $deleted_banner_list = [];

        foreach ($deleted_banner_id as $key => $one_deleted_banner_id) {
            $deleted_banner_list[$key] = BannerTop::where('banner_top_id', $one_deleted_banner_id->id)
                ->first();
        }

        $deleted_banner_list = array_filter($deleted_banner_list, 'strlen');
        return view($view, get_defined_vars());
    }

    public function changePosition(Request $request)
    {
        $positionItems = BannerTopId::get();
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

    // ajax response for active
    public function changeActive(Request $request)
    {
        $active = $request->input('active');
        $id = $request->input('id');

        $element_id = BannerTopId::findOrFail($id);

        if (!is_null($element_id))
            $element_name = GetNameByLang($element_id->id, LANG_ID, 'BannerTop', 'banner_top_id');
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

        BannerTopId::where('id', $id)->update(['active' => $change_active]);

        return response()->json([
            'status' => true,
            'type' => 'info',
            'messages' => [$msg]
        ]);
    }

    public function createItem()
    {
        $view = 'admin.slider.create-slider';
        $modules_name = $this->menu()['modules_name'];

        return view($view, get_defined_vars());
    }

    public function editItem(Request $request, $id, $lang_id)
    {
        if ($request->input('mob_slide') == 1)
            $view = 'admin.slider.add-mob-slider';
        else
            $view = 'admin.slider.edit-slider';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $banner_top_without_lang = BannerTop::where('banner_top_id', $id)
            ->first();

        if (is_null($banner_top_without_lang)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $banner_top = BannerTop::where('banner_top_id', $banner_top_without_lang->banner_top_id)
            ->where('lang_id', $lang_id)
            ->first();

        if (!is_null($banner_top_without_lang)) {
            $banner_top_id = BannerTopId::where('id', $banner_top_without_lang->banner_top_id)
                ->first();
        } elseif (!is_null($banner_top)) {
            $banner_top_id = BannerTopId::where('id', $banner_top->banner_top_id)
                ->first();
        }

        return view($view, get_defined_vars());
    }

    public function save(Request $request, $id, $lang_id)
    {
        $item = Validator::make($request->all(), [
            'name' => 'required',
            'uploaded_files' => 'nullable|max:10',
            'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'upload_files.*.mimes' => __('variables.custom_image_mime'),
            'upload_files.*.max' => __('variables.custom_image_size'),
        ]);

        if ($item->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $item->messages(),
            ]);
        }

        $maxPosition = GetMinPosition('banner_top_id');

        if ($id) {
            $currentPosition = GetPosition('banner_top_id', $id);
            $position = $currentPosition;
        } else {
            $position = $maxPosition - 1;
        }

        if (checkIfLangExist($request->input('lang')) == false)
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.lang_not_exist', LANG)],
            ]);

        $banner_top_id = BannerTopId::updateOrCreate(['id' => $id], [
            'position' => $position,
            //'active' => $active,
            //'deleted' => 0,
        ]);

        BannerTop::updateOrCreate([
            'banner_top_id' => $banner_top_id->id,
            'lang_id' => $request->input('lang'),
        ], [
            'name' => $request->input('name'),
            'body' => $request->input('body'),
            'link' => $request->input('link'),
            'img' => $request->file('upload_files') ? uploadMultipleFiles($request->file('upload_files'), $request->input('uploaded_files'), 'slider', 0) : ($banner_top_id->itemByLang->img ?? null),

        ]);

        $banner_top_id->push();

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
            'redirect' => urlForLanguage(LANG, 'edititem/' . $id . '/' . $lang_id)
        ]);
    }

    public function saveImgMobile(Request $request, $id, $lang_id)
    {
        $item = Validator::make($request->all(), [
            'uploaded_files' => 'nullable|max:10',
            'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'upload_files.*.mimes' => __('variables.custom_image_mime'),
            'upload_files.*.max' => __('variables.custom_image_size'),
        ]);

        if ($item->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $item->messages(),
            ]);
        }

        $banner_top_id = BannerTopId::updateOrCreate(['id' => $id], [
            //'active' => $active,
            //'deleted' => 0,
        ]);

        BannerTop::updateOrCreate([
            'banner_top_id' => $banner_top_id->id,
            'lang_id' => $lang_id,
        ], [
            'img_mobile' => $request->file('upload_files') ? uploadMultipleFiles($request->file('upload_files'), $request->input('uploaded_files'), 'slider-mobile', 0) : ($banner_top_id->itemByLang->img_mobile ?? null),
        ]);

        $banner_top_id->push();

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
            'redirect' => urlForLanguage(LANG, 'edititem/' . $id . '/' . $lang_id).'?mob_slide=1'
        ]);
    }


    public function destroyBannerFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $banner_item_elems_id = BannerTopId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$banner_item_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($banner_item_elems_id as $one_banner_item_elems_id) {

                    $banner_item_elems = BannerTop::where('banner_top_id', $one_banner_item_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($banner_item_elems)) {
                        $banner_item_elems = BannerTop::where('banner_top_id', $one_banner_item_elems_id->id)
                            ->first();
                    }

                    if ($one_banner_item_elems_id->deleted == 1 && $one_banner_item_elems_id->active == 0) {

                        $banner_item_elems_for_img = BannerTop::where('banner_top_id', $one_banner_item_elems_id->id)
                            ->get();

                        if (!$banner_item_elems_for_img->isEmpty()) {
                            foreach ($banner_item_elems_for_img as $item) {
                                if (File::exists('upfiles/slider/s/' . showImg($item->img)))
                                    File::delete('upfiles/slider/s/' . showImg($item->img));

                                if (File::exists('upfiles/slider/m/' . showImg($item->img)))
                                    File::delete('upfiles/slider/m/' . showImg($item->img));

                                if (File::exists('upfiles/slider/' . $item->img))
                                    File::delete('upfiles/slider/' . $item->img);

                                //Slider mobile
                                if (File::exists('upfiles/slider-mobile/s/' . showImg($item->img_mobile)))
                                    File::delete('upfiles/slider-mobile/s/' . showImg($item->img_mobile));

                                if (File::exists('upfiles/slider-mobile/m/' . showImg($item->img_mobile)))
                                    File::delete('upfiles/slider-mobile/m/' . showImg($item->img_mobile));

                                if (File::exists('upfiles/slider-mobile/' . $item->img_mobile))
                                    File::delete('upfiles/slider-mobile/' . $item->img_mobile);
                            }
                        }

                        $del_message .= $banner_item_elems->name . ', ';

                        BannerTopId::destroy($one_banner_item_elems_id->id);
                        BannerTop::where('banner_top_id', $one_banner_item_elems_id->id)->delete();
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

    public function destroyBannerToCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $banner_item_elems_id = BannerTopId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$banner_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($banner_item_elems_id as $one_banner_item_elems_id) {

                    $banner_item_elems = BannerTop::where('banner_top_id', $one_banner_item_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($banner_item_elems)) {
                        $banner_item_elems = BannerTop::where('banner_top_id', $one_banner_item_elems_id->id)
                            ->first();
                    }

                    if ($one_banner_item_elems_id->deleted == 0) {

                        $cart_message .= $banner_item_elems->name . ', ';

                        BannerTopId::where('id', $one_banner_item_elems_id->id)
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

    public function restoreBanner(Request $request)
    {
        $restored_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($restored_elements_id)) {
            $restored_elements_id_arr = explode(',', $restored_elements_id);

            $slider_item_elems_id = BannerTopId::whereIn('id', $restored_elements_id_arr)->get();

            if (!$slider_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($slider_item_elems_id as $one_slider_item_elems_id) {

                    $slider_name = GetNameByLang($one_slider_item_elems_id->id, LANG_ID, 'BannerTop', 'banner_top_id');

                    if ($one_slider_item_elems_id->restored == 0) {

                        $cart_message .= $slider_name . ', ';

                        BannerTopId::where('id', $one_slider_item_elems_id->id)
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


