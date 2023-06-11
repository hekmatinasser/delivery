<?php

use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\VehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\TransactionController;

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


    // Route::resource('products', ProductController::class);

    Route::prefix('wallet')->as('wallet::')->group(function () {
        Route::get('/show', [\App\Http\Controllers\API\WalletController::class, 'show'])->name('show');
        Route::post('/transaction', [\App\Http\Controllers\API\WalletController::class, 'storeTransaction'])->name('store-transaction');
        Route::post('/buy-coin', [\App\Http\Controllers\API\WalletController::class, 'buyCoin'])->name('buy-coin');
        Route::post('/increase/online', [\App\Http\Controllers\API\WalletController::class, 'increaseWalletOnline'])->name('increase-online');
    });

    Route::prefix('coin-wallet')->as('coin-wallet::')->group(function () {
        Route::get('/show', [\App\Http\Controllers\API\CoinWalletController::class, 'show'])->name('show');
        Route::post('/transaction', [\App\Http\Controllers\API\CoinWalletController::class, 'storeTransaction'])->name('store-transaction');
        Route::post('/travel-transaction', [\App\Http\Controllers\API\CoinWalletController::class, 'storeTravelTransaction'])->name('store-travel-transaction');
        Route::post('/buy-coin/online', [\App\Http\Controllers\API\CoinWalletController::class, 'buyCoinOnline'])->name('buy-coin-online');
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
