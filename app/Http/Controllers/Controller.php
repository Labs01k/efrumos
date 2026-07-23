<?php

namespace App\Http\Controllers;

use App\Models\AdminUserActionPermision;
use App\Models\Lang;
use App\Models\Modules;
use App\Models\ModulesId;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function menu()
    {
        if (Auth::check()){
            if(!is_null(Auth::user()->admin_user_group_id)){
                $user = User::find(Auth::user()->id);
                $user_group_id = Auth::user()->admin_user_group_id;

                $menuIds = array_keys($user->group()->first()
                    ->userPermission()->get(['modules_id'])
                    ->groupBy(['modules_id'])
                    ->toArray()
                );

                $menu_modules_id = ModulesId::where('level', 1)
                    ->where('active', 1)
                    ->where('deleted', 0)
                    ->orderBy('position', 'asc')
                    ->findMany($menuIds);

                $menu = [];
                if(!$menu_modules_id->isEmpty()) {
                    foreach ($menu_modules_id as $item) {
                        $menu[] = Modules::where('modules_id', $item->id)
                            ->where('lang_id', LANG_ID)
                            ->first();
                    }
                }

                $modules_name_id = ModulesId::where('alias', request()->segment(3))
                    ->where('active', 1)
                    ->where('deleted', 0)
                    ->first();

                $modules_name = [];

                if(!is_null($modules_name_id)) {
                    $modules_name = Modules::where('modules_id', $modules_name_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();
                }

                $modules_sumbenu_name_id = ModulesId::where('alias', request()->segment(4))
                    ->where('active', 1)
                    ->where('deleted', 0)
                    ->first();

                $modules_sumbenu_name = [];

                if(!is_null($modules_sumbenu_name_id)) {
                    $modules_sumbenu_name = Modules::where('modules_id', $modules_sumbenu_name_id->id)
                        ->where('lang_id', LANG_ID)
                        ->first();
                }

                if(!is_null($modules_name_id)) {
                    $groupSubRelations = AdminUserActionPermision::where('admin_user_group_id', $user_group_id)
                        ->where('modules_id', $modules_name_id->id)
                        ->first();
                }
                elseif(!is_null($modules_sumbenu_name_id)){
                    $groupSubRelations = AdminUserActionPermision::where('admin_user_group_id', $user_group_id)
                        ->where('modules_id', $modules_sumbenu_name_id->id)
                        ->first();
                }
                else{
                    $groupSubRelations = [];
                }
            }
            else {
                $menu = [];
                $modules_name = [];
                $modules_sumbenu_name = [];
                $groupSubRelations = [];
            }
        }

        return get_defined_vars();
    }

}
