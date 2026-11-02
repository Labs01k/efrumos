<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
| API для приложения лояльности (eFrumos-backend): /api/app/v1/*.
| Контракт — efrumos-docs/app-api-contract.md. Сервис-к-сервису (Bearer
| APP_API_TOKEN), клиента приложения передаёт заголовок X-App-Client-Id,
| язык — X-App-Lang (ro|ru|en).
*/
Route::prefix('app/v1')
    ->middleware(['appApiAuth', 'appApiLocale', 'throttle:120,1'])
    ->group(function () {
        Route::get('/catalog/categories', [\App\Http\Controllers\AppApi\CatalogController::class, 'categories']);
        Route::get('/catalog/products', [\App\Http\Controllers\AppApi\CatalogController::class, 'products']);
        Route::get('/catalog/products/{product}', [\App\Http\Controllers\AppApi\CatalogController::class, 'product']);

        Route::post('/orders/quote', [\App\Http\Controllers\AppApi\OrderController::class, 'quote']);
        Route::post('/orders', [\App\Http\Controllers\AppApi\OrderController::class, 'store']);
        Route::get('/orders', [\App\Http\Controllers\AppApi\OrderController::class, 'index']);
        Route::get('/orders/{order}', [\App\Http\Controllers\AppApi\OrderController::class, 'show'])->whereNumber('order');

        // EFA-35 / E1-BE-04 — погашение одноразового токена связки (EFA-43).
        Route::post('/account-link/redeem', [\App\Http\Controllers\AppApi\AccountLinkController::class, 'redeem']);

        // EFA-77 / E4-BES-01 — избранное приложения, тот же список, что на сайте.
        Route::get('/favorites', [\App\Http\Controllers\AppApi\FavoriteController::class, 'index']);
        Route::post('/favorites', [\App\Http\Controllers\AppApi\FavoriteController::class, 'store']);
        Route::delete('/favorites/{productId}', [\App\Http\Controllers\AppApi\FavoriteController::class, 'destroy'])->whereNumber('productId');
    });
