<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\PenyediaKomunitasAuthController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\PenyediaKomunitas\DashboardController as PenyediaDashboardController;
use App\Http\Controllers\User\CommunityController;
use App\Models\Community;
use App\Models\Schedule;

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

use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\PenyediaKomunitas\EventController as PenyediaEventController;

// ============================================================
// USER DASHBOARD & PROFILE ROUTES
// ============================================================
Route::prefix('user')->name('user.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/explore', function () {
        return view('user.explore', [
            'communities' => Community::with('schedules')->where('is_active', true)->paginate(6),
            'sportCategories' => Community::where('is_active', true)
                                ->pluck('sport_category')
                                ->map(fn($item) => trim($item))
                                ->unique(fn($item) => strtolower($item))
                                ->values(),
            'cities' => Community::distinct()->pluck('city'),
            'totalActiveSessions' => Schedule::count(),
        ]);
    })->name('explore');
    Route::post('/communities/{community}/follow', [DashboardController::class, 'followCommunity'])->name('communities.follow');
    Route::delete('/communities/{community}/unfollow', [DashboardController::class, 'unfollowCommunity'])->name('communities.unfollow');
    Route::post('/communities/{id}/join', [CommunityController::class, 'join'])->name('communities.join');
    Route::post('/schedules/{schedule}/join', [DashboardController::class, 'joinSchedule'])->name('schedules.join');
    Route::post('/events/{event}/join', [DashboardController::class, 'joinEvent'])->name('events.join');
    Route::post('/rewards/{reward}/redeem', [DashboardController::class, 'redeemReward'])->name('rewards.redeem');
    Route::post('/streak/check-in', [DashboardController::class, 'streakCheckIn'])->name('streak.check-in');
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
    Route::get('/events/create', [PenyediaEventController::class, 'create'])->name('events.create');
    Route::post('/events', [PenyediaEventController::class, 'store'])->name('events.store');
    Route::post('/logout', [PenyediaKomunitasAuthController::class, 'logout'])->name('logout');
});