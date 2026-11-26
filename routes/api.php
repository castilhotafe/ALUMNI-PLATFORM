<?php

use App\Http\Controllers\Mobile\AuthController;
use App\Http\Controllers\Mobile\FeedController;
use App\Http\Controllers\Mobile\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->name('mobile.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::middleware('verified')->group(function () {
            Route::get('/me', [ProfileController::class, 'show'])->name('me');
            Route::patch('/me', [ProfileController::class, 'update'])->name('me.update');
            Route::get('/feed', [FeedController::class, 'index'])->name('feed');
            Route::get('/feed/{id}', [FeedController::class, 'show'])
                ->whereNumber('id')->name('feed.show');
        });
    });
});
