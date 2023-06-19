<?php

use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\CoinWalletController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\VehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\StoreController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\WalletController;

Route::controller(RegisterController::class)->prefix('v1')->group(function () {
    Route::post('register', 'register');
    Route::post('verify', 'verify');

    Route::post('login', 'login')->name('login');
    Route::post('login/code', 'loginWithCode')->name('login.code');
    Route::post('login/password', 'loginWithPassword')->name('login.password');

    Route::post('forgot-password', 'forgotPassword');
    Route::post('reset-password', 'restPassword');

    Route::post('logout', 'logout');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('v1/user')->controller(UserController::class)->group(function () {
        Route::get('', 'profile');
        Route::post('', 'update');


        Route::prefix('wallet')->as('wallet::')->group(function () {
            Route::get('', [WalletController::class, 'show'])->name('show');
            Route::post('/transaction', [WalletController::class, 'storeTransaction'])->name('store-transaction');
            Route::post('/buy-coin', [WalletController::class, 'buyCoin'])->name('buy-coin');
            Route::post('/increase/online', [WalletController::class, 'increaseWalletOnline'])->name('increase-online');


            Route::get('/reasons', [WalletController::class, 'getReasons'])->name('reasons');
        });


        Route::prefix('coin-wallet')->as('coin-wallet::')->group(function () {
            Route::get('', [CoinWalletController::class, 'show'])->name('show');
            Route::post('/transaction', [CoinWalletController::class, 'storeTransaction'])->name('store-transaction');
            Route::post('/travel-transaction', [CoinWalletController::class, 'storeTravelTransaction'])->name('store-travel-transaction');
            Route::post('/buy-coin/online', [CoinWalletController::class, 'buyCoinOnline'])->name('buy-coin-online');


            Route::get('/reasons', [CoinWalletController::class, 'getReasons'])->name('reasons');
        });
    });

    Route::prefix('v1/admin')->controller(AdminController::class)->group(function () {
        Route::prefix('employee')->group(function () {
            Route::post('', 'createEmployee')->middleware(['ability:user-modify']);
            Route::get('', 'getEmployees')->middleware(['ability:user-modify']);
            Route::get('{employeeId}', 'getEmployee')->middleware(['ability:user-modify']);
            Route::put('{employee_id}', 'updateEmployee')->middleware(['ability:user-modify']);
            Route::delete('{employee_id}', 'deleteEmployee')->middleware(['ability:user-modify']);
        });
        Route::prefix('store')->group(function () {
            Route::post('', 'createStore')->middleware(['ability:user-modify']);
            Route::get('', 'getStores')->middleware(['ability:user-modify']);
            Route::get('{storeId}', 'getStore')->middleware(['ability:user-modify']);
            Route::post('{storeId}/update', 'updateStore')->middleware(['ability:user-modify']);
            Route::delete('{storeId}', 'deleteStore')->middleware(['ability:user-modify']);
        });
        Route::prefix('vehicle')->group(function () {
            Route::post('', 'createVehicle')->middleware(['ability:user-modify']);
            Route::get('', 'getVehicles')->middleware(['ability:user-modify']);
            Route::get('{vehicle_id}', 'getVehicle')->middleware(['ability:user-modify']);
            Route::post('{vehicle_id}/update', 'updateVehicle')->middleware(['ability:user-modify']);
            Route::delete('{vehicle_id}', 'deleteVehicle')->middleware(['ability:user-modify']);
        });
        Route::get('roles', 'getRoles')->middleware(['ability:user-modify']);
    });


    Route::prefix('v1/vehicle')->controller(VehicleController::class)->group(function () {
        Route::post('', 'store');
        Route::put('', 'update');
        Route::delete('', 'delete');

        Route::get('my', 'my');

        Route::get('types', 'types');
    });


    Route::prefix('v1/store')->controller(StoreController::class)->group(function () {
        Route::post('', 'store');
        Route::put('', 'update');
        Route::delete('', 'delete');

        Route::get('my', 'my');

        Route::get('areaTypes', 'areaTypes');
        Route::get('categories', 'categories');
    });


    Route::prefix('transaction')->controller(TransactionController::class)->group(function () {
        Route::post('store', 'store');
    });

    Route::get('/images/download/{image_path}', function ($image_path) {
        $image = public_path() . '/' . str_replace('&&', '/', $image_path);

        return \Illuminate\Support\Facades\Response::download($image);
    });
});

Route::get('/test', function () {
    return redirect()->route('payment::mellat.pay', 23);
    //   $melat = new App\Payment\Gateways\Mellat\Mellat();
    //   $a =  $melat->verify(['ref_id' => 123]);
    //   return $a->isOk()? 'yes': 'no';
});

Route::get('/payment/mellat/{ref_id}/pay', function ($ref_id) {
    return "<form name='myform' action='" . config('payment.gateways.mellat.pay_url') . "' method='POST'><input type='hidden' id='RefId' name='RefId' value='{$ref_id}'></form><script type='text/javascript'>window.onload = formSubmit; function formSubmit() { document.forms[0].submit(); }</script>";
})->name('payment::mellat.pay');

Route::any('/wallet/increase/payment/verify', [\App\Http\Controllers\API\WalletController::class, 'verifyIncreaseWalletPayment'])->name('wallet::increase.verify-payment');
Route::any('/coin-wallet/buy-coin/payment/verify', [\App\Http\Controllers\API\CoinWalletController::class, 'verifyBuyCoinPayment'])->name('coin-wallet::buy-coin.verify-payment');
