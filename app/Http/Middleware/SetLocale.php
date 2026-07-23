<?php

namespace App\Http\Middleware;

use App\Models\Lang;
use App\Traits\LocaleTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

    use LocaleTrait;

    public function handle(Request $request, Closure $next)
    {
        $lang_data = Lang::select('id', 'lang', 'active', 'active_admin')
            ->orderBy('position', 'desc')
            ->get()
            ->keyBy('id');

        /*получаем языки для админки*/
        $langs = $lang_data->map(function ($item) {
            if ($item->active_admin == 1)
            return $item->lang;
        })->reject(function ($item) {
            return $item == null;
        });

        /*получаем языки для фронта*/
        $lang_list = $lang_data->map(function ($item) {
            if ($item->active == 1)
                return $item->lang;
        })->reject(function ($item) {
            return $item == null;
        });

        $langPrefix = ltrim(self::locale(), '/');

        if ($langPrefix && $lang_list->contains($langPrefix))
            App::setLocale($langPrefix);

        $lang_id = self::GetLangId();

        define('LANG', App::getLocale());//текущий язык
        define('LANG_ID', $lang_id);//id языка
        define('LANGS_ADMIN', $langs); //языки для админки
        define('LANGS_FRONT', $lang_list);//языки для фронта

        return $next($request);
    }
}
