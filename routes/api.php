<?php

use App\Http\Controllers\API\CoinWalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TripController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\StoreController;
use App\Http\Controllers\API\VehicleController;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ActiveTripController;
use App\Http\Controllers\API\BlockedController;
use App\Http\Controllers\API\ConstraintController;
use App\Http\Controllers\API\CoinSettingController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\NeighborhoodController;
use App\Http\Controllers\API\TripFeedBackController;
use App\Http\Controllers\API\InterNeighborhoodFareController;
use App\Http\Controllers\API\VehicleConstraintController;
use Illuminate\Support\Facades\Storage;

Route::controller(RegisterController::class)->prefix('v1')->group(function () {
    Route::post('register', 'register');
    Route::post('verify', 'verify');
    Route::post('verify/code', 'verifyCode');

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
            // Route::post('/transaction', [WalletController::class, 'storeTransaction'])->name('store-transaction');
            Route::post('/buy-coin', [WalletController::class, 'buyCoin'])->name('buy-coin');
            Route::post('/increase/online', [WalletController::class, 'increaseWalletOnline'])->name('increase-online');


            Route::get('/reasons', [WalletController::class, 'getReasons'])->name('reasons');
        });


        Route::prefix('coin-wallet')->as('coin-wallet::')->group(function () {
            Route::get('', [CoinWalletController::class, 'show'])->name('show');
            // Route::post('/transaction', [CoinWalletController::class, 'storeTransaction'])->name('store-transaction');
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
            Route::put('{vehicle_id}/update-access', 'updateVehicleAccess')->middleware(['ability:user-modify']);
            Route::delete('{vehicle_id}', 'deleteVehicle')->middleware(['ability:user-modify']);
        });

        Route::prefix('neighborhood')->controller(NeighborhoodController::class)->group(function () {
            Route::post('', 'store')->middleware(['ability:neighborhood-modify']);
            Route::post('{neighborhood_id}', 'update')->middleware(['ability:neighborhood-modify']);
            Route::delete('{neighborhood_id}', 'destroy')->middleware(['ability:neighborhood-modify']);
            //Inter Neighborhood Fare routes
            Route::prefix('fare')->controller(InterNeighborhoodFareController::class)->group(function () {
                Route::post('', [InterNeighborhoodFareController::class, 'calculatingInterNeighborhoodFare']);
                Route::put('/edit/{interNeighborhoodFare}', [InterNeighborhoodFareController::class, 'editInterNeighborhoodFare']);
            });
        });

        Route::prefix('coin-setting')->controller(CoinSettingController::class)->group(function () {
            Route::put('/', [CoinSettingController::class, 'saveCoinSetting']);
        });

        Route::prefix('trip')->controller(TripController::class)->middleware(['ability:trip-modify'])->group(function () {
            Route::post('/', [TripController::class, 'create']);
            Route::get('/', [TripController::class, 'getAll']);
            Route::get('/feedbacks', [TripFeedBackController::class, 'index']);
            Route::get('/{tripId}/changes', [TripController::class, 'tripChanges']);
            Route::get('/{code}/feedbacks', [TripFeedBackController::class, 'get']);
            Route::post('/{code}/feedbacks', [TripFeedBackController::class, 'create']);
            Route::put('/{code}/feedbacks/{id}', [TripFeedBackController::class, 'update']);
            Route::put('/{code}', [TripController::class, 'update']);
            Route::get('/{code}', [TripController::class, 'get']);
        });

        Route::get('roles', 'getRoles')->middleware(['ability:user-modify']);
        Route::post('update-password', 'updatePassword')->middleware(['ability:user-modify']);
        Route::post('roles', 'addNewRole')->middleware(['ability:user-modify']);
        Route::get('permissions', 'getPermissions')->middleware(['ability:user-modify']);
    });

    Route::prefix('v1/vehicle')->controller(VehicleController::class)->group(function () {
        Route::post('', 'store');
        Route::put('', 'update');
        Route::delete('', 'delete');

        Route::get('my', 'my');

        Route::get('types', 'types');


        Route::prefix('trip')->controller(TripController::class)->group(function () {
            Route::get('/', [TripController::class, 'index']);
            Route::get('/my', [TripController::class, 'vehicleTrips']);
            Route::get('/{code}', [TripController::class, 'details']);
            Route::post('/{code}/accept', [TripController::class, 'acceptTripByVehicle']);
            Route::post('/{code}/waiting', [TripController::class, 'waitingToReceiveThePackageByVehicle']);
            Route::post('/{code}/on-the-way', [TripController::class, 'onTheWayTripByVehicle']);
            Route::post('/{code}/deliver', [TripController::class, 'deliverTripByVehicle']);
            Route::post('/{code}/cancel', [TripController::class, 'cancelTripByVehicle']);
            Route::post('/{code}/feedback', [TripFeedBackController::class, 'createWithVehicle']);
            Route::put('/{code}/feedback/{id}', [TripFeedBackController::class, 'updateWithVehicle']);
            Route::get('/{code}/feedback', [TripFeedBackController::class, 'getWithVehicle']);
        });

        Route::prefix('block')->controller(BlockedController::class)->group(function () {
            Route::get('/', [BlockedController::class, 'getBlockedStoreWithVehicle']);
            Route::post('/', [BlockedController::class, 'addBlockedStoreWithVehicle']);
            Route::delete('/{id}', [BlockedController::class, 'deleteBlockedStoreWithVehicle']);
        });
    });

    Route::prefix('v1/store')->controller(StoreController::class)->group(function () {
        Route::post('', 'store');
        Route::put('', 'update');
        Route::delete('', 'delete');

        Route::get('my', 'my');

        Route::get('areaTypes', 'areaTypes');
        Route::get('categories', 'categories');

        Route::prefix('trip')->controller(TripController::class)->group(function () {
            Route::post('/', [TripController::class, 'createTripWithStore']);
            Route::put('/{code}', [TripController::class, 'updateTripWithStore']);
            Route::put('/{code}/cancel', [TripController::class, 'cancelTripWithStore']);
            Route::get('/my', [TripController::class, 'storeTrips']);
            Route::get('/{code}', [TripController::class, 'detailsForStore']);
            Route::post('/{code}/feedback', [TripFeedBackController::class, 'createWithStore']);
            Route::put('/{code}/feedback/{id}', [TripFeedBackController::class, 'updateWithStore']);
            Route::get('/{code}/feedback', [TripFeedBackController::class, 'getWithStore']);
        });


        Route::prefix('block')->controller(BlockedController::class)->group(function () {
            Route::get('/', [BlockedController::class, 'getBlockedVehicleWithStore']);
            Route::post('/', [BlockedController::class, 'addBlockedVehicleWithStore']);
            Route::delete('/{id}', [BlockedController::class, 'deleteBlockedVehicleWithStore']);
        });
    });

    Route::prefix('v1/neighborhood')->controller(NeighborhoodController::class)->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('/fee', 'fees')->name('fees');
        Route::get('/fee/histories', 'histories')->name('fees.histories')->middleware(['ability:user-modify']);
        ;
        Route::get('{neighborhood_id}', 'show')->name('show');

        //Inter Neighborhood Fare routes
        Route::prefix('/fare')->controller(InterNeighborhoodFareController::class)->group(function () {
            Route::get('calculator', [InterNeighborhoodFareController::class, 'InterNeighborhoodFare']);
        });
    });

    Route::prefix('v1/coin-setting')->controller(CoinSettingController::class)->group(function () {
        Route::get('/', [CoinSettingController::class, 'getCoinSetting']);
    });

    Route::prefix('transaction')->controller(TransactionController::class)->group(function () {
        Route::post('store', 'store');
    });

    Route::get('/images/download/{image_path}', function ($image_path) {
        $image = public_path() . '/' . str_replace('&&', '/', $image_path);
        return \Illuminate\Support\Facades\Response::download($image);
    });
});

