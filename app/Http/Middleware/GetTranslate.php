<?php

namespace App\Http\Middleware;

use App\Models\Labels;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class GetTranslate
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
        $all_translate = Labels::select('name', 'labels_id')->where('lang_id', LANG_ID)
            ->get()
            ->pluck('name', 'labels_id')
            ->toArray();

        Config::set('translator', $all_translate);
        return $next($request);
    }
}
