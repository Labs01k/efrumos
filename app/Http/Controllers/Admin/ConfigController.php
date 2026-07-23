<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function index()
    {
        $view = 'admin.config.config-list';

        $modules_name = $this->menu()['modules_name'];
        $url_for_active_elem = '/' . LANG . '/back/' . $modules_name->modulesId->alias;

        $config_list = [];
        $config_list = config('custom.front');

        return view($view, get_defined_vars());
    }

    function ajaxUpdateConfig(Request $request)
    {
        $config_key = $request->input('config_key');
        $config_value = intval($request->input('config_value'));

        if($config_key && $config_value){
            $this->updateConfigFile($config_key, $config_value);
        }

        return response()->json([
            'status' => true,
            'message' => [controllerTrans('variables.save', LANG)],
            'redirect' => urlForFunctionLanguage(LANG, '')
        ]);
    }

     public function updateConfigFile($key, $new_value)
    {
        $path = config_path('custom.php');
        // get old value from current config
        $old_value = config('custom.front.'.$key);

        // was there any change?
        if ($old_value === $new_value) {
            return;
        }

        // rewrite file content with changed data
        if (file_exists($path)) {
            // replace current value with new value
            file_put_contents(
                $path, str_replace(
                    "'".$key."' => " . $old_value ,
                    "'".$key."' => " . $new_value ,
                    file_get_contents($path)
                )
            );
        }
    }
}