// Route::get('/test', function () {
//     return redirect()->route('payment::mellat.pay', 23);
//     //   $melat = new App\Payment\Gateways\Mellat\Mellat();
//     //   $a =  $melat->verify(['ref_id' => 123]);
//     //   return $a->isOk()? 'yes': 'no';
// });

Route::get('/payment/mellat/{ref_id}/pay', function ($ref_id) {
    return "<form name='myform' action='" . config('payment.gateways.mellat.pay_url') . "' method='POST'><input type='hidden' id='RefId' name='RefId' value='{$ref_id}'></form><script type='text/javascript'>window.onload = formSubmit; function formSubmit() { document.forms[0].submit(); }</script>";
})->name('payment::mellat.pay');

Route::any('/wallet/increase/payment/verify', [WalletController::class, 'verifyIncreaseWalletPayment'])->name('wallet::increase.verify-payment');
Route::any('/coin-wallet/buy-coin/payment/verify', [CoinWalletController::class, 'verifyBuyCoinPayment'])->name('coin-wallet::buy-coin.verify-payment');


//Active Trips routes
// Route::get('/active/trips', [ActiveTripController::class, 'index']);
// Route::post('/activeTrip/updateOrCreate', [ActiveTripController::class, 'updateOrCreate']);

//Constraint Routes
// Route::get('/getActiveConstraints', [ConstraintController::class, 'getActiveConstraints'])->middleware('auth:sanctum');
// Route::post('/applyConstraint', [ConstraintController::class, 'applyConstraint'])->middleware('auth:sanctum');
// Route::post('/Constraint/changeStatus', [ConstraintController::class, 'changeStatus'])->middleware('auth:sanctum');


//vehicle constrait routes
// Route::post('/applyVehicleConstraint', [VehicleConstraintController::class, 'applyVehicleConstraint']);



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/files/images/national_photos/{image_path}', function ($image_path) {
        $image = "/images/national_photos/$image_path";
        if (Storage::disk('liara')->exists($image)) {
            $file = Storage::disk('liara')->get($image);
            $type = Storage::disk('liara')->mimeType($image);
            // $response = Response::make($file, 200);
            // $response->header("Content-Type", $type);
            $imagez = "data:$type;base64," . base64_encode(($file));

            return $imagez;
            // return Storage::disk('liara')->download($image);
        } else {
            return '-------';
        }
    })->middleware(['ability:user-modify']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/files/{id}/images/national_photos/{image_path}', function ($id, $image_path) {

        $user = Auth::user();
        $path = 'images/national_photos/' . $image_path;
        if (($user->nationalPhoto) != $path)
            return '';

        $image = "/images/national_photos/$image_path";
        if (Storage::disk('liara')->exists($image)) {
            $file = Storage::disk('liara')->get($image);
            $type = Storage::disk('liara')->mimeType($image);
            // $response = Response::make($file, 200);
            // $response->header("Content-Type", $type);
            $imagez = "data:$type;base64," . base64_encode(($file));

            return $imagez;
            // return Storage::disk('liara')->download($image);
        } else {
            return '-------';
        }
    });
});

Route::get('/files/images/public/{image_path}', function ($image_path) {
    $image = "/images/public/$image_path";
    if (Storage::disk('liara')->exists($image)) {
        return Storage::disk('liara')->get($image);
    } else {
        return '-------';
    }
});