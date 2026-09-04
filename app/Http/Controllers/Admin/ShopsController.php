<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CityId;
use App\Models\Shops;
use App\Models\ShopsId;
use App\Models\ShopsImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ShopsController extends Controller
{
    public function index()
    {
        $view = 'admin.shops.shops-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $shops_list_id = ShopsId::orderBy('position', 'asc')
            ->paginate(config('custom.back.shops_items_per_page'));

        $shops_list = [];
        foreach ($shops_list_id as $key => $one_shops_id) {
            $shops_list[$key] = Shops::where('shops_id', $one_shops_id->id)
                ->first();
        }

        $shops_list = array_filter($shops_list);

        return view($view, get_defined_vars());
    }

    public function changeActive(Request $request)
    {
        $active = $request->input('active');
        $id = $request->input('id');

        $element_id = ShopsId::findOrFail($id);

        if (!is_null($element_id))
            $element_name = GetNameByLang($element_id->id, LANG_ID, 'Shops', 'shops_id');
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

        ShopsId::where('id', $id)->update(['active' => $change_active]);

        return response()->json([
            'status' => true,
            'type' => 'info',
            'messages' => [$msg]
        ]);

    }

    public function changePosition(Request $request)
    {
        $positionItems = ShopsId::get();
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


    public function createItem()
    {
        $view = 'admin.shops.create-shops';

        $modules_name = $this->menu()['modules_name'];

        $city_id = CityId::where('active', 1)
            ->orderBy('position', 'asc')
            ->get();

        $city = [];

        if (!$city_id->isEmpty()) {
            foreach ($city_id as $one_city_id) {
                $city[] = City::where('city_id', $one_city_id->id)
                    ->where('lang_id', LANG_ID)
                    ->first();
            }

            $city = array_filter($city);
        }

        return view($view, get_defined_vars());
    }

    public function editItem($id, $lang_id)
    {
        $view = 'admin.shops.edit-shops';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $shops_id = ShopsId::where('id', $id)
            ->first();

        $shops_without_lang = Shops::where('shops_id', $id)
            ->first();

        $shops = Shops::where('shops_id', $shops_without_lang->shops_id)
            ->where('lang_id', $lang_id)
            ->first();

        $city_id = CityId::where('active', 1)
            ->orderBy('position', 'asc')
            ->get();

        $city = [];

        if (!$city_id->isEmpty()) {
            foreach ($city_id as $one_city_id) {
                $city[] = City::where('city_id', $one_city_id->id)
                    ->where('lang_id', LANG_ID)
                    ->first();
            }

            $city = array_filter($city);
        }

        $images = ShopsImages::where('shops_id', $id)
            ->orderBy('position', 'asc')
            ->get();

        return view($view, get_defined_vars());
    }

    public function save(Request $request, $id, $lang_id)
    {
        if (is_null($id)) {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:shops_id',
                'uploaded_files' => 'nullable|max:10',
                'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'upload_files.*.mimes' => __('variables.custom_image_mime'),
                'upload_files.*.max' => __('variables.custom_image_size'),
            ]);
        } else {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:shops_id,alias,'.$id,
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

        if (checkIfLangExist($request->input('lang')) == false)
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.lang_not_exist', LANG)],
            ]);

        $maxPosition = GetMinPosition('shops_id');

        if ($id) {
            $currentPosition = GetPosition('shops_id', $id);
            $position = $currentPosition;
        } else {
            $position = $maxPosition - 1;
        }

        $shops_id = ShopsId::updateOrCreate(['id' => $id], [
            'alias' => $request->input('alias'),
            'city_id' => $request->input('city_id'),
            'phone' => $request->input('phone'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'map_iframe' => $request->input('map_iframe'),
            'store_guid' => $request->input('store_guid'),
            'google_place_id' => $request->input('google_place_id'),
            'position' => $position,
        ]);

        Shops::updateOrCreate([
            'shops_id' => $shops_id->id,
            'lang_id' => $request->input('lang'),
        ], [
            'name' => $request->input('name'),
            //'type' => $request->input('type'),
            //'distr' => $request->input('distr'),
            //'cafe' => $request->input('cafe'),
            'address' => $request->input('address'),
            'schedule' => $request->input('schedule'),
        ]);
        $shops_id->push();

        if ($request->file('upload_files') && $shops_id)
            uploadMultipleFiles($request->file('upload_files'), $request->input('uploaded_files'), 'shops', $shops_id);

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

    public function destroyShopsFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $shops_item_elems_id = ShopsId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$shops_item_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($shops_item_elems_id as $one_shops_item_elems_id) {

                    $shops_item_elems = Shops::where('shops_id', $one_shops_item_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($shops_item_elems)) {
                        $shops_item_elems = Shops::where('shops_id', $one_shops_item_elems_id->id)
                            ->first();
                    }

                    $shop_images = $one_shops_item_elems_id->moduleMultipleImg;

                    if (!is_null($shop_images) && !$shop_images->isEmpty()) {
                        foreach ($shop_images as $shop_image) {
                            if (File::exists('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/s/' . showImg($shop_image->img)))
                                File::delete('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/s/' . showImg($shop_image->img));

                            if (File::exists('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/m/' . showImg($shop_image->img)))
                                File::delete('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/m/' . showImg($shop_image->img));

                            if (File::exists('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/' . $shop_image->img))
                                File::delete('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/' . $shop_image->img);
                        }
                    }

                    $del_message .= $shops_item_elems->name . ', ';

                    ShopsId::destroy($one_shops_item_elems_id->id);
                    Shops::where('shops_id', $one_shops_item_elems_id->id)->delete();

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
