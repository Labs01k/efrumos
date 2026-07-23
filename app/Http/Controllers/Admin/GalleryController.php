<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use App\Models\GalleryItemId;
use App\Models\GallerySubject;
use App\Models\GallerySubjectId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GalleryController extends Controller
{

    public function index()
    {
        $view = 'admin.gallery.gallery-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $gallery_subject_id_list = GallerySubjectId::where('deleted', 0)
            ->where('level', 1)
            ->orderBy('position', 'asc')
            ->paginate(config('custom.back.gallery_subjects_per_page'));

        $gallery_subject_list = [];
        foreach ($gallery_subject_id_list as $key => $one_gallery_subject_id_list) {
            $gallery_subject_list[$key] = GallerySubject::where('gallery_subject_id', $one_gallery_subject_id_list->id)
                ->first();
        }

        //Remove all null values --start
        $gallery_subject_list = array_filter($gallery_subject_list, 'strlen');
        //Remove all null values --end

        return view($view, get_defined_vars());
    }

    public function changePosition(Request $request)
    {
        $positionItems = [];
        switch ($request->input('action')) {
            case 'subject':
                $positionItems = GallerySubjectId::get();
                break;
            case 'item':
                $positionItems = GalleryItemId::get();
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

    // ajax response for active
    public function changeActive(Request $request)
    {
        $active = $request->input('active');
        $id = $request->input('id');
        $action = $request->input('action');


        if ($action == 'item' || $action == 'show_on_main')
            $element_id = GalleryItemId::findOrFail($id);
        elseif ($action == 'subject')
            $element_id = GallerySubjectId::findOrFail($id);
        else
            $element_id = null;

        if (!is_null($element_id)) {
            if ($action == 'item')
                $element_name = GetNameByLang($element_id->id, LANG_ID, 'GalleryItem', 'gallery_item_id');
            elseif ($action == 'subject')
                $element_name = GetNameByLang($element_id->id, LANG_ID, 'GallerySubject', 'gallery_subject_id');
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
            GalleryItemId::where('id', $id)->update(['active' => $change_active]);
        elseif ($action == 'subject')
            GallerySubjectId::where('id', $id)->update(['active' => $change_active]);
        elseif ($action == 'show_on_main')
            GalleryItemId::where('id', $id)->update(['show_on_main' => $change_active]);

        return response()->json([
            'status' => true,
            'type' => 'info',
            'messages' => [$msg]
        ]);
    }

    public function createGallerySubject()
    {
        $view = 'admin.gallery.create-gallery-subject';

        $modules_name = $this->menu()['modules_name'];
        $curr_page_id = GallerySubjectId::where('alias', request()->segment(4))
            ->first();

        if (!is_null($curr_page_id)) {
            $curr_page_id = $curr_page_id->id;
        } else {
            $curr_page_id = null;
        }

        return view($view, get_defined_vars());
    }

    public function editGallerySubject($id, $lang_id)
    {
        $view = 'admin.gallery.edit-gallery-subject';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;
        $gallery_without_lang = GallerySubject::where('gallery_subject_id', $id)->first();

        if (is_null($gallery_without_lang)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $gallery_elems = GallerySubject::where('lang_id', $lang_id)
            ->where('gallery_subject_id', $gallery_without_lang->gallery_subject_id)
            ->first();

        if (!is_null($gallery_without_lang)) {
            $gallery_subject_id = GallerySubjectId::where('id', $gallery_without_lang->gallery_subject_id)
                ->first();
        } elseif (!is_null($gallery_elems)) {
            $gallery_subject_id = GallerySubjectId::where('id', $gallery_elems->gallery_subject_id)
                ->first();
        }

        return view($view, get_defined_vars());
    }

    public function saveSubject(Request $request, $id, $lang_id)
    {
        if (is_null($id)) {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:gallery_subject_id',
                'uploaded_files' => 'nullable|max:10',
                'upload_files.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ], [
                'upload_files.*.mimes' => __('variables.custom_image_mime'),
                'upload_files.*.max' => __('variables.custom_image_size'),
            ]);
        } else {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:gallery_subject_id,alias,' . $id,
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

        if (is_null($request->input('p_id')))
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables._wrong_message', LANG)]
            ]);

        $maxPosition = GetMinPosition('gallery_subject_id');
        $level = GetLevel($request->input('p_id'), 'gallery_subject_id');
        if ($id) {
            $currentPosition = GetPosition('gallery_subject_id', $id);
            $position = $currentPosition;
        } else {
            $position = $maxPosition - 1;
        }

        if (checkIfLangExist($request->input('lang')) == false)
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.lang_not_exist', LANG)],
            ]);

        $subject_id = GallerySubjectId::updateOrCreate(['id' => $id], [
            'p_id' => $request->input('p_id'),
            'level' => $level + 1,
            'alias' => $request->input('alias'),
            'position' => $position,
            'img' => $request->file('upload_files') ? uploadMultipleFiles($request->file('upload_files'), $request->input('uploaded_files'), 'gallery-subject', 0) : getImageById($id, 'GallerySubjectId'),
            'active' => 1,
            'deleted' => 0,
        ]);

        GallerySubject::updateOrCreate([
            'gallery_subject_id' => $subject_id->id,
            'lang_id' => $request->input('lang'),
        ], [
            'name' => $request->input('name'),
            'body' => $request->input('body'),
            'page_title' => $request->input('page_title'),
            'h1_title' => $request->input('h1_title'),
            'meta_title' => $request->input('meta_title'),
            'meta_keywords' => $request->input('meta_keywords'),
            'meta_description' => $request->input('meta_description'),
        ]);

        $subject_id->push();

        if (is_null($id)) {
            if ($subject_id->level == 1) {
                return response()->json([
                    'status' => true,
                    'messages' => [controllerTrans('variables.save', LANG)],
                    'redirect' => urlForFunctionLanguage(LANG, '')
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'messages' => [controllerTrans('variables.save', LANG)],
                    'redirect' => urlForFunctionLanguage(LANG, GetParentAlias($subject_id->id, 'gallery_subject_id') . '/memberslist')
                ]);
            }

        }
        return response()->json([
            'status' => true,
            'messages' => [controllerTrans('variables.updated_text', LANG)],
            'redirect' => urlForLanguage(LANG, 'editgallerysubject/' . $id . '/' . $lang_id)
        ]);
    }

    public function gallerySubjectCart()
    {
        $view = 'admin.gallery.subject-cart';

        $deleted_elems_by_alias = GallerySubjectId::where('alias', request()->segment(4))
            ->first();

        if (is_null($deleted_elems_by_alias)) {
            $deleted_subject_id_elems = GallerySubjectId::where('deleted', 1)
                ->where('active', 0)
                ->where('p_id', 0)
                ->get();
        } else {
            $deleted_subject_id_elems = GallerySubjectId::where('deleted', 1)
                ->where('active', 0)
                ->where('p_id', $deleted_elems_by_alias->id)
                ->get();
        }

        $deleted_subject_elems = [];
        foreach ($deleted_subject_id_elems as $key => $one_deleted_subject_elem) {
            $deleted_subject_elems[$key] = GallerySubject::where('gallery_subject_id', $one_deleted_subject_elem->id)
                ->first();
        }

        $deleted_subject_elems = array_filter($deleted_subject_elems, 'strlen');

        return view($view, get_defined_vars());
    }

    public function destroyGallerySubjectFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $gallery_subject_elems_id = GallerySubjectId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$gallery_subject_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($gallery_subject_elems_id as $one_gallery_subject_elems_id) {

                    $gallery_subject_elems = GallerySubject::where('gallery_subject_id', $one_gallery_subject_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($gallery_subject_elems)) {
                        $gallery_subject_elems = GallerySubject::where('gallery_subject_id', $one_gallery_subject_elems_id->id)
                            ->first();
                    }

                    if ($one_gallery_subject_elems_id->deleted == 1 && $one_gallery_subject_elems_id->active == 0) {

                        $gallery_photo = GalleryItemId::where('gallery_subject_id', $one_gallery_subject_elems_id->id)->get();
                        if (!is_null($gallery_photo)) {
                            foreach ($gallery_photo as $one_gallery_photo) {
                                if (File::exists('upfiles/gallery-items/s/' . showImg($one_gallery_photo->img)))
                                    File::delete('upfiles/gallery-items/s/' . showImg($one_gallery_photo->img));

                                if (File::exists('upfiles/gallery-items/m/' . showImg($one_gallery_photo->img)))
                                    File::delete('upfiles/gallery-items/m/' . showImg($one_gallery_photo->img));

                                if (File::exists('upfiles/gallery-items/' . $one_gallery_photo->img))
                                    File::delete('upfiles/gallery-items/' . $one_gallery_photo->img);
                            }
                        }

                        if (File::exists('upfiles/gallery-subject/s/' . showImg($one_gallery_subject_elems_id->img)))
                            File::delete('upfiles/gallery-subject/s/' . showImg($one_gallery_subject_elems_id->img));

                        if (File::exists('upfiles/gallery-subject/m/' . showImg($one_gallery_subject_elems_id->img)))
                            File::delete('upfiles/gallery-subject/m/' . showImg($one_gallery_subject_elems_id->img));

                        if (File::exists('upfiles/gallery-subject/' . $one_gallery_subject_elems_id->img))
                            File::delete('upfiles/gallery-subject/' . $one_gallery_subject_elems_id->img);

                        $del_message .= $gallery_subject_elems->name . ', ';

                        GallerySubjectId::destroy($one_gallery_subject_elems_id->id);
                        GallerySubject::where('gallery_subject_id', $one_gallery_subject_elems_id->id)->delete();

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

    public function destroyGallerySubjectToCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $gallery_subject_elems_id = GallerySubjectId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$gallery_subject_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($gallery_subject_elems_id as $one_gallery_subject_elems_id) {

                    $gallery_subject_elems = GallerySubject::where('gallery_subject_id', $one_gallery_subject_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($gallery_subject_elems)) {
                        $gallery_subject_elems = GallerySubject::where('gallery_subject_id', $one_gallery_subject_elems_id->id)
                            ->first();
                    }


                    if ($one_gallery_subject_elems_id->deleted == 0) {

                        $cart_message .= $gallery_subject_elems->name . ', ';

                        GallerySubjectId::where('id', $one_gallery_subject_elems_id->id)
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

    public function restoreGallerySubject(Request $request)
    {
        $restored_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($restored_elements_id)) {
            $restored_elements_id_arr = explode(',', $restored_elements_id);

            $gallery_item_elems_id = GallerySubjectId::whereIn('id', $restored_elements_id_arr)->get();

            if (!$gallery_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($gallery_item_elems_id as $one_gallery_item_elems_id) {

                    $gallery_name = GetNameByLang($one_gallery_item_elems_id->id, LANG_ID, 'GallerySubject', 'gallery_subject_id');

                    if ($one_gallery_item_elems_id->restored == 0) {

                        $cart_message .= $gallery_name . ', ';

                        GallerySubjectId::where('id', $one_gallery_item_elems_id->id)
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
        $view = 'admin.gallery.child-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $gallery_list_id = GallerySubjectId::where('alias', request()->segment(4))
            ->first();

        if (is_null($gallery_list_id)) {
            return App::abort(503, 'Unauthorized action.');
        }

        if (CheckIfSubjectHasItems('gallery', $gallery_list_id->id)->isEmpty()) {
            $child_gallery_list_id = GallerySubjectId::where('p_id', $gallery_list_id->id)
                ->where('deleted', 0)
                ->orderBy('position', 'asc')
                ->paginate(config('custom.back.gallery_subjects_per_page'));
            $child_gallery_list = [];
            foreach ($child_gallery_list_id as $key => $one_gallery_elem) {
                $child_gallery_list[$key] = GallerySubject::where('gallery_subject_id', $one_gallery_elem->id)
                    ->first();
            }

            $child_gallery_list = array_filter($child_gallery_list, 'strlen');
            $child_gallery_item_list = [];
        } else {
            $child_gallery_item_list_id = GalleryItemId::where('gallery_subject_id', $gallery_list_id->id)
                ->where('deleted', 0)
                ->orderBy('position', 'asc')
                ->paginate(config('custom.back.gallery_subjects_items_per_page'));
            $child_gallery_item_list = [];
            foreach ($child_gallery_item_list_id as $key => $one_gallery_elem) {
                $child_gallery_item_list[$key] = GalleryItem::where('gallery_item_id', $one_gallery_elem->id)
                    ->first();
            }

            $child_gallery_item_list = array_filter($child_gallery_item_list, 'strlen');
            $child_gallery_list = [];
        }

        return view($view, get_defined_vars());
    }

    public function itemsPhoto()
    {
        $view = 'admin.gallery.items-photo';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $gallery_subject_id = GallerySubjectId::where('alias', request()->segment(4))
            ->first();

        $gallery_parent_subject_id = GallerySubjectId::where('id', $gallery_subject_id->p_id)
            ->first();

        if (is_null($gallery_subject_id)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $gallery_subject = GallerySubject::where('gallery_subject_id', $gallery_subject_id->id)
            ->where('lang_id', LANG_ID)
            ->first();

        if (!is_null($gallery_subject_id)) {

            $gallery_item_id = GalleryItemId::where('gallery_subject_id', $gallery_subject_id->id)
                ->where('type', 'photo')
                ->where('deleted', 0)
                ->orderBy('position', 'asc')
                ->get();

            $gallery_item = [];
            if (!$gallery_item_id->isEmpty()) {
                foreach ($gallery_item_id as $one_gallery_item_id) {
                    $gallery_item[] = GalleryItem::where('gallery_item_id', $one_gallery_item_id->id)
//                        ->where('lang_id', $lang_id)
                        ->first();
                }

                $gallery_item = array_filter($gallery_item);
            }

        }

        return view($view, get_defined_vars());
    }

    public function galleryItemCart()
    {
        $view = 'admin.gallery.item-cart';


        $deleted_elems_by_alias = GallerySubjectId::where('alias', request()->segment(4))
            ->first();

        $deleted_item_id_elems = GalleryItemId::where('deleted', 1)
            ->where('active', 0)
            ->where('gallery_subject_id', $deleted_elems_by_alias->id)
            ->get();

        $deleted_item_elems = [];
        foreach ($deleted_item_id_elems as $key => $one_deleted_item_elem) {
            $deleted_item_elems[$key] = GalleryItem::where('gallery_item_id', $one_deleted_item_elem->id)
                ->first();
        }

        $deleted_item_elems = array_filter($deleted_item_elems, 'strlen');

        return view($view, get_defined_vars());
    }

    public function destroyGalleryItemFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $gallery_item_elems_id = GalleryItemId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$gallery_item_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($gallery_item_elems_id as $one_gallery_item_elems_id) {

                    $gallery_item_elems = GalleryItem::where('gallery_item_id', $one_gallery_item_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($gallery_item_elems)) {
                        $gallery_item_elems = GalleryItem::where('gallery_item_id', $one_gallery_item_elems_id->id)
                            ->first();
                    }

                    if ($one_gallery_item_elems_id->deleted == 1 && $one_gallery_item_elems_id->active == 0) {

                        if (File::exists('upfiles/gallery-items/s/' . showImg($one_gallery_item_elems_id->img)))
                            File::delete('upfiles/gallery-items/s/' . showImg($one_gallery_item_elems_id->img));

                        if (File::exists('upfiles/gallery-items/m/' . showImg($one_gallery_item_elems_id->img)))
                            File::delete('upfiles/gallery-items/m/' . showImg($one_gallery_item_elems_id->img));

                        if (File::exists('upfiles/gallery-items/' . $one_gallery_item_elems_id->img))
                            File::delete('upfiles/gallery-items/' . $one_gallery_item_elems_id->img);

                        $del_message .= $gallery_item_elems->name . ', ';

                        GalleryItemId::destroy($one_gallery_item_elems_id->id);
                        GalleryItem::where('gallery_item_id', $one_gallery_item_elems_id->id)->delete();

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

    public function destroyGalleryItemToCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $gallery_item_elems_id = GalleryItemId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$gallery_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($gallery_item_elems_id as $one_gallery_item_elems_id) {

                    $gallery_item_elems = GalleryItem::where('gallery_item_id', $one_gallery_item_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($gallery_item_elems)) {
                        $gallery_item_elems = GalleryItem::where('gallery_item_id', $one_gallery_item_elems_id->id)
                            ->first();
                    }


                    if ($one_gallery_item_elems_id->deleted == 0) {

                        $cart_message .= $gallery_item_elems->name . ', ';

                        GalleryItemId::where('id', $one_gallery_item_elems_id->id)
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

    public function restoreGalleryItem(Request $request)
    {
        $restored_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($restored_elements_id)) {
            $restored_elements_id_arr = explode(',', $restored_elements_id);

            $gallery_item_elems_id = GalleryItemId::whereIn('id', $restored_elements_id_arr)->get();

            if (!$gallery_item_elems_id->isEmpty()) {

                $cart_message = '';

                foreach ($gallery_item_elems_id as $one_gallery_item_elems_id) {

                    $gallery_name = GetNameByLang($one_gallery_item_elems_id->id, LANG_ID, 'GalleryItem', 'gallery_item_id');

                    if ($one_gallery_item_elems_id->restored == 0) {

                        $cart_message .= $gallery_name . ', ';

                        GalleryItemId::where('id', $one_gallery_item_elems_id->id)
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

    public function itemsVideo()
    {
        $view = 'admin.gallery.items-video';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $gallery_subject_id = GallerySubjectId::where('alias', request()->segment(4))
            ->first();

        if (is_null($gallery_subject_id)) {
            return App::abort(503, 'Unauthorized action.');
        }

        $gallery_subject = GallerySubject::where('gallery_subject_id', $gallery_subject_id->id)
            ->where('lang_id', LANG_ID)
            ->first();

        if (!is_null($gallery_subject_id)) {

            $gallery_item_id = GalleryItemId::where('gallery_subject_id', $gallery_subject_id->id)
                ->where('type', 'video')
                ->where('deleted', 0)
                ->orderBy('position', 'asc')
                ->get();

            $gallery_item = [];
            if (!$gallery_item_id->isEmpty()) {
                foreach ($gallery_item_id as $one_gallery_item_id) {
                    $gallery_item[] = GalleryItem::where('gallery_item_id', $one_gallery_item_id->id)
                        //->where('lang_id', $lang_id)
                        ->first();
                }

                $gallery_item = array_filter($gallery_item);
            }

        }

        return view($view, get_defined_vars());
    }

    public function createItemsVideo(Request $request)
    {

        if (!$request->input('current_item')) {
            $item = Validator::make($request->all(), [
                'youtube_link' => 'required',
                'alias' => 'unique:gallery_item_id'
            ]);
        } else {

            $item = Validator::make($request->all(), [
                'youtube_link' => 'required',
            ]);
        }

        if ($item->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $item->messages(),
            ]);
        }

        $gallery_subject_id = GallerySubjectId::where('alias', request()->segment(4))
            ->first();

        if (is_null($gallery_subject_id)) {
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables._wrong_message', LANG)],
            ]);
        }

//        Check if lang exist
        if (checkIfLangExist($request->input('lang')) == false)
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.lang_not_exist', LANG)],
            ]);
//        Check if lang exist

        if (!is_null($request->input('youtube_id')))
            $youtube_id = $request->input('youtube_id');
        elseif (is_null($request->input('youtube_id')) && !is_null($request->input('youtube_link')))
            $youtube_id = $this->youtubeId($request->input('youtube_link'));
        else
            $youtube_id = '';

        $maxPosition = GetMinPosition('gallery_item_id');

        if (!$request->input('current_item')) {

            $data = [
                'gallery_subject_id' => $gallery_subject_id->id,
                'alias' => !is_null($request->input('name')) ? Str::slug($request->input('name') . '-' . $youtube_id) : Str::slug($youtube_id),
                'active' => 1,
                'deleted' => 0,
                'position' => $maxPosition - 1,
                'show_on_main' => 0,
                'img' => '',
                'youtube_id' => $youtube_id,
                'youtube_link' => $request->input('youtube_link'),
                'type' => 'video'

            ];

            $gallery_item_id = GalleryItemId::create($data);

            $data = [
                'gallery_item_id' => $gallery_item_id->id,
                'name' => !is_null($request->input('name')) ? $request->input('name') : '',
                'lang_id' => LANG_ID,
                'body' => !is_null($request->input('body')) ? $request->input('body') : ''
            ];

            GalleryItem::create($data);

            return response()->json([
                'status' => true,
                'messages' => [controllerTrans('variables.save', LANG)],
                'redirect' => urlForLanguage(LANG, 'itemsvideo')
            ]);
        } else {

            $exist_video = GalleryItem::where('gallery_item_id', $request->input('current_item'))->first();

            if (is_null($exist_video)) {
                return App::abort(503, 'Unauthorized action.');
            }

//            Check if alias exist
            if (checkIfAliasExist($exist_video->gallery_item_id, Str::slug($youtube_id), 'gallery_item_id') == true) {
                return response()->json([
                    'status' => false,
                    'messages' => [controllerTrans('variables.alias_exist', LANG)],
                ]);
            }
//            Check if alias exist

            $exist_video_by_lang = GalleryItem::where('gallery_item_id', $exist_video->gallery_item_id)
                ->where('lang_id', $request->input('lang'))
                ->first();

            $data = [
                'gallery_subject_id' => $gallery_subject_id->id,
                'alias' => !is_null($request->input('name')) ? Str::slug($request->input('name') . '-' . $youtube_id) : Str::slug($youtube_id),
                'youtube_id' => $youtube_id,
                'youtube_link' => $request->input('youtube_link')
            ];

            GalleryItemId::where('id', $exist_video->gallery_item_id)
                ->update($data);

            $data = array_filter([
                'name' => !is_null($request->input('name')) ? $request->input('name') : '',
                'body' => !is_null($request->input('body')) ? $request->input('body') : ''

            ]);

            if (!is_null($exist_video_by_lang)) {
                GalleryItem::where('gallery_item_id', $exist_video->gallery_item_id)
                    ->where('lang_id', $request->input('lang'))
                    ->update($data);
            } else {

                $create_data = [
                    'gallery_item_id' => $exist_video->gallery_item_id,
                    'lang_id' => $request->input('lang')
                ];

                $data = array_merge($data, $create_data);

                GalleryItem::create($data);
            }

            return response()->json([
                'status' => true,
                'messages' => [controllerTrans('variables.updated_text', LANG)],
                'redirect' => urlForLanguage(LANG, 'itemsvideo')
            ]);
        }

    }

    public function changeItemName(Request $request)
    {
        $item_id = $request->input('id');
        $item_name = $request->input('name');
        $lang_id = $request->input('lang_id');
        $edited_row = $request->input('edited_row');

        if (is_null($item_name) || empty($item_name))
            return response()->json([
                'status' => false,
                'messages' => controllerTrans('variables._wrong_message', LANG)
            ]);

        if (!empty($item_id)) {
            $gallery_item_id = GalleryItemId::where('id', $item_id)
                ->where('deleted', 0)
                ->first();

            if (!is_null($gallery_item_id)) {
                $gallery_item_by_lang = GalleryItem::where('gallery_item_id', $item_id)
                    ->where('lang_id', $lang_id)
                    ->first();

                if (!is_null($gallery_item_by_lang)) {
                    if ($edited_row == 'name')
                        $data = [
                            'name' => $item_name
                        ];
                    else
                        $data = [
                            'body' => $item_name
                        ];

                    GalleryItem::where('gallery_item_id', $item_id)
                        ->where('lang_id', $lang_id)
                        ->update($data);

                    return response()->json([
                        'status' => true,
                        'messages' => controllerTrans('variables.updated_text', LANG),
                        'new_name' => $item_name,
                        'new_body' => $item_name
                    ]);
                } else {
                    if ($edited_row == 'name')
                        $data = [
                            'gallery_item_id' => $gallery_item_id->id,
                            'name' => $item_name,
                            'lang_id' => $lang_id
                        ];
                    else
                        $data = [
                            'gallery_item_id' => $gallery_item_id->id,
                            'body' => $item_name,
                            'lang_id' => $lang_id
                        ];

                    GalleryItem::create($data);

                    return response()->json([
                        'status' => true,
                        'messages' => controllerTrans('variables.save', LANG),
                        'new_name' => $item_name,
                        'new_body' => $item_name
                    ]);
                }
            }

            return response()->json([
                'status' => false,
                'messages' => controllerTrans('variables._wrong_message', LANG)
            ]);
        }

        return response()->json([
            'status' => false,
            'messages' => controllerTrans('variables._wrong_message', LANG)
        ]);
    }

    // ajax response for youtube id
    public function youtubeId(Request $request, $youtube_link = null)
    {
        if (is_null($youtube_link))
            $code = $request->input('code');
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

    public function ajaxVideoContent(Request $request)
    {
        $id = $request->input('id');
        $lang_id = $request->input('lang_id');

//        Check if lang exist
        if (checkIfLangExist($lang_id) == false)
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.lang_not_exist', LANG)],
            ]);
//        Check if lang exist

        $gallery_item_id = GalleryItemId::where('id', $id)
            ->where('deleted', 0)
            ->first();

        if (is_null($gallery_item_id))
            return response()->json([
                'status' => false,
                'messages' => controllerTrans('variables._wrong_message', LANG)
            ]);

        $gallery_item = GalleryItem::where('gallery_item_id', $gallery_item_id->id)
            ->where('lang_id', $lang_id)
            ->first();

        return response()->json([
            'status' => true,
            'lang' => $lang_id,
            'name' => !is_null($gallery_item) ? $gallery_item->name : '',
            'body' => !is_null($gallery_item) ? $gallery_item->body : '',
            'link' => $gallery_item_id->youtube_link,
            'youtube_id' => $gallery_item_id->youtube_id
        ]);
    }

    public function ajaxAudioContent(Request $request)
    {
        $id = $request->input('id');
        $lang_id = $request->input('lang_id');

//        Check if lang exist
        if (checkIfLangExist($lang_id) == false)
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.lang_not_exist', LANG)],
            ]);
//        Check if lang exist

        $gallery_item_id = GalleryItemId::where('id', $id)
            ->where('deleted', 0)
            ->first();

        if (is_null($gallery_item_id))
            return response()->json([
                'status' => false,
                'messages' => controllerTrans('variables._wrong_message', LANG)
            ]);

        $gallery_item = GalleryItem::where('gallery_item_id', $gallery_item_id->id)
            ->where('lang_id', $lang_id)
            ->first();

        return response()->json([
            'status' => true,
            'lang' => $lang_id,
            'name' => !is_null($gallery_item) ? $gallery_item->name : '',
            'body' => !is_null($gallery_item) ? $gallery_item->body : '',
        ]);
    }
}
