<?php

namespace App\Http\Middleware;

use App\Models\SettingsId;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Config;

class GetSettings
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $query = SettingsId::with('ItemByLang')->get()->keyBy('alias');
        $data = [];
        if (!empty($query) && count($query) > 0) {
            foreach ($query as $alias => $collect) {
                $data[$alias] = $collect->lang_dependent == 1 ? ($collect->ItemByLang->body ?? '') : $collect->body_without_lang;
            }
        }
        Config::set('settings', $data);

        return $next($request);
    }
}
