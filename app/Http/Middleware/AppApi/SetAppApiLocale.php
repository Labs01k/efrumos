<?php

namespace App\Http\Middleware\AppApi;

use App\Http\Middleware\SetLocale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Язык ответа API: заголовок `X-App-Lang` (или ?lang=) — ro | ru | en.
 * Без него или с неизвестным значением — язык сайта по умолчанию.
 *
 * Сайтовый SetLocale определяет язык по первому сегменту URL (/ru/...), у
 * /api/app/v1/* его нет — поэтому язык выставляем здесь и отдаём остальную
 * работу (константы LANG, LANG_ID, LANGS_*, от которых зависят хелперы и
 * модели) самому SetLocale.
 */
class SetAppApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $lang = (string) ($request->header('X-App-Lang') ?: $request->query('lang', ''));

        if (in_array($lang, config('app.locales'), true)) {
            App::setLocale($lang);
        }

        return app(SetLocale::class)->handle($request, $next);
    }
}
