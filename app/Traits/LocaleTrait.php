<?php

namespace App\Traits;

use Illuminate\Support\Facades\App;

trait LocaleTrait
{
    public function langRoutePrefix(): string
    {
        return $this->locale() == '' ? App::getLocale() : '';
    }

    public function locale(): string
    {
        $locale = request()->segment(1, '');

        if ($locale && in_array($locale, config('app.locales'))) {
            return $locale;
        }
        return '';
    }

    private function GetLangId(): bool|int|string
    {
        return array_search(App::getLocale(), config('app.locales'));
    }

    private function SetLangPrefix(): array
    {
        $prefix = [];
        foreach (config('app.locales') as $lang) {
            $lang == config('app.fallback_locale') ? $prefix[$lang] = '/' : $prefix[$lang] = '/' . $lang;
        }

        return $prefix;
    }
}
