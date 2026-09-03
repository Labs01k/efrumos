<?php

use App\Http\Controllers\Routes;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\SocialAuthController;
use App\Http\Controllers\Admin\SitemapsController;
use App\Http\Controllers\Exchange\ImportFrom1C;
use App\Http\Controllers\Front\GFGoodsXmlController;
use App\Http\Controllers\Front\ParseController;
use App\Http\Controllers\Payment\VictoriaBankController;
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

// Epic 1 / 1.1 — VictoriaBank e-Gateway: initiate starts TRTYPE=0, backref is
// the single BACKREF the customer's browser lands on either way (result is
// in its query string, informational only), callback is the authoritative
// server-to-server webhook. Unprefixed by locale, like the 1C exchange
// routes above — a bank redirect/webhook is not a localized page.
// URL deliberately says "bank", not the provider name — keep which acquirer
// we use out of public URLs. VictoriaBank* is still the real name internally.
Route::controller(VictoriaBankController::class)->prefix('payments/bank')->name('payments.bank.')->group(function () {
    // GET, not POST — the checkout AJAX response hands the browser this URL
    // and does a plain navigation (window.location), it doesn't submit a form here.
    Route::get('/initiate/{order}', 'initiate')->name('initiate');
    Route::get('/backref/{order}', 'backref')->name('backref');
    Route::post('/callback', 'callback')->name('callback');
});

Route::prefix(LocalizationService::locale())->middleware(['SetLocale', 'GetTranslate', 'GetSettings', 'denyAccess'])->group(function () {

    //Generate sitemap
    Route::get('/generatesitemap', [SitemapsController::class, 'index']);

    //Generate feeds
    Route::any('/generategoodsxml', [GFGoodsXmlController::class, 'generateGFGoodsXML'])->name('generate-gf-feeds');

    Routes::back();

    Routes::front();
});

Routes::fallback();

