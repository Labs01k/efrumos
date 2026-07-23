<?php

use App\Http\Controllers\Front\DefaultController;
use App\Http\Controllers\Front\CatalogController;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\CabinetController;
use App\Http\Controllers\Front\UserAuthController;
use App\Http\Controllers\Front\WishController;
use App\Http\Controllers\Front\OrderController;
use App\Http\Controllers\Front\NewsController;
use App\Http\Controllers\Front\ShopsController;
use App\Http\Controllers\Front\BlogController;
use App\Http\Controllers\Front\PromoController;
use App\Http\Controllers\Front\ParseController;
use App\Http\Controllers\Front\BrandController;
use App\Http\Controllers\Front\FastOrderController;
use App\Http\Controllers\Front\GoodsReviewsController;
use App\Services\Localization\LocalizationService;

Route::middleware('FrontGlobal')->group(function () {

    Route::prefix(LocalizationService::langRoutePrefix())->group(function () {

        /* Catalog */
        Route::controller(CatalogController::class)->group(function () {

            Route::get('/catalog/{product?}/{item?}', 'index')->name('catalog-product');
            Route::get('/category/{item?}', 'index')->name('category');
            Route::post('/catalog/filter', 'ajaxFilterResults')->name('catalog-filter');
            Route::get('/category-page/{link}', 'categorySeoPage')->name('category-seo-page');

            Route::get('/search', 'goodsSearch')->name('search');
            Route::post('/ajaxGoodSearch', 'ajaxGoodsSearch');
            Route::post('/ajaxSortPage', 'ajaxSortPage');
            Route::post('/ajaxQuickViewGoods', 'ajaxQuickViewGoods');

        });

        /* Goods reviews */
        Route::controller(GoodsReviewsController::class)->group(function () {
            Route::post('/ajaxSaveGoodsItemReview', 'ajaxSaveGoodsItemReview')->name('ajax-new-review');
        });

        /* Brand */
        Route::controller(BrandController::class)->group(function () {
            Route::get('/brands/{item?}', 'index')->name('brands');
        });

        /* Cart */
        Route::controller(CartController::class)->group(function () {
            Route::get('/cart', 'index')->name('cart');
            Route::get('/checkout', 'index')->name('checkout');
            Route::post('/ajaxAddToCart', 'ajaxAddToCart');
            Route::post('/ajaxDestroyItemCart', 'ajaxDestroyItemCart');
            Route::post('/ajaxDestroyAllItemsCart', 'ajaxDestroyAllItemsCart');
            //Route::post('/ajaxSelectUserAddress', 'ajaxSelectUserAddress');
            Route::post('/ajaxDiffSumItemCart', 'ajaxDiffSumItemCart');
            Route::post('/ajaxChangeDeliveryMethod', 'ajaxChangeDeliveryMethod');
            Route::post('/ajaxSelectDistrict', 'ajaxSelectDistrict');

            Route::post('ajaxCheckPromoCode', 'ajaxCheckPromoCode')->name('check-promocod');
            Route::post('ajaxSelectPromoGift', 'ajaxSelectPromoGift');
        });

        /* Wish */
        Route::controller(WishController::class)->middleware('checkAuthUser')->group(function () {
            Route::post('/ajaxAddToWish', 'ajaxAddToWish');
            Route::post('/ajaxDestroyWish', 'ajaxDestroyWish');
            Route::post('/ajaxAddAllWishToBasket', 'ajaxAddAllWishToBasket');
        });

        /* Order */
        Route::controller(OrderController::class)->group(function () {
            Route::post('/ajaxNewOrder', 'ajaxNewOrder')->name('ajax-new-order');
            Route::get('/checkout-success', 'checkoutSuccess')->name('checkout-success');
        });

        /* Fast Order */
        Route::controller(FastOrderController::class)->group(function () {
            Route::post('/ajaxNewFastOrder', 'ajaxNewFastOrder')->name('ajax-new-fast-order');
            //Route::get('/checkout-success', 'checkoutSuccess')->name('checkout-success');
        });

        /* User Auth */
        Route::controller(UserAuthController::class)->group(function () {
            //Register
            Route::get('/register', 'registerIndex')->name('register');
            Route::post('/ajaxRegisterUser', 'ajaxRegisterUser')->name('ajax-register-user');
            Route::get('/register-success', 'registerSuccess')->name('register-success');
            Route::get('/user-confirmation/{confirmation_hash}', 'userConfirmation')->name('user-confirmation');
            Route::get('/resend-confirmation/{confirmation_hash}', 'resendUserConfirmation')->name('resend-user-confirmation');
            //Login
            Route::post('/ajaxLoginUser', 'ajaxLoginUser')->name('ajax-login-user');
            //Recovery
            Route::post('/ajaxRestorePassword', 'ajaxRestorePassword')->name('ajax-restore-password');
            Route::post('ajaxNewPassword', 'ajaxNewPassword')->name('ajax-new-password');
            //Route::get('/login', 'loginIndex')->name('login');
            //Logout
            Route::get('/logout', 'userLogout')->name('logout');

        });

        /* User cabinet */
        Route::controller(CabinetController::class)->middleware('checkAuthUser')->group(function () {
            Route::group(['prefix' => 'cabinet'], function () {
                Route::get('/', 'cabinetProfile')->name('cabinet-profile');
                Route::get('/orders', 'cabinetOrders')->name('cabinet-orders');
                Route::get('/wish', 'cabinetWish')->name('cabinet-wish');
                Route::get('/password', 'cabinetPassword')->name('cabinet-password');

            });

            //Route::post('/ajaxUpdateOrCreateAddress', 'ajaxUpdateOrCreateAddress')->name('ajax-action-address');
            //Route::post('/ajaxSelectDefaultAddress', 'ajaxSelectDefaultAddress');
            //Route::post('/ajaxDestroyAddress', 'ajaxDestroyAddress');
            Route::post('/ajaxUpdateProfile', 'ajaxUpdateProfile')->name('ajax-update-profile');
            Route::post('/ajaxShowOrderDetails', 'ajaxShowOrderDetails');
            Route::post('/ajaxRepeatOrder', 'ajaxRepeatOrder');
            Route::post('/ajaxUpdatePassword', 'ajaxUpdatePassword')->name('ajax-update-password');
        });

        /* News */
        Route::controller(NewsController::class)->group(function () {
            Route::get('/news/{item?}', 'index')->name('news');
        });

        /* Blog */
        Route::controller(BlogController::class)->group(function () {
            Route::get('/blog/{item?}', 'index')->name('blog');
        });

        /* Promo */
        Route::controller(PromoController::class)->group(function () {
            Route::get('/promo/{item?}', 'index')->name('promo');
            Route::post('/ga4PromoClick', 'ga4PromoClick')->name('ga4-promo-click');
        });

        /* Shops */
        Route::get('/shops', [ShopsController::class, 'index'])->name('shops');

        /* Parse */
        Route::get('/parsesubjectsname', [ParseController::class, 'parseGoodsSubjectName']);

        /* Main page*/
        Route::controller(DefaultController::class)->group(function () {
            Route::get('/{parent}/{children?}', 'menuElements')->name('menu');
            Route::post('/simpleFeedbackAjax', 'simpleFeedbackAjax')->name('ajax-feedback');
            Route::post('/ajaxNewSubscribers', 'ajaxNewSubscribers')->name('ajax-subscribers');
            //Route::post('/cookie-control','cookieClient')->name('cookie-control');
        });
    });

    Route::get('/', [DefaultController::class, 'index'])->name('/');

//Route::get('/', [DefaultController::class, 'closed'])->name('/');
});
