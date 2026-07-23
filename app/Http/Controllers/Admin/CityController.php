<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CityId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    public function index()
    {
        $view = 'admin.city.cities-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $city_list_id = CityId::orderBy('position', 'asc')
            ->paginate(config('custom.back.cities_items_per_page'));

        $city_list = [];
        foreach($city_list_id as $key => $one_city_id){
            $city_list[$key] = City::where('city_id' ,$one_city_id->id)
                ->first();
        }

        $city_list = array_filter( $city_list);

        return view($view, get_defined_vars());
    }

    public function changePosition(Request $request)
    {
        $positionItems = CityId::get();
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

		$element_id = CityId::findOrFail($id);

		if(!is_null($element_id))
			$element_name = GetNameByLang($element_id->id, LANG_ID, 'City', 'city_id');
		else
			return response()->json([
				'status' => false,
				'type' => 'error',
				'messages' => [controllerTrans('variables.something_wrong', LANG)]
			]);

		if($active == 1) {
			$change_active = 0;
			$msg = controllerTrans('variables.element_is_inactive', LANG, ['name' => $element_name]);
		}
		else{
			$change_active = 1;
			$msg = controllerTrans('variables.element_is_active', LANG, ['name' => $element_name]);
		}

		CityId::where('id', $id)->update(['active' => $change_active]);

		return response()->json([
			'status' => true,
			'type' => 'info',
			'messages' => [$msg]
		]);

	}

    public function createItem()
    {
        $view = 'admin.city.create-city';

        return view($view, get_defined_vars());
    }

    public function editItem($id, $lang_id)
    {
        $view = 'admin.city.edit-city';

	    $modules_name = $this->menu()['modules_name'];
	    $url_for_active_elem = '/'.LANG.'/back/'.$modules_name->modulesId->alias;

        $city_without_lang = City::where('city_id', $id)
            ->first();

        $city = City::where('city_id', $city_without_lang->city_id)
            ->where('lang_id', $lang_id)
            ->first();

        return view($view, get_defined_vars());
    }

    public function save(Request $request, $id, $lang_id)
    {
        if(is_null($id)){
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:city_id',
            ]);
        }
        else {
            $item = Validator::make($request->all(), [
                'name' => 'required',
                'alias' => 'required|unique:city_id,alias,'.$id,
            ]);
        }

        if($item->fails()){
            return response()->json([
                'status' => false,
                'messages' => $item->messages(),
            ]);
        }

        $maxPosition = GetMinPosition('city_id');

        if ($id) {
            $currentPosition = GetPosition('city_id', $id);
            $position = $currentPosition;
        } else {
            $position = $maxPosition - 1;
        }

        if(checkIfLangExist($request->input('lang')) == false)
            return response()->json([
                'status' => false,
                'messages' => [controllerTrans('variables.lang_not_exist', LANG)],
            ]);

        $city_id = CityId::updateOrCreate(['id' => $id], [
            'alias' => $request->input('alias'),
            'position' => $position,
        ]);

        City::updateOrCreate([
            'city_id' => $city_id->id,
            'lang_id' => $request->input('lang'),
        ], [
            'name' => $request->input('name'),
        ]);

        $city_id->push();

        if(is_null($id)){
            return response()->json([
                'status' => true,
                'messages' => [controllerTrans('variables.save', LANG)],
                'redirect' => urlForFunctionLanguage(LANG, '')
            ]);
        }
        return response()->json([
            'status' => true,
            'messages' => [controllerTrans('variables.updated_text', LANG)],
            'redirect' => urlForLanguage(LANG, 'edititem/'.$id.'/'.$lang_id)
        ]);

    }

    public function destroyCityFromCart(Request $request)
    {
        $deleted_elements_id = $request->input('data_goods_id');
        $data_current_url = $request->input('data_current_url');

        if (!empty($deleted_elements_id)) {
            $deleted_elements_id_arr = explode(',', $deleted_elements_id);

            $city_elems_id = CityId::whereIn('id', $deleted_elements_id_arr)->get();

            if (!$city_elems_id->isEmpty()) {

                $del_message = '';

                foreach ($city_elems_id as $one_city_elems_id) {
                    $city = City::where('city_id', $one_city_elems_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();

                    $del_message .= $city->name . ', ';

                    City::where('city_id', $one_city_elems_id->id)->delete();
                    CityId::destroy($one_city_elems_id->id);
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
