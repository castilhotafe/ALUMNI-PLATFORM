<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('login');

// Guest Routes (Only for logged-out users)
Route::middleware('guest')->group(function () {

    // Registration
    Route::post('register', [RegisterController::class, 'store'])
        ->name('register.store');

    // Login
    Route::post('login', [LoginController::class, 'store'])
        ->name('login.store');

    // Password Reset
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

// Logout requires an authenticated user
Route::post('logout', [LogoutController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Authenticated and Verified Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
});
