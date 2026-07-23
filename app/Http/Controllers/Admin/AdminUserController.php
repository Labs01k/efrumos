<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUserActionPermision;
use App\Models\AdminUserGroup;
use App\Models\Modules;
use App\Models\ModulesId;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    public function index()
    {
        $view = 'admin.user.users-group';

        $user_group = AdminUserGroup::where('deleted', 0)
            ->where('active', 1)
            ->paginate(config('custom.back.user_group_items_per_page'));

        return view($view, get_defined_vars());
    }

    public function createItem()
    {
        $view = 'admin.user.create-group';

        return view($view, get_defined_vars());
    }

    public function createUser()
    {
        $view = 'admin.user.create-user';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        return view($view, get_defined_vars());
    }

    public function cartItems()
    {
        $view = 'admin.user.group-cart';

        $deleted_group = AdminUserGroup::where('deleted', 1)
            ->where('active', 0)
            ->get();

        return view($view, get_defined_vars());
    }

    public function membersList()
    {
        $view = 'admin.user.users';

        $user_group_id = AdminUserGroup::where('deleted', 0)
            ->where('active', 1)
            ->where('alias', request()->segment(4))
            ->first();

        if (is_null($user_group_id)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $user = User::with('group')
            ->where('admin_user_group_id', $user_group_id->id)
            ->paginate(config('custom.back.user_group_items_per_page'));

        return view($view, get_defined_vars());
    }

    public function editUser($id)
    {
        $view = 'admin.user.edit-user';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $group = User::find($id)->group;

        $user = User::where('id', $id)->first();

        if (is_null($user)) {
            return App::abort(503, 'Unauthorized action.');
        }

        if ($group->alias != request()->segment(4)) {
            return redirect(url(LANG_ID, ['back', 'admin_user']));
        }

        $all_group = AdminUserGroup::where('deleted', 0)
            ->where('active', 1)
            ->get();

        return view($view, get_defined_vars());

    }

    public function save(Request $request, $id)
    {
        if (is_null($id)) {
            $item = Validator::make($request->all(), [
                'login' => 'required|min:3',
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required|confirmed|min:4',
                'uploaded_files' => 'nullable|max:10',
                'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'upload_files.*.mimes' => __('variables.custom_image_mime'),
                'upload_files.*.max' => __('variables.custom_image_size'),
            ]);
        } else {
            if (!empty($request->input('password'))) {
                $item = Validator::make($request->all(), [
                    'login' => 'required|min:3',
                    'name' => 'required',
                    'email' => 'required|email',
                    'password' => 'required|confirmed|min:4',
                    'uploaded_files' => 'nullable|max:10',
                    'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ], [
                    'upload_files.*.mimes' => __('variables.custom_image_mime'),
                    'upload_files.*.max' => __('variables.custom_image_size')
                ]);
            } else {
                $item = Validator::make($request->all(), [
                    'login' => 'required|min:3',
                    'name' => 'required',
                    'email' => 'required|email',
                    'uploaded_files' => 'nullable|max:10',
                    'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ], [
                    'upload_files.*.mimes' => __('variables.custom_image_mime'),
                    'upload_files.*.max' => __('variables.custom_image_size'),
                ]);
            }
        }

        if ($item->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $item->messages(),
            ]);
        }

        $group_id = AdminUserGroup::where('alias', $request->segment(4))
            ->where('active', 1)
            ->where('deleted', 0)
            ->first();

        if (auth()->user()->id != $id && empty($group_id)) {
            $group_id = AdminUserGroup::where('id', $request->input('group-list'))
                ->where('active', 1)
                ->where('deleted', 0)
                ->first();

            if (is_null($group_id))
                return response()->json([
                    'status' => false,
                    'messages' => [controllerTrans('variables.group-not-exist', LANG)],
                ]);
        }

        $user = User::updateOrCreate(['id' => $id], [
            'login' => $request->input('login'),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            //'password' => bcrypt($request->input('password')),
            'remember_token' => $request->input('_token'),
            'admin_user_group_id' => $group_id->id,
            'img' => $request->file('upload_files') ? uploadMultipleFiles($request->file('upload_files'), $request->input('uploaded_files'), 'admin-user', 0) : getImageById($id, 'User'),
            //'root' => 0,
        ]);

        $user->push();

        if ($request->input('password'))
            $user->password = bcrypt($request->input('password'));

        $user->update();

        return response()->json([
            'status' => true,
            'messages' => [controllerTrans('variables.updated_text', LANG_ID)],
            'redirect' => urlForLanguage(LANG, 'edituser/' . $user->id)
        ]);
    }

    public function editList($id)
    {
        $view = 'admin.user.edit-list';

        $group = AdminUserGroup::where('id', $id)->first();

        if (is_null($group)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $result = [];

        $modules_id = ModulesId::where('active', 1)
            ->where('deleted', 0)
            ->where('level', 1)
            ->orderBy('position', 'asc')
            ->get();

        $modules = [];
        if (!$modules_id->isEmpty()) {
            foreach ($modules_id as $item) {
                $modules[] = Modules::where('modules_id', $item->id)
                    ->where('lang_id', LANG_ID)
                    ->first();
            }

            $modules = array_filter($modules);
        }

        if (!empty($modules)) {

            foreach ($modules as $keyModules => $singleModules) {
                $result[$keyModules]['modules'] = $singleModules;
                $result[$keyModules]['name'] = !empty(IfHasName($singleModules->modules_id, LANG_ID, 'modules')) ? IfHasName($singleModules->modules_id, LANG_ID, 'modules') : trans('variables.another_name');
                $result[$keyModules]['permission'] = $singleModules->modulesPermission;
                $result[$keyModules]['roles'] = [];

                $pretendentRole = AdminUserActionPermision::where('admin_user_group_id', $id)->get();
                foreach ($pretendentRole as $k => $roles) {
                    if ($roles)
                        $result[$keyModules]['roles'][] = $roles;
                }

            }
        }

        $child_modules = AdminUserGroup::findOrFail($id)->userPermission;
        $arr = [];
        $new = [];
        $save = [];
        $active = [];
        $del_to_rec = [];
        $del_from_rec = [];
        $moderate = [];
        foreach ($child_modules as $k => $v) {
            $arr[] = $v->modules_id;
            $new[] = $v->new . $v->modules_id;
            $save[] = $v->save . $v->modules_id;
            $active[] = $v->active . $v->modules_id;
            $del_to_rec[] = $v->del_to_rec . $v->modules_id;
            $del_from_rec[] = $v->del_from_rec . $v->modules_id;
            $moderate[] = $v->moderate . $v->modules_id;
        }

        return view($view, get_defined_vars());
    }

    public function saveList(Request $request, $id)
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

        $admin_group = AdminUserGroup::updateOrCreate(['id' => $id], [
            'name' => $request->input('name'),
            'alias' => Str::slug($request->input('name')),
            'active' => 1,
            'deleted' => 0,
        ]);

        $new = $request->input('new');
        $save = $request->input('save');
        $active = $request->input('active');
        $del_to_rec = $request->input('del_to_rec');
        $del_from_rec = $request->input('del_from_rec');
        $moderate = $request->input('moderate');
        $modules_id = $request->input('modules_id');

        $arr = [
            'new' => $new,
            'save' => $save,
            'active' => $active,
            'del_to_rec' => $del_to_rec,
            'del_from_rec' => $del_from_rec,
            'moderate' => $moderate
        ];

        if (count($modules_id) <= $admin_group->userPermission->count()) {
            AdminUserActionPermision::where('admin_user_group_id', $admin_group->id)->delete();
        }

        if (!empty($modules_id)) {
            foreach ($modules_id as $key => $mod_id) {
                $arr['modules_id'] = $mod_id;
                isset($arr['new'][$key]) ? $new_val = 1 : $new_val = 0;
                isset($arr['save'][$key]) ? $save_val = 1 : $save_val = 0;
                isset($arr['active'][$key]) ? $active_val = 1 : $active_val = 0;
                isset($arr['del_to_rec'][$key]) ? $del_to_rec_val = 1 : $del_to_rec_val = 0;
                isset($arr['del_from_rec'][$key]) ? $del_from_rec_val = 1 : $del_from_rec_val = 0;
                isset($arr['moderate'][$key]) ? $moderate_val = 1 : $moderate_val = 0;

                $admin_group->userPermission()->updateOrCreate([
                    'admin_user_group_id' => $admin_group->id,
                    'modules_id' => $mod_id,
                ], [
                    'new' => $new_val,
                    'save' => $save_val,
                    'active' => $active_val,
                    'del_to_rec' => $del_to_rec_val,
                    'del_from_rec' => $del_from_rec_val,
                    'moderate' => $moderate_val,
                ]);

            }
        }

        if (is_null(AdminUserGroup::find($id))) {
            return response()->json([
                'status' => true,
                'messages' => [controllerTrans('variables.save', LANG_ID)],
                'redirect' => urlForFunctionLanguage(LANG, '')
            ]);
        }
        return response()->json([
            'status' => true,
            'messages' => [controllerTrans('variables.save', LANG_ID)],
            'redirect' => urlForLanguage(LANG, 'editlist/' . $id)
        ]);
    }

    public function destroyUserFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $user_item_elems_id = User::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$user_item_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($user_item_elems_id as $one_user_item_elems_id) {

                    if (File::exists('upfiles/admin-user/s/' . showImg($one_user_item_elems_id->img)))
                        File::delete('upfiles/admin-user/s/' . showImg($one_user_item_elems_id->img));

                    if (File::exists('upfiles/admin-user/m/' . showImg($one_user_item_elems_id->img)))
                        File::delete('upfiles/admin-user/m/' . showImg($one_user_item_elems_id->img));

                    if (File::exists('upfiles/admin-user/' . $one_user_item_elems_id->img))
                        File::delete('upfiles/admin-user/' . $one_user_item_elems_id->img);

                    $del_message .= $one_user_item_elems_id->name . ', ';

                    User::destroy($one_user_item_elems_id->id);

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

    public function destroyGroupFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $group_item_elems_id = AdminUserGroup::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$group_item_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($group_item_elems_id as $one_group_item_elems_id) {

                    if ($one_group_item_elems_id->deleted == 1 && $one_group_item_elems_id->active == 0) {

                        $del_message .= $one_group_item_elems_id->name . ', ';

                        AdminUserGroup::destroy($one_group_item_elems_id->id);
                        AdminUserActionPermision::where('admin_user_group_id', $one_group_item_elems_id->id)->delete();
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

    public function destroyGroupToCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $group_item_elems_id = AdminUserGroup::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$group_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($group_item_elems_id as $one_group_item_elems_id) {

                    if ($one_group_item_elems_id->deleted == 0) {

                        $cart_message .= $one_group_item_elems_id->name . ', ';

                        AdminUserGroup::where('id', $one_group_item_elems_id->id)
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

    public function restore(Request $request)
    {
        $restored_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($restored_elements_id)) {
            $restored_elements_id_arr = explode(',', $restored_elements_id);

            $admin_item_elems_id = AdminUserGroup::whereIn('id', $restored_elements_id_arr)->get();

            if (!$admin_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($admin_item_elems_id as $one_admin_item_elems_id) {

                    $admin_name = $one_admin_item_elems_id->name;

                    if ($one_admin_item_elems_id->restored == 0) {

                        $cart_message .= $admin_name . ', ';

                        AdminUserGroup::where('id', $one_admin_item_elems_id->id)
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
