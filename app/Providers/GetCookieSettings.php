<?php

namespace App\Providers;

use App\Models\SettingsId;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class GetCookieSettings extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        /*for cookies*/
        View::composer(['front.app'], function ($view) {
            $cookie_functional = Cookie::has('cookie-functional') ? Cookie::get('cookie-functional') : null;
            $cookie_advertisement = Cookie::has('cookie-advertisement') ? Cookie::get('cookie-advertisement') : null;
            $cookies_alias = [];
            $get_settings = [];
            if($cookie_functional == 1 || $cookie_functional == null)
            {
                $cookies_alias[] = 'before-body';
                $cookies_alias[] = 'after-head';
            }

            if($cookie_advertisement == 1 || $cookie_advertisement == null)
            {
                $cookies_alias[] = 'for-advertisement';
            }

            if(!empty($cookies_alias) && count($cookies_alias)>0)
            {
                $get_settings = SettingsId::select('alias','body_without_lang')
                                          ->whereIn('alias',$cookies_alias)
                                          ->get()->pluck('body_without_lang','alias');
            }


            $view->cookie_settings = $get_settings;

        });
        /*for cookies end*/
    }
}
