<?php

namespace App\Providers;

use App\Models\AdminUserActionPermision;
use App\Models\AdminUserGroup;
use App\Models\BannerTopId;
use App\Models\BrandId;
use App\Models\CityId;
use App\Models\FrontUser;
use App\Models\GallerySubjectId;
use App\Models\GoodsItemId;
use App\Models\GoodsItemReviews;
use App\Models\GoodsPageId;
use App\Models\GoodsPromo;
use App\Models\GoodsSubjectId;
use App\Models\GoodsTypeId;
use App\Models\InfoLineId;
use App\Models\LabelsId;
use App\Models\MenuId;
use App\Models\Modules;
use App\Models\ModulesId;
use App\Models\ModulesSubmenu;
use App\Models\PromotionsId;
use App\Models\SettingsId;
use App\Models\ShopsId;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;


class DefineElementsOfLeftMenu extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        /*Sidebar, footer, breadcrumbs*/
        View::composer(['admin.templates.sidebar','admin.footer', 'admin.templates.breadcrumbs', 'admin.user.create-group'], function($view) {
            if (Auth::check()){
                if(!is_null(Auth::user()->admin_user_group_id)){
                    $user = User::find(Auth::user()->id);
                    $user_group_id = Auth::user()->admin_user_group_id;

                    $menuIds = array_keys($user->group()->first()
                        ->userPermission()->get(['modules_id'])
                        ->groupBy(['modules_id'])
                        ->toArray()
                    );

                    $menu_modules_id = ModulesId::where('active', 1)
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


                    // Back breadcrumbs module
                    $back_breadcrumbs = '';
                    if(request()->segment(2) == 'back' && !is_null(request()->segment(3))) {
                        $element_list_id = $users_list_id = null;
                        $curr_model = null;
                        $curr_row_id = null;
                        $module_has_cart = true;

                        if(request()->segment(3) == 'goods') {
                            $element_list_id = GoodsSubjectId::where('alias', request()->segment(4))
                                ->first();
                            $curr_model = 'GoodsSubject';
                            $curr_row_id = 'goods_subject_id';
                        }
                        if(request()->segment(4) == 'reviews') {
                            $element_list_id = GoodsItemId::where('id', request()->segment(6))
                                ->first();
                            $curr_model = 'GoodsItem';
                            $curr_row_id = 'goods_item_id';
                        }
                        elseif(request()->segment(3) == 'menu') {
                            $element_list_id = MenuId::where('alias', request()->segment(4))
                                ->first();
                            $curr_model = 'Menu';
                            $curr_row_id = 'menu_id';
                        }
                        elseif(request()->segment(3) == 'goods-pages') {
                            $element_list_id = GoodsPageId::where('alias', request()->segment(4))
                                ->first();
                            $curr_model = 'GoodsPage';
                            $curr_row_id = 'goods_page_id';
                        }
                        elseif(request()->segment(3) == 'modules-constructor') {
                            $element_list_id = ModulesId::where('alias', request()->segment(4))
                                ->first();
                            $curr_model = 'Modules';
                            $curr_row_id = 'modules_id';
                        }
                        elseif(request()->segment(3) == 'info_line') {
                            $element_list_id = InfoLineId::where('alias', request()->segment(4))
                                ->first();
                            $curr_model = 'InfoLine';
                            $curr_row_id = 'info_line_id';
                        }
                        elseif(request()->segment(3) == 'gallery') {
                            $element_list_id = GallerySubjectId::where('alias', request()->segment(4))
                                ->first();
                            $curr_model = 'GallerySubject';
                            $curr_row_id = 'gallery_subject_id';
                        }
                        elseif(request()->segment(3) == 'promotions') {
                            $element_list_id = PromotionsId::where('alias', request()->segment(4))
                                ->first();
                            $curr_model = 'Promotions';
                            $curr_row_id = 'promotions_id';
                        }
                        elseif(request()->segment(3) == 'banners') {
                            $curr_model = 'Banner';
                            $curr_row_id = 'banner_id';
                        }
                        elseif(request()->segment(3) == 'brand') {

                            $element_list_id = BrandId::where('alias', request()->segment(4))
                                ->first();

                            $curr_model = 'Brand';
                            $curr_row_id = 'id';
                        }
                        elseif(request()->segment(3) == 'goods-reviews') {

                            $element_list_id = GoodsItemReviews::where('id', intval(request()->segment(6)))
                                ->first();

                            $curr_model = 'GoodsItemReviews';
                            $curr_row_id = 'id';
                        }
                        elseif(request()->segment(3) == 'promo') {

                            $element_list_id = GoodsPromo::where('id', intval(request()->segment(6)))
                                ->first();

                            $curr_model = 'GoodsPromo';
                            $curr_row_id = 'id';
                        }
                        elseif(request()->segment(3) == 'banner_top') {

                            $element_list_id = BannerTopId::where('id', intval(request()->segment(6)))
                                ->first();

                            $curr_model = 'BannerTop';
                            $curr_row_id = 'banner_top_id';
                        }
                        elseif(request()->segment(3) == 'goods-type') {

                            $element_list_id = GoodsTypeId::where('id', intval(request()->segment(6)))
                                ->first();

                            $curr_model = 'GoodsTypeId';
                            $curr_row_id = 'id';
                        }

                        elseif(request()->segment(3) == 'front-user') {

                            $element_list_id = FrontUser::where('id', intval(request()->segment(6)))
                                ->first();

                            $curr_model = 'FrontUser';
                            $curr_row_id = 'id';
                        }

                        elseif(request()->segment(3) == 'shops') {
                            $element_list_id = ShopsId::where('alias', request()->segment(4))
                                ->first();
                            $curr_model = 'Shops';
                            $curr_row_id = 'shops_id';
                            $module_has_cart = false;
                        }
                        elseif(request()->segment(3) == 'city') {
                            $element_list_id = CityId::where('alias', request()->segment(4))
                                ->first();
                            $curr_model = 'City';
                            $curr_row_id = 'city_id';
                            $module_has_cart = false;
                        }
                        elseif(request()->segment(3) == 'orders') {
                            $curr_model = 'Orders';
                            $curr_row_id = 'id';
                        }
                        elseif(request()->segment(3) == 'feedform') {
                            $curr_model = 'Feedform';
                            $curr_row_id = 'id';
                            $module_has_cart = false;
                        }
                        elseif(request()->segment(3) == 'settings') {
                            $element_list_id = SettingsId::where('alias', request()->segment(4))
                                ->first();
                            $curr_model = 'Settings';
                            $curr_row_id = 'settings_id';
                            $module_has_cart = false;
                        }
                        elseif(request()->segment(3) == 'labels') {
                            $element_list_id = LabelsId::where('id', request()->segment(4))
                                ->first();
                            $curr_model = 'Labels';
                            $curr_row_id = 'labels_id';
                            $module_has_cart = false;
                        }

                        elseif(request()->segment(3) == 'config') {
                            $curr_model = 'Config';
                            $curr_row_id = 'id';
                            $module_has_cart = false;
                        }

                        elseif(request()->segment(3) == 'social-media') {
                            $curr_model = 'SocialMedia';
                            $curr_row_id = 'id';
                            $module_has_cart = false;
                        }

                        if(!is_null($element_list_id))
                            $back_breadcrumbs = universalBreadcrumbsByDbFinal(LANG, LANG_ID, $element_list_id->id, $modules_name, $modules_sumbenu_name, request()->segment(3), $curr_model, $curr_row_id, $module_has_cart);
                        else
                            $back_breadcrumbs = universalBreadcrumbsByDbFinal(LANG, LANG_ID, null, $modules_name, $modules_sumbenu_name, request()->segment(3), $curr_model, $curr_row_id, $module_has_cart);

                        if(request()->segment(3) == 'admin_user') {
                            $users_list_id = AdminUserGroup::where('alias', request()->segment(4))
                                ->first();

                            $user_id = User::where('id', request()->segment(6))->first();

                            $back_breadcrumbs = adminUsersBreadcrumbsByDbFinal(LANG, $users_list_id, $modules_name, request()->segment(3), $user_id);
                        }
                    }
                    // Back breadcrumbs module


//                SubRelations (new, save, active, del_to_rec, del_from_rec)
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
                    $new_feedform = [];
                    $back_breadcrumbs = '';
                }
            }
            else {
                $menu = [];
                $modules_name = [];
                $modules_sumbenu_name = [];
                $groupSubRelations = [];
                $new_feedform = [];
                $back_breadcrumbs = '';
            }

            /*return*/
            $view->menu = $menu;
            $view->groupSubRelations = $groupSubRelations;
            $view->back_breadcrumbs = $back_breadcrumbs;
            $view->modules_name = $modules_name;
            $view->modules_submenu_name = $modules_sumbenu_name;
        });

        /*Global*/
        View::composer('admin.*', function ($view) {
            //Languages
            $lang_list = LANGS_ADMIN;
            $lang = LANG;
            $lang_id = LANG_ID;

            $user_group_id = '';

            if(Auth::user())
                $user_group_id = Auth::user()->admin_user_group_id;

            $module = ModulesId::where('alias',request()->segment(3))->first();

            $groupSubRelations = [];

            if($module && $user_group_id)
                $groupSubRelations = AdminUserActionPermision::where('admin_user_group_id', $user_group_id)
                    ->where('modules_id', $module->id)
                    ->first();

            $user_settings_url = url(LANG, 'back');
            $user_group_alias = AdminUserGroup::where('id', $user_group_id)->value('alias');
            if($user_group_alias)
                $user_settings_url = url(LANG, ['back', 'admin_user', $user_group_alias, 'edituser', Auth::user()->id]);

            $view->lang = $lang;
            $view->lang_list = $lang_list;
            $view->lang_id = $lang_id;
            $view->lang_to_edit = request()->segment(7);
            $view->groupSubRelations = $groupSubRelations;
            $view->user_settings_url = $user_settings_url;

        });
    }
}
