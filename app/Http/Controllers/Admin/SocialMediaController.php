<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class SocialMediaController extends Controller
{
    public function index()
    {
        $view = 'admin.social-media.social-media-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $social_list = SocialMedia::orderBy('position', 'asc')
            ->paginate(config('custom.back.social_items_per_page'));

        return view($view, get_defined_vars());
    }

    public function createItem()
    {
        $view = 'admin.social-media.create-social-media';
        return view($view, get_defined_vars());
    }

    public function editItem($id, $lang_id)
    {
        $view = 'admin.social-media.edit-social-media';

        $social_media = SocialMedia::where('id', $id)
            ->first();

        if (is_null($social_media)) {
            return App::abort(503, 'Unauthorized action.');
        }

        return view($view, get_defined_vars());
    }

    public function save(Request $request, $id, $lang_id)
    {
        if (is_null($id)) {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'uploaded_files' => 'nullable|max:10',
                'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'upload_files.*.mimes' => __('variables.custom_image_mime'),
                'upload_files.*.max' => __('variables.custom_image_size'),
            ]);
        } else {
            $item = Validator::make($request->all(), [
                'name' => 'required',
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

        $maxPosition = GetMinPosition('social_media');

        if ($id) {
            $currentPosition = GetPosition('social_media', $id);
            $position = $currentPosition;
        } else {
            $position = $maxPosition - 1;
        }

         SocialMedia::updateOrCreate([
            'id' => $id,
        ], [
            'name' => $request->input('name'),
            'link' => $request->input('link'),
            'icon_name' => $request->input('icon_name'),
            'position' => $position,
            'img' => $request->file('upload_files') ? uploadMultipleFiles($request->file('upload_files'), $request->input('uploaded_files'), 'social-media', 0) : getImageById($id, 'SocialMedia')
        ]);


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

    public function destroyItem(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $social_media_ids = SocialMedia::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$social_media_ids->isEmpty()) {

                $del_message = '';

                foreach ($social_media_ids as $one_social_item) {

                    if (File::exists('upfiles/social-media/s/' . showImg($one_social_item->img)))
                        File::delete('upfiles/social-media/s/' . showImg($one_social_item->img));

                    if (File::exists('upfiles/social-media/m/' . showImg($one_social_item->img)))
                        File::delete('upfiles/social-media/m/' . showImg($one_social_item->img));

                    if (File::exists('upfiles/social-media/' . $one_social_item->img))
                        File::delete('upfiles/social-media/' . $one_social_item->img);

                    $del_message .= $one_social_item->name . ', ';

                    SocialMedia::destroy($one_social_item->id);
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

    public function changePosition(Request $request)
    {
        $positionItems = SocialMedia::get();
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

        $element_id = SocialMedia::findOrFail($id);

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

        SocialMedia::where('id', $id)->update(['active' => $change_active]);

        return response()->json([
            'status' => true,
            'type' => 'info',
            'messages' => [$msg]
        ]);

    }
}
