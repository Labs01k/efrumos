<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\BrandId;
use App\Models\BrandImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{

    public function index(Request $request)
    {
        $view = 'admin.brand.brand-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $brand_list = BrandId::where('deleted', 0)
            ->where('level', 1)
            ->orderBy('position', 'asc')
            ->paginate(config('custom.back.brands_items_per_page'));

        $brand_elements = [];
        if ($brand_list) {
            foreach ($brand_list as $key => $brand_id_element) {
                $brand_elements[$key] = Brand::where('goods_brand_id', $brand_id_element->id)
                    ->first();
            }
        }

        $brand_elements = array_filter($brand_elements, 'strlen');

        return view($view, get_defined_vars());
    }

    // ajax response for active
    public function changeActive(Request $request)
    {
        $active = $request->input('active');
        $id = $request->input('id');

        $element_id = BrandId::join('goods_brand', 'goods_brand.goods_brand_id', '=', 'goods_brand_id.id')
            ->where('lang_id', LANG_ID)
            ->select('*', 'goods_brand_id.id as id')
            ->findOrFail($id);

        if (!is_null($element_id))
            $element_name = $element_id->name;
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

        BrandId::where('id', $id)->update(['active' => $change_active]);

        return response()->json([
            'status' => true,
            'type' => 'info',
            'messages' => [$msg]
        ]);
    }

    public function changePosition(Request $request)
    {
        $positionItems = BrandId::get();
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
                    BrandImages::find($oneImagePosition['id'])->update(['position' => $oneImagePosition['position']]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => __('variables.change_position')
        ]);
    }

    public function createBrand()
    {
        $view = 'admin.brand.create-brand';

        $modules_name = $this->menu()['modules_name'];

        $curr_page_brand_id = BrandId::where('alias', request()->segment(4))
            ->first();

        if (!is_null($curr_page_brand_id)) {
            $curr_page_id = $curr_page_brand_id->id;
        } else {
            $curr_page_id = null;
        }

        return view($view, get_defined_vars());
    }

    public function editItem($id, $lang_id)
    {
        $view = 'admin.brand.edit-brand';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $brand_without_lang = Brand::where('goods_brand_id', $id)->first();

        if (is_null($brand_without_lang)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $brand_elems = Brand::where('lang_id', $lang_id)
            ->where('goods_brand_id', $brand_without_lang->goods_brand_id)
            ->first();

        if (!is_null($brand_without_lang)) {
            $brand_id = BrandId::where('id', $brand_without_lang->goods_brand_id)
                ->first();
        } elseif (!is_null($brand_elems)) {
            $brand_id = BrandId::where('id', $brand_elems->goods_brand_id)
                ->first();
        }

        $images = BrandImages::where('goods_brand_id', $brand_id->id)
            ->orderBy('position', 'asc')
            ->get();

        return view($view, get_defined_vars());
    }

    public function save(Request $request, $id, $lang_id)
    {
        if (is_null($id)) {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:goods_brand_id',
                'uploaded_files' => 'nullable|max:10',
                'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'upload_files.*.mimes' => __('variables.custom_image_mime'),
                'upload_files.*.max' => __('variables.custom_image_size'),
            ]);
        } else {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:goods_brand_id,alias,'.$id,
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

        $maxPosition = GetMinPosition('goods_brand_id');
        $level = GetLevel($request->input('p_id'), 'goods_brand_id');

        if ($id) {
            $currentPosition = GetPosition('goods_brand_id', $id);
            $position = $currentPosition;
        } else {
            $position = $maxPosition - 1;
        }

//        Check if lang exist
        if (checkIfLangExist(request()->input('lang')) == false)
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.lang_not_exist', LANG)],
            ]);

        $brand_id = BrandId::updateOrCreate(['id' => $id], [
            'p_id' => $request->input('p_id'),
            'position' => $position,
            'level' => $level + 1,
            'alias' => request()->input('alias'),
            'img_palette' => $request->file('img_palette') ? uploadOneFile($request->file('img_palette'), 'goods-brand-palette') : getImageNameByColumnName($id, 'img_palette',  'BrandId'),
            'img_certificate' => $request->file('img_certificate') ? uploadOneFile($request->file('img_certificate'), 'goods-brand-certificate') : getImageNameByColumnName($id, 'img_certificate',  'BrandId')
        ]);

        Brand::updateOrCreate([
            'goods_brand_id' => $brand_id->id,
            'lang_id' => $request->input('lang'),
        ], [
            'name' => $request->input('name'),
            'body' => $request->input('body'),
            'link' => $request->input('link'),
            'meta_title' => $request->input('meta_title'),
            'meta_keywords' => $request->input('meta_keywords'),
            'meta_description' => $request->input('meta_description'),
        ]);
        $brand_id->push();

        if ($request->file('upload_files') && $brand_id)
            uploadMultipleFiles($request->file('upload_files'), $request->input('uploaded_files'), 'brand', $brand_id);

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

    public function membersList()
    {
        $view = 'admin.brand.child-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $brand_list_id = BrandId::where('alias', request()->segment(4))
            ->first();

        if (is_null($brand_list_id)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $child_brand_list_id = BrandId::where('p_id', $brand_list_id->id)
            ->where('deleted', 0)
            ->orderBy('position', 'asc')
            ->paginate(config('custom.back.brands_items_per_page'));

        $child_brand_list = [];
        foreach ($child_brand_list_id as $key => $one_brand_elem) {
            $child_brand_list[$key] = Brand::where('goods_brand_id', $one_brand_elem->id)
                ->first();
        }

        //Remove all null values --start
        $child_brand_list = array_filter($child_brand_list, 'strlen');
        //Remove all null values --end

        return view($view, get_defined_vars());
    }

    public function destroyBrandToCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $brand_item_elems_id = BrandId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$brand_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($brand_item_elems_id as $one_brand_item_elems_id) {

                    $brand_name = GetNameByLang($one_brand_item_elems_id->id, LANG_ID, 'Brand', 'goods_brand_id');

                    if ($one_brand_item_elems_id->deleted == 0) {

                        $cart_message .= $brand_name . ', ';

                        BrandId::where('id', $one_brand_item_elems_id->id)
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

    public function cartItems()
    {
        $view = 'admin.brand.brand-cart';

        $deleted_brands_id = BrandId::where('deleted', 1)
            ->where('active', 0)
            ->get();

        $deleted_brand_list = BrandId::where('deleted', 1)
            ->where('active', 0)
            ->join('goods_brand', 'goods_brand.goods_brand_id', '=', 'goods_brand_id.id')
            ->where('lang_id', LANG_ID)
            ->select('*', 'goods_brand_id.id as id')
            ->orderBy('position', 'asc')
            ->get();

        return view($view, get_defined_vars());
    }

    public function destroyBrandFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $brand_item_elems_id = BrandId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$brand_item_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($brand_item_elems_id as $one_brand_item_elems_id) {

                    $brand_name = GetNameByLang($one_brand_item_elems_id->id, LANG_ID, 'Brand', 'goods_brand_id');

                    if ($one_brand_item_elems_id->deleted == 1 && $one_brand_item_elems_id->active == 0) {

                        $brand_images = $one_brand_item_elems_id->moduleMultipleImg;

                        if (!is_null($brand_images) && !$brand_images->isEmpty()) {
                            foreach ($brand_images as $brand_image) {
                                if (File::exists('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/s/' . showImg($brand_image->img)))
                                    File::delete('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/s/' . showImg($brand_image->img));

                                if (File::exists('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/m/' . showImg($brand_image->img)))
                                    File::delete('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/m/' . showImg($brand_image->img));

                                if (File::exists('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/' . $brand_image->img))
                                    File::delete('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/' . $brand_image->img);
                            }
                        }

                        //For meta images
                        if (File::exists('upfiles/goods-brand-palette/s/' . showImg($one_brand_item_elems_id->img_palette)))
                            File::delete('upfiles/goods-brand-palette/s/' . showImg($one_brand_item_elems_id->img_palette));

                        if (File::exists('upfiles/goods-brand-palette/m/' . showImg($one_brand_item_elems_id->img_palette)))
                            File::delete('upfiles/goods-brand-palette/m/' . showImg($one_brand_item_elems_id->img_palette));

                        if (File::exists('upfiles/goods-brand-palette/' . $one_brand_item_elems_id->img_palette))
                            File::delete('upfiles/goods-brand-palette/' . $one_brand_item_elems_id->img_palette);

                        if (File::exists('upfiles/goods-brand-certificate/s/' . showImg($one_brand_item_elems_id->img_palette)))
                            File::delete('upfiles/goods-brand-certificate/s/' . showImg($one_brand_item_elems_id->img_palette));

                        if (File::exists('upfiles/goods-brand-certificate/m/' . showImg($one_brand_item_elems_id->img_palette)))
                            File::delete('upfiles/goods-brand-certificate/m/' . showImg($one_brand_item_elems_id->img_palette));

                        if (File::exists('upfiles/goods-brand-certificate/' . $one_brand_item_elems_id->img_palette))
                            File::delete('upfiles/goods-brand-certificate/' . $one_brand_item_elems_id->img_palette);

                        $del_message .= $brand_name . ', ';

                        BrandId::destroy($one_brand_item_elems_id->id);

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

    public function restoreBrand(Request $request)
    {
        $restored_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($restored_elements_id)) {
            $restored_elements_id_arr = explode(',', $restored_elements_id);

            $brand_item_elems_id = BrandId::whereIn('id', $restored_elements_id_arr)->get();

            if (!$brand_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($brand_item_elems_id as $one_brand_item_elems_id) {

                    $brand_name = GetNameByLang($one_brand_item_elems_id->id, LANG_ID, 'Brand', 'goods_brand_id');

                    if ($one_brand_item_elems_id->restored == 0) {

                        $cart_message .= $brand_name . ', ';

                        BrandId::where('id', $one_brand_item_elems_id->id)
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


