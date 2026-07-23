<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FrontUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class FrontUserManageController extends Controller
{
    public function index()
    {
        $view = 'admin.front-user-manage.users-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $users = FrontUser::orderBy('created_at', 'desc')->paginate(config('custom.back.front_users_per_page'));
        return view($view, get_defined_vars());
    }

    public function changeActive(Request $request)
    {
        $active = $request->input('active');
        $action = $request->input('action');
        $id = $request->input('id');

        $element_id = FrontUser::findOrFail($id);

        if (!is_null($element_id))
            $element_name = $element_id->name . ' ' . $element_id->last_name;
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

        if ($action == 'main-active')
            FrontUser::where('id', $id)->update(['active' => $change_active]);
        elseif ($action == 'confirmation')
            FrontUser::where('id', $id)->update(['confirmation' => $change_active]);

        return response()->json([
            'status' => true,
            'type' => 'info',
            'messages' => [$msg]
        ]);

    }

    public function createitem()
    {
        $view = 'admin.front-user-manage.create-users';

        return view($view, get_defined_vars());
    }

    public function editUser(Request $request, $id)
    {
        $view = 'admin.front-user-manage.edit-user';
        $user = FrontUser::where('id', $id)->first();

        return view($view, get_defined_vars());
    }

    public function save(Request $request, $id)
    {
        if (is_null($id)) {
            if (!empty($request->input('password'))) {
                $item = Validator::make($request->all(), [
                    'name' => 'required',
                    'email' => 'required|unique:front_user,email',
                    'password' => [
                        'required',
                        'string',
                        Password::min(6) //Require at least 8 characters
                        ->mixedCase() //Require at least one uppercase and one lowercase letter
                        ->numbers() // Require at least one number
                        ->letters(), //Require at least one letter
                        //->symbols() //Require at least one symbol
                        //->uncompromised(),
                        'confirmed'
                    ],
                    'uploaded_files' => 'nullable|max:10',
                    'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ], [
                    'upload_files.*.mimes' => __('variables.custom_image_mime'),
                    'upload_files.*.max' => __('variables.custom_image_size'),
                ]);
            } else {
                $item = Validator::make($request->all(), [
                    'name' => 'required',
                    'email' => 'required|unique:front_user,email',
                    'uploaded_files' => 'nullable|max:10',
                    'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ], [
                    'upload_files.*.mimes' => __('variables.custom_image_mime'),
                    'upload_files.*.max' => __('variables.custom_image_size'),
                ]);
            }
        } else {
            if (!empty($request->input('password'))) {
                $item = Validator::make($request->all(), [
                    'name' => 'required',
                    'email' => 'required|front_user:email,' . $id,
                    'password' => [
                        'required',
                        'string',
                        Password::min(6) //Require at least 8 characters
                        ->mixedCase() //Require at least one uppercase and one lowercase letter
                        ->numbers() // Require at least one number
                        ->letters(), //Require at least one letter
                        //->symbols() //Require at least one symbol
                        //->uncompromised(),
                        'confirmed'
                    ],
                    'upload_files' => 'nullable|max:10',
                    'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ], [
                    'upload_files.*.mimes' => __('variables.custom_image_mime'),
                    'upload_files.*.max' => __('variables.custom_image_size'),
                ]);
            } else {
                $item = Validator::make($request->all(), [
                    'name' => 'required',
                    'email' => 'required|front_user:email,' . $id,
                    'upload_files' => 'nullable|max:10',
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

        $user = FrontUser::updateOrCreate(['id' => $id], [
            'name' => $request->input('name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'remember_token' => $request->input('_token'),
            'img' => $request->file('upload_files') ? uploadMultipleFiles($request->file('upload_files'), $request->input('uploaded_files'), 'front-user', 0) : getImageById($id, 'FrontUser'),
        ]);

        $user->push();

        if ($request->input('password'))
            $user->password = bcrypt($request->input('password'));

        $user->update();

        if (is_null($id)) {
            return response()->json([
                'status' => true,
                'messages' => [controllerTrans('variables.save', LANG)],
                'redirect' => urlForLanguage(LANG, '/')
            ]);
        }
        return response()->json([
            'status' => true,
            'messages' => [controllerTrans('variables.updated_text', LANG)],
            'redirect' => urlForLanguage(LANG, 'edituser/' . $id)
        ]);
    }

    public function destroyFrontUser(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $front_users_ids = FrontUser::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$front_users_ids->isEmpty()) {

                $del_message = '';

                foreach ($front_users_ids as $one_front_user) {

                    if (File::exists('upfiles/front-user/s/' . showImg($one_front_user->img)))
                        File::delete('upfiles/front-user/s/' . showImg($one_front_user->img));

                    if (File::exists('upfiles/front-user/m/' . showImg($one_front_user->img)))
                        File::delete('upfiles/front-user/m/' . showImg($one_front_user->img));

                    if (File::exists('upfiles/front-user/' . $one_front_user->img))
                        File::delete('upfiles/front-user/' . $one_front_user->img);

                    $del_message .= $one_front_user->email . ', ';

                    FrontUser::destroy($one_front_user->id);
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
