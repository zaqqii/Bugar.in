<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\PenyediaKomunitasAuthController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\PenyediaKomunitas\DashboardController as PenyediaDashboardController;

// Landing Page
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Halaman pilih role
Route::get('/auth/selection', function () {
    return view('auth.selection');
})->name('auth.selection');

// ============================================================
// USER AUTH ROUTES
// ============================================================
Route::prefix('auth/user')->name('auth.user.')->group(function () {
    Route::get('/register', [UserAuthController::class, 'showForm'])->name('register');
    Route::get('/login', function () {
        return app(UserAuthController::class)->showForm('login');
    })->name('login');
    Route::post('/register', [UserAuthController::class, 'register'])->name('register.post');
    Route::post('/login', [UserAuthController::class, 'login'])->name('login.post');
});

// ============================================================
// USER DASHBOARD ROUTES (protected)
// ============================================================
Route::prefix('user')->name('user.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');
});

// ============================================================
// PENYEDIA KOMUNITAS AUTH ROUTES (publik)
// ============================================================
Route::prefix('auth/penyedia-komunitas')->name('auth.penyedia-komunitas.')->group(function () {
    Route::get('/register', [PenyediaKomunitasAuthController::class, 'showForm'])->name('register');
    Route::get('/login', function () {
        return app(PenyediaKomunitasAuthController::class)->showForm('login');
    })->name('login');
    Route::post('/register', [PenyediaKomunitasAuthController::class, 'register'])->name('register.post');
    Route::post('/login', [PenyediaKomunitasAuthController::class, 'login'])->name('login.post');
});

// ============================================================
// PENYEDIA KOMUNITAS ROUTES (protected, role: community_owner)
// ============================================================
Route::prefix('penyedia-komunitas')->name('penyedia-komunitas.')->middleware(['auth', 'community_owner'])->group(function () {
    Route::get('/dashboard', [PenyediaDashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [PenyediaKomunitasAuthController::class, 'logout'])->name('logout');
});