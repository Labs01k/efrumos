<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\BannerId;
use App\Models\BannerImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    public function index()
    {
        $view = 'admin.banner.banners-list';
        $groupSubRelations = $this->menu()['groupSubRelations'];

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $banner_list_ids = BannerId::where('deleted', 0)
            ->where('p_id', 0)
            ->orderBy('position', 'asc')
            ->paginate(config('custom.back.banners_items_per_page'));

        $banner_list = [];
        foreach ($banner_list_ids as $key => $one_slider_ids) {
            $banner_list[$key] = Banner::where('banner_id', $one_slider_ids->id)
                ->first();
        }
        $banner_list = array_filter($banner_list, 'strlen');

        return view($view, get_defined_vars());
    }

    public function changePosition(Request $request)
    {
        $positionItems = BannerId::get();
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

    public function cartItems()
    {
        $view = 'admin.banner.banner-cart';

        $deleted_banner_id = BannerId::where('deleted', 1)
            ->where('active', 0)
            ->get();

        $deleted_banner_list = [];

        foreach ($deleted_banner_id as $key => $one_deleted_banner_id) {
            $deleted_banner_list[$key] = Banner::where('banner_id', $one_deleted_banner_id->id)
                ->first();
        }

        $deleted_banner_list = array_filter($deleted_banner_list, 'strlen');
        return view($view, get_defined_vars());
    }

    // ajax response for active
    public function changeActive(Request $request)
    {
        $active = $request->input('active');
        $id = $request->input('id');

        $element_id = BannerId::findOrFail($id);

        if (!is_null($element_id))
            $element_name = GetNameByLang($element_id->id, LANG_ID, 'Banner', 'banner_id');
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

        BannerId::where('id', $id)->update(['active' => $change_active]);

        return response()->json([
            'status' => true,
            'type' => 'info',
            'messages' => [$msg]
        ]);
    }

    public function changeImgPosition(Request $request)
    {
        $imagePositions = $request->input('position');

        if ($imagePositions && !empty($imagePositions) && count($imagePositions)) {
            foreach ($imagePositions as $oneImagePosition) {
                if ($oneImagePosition['id'])
                    BannerImages::find($oneImagePosition['id'])->update(['position' => $oneImagePosition['position']]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => __('variables.change_position')
        ]);
    }

    public function createItem()
    {
        $view = 'admin.banner.create-banner';
        $modules_name = $this->menu()['modules_name'];

        $banner_p_id = intval(request()->segment(4)) ?? 0;

        return view($view, get_defined_vars());
    }

    public function editItem($id, $lang_id)
    {
        $view = 'admin.banner.edit-banner';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $banner_without_lang = Banner::where('banner_id', $id)
            ->first();

        if (is_null($banner_without_lang)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $banner = Banner::where('banner_id', $banner_without_lang->banner_id)
            ->where('lang_id', $lang_id)
            ->first();

        if (!is_null($banner_without_lang)) {
            $banner_id = BannerId::where('id', $banner_without_lang->banner_id)
                ->first();
        } elseif (!is_null($banner)) {
            $banner_id = BannerId::where('id', $banner->banner_id)
                ->first();
        }

        $images = BannerImages::where('banner_id', $id)
            ->orderBy('position', 'asc')
            ->get();

        return view($view, get_defined_vars());
    }

    public function save(Request $request, $id, $lang_id)
    {
        if (is_null($id)) {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:banner_id',
                'uploaded_files' => 'nullable|max:10',
                'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'upload_files.*.mimes' => __('variables.custom_image_mime'),
                'upload_files.*.max' => __('variables.custom_image_size'),
            ]);
        } else {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:banner_id,alias,' . $id,
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

        $maxPosition = GetMinPosition('banner_id');

        if ($id) {
            $currentPosition = GetPosition('banner_id', $id);
            $position = $currentPosition;
        } else {
            $position = $maxPosition - 1;
        }

        if (checkIfLangExist($request->input('lang')) == false)
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.lang_not_exist', LANG)],
            ]);

        $banner_id = BannerId::updateOrCreate(['id' => $id], [
            'p_id' => $request->input('p_id'),
            'alias' => $request->input('alias'),
            'position' => $position,
            'percent' => $request->input('percent'),
            'color_code' => $request->input('color_code'),
            'color_code_button' => $request->input('color_code_button'),
            'active' => 1,
            'deleted' => 0,
        ]);

        Banner::updateOrCreate([
            'banner_id' => $banner_id->id,
            'lang_id' => $request->input('lang'),
        ], [
            'name' => $request->input('name'),
            'short_descr' => $request->input('short_descr'),
            'body' => $request->input('body'),
            'link' => $request->input('link'),
            'link_name' => $request->input('link_name'),
        ]);

        $banner_id->push();

        if ($request->file('upload_files') && $banner_id)
            uploadMultipleFiles($request->file('upload_files'), $request->input('uploaded_files'), 'banners', $banner_id);

        if (is_null($id)) {
            if ($banner_id->p_id == 0) {
                return response()->json([
                    'status' => true,
                    'messages' => [controllerTrans('variables.save', LANG)],
                    'redirect' => urlForFunctionLanguage(LANG, '')
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'messages' => [controllerTrans('variables.save', LANG)],
                    'redirect' => urlForFunctionLanguage(LANG, $banner_id->p_id . '/memberslist')
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
        $view = 'admin.banner.child-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $banner_id = BannerId::where('id', request()->segment(4))
            ->first();

        if (is_null($banner_id)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $child_banner_list_id = BannerId::where('p_id', $banner_id->id)
            ->where('deleted', 0)
            ->orderBy('position', 'asc')
            ->with('itemByLang')
            ->paginate(config('custom.back.banners_items_per_page'));


        return view($view, get_defined_vars());
    }

    public function destroyBannerToCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $banner_item_elems_id = BannerId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$banner_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($banner_item_elems_id as $one_banner_item_elems_id) {

                    $banner_item_elems = Banner::where('banner_id', $one_banner_item_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($banner_item_elems)) {
                        $banner_item_elems = Banner::where('banner_id', $one_banner_item_elems_id->id)
                            ->first();
                    }

                    if ($one_banner_item_elems_id->deleted == 0) {

                        $cart_message .= $banner_item_elems->name . ', ';

                        BannerId::where('id', $one_banner_item_elems_id->id)
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

    public function destroyBannerFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $banner_item_elems_id = BannerId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$banner_item_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($banner_item_elems_id as $one_banner_item_elems_id) {

                    $banner_item_elems = Banner::where('banner_id', $one_banner_item_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($banner_item_elems)) {
                        $banner_item_elems = Banner::where('banner_id', $one_banner_item_elems_id->id)
                            ->first();
                    }

                    if ($one_banner_item_elems_id->deleted == 1 && $one_banner_item_elems_id->active == 0) {

                        $banners_images = $one_banner_item_elems_id->moduleMultipleImg;

                        if (!is_null($banners_images) && !$banners_images->isEmpty()) {
                            foreach ($banners_images as $banner_image) {
                                if (File::exists('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/s/' . showImg($banner_image->img)))
                                    File::delete('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/s/' . showImg($banner_image->img));

                                if (File::exists('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/m/' . showImg($banner_image->img)))
                                    File::delete('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/m/' . showImg($banner_image->img));

                                if (File::exists('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/xs/' . showImg($banner_image->img)))
                                    File::delete('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/xs/' . showImg($banner_image->img));

                                if (File::exists('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/l/' . showImg($banner_image->img)))
                                    File::delete('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/l/' . showImg($banner_image->img));

                                if (File::exists('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/' . $banner_image->img))
                                    File::delete('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/' . $banner_image->img);
                            }
                        }

                        $del_message .= $banner_item_elems->name . ', ';

                        BannerId::destroy($one_banner_item_elems_id->id);
                        Banner::where('banner_id', $one_banner_item_elems_id->id)->delete();

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

    public function restoreBanner(Request $request)
    {
        $restored_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($restored_elements_id)) {
            $restored_elements_id_arr = explode(',', $restored_elements_id);

            $banner_item_elems_id = BannerId::whereIn('id', $restored_elements_id_arr)->get();

            if (!$banner_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($banner_item_elems_id as $one_banner_item_elems_id) {

                    $banner_name = GetNameByLang($one_banner_item_elems_id->id, LANG_ID, 'Banner', 'banner_id');

                    if ($one_banner_item_elems_id->restored == 0) {

                        $cart_message .= $banner_name . ', ';

                        BannerId::where('id', $one_banner_item_elems_id->id)
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


