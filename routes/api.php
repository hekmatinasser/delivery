<?php

use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\VehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\TransactionController;

Route::controller(RegisterController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login')->name('login');

    Route::post('forgetPass', 'forgetPass');
    Route::post('logout', 'logout');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('user')->controller(UserController::class)->group(function () {
        Route::post('profile', 'profile');
        Route::post('update', 'update');
    });


    Route::prefix('vehicle')->controller(VehicleController::class)->group(function () {
        Route::post('types', 'types');
        Route::post('my', 'my');
        Route::post('store', 'store');
        Route::post('update', 'update');
        Route::post('delete', 'delete');
    });


    Route::prefix('vehicle')->controller(VehicleController::class)->group(function () {
        Route::post('areaTypes', 'areaTypes');
        Route::post('categories', 'categories');
        Route::post('my', 'my');
        Route::post('store', 'store');
        Route::post('update', 'update');
        Route::post('delete', 'delete');
    });


    Route::prefix('transaction')->controller(TransactionController::class)->group(function () {
        Route::post('store', 'store');
    });




    Route::resource('products', ProductController::class);
});
