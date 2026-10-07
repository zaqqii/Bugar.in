<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\User\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Halaman pilih role (user / penyedia komunitas)
Route::get('/auth/selection', function () {
    return view('auth.selection');
})->name('auth.selection');

// ============================================================
// USER AUTH ROUTES
// ============================================================
Route::prefix('auth/user')->name('auth.user.')->group(function () {

    // Halaman form daftar / masuk
    Route::get('/register', [UserAuthController::class, 'showForm'])
        ->name('register');

    Route::get('/login', function () {
        return app(UserAuthController::class)->showForm('login');
    })->name('login');

    // Proses daftar
    Route::post('/register', [UserAuthController::class, 'register'])
        ->name('register.post');

    // Proses masuk
    Route::post('/login', [UserAuthController::class, 'login'])
        ->name('login.post');
});

// ============================================================
// USER DASHBOARD ROUTES (protected — harus login)
// ============================================================
Route::prefix('user')->name('user.')->middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::post('/logout', [UserAuthController::class, 'logout'])
        ->name('logout');
});
