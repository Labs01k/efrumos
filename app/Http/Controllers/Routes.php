<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

class Routes extends Controller
{
    public static function back()
    {
        require_once(base_path('/routes/back.php'));
    }

    public static function front()
    {
        require_once(base_path('/routes/front.php'));
    }

    public static function fallback()
    {
        Route::Fallback(function () {
            abort(404);
        })->middleware(['SetLocale', 'FrontGlobal', 'GetTranslate']);
    }
}
