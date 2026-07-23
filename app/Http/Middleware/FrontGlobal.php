<?php

namespace App\Http\Middleware;

use App;
use App\Models\SocialMedia;
use App\Models\GoodsSubjectId;
use Closure;
use Illuminate\Http\Request;
use View;
use Symfony\Component\HttpFoundation\Response;

class FrontGlobal
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $social_media = SocialMedia::where('active', 1)
            ->orderBy('position', 'asc')
            ->get();

        $header_goods_subjects = GoodsSubjectId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'catalog')
            ->has('itemByLang')
            ->with('itemByLang')
            ->with('children.children')
            ->first();

        View::share([
            'global_user' => app('global_user'),
            'social_media' => $social_media,
            'header_goods_subjects' => $header_goods_subjects,
        ]);

        return $next($request);
    }
}
