<?php

use App\Http\Controllers\Auth\Peyvandtel\LoginController;
use App\Http\Controllers\Auth\User\LoginController as UserLoginController;
use App\Http\Controllers\BackEnd\Peyvandtel\ServicePricesController;
use App\Http\Controllers\BackEnd\Peyvandtel\ServicesController;
use App\Http\Controllers\BackEnd\Peyvandtel\UsersController;
use App\Http\Controllers\BackEnd\User\CreditHistoryController as UserCreditHistoryController;
use App\Http\Controllers\BackEnd\User\ServicesController as UserServicesController;
use Illuminate\Support\Facades\Route;

//============================ PEYVANDTEL ============================
Route::prefix('peyvandtel')->name('peyvandtel.')->group(function () {

    //authentication
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::prefix('login')->controller(LoginController::class)->name('login.')->group(function () {
            Route::post('/', 'login')->name('login');
        });
    });

    //routes that needs token
    Route::middleware(["auth:sanctum", "isPeyvandtelAdmin"])->group(function () {
        //users
        Route::prefix('users')->controller(UsersController::class)->name('users.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{user}', 'show')->name('show');
            Route::post('/', 'store')->name('store');
            Route::patch('/{user}', 'update')->name('update');
        });

        //services
        Route::prefix('services')->controller(ServicesController::class)->name('services.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/{service}/credential/token', 'setTokenCredential')->name('setTokenCredential');
            Route::put('/{service}/credential/usernamePassword', 'setUsernamePasswordCredential')->name('setUsernamePasswordCredential');
            Route::put('/{service}/toggleActive', 'toggleActiveState')->name('toggleActive');
        });

        //service prices
        Route::prefix('servicePrices')->controller(ServicePricesController::class)->name('servicePrices.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{servicePrice}', 'update')->name('update');
            Route::delete('/{servicePrice}', 'destroy')->name('destroy');
        });
    });
});


//============================ USER ============================
Route::prefix('user')->name('user.')->group(function () {
    //authentication
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::prefix('login')->controller(UserLoginController::class)->name('login.')->group(function () {
            Route::post('/', 'login')->name('login');
        });
    });

    //routes that needs token
    Route::middleware(["auth:sanctum", "isUser"])->group(function () {
        //services
        Route::prefix('services')->controller(UserServicesController::class)->name('services.')->group(function () {
            Route::get('/{service}', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{service}/{id}', 'show')->name('show');
        });

        //credit history
        Route::prefix('creditHistory')->controller(UserCreditHistoryController::class)->name('services.')->group(function () {
            Route::get('/', 'index')->name('index');
        });
    });
});
