<?php

use App\Http\Controllers\Routes;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\SocialAuthController;
use App\Http\Controllers\Admin\SitemapsController;
use App\Http\Controllers\Exchange\ImportFrom1C;
use App\Http\Controllers\Front\GFGoodsXmlController;
use App\Http\Controllers\Front\ParseController;
use App\Services\Localization\LocalizationService;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
| To compress html can use middleware compress.
|
*/

Route::controller(SocialAuthController::class)->group(function () {
    Route::get('login/facebook', 'redirectToFacebook')->name('login-facebook');
    Route::any('login/facebook/callback', 'handleFacebookCallback');
    Route::get('login/google', 'redirectToGoogle')->name('login-google');
    Route::any('login/google/callback', 'handleGoogleCallback');
});

//ImportFrom1C
Route::controller(ImportFrom1C::class)->group(function () {
    Route::any('/fullExchange', 'fullExchange');
    Route::any('/onlyChangedExchange', 'onlyChangedExchange');
    Route::any('/fullExchange/download', 'fullExchangeDownload');
    Route::any('/fullExchange/update', 'fullExchangeUpdate');
});

//Update goods guid
Route::get('/updategoodsguid', [ParseController::class, 'updateGoodsGuid']);

Route::prefix(LocalizationService::locale())->middleware(['SetLocale', 'GetTranslate', 'GetSettings', 'denyAccess'])->group(function () {

    //Generate sitemap
    Route::get('/generatesitemap', [SitemapsController::class, 'index']);

    //Generate feeds
    Route::any('/generategoodsxml', [GFGoodsXmlController::class, 'generateGFGoodsXML'])->name('generate-gf-feeds');

    Routes::back();

    Routes::front();
});

Routes::fallback();

