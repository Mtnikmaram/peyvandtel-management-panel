<?php

use App\Http\Controllers\Auth\Peyvandtel\LoginController;
use App\Http\Controllers\BackEnd\Peyvandtel\UsersController;
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
    Route::middleware("auth:sanctum", "isPeyvandtelAdmin")->group(function () {
        //users
        Route::prefix('users')->controller(UsersController::class)->name('users.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{user}', 'show')->name('show');
            Route::post('/', 'store')->name('store');
            Route::patch('/{user}', 'update')->name('update');
        });
    });
});
