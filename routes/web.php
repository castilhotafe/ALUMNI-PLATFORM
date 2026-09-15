<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Guest Route (logged-out users)
// Need Register and Login Controllers to handle authentication logic
Route::middleware('guest')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::view('/register', 'auth.register')->name('register');
});

// Authenticated and Verified Routes (Only for logged-in users with verified email)
Route::middleware(['', ''])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
});
