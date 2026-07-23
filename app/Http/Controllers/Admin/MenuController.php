<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuId;
use App\Models\MenuImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    public function index()
    {
        $view = 'admin.menu.elements-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $menu_id_elements = MenuId::where('level', 1)
            ->where('deleted', 0)
            ->orderBy('position', 'asc')
            ->paginate(config('custom.back.menu_items_per_page'));

        $menu_elements = [];
        foreach ($menu_id_elements as $key => $menu_id_element) {
            $menu_elements[$key] = Menu::where('menu_id', $menu_id_element->id)
                ->first();
        }

        //Remove all null values --start
        $menu_elements = array_filter($menu_elements, 'strlen');
        //Remove all null values --end

        return view($view, get_defined_vars());
    }

    public function changePosition(Request $request)
    {
        $positionItems = MenuId::get();
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
                    MenuImages::find($oneImagePosition['id'])->update(['position' => $oneImagePosition['position']]);
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

        if ($action == 'main-active' || $action == 'top-menu' || $action == 'footer-menu')
            $element_id = MenuId::findOrFail($id);
        else
            $element_id = '';

        if (!is_null($element_id)) {
            if ($action == 'main-active' || $action == 'top-menu' || $action == 'footer-menu')
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
            MenuId::where('id', $id)->update(['active' => $change_active]);
        elseif ($action == 'top-menu')
            MenuId::where('id', $id)->update(['top_menu' => $change_active]);
        elseif ($action == 'footer-menu')
            MenuId::where('id', $id)->update(['footer_menu' => $change_active]);

        return response()->json([
            'status' => true,
            'type' => 'info',
            'messages' => [$msg]
        ]);
    }

    public function createMenu()
    {
        $view = 'admin.menu.create-menu';

        $modules_name = $this->menu()['modules_name'];
        $curr_page_menu_id = MenuId::where('alias', request()->segment(4))
            ->first();

        if (!is_null($curr_page_menu_id)) {
            $curr_page_id = $curr_page_menu_id->id;
        } else {
            $curr_page_id = null;
        }

        return view($view, get_defined_vars());
    }

    public function editMenu($id, $lang_id)
    {
        $view = 'admin.menu.edit-menu';
        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $menu_without_lang = Menu::where('menu_id', $id)->first();

        $parent_menu_id = MenuId::whereRaw('id IN(SELECT p_id FROM menu_id WHERE id = ' . $id . ')')
            ->first();

        if (is_null($menu_without_lang)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $menu_elems = Menu::where('lang_id', $lang_id)
            ->where('menu_id', $menu_without_lang->menu_id)
            ->first();

        if (!is_null($menu_without_lang)) {
            $menu_id = MenuId::where('id', $menu_without_lang->menu_id)
                ->first();
        } elseif (!is_null($menu_elems)) {
            $menu_id = MenuId::where('id', $menu_elems->menu_id)
                ->first();
        }

        $images = MenuImages::where('menu_id', $id)
            ->orderBy('position', 'asc')
            ->get();

        return view($view, get_defined_vars());
    }

    public function saveMenu(Request $request, $id, $lang_id)
    {
        if (is_null($id)) {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:menu_id',
                'controller' => 'not_in:controller|not_in:Controller|min:10',
                'uploaded_files' => 'nullable|max:10',
                'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'upload_files.*.mimes' => __('variables.custom_image_mime'),
                'upload_files.*.max' => __('variables.custom_image_size'),
            ]);
        } else {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:menu_id,alias,' . $id,
                'controller' => 'not_in:controller|not_in:Controller|min:10',
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

        $maxPosition = GetMaxPosition('menu_id');
        $level = GetLevel($request->input('p_id'), 'menu_id');

        if ($id) {
            $currentPosition = GetPosition('menu_id', $id);
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

        $menu_id = MenuId::updateOrCreate(['id' => $id], [
            'p_id' => $request->input('p_id'),
            'level' => $level + 1,
            'alias' => $request->input('alias'),
            'page_type' => $request->input('page_type'),
            'position' => $position,
        ]);

        Menu::updateOrCreate([
            'menu_id' => $menu_id->id,
            'lang_id' => $request->input('lang'),
        ], [
            'name' => $request->input('name'),
            'short_descr' => $request->input('short_descr'),
            'body' => $request->input('body'),
            'body_two' => $request->input('body_two'),
            'link' => $request->input('link'),
            'page_title' => $request->input('page_title'),
            'h1_title' => $request->input('h1_title'),
            'meta_title' => $request->input('meta_title'),
            'meta_keywords' => $request->input('meta_keywords'),
            'meta_description' => $request->input('meta_description'),
        ]);
        $menu_id->push();

        if ($request->file('upload_files') && $menu_id)
            uploadMultipleFiles($request->file('upload_files'), $request->input('uploaded_files'), 'menu', $menu_id);

        if (is_null($id)) {
            if ($menu_id->level == 1) {
                return response()->json([
                    'status' => true,
                    'messages' => [controllerTrans('variables.save', LANG)],
                    'redirect' => urlForFunctionLanguage(LANG, '')
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'messages' => [controllerTrans('variables.save', LANG)],
                    'redirect' => urlForFunctionLanguage(LANG, GetParentAlias($menu_id->id, 'menu_id') . '/memberslist')
                ]);
            }
        }
        return response()->json([
            'status' => true,
            'messages' => [controllerTrans('variables.updated_text', LANG)],
            'redirect' => urlForLanguage(LANG, 'editmenu/' . $id . '/' . $lang_id)
        ]);
    }

    public function membersList()
    {
        $view = 'admin.menu.child-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $menu_list_id = MenuId::where('alias', request()->segment(4))
            ->first();

        if (is_null($menu_list_id)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $child_menu_list_id = MenuId::where('p_id', $menu_list_id->id)
            ->where('deleted', 0)
            ->orderBy('position', 'asc')
            ->paginate(config('custom.back.menu_items_per_page'));

        $child_menu_list = [];
        foreach ($child_menu_list_id as $key => $one_menu_elem) {
            $child_menu_list[$key] = Menu::where('menu_id', $one_menu_elem->id)
                ->first();
        }

        //Remove all null values --start
        $child_menu_list = array_filter($child_menu_list, 'strlen');
        //Remove all null values --end

        return view($view, get_defined_vars());
    }

    public function menuCart()
    {
        $view = 'admin.menu.menu-cart';

        $deleted_elems_by_alias = MenuId::where('alias', request()->segment(4))
            ->first();

        if (is_null($deleted_elems_by_alias)) {
            $deleted_menu_id_elems = MenuId::where('deleted', 1)
                ->where('active', 0)
                ->where('p_id', 0)
                ->get();
        } else {
            $deleted_menu_id_elems = MenuId::where('deleted', 1)
                ->where('active', 0)
                ->where('p_id', $deleted_elems_by_alias->id)
                ->get();
        }

        $deleted_menu_elems = [];

        foreach ($deleted_menu_id_elems as $key => $one_deleted_menu_elem) {
            $deleted_menu_elems[$key] = Menu::where('menu_id', $one_deleted_menu_elem->id)
                ->first();
        }

        $deleted_menu_elems = array_filter($deleted_menu_elems, 'strlen');

        return view($view, get_defined_vars());
    }


    public function destroyMenuToCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $menu_item_elems_id = MenuId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$menu_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($menu_item_elems_id as $one_menu_item_elems_id) {

                    $menu_item_elems = Menu::where('menu_id', $one_menu_item_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($menu_item_elems)) {
                        $menu_item_elems = Menu::where('menu_id', $one_menu_item_elems_id->id)
                            ->first();
                    }

                    if ($one_menu_item_elems_id->deleted == 0) {

                        $cart_message .= $menu_item_elems->name . ', ';

                        MenuId::where('id', $one_menu_item_elems_id->id)
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

    public function restoreMenu(Request $request)
    {
        $restored_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($restored_elements_id)) {
            $restored_elements_id_arr = explode(',', $restored_elements_id);

            $promotion_item_elems_id = MenuId::whereIn('id', $restored_elements_id_arr)->get();

            if (!$promotion_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($promotion_item_elems_id as $one_promotion_item_elems_id) {

                    $promotion_name = GetNameByLang($one_promotion_item_elems_id->id, LANG_ID, 'Menu', 'menu_id');

                    if ($one_promotion_item_elems_id->restored == 0) {

                        $cart_message .= $promotion_name . ', ';

                        MenuId::where('id', $one_promotion_item_elems_id->id)
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

    public function destroyMenuFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $menu_item_elems_id = MenuId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$menu_item_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($menu_item_elems_id as $one_menu_item_elems_id) {

                    $menu_item_elems = Menu::where('menu_id', $one_menu_item_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($menu_item_elems)) {
                        $menu_item_elems = Menu::where('menu_id', $one_menu_item_elems_id->id)
                            ->first();
                    }

                    if ($one_menu_item_elems_id->deleted == 1 && $one_menu_item_elems_id->active == 0) {

                        $menu_images = $one_menu_item_elems_id->moduleMultipleImg;

                        if (!is_null($menu_images) && !$menu_images->isEmpty()) {
                            foreach ($menu_images as $menu_image) {
                                if (File::exists('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/s/' . showImg($menu_image->img)))
                                    File::delete('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/s/' . showImg($menu_image->img));

                                if (File::exists('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/m/' . showImg($menu_image->img)))
                                    File::delete('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/m/' . showImg($menu_image->img));

                                if (File::exists('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/' . $menu_image->img))
                                    File::delete('upfiles/' . $this->menu()['modules_name']->modulesId->alias . '/' . $menu_image->img);
                            }
                        }

                        $del_message .= $menu_item_elems->name . ', ';

                        MenuId::destroy($one_menu_item_elems_id->id);
                        Menu::where('menu_id', $one_menu_item_elems_id->id)->delete();

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
}
