<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\Auth\CustomAuthController;
use App\Http\Controllers\RoleManager;

Route::prefix('back')->group(function () {

    Route::controller(CustomAuthController::class)->group(function () {
        Route::get('auth/login', 'login')->name('login');
        Route::post('auth/login', 'checkLogin')->name('login.ajaxLogin');
        Route::get('auth/register', 'register');
        Route::post('auth/register', 'checkRegister');
        Route::get('auth/logout', 'logout');
    });

    /*File upload routes*/
    Route::controller(FileController::class)->group(function () {
        Route::any('/upload', 'upload');
        Route::post('/destroyFile', 'destroyOneSingleImg');
        Route::post('/destroyFiles', 'destroyOneMultipleImg');
        Route::post('/activateFile', 'activateOneImg');
        Route::any('/uploadGalleryPhoto', 'uploadGalleryPhoto');
        Route::any('/uploadGoodsSubjectGallery', 'uploadGoodsSubjectGallery');
    });

    Route::any('/{module?}/{submenu?}/{action?}/{id?}/{lang_id?}', [RoleManager::class, 'routeResponder'])->name('admin');



});
