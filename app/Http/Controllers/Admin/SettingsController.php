<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Models\SettingsId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function index()
    {
        $view = 'admin.settings.settings-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $settings_list_id = SettingsId::orderBy('position', 'asc')
            ->paginate(config('custom.back.settings_items_per_page'));
 
        /*$settings_list = [];
        foreach ($settings_list_id as $key => $one_settings_list_id) {

            $settings_item = Settings::where('settings_id', $one_settings_list_id->id)->first();

            if($settings_item)
                $settings_list[$key] = $settings_item;
        }*/

        //Remove all null values --start
        //$settings_list = array_filter($settings_list, 'strlen');
        //Remove all null values --end
        return view($view, get_defined_vars());
    }

    public function createItem()
    {
        $view = 'admin.settings.create-setting';
        return view($view, get_defined_vars());
    }

    public function editItem($id, $lang_id)
    {
        $view = 'admin.settings.edit-setting';
        $settings_without_lang = Settings::where('settings_id', $id)->first();
        if (is_null($settings_without_lang)) {

            return App::abort(503, 'Unauthorized action.');
        }
        $settings = Settings::where('settings_id', $settings_without_lang->settings_id)
            ->where('lang_id', $lang_id)
            ->first();

        if (!is_null($settings_without_lang)) {

            $settings_id = SettingsId::where('id', $settings_without_lang->settings_id)->first();
        } elseif (!is_null($settings)) {

            $settings_id = SettingsId::where('id', $settings->settings_id)->first();
        }

        if($settings_id && $settings_id->lang_dependent == 1)
            $settings_body = $settings->body ?? $settings_without_lang->body;
        else
            $settings_body = $settings_id->body_without_lang;

        return view($view, get_defined_vars());
    }

    public function save(Request $request, $id, $lang_id)
    {
        if (is_null($id)) {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:settings_id'
            ]);
        } else {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:settings_id,alias,'.$id,
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

        $maxPosition = GetMinPosition('settings_id');

        if ($id) {
            $currentPosition = GetPosition('settings_id', $id);
            $position = $currentPosition;
        } else {
            $position = $maxPosition - 1;
        }

        $lang_id = $request->input('lang');
        $body_without_lang = '';

        if($request->input('lang_dependent') == 0){
            if ($request->input('textarea'))
                $body_without_lang = $request->input('textarea');
            if ($request->input('input'))
                $body_without_lang = $request->input('input');
        }

        $settings_id = SettingsId::updateOrCreate([
            'id' => $id,
        ], [
            'alias' => $request->input('alias'),
            'set_type' => $request->input('set_type'),
            'position' => $position,
            'lang_dependent' => $request->input('lang_dependent') == 'on' ? 1 : 0,
            'body_without_lang' => $body_without_lang,
        ]);

        $body = '';

       if($settings_id->lang_dependent == 1){
           if ($request->input('textarea'))
               $body = $request->input('textarea');
           if ($request->input('input'))
               $body = $request->input('input');
       }

        Settings::updateOrCreate([
            'settings_id' => $settings_id->id,
            'lang_id' => $lang_id,
        ], [
            'name' => $request->input('name'),
            'body' => $body,
        ]);
        $settings_id->push();

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

    public function changePosition(Request $request)
    {
        $positionItems = SettingsId::get();
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


    public function destroySettingFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {

            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $setting_item_elems_id = SettingsId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$setting_item_elems_id->isEmpty()) {
                $del_message = '';

                foreach ($setting_item_elems_id as $one_setting_item_elems_id) {

                    $setting_item_elems = Settings::where('settings_id', $one_setting_item_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    if (is_null($setting_item_elems)) {
                        $setting_item_elems = Settings::where('settings_id', $one_setting_item_elems_id->id)
                            ->first();
                    }

                    $del_message .= $setting_item_elems->name . ', ';

                    SettingsId::destroy($one_setting_item_elems_id->id);
                    Settings::where('settings_id', $one_setting_item_elems_id->id)->delete();

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

    /**
     * return to another url, if method membersList does not exist
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */

    public function membersList()
    {
        return redirect(urlForFunctionLanguage(LANG, ''));
    }
}
