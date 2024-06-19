<?php

use App\Http\Controllers\Auth\Peyvandtel\LoginController;
use Illuminate\Support\Facades\Route;

//============================ PEYVANDTEL ============================
Route::prefix('peyvandtel')->name('peyvandtel.')->group(function () {

    //authentication
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::prefix('login')->controller(LoginController::class)->name('login.')->group(function () {
            Route::post('/', 'login')->name('login');
        });
    });

});
