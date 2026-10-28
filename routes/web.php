<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\PenyediaKomunitasAuthController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\PenyediaKomunitas\DashboardController as PenyediaDashboardController;
use App\Http\Controllers\User\CommunityController;
use App\Http\Controllers\PenyediaKomunitas\EventController;
use App\Models\Community;
use App\Models\Schedule;

use App\Http\Controllers\Auth\GoogleAuthController;

// Landing Page
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Halaman pilih role
Route::get('/auth/selection', function () {
    return view('auth.selection');
})->name('auth.selection');

// ============================================================
// GOOGLE OAUTH ROUTES (Shared / Role-aware)
// ============================================================
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

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
    Route::get('/google-complete', [GoogleAuthController::class, 'showUserCompleteForm'])->name('google-complete');
    Route::post('/google-complete', [GoogleAuthController::class, 'storeUserComplete'])->name('google-complete.post');
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
    Route::get('/communities/{id}', [CommunityController::class, 'show'])->name('communities.show');
    Route::post('/communities/{community}/follow', [DashboardController::class, 'followCommunity'])->name('communities.follow');
    Route::delete('/communities/{community}/unfollow', [DashboardController::class, 'unfollowCommunity'])->name('communities.unfollow');
    Route::post('/communities/{id}/join', [CommunityController::class, 'join'])->name('communities.join');
    Route::post('/schedules/{schedule}/join', [DashboardController::class, 'joinSchedule'])->name('schedules.join');
    Route::post('/events/{event}/join', [DashboardController::class, 'joinEvent'])->name('events.join');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
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
    Route::get('/google-complete', [GoogleAuthController::class, 'showPenyediaCompleteForm'])->name('google-complete');
    Route::post('/google-complete', [GoogleAuthController::class, 'storePenyediaComplete'])->name('google-complete.post');
});

// ============================================================
// PENYEDIA KOMUNITAS ROUTES (protected, role: community_owner)
// ============================================================
Route::prefix('penyedia-komunitas')->name('penyedia-komunitas.')->middleware(['auth', 'community_owner'])->group(function () {
    Route::get('/dashboard', [PenyediaDashboardController::class, 'index'])->name('dashboard');
    Route::view('/komunitas', 'penyedia-komunitas.komunitas.index')->name('komunitas.index');
    Route::view('/jadwal', 'penyedia-komunitas.jadwal.index')->name('jadwal.index');
    Route::get('/events', [PenyediaEventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [PenyediaEventController::class, 'create'])->name('events.create');
    Route::post('/events', [PenyediaEventController::class, 'store'])->name('events.store');
    Route::get('events/{event}/manage', [EventController::class, 'manage'])->name('events.show');
    Route::get('events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::view('/statistik', 'penyedia-komunitas.statistik.index')->name('statistik.index');
    Route::view('/akun', 'penyedia-komunitas.akun.index')->name('akun.index');
    Route::post('/logout', [PenyediaKomunitasAuthController::class, 'logout'])->name('logout');
});

// ============================================================
// MEDIA STREAMING ROUTE (Cloudflare R2 with Fast Local Disk Cache)
// ============================================================
Route::get('/media/{path}', function (string $path) {
    $cachedFile = public_path('media/' . $path);

    // 1. Jika sudah ada di cache lokal public/media, kirim langsung (super cepat ~1ms)
    if (file_exists($cachedFile)) {
        return response()->file($cachedFile, [
            'Cache-Control' => 'public, max-age=604800, immutable',
        ]);
    }

    // 2. Jika ada di local storage/app/public, cache dan kirim
    $localPublic = storage_path('app/public/' . $path);
    if (file_exists($localPublic)) {
        @mkdir(dirname($cachedFile), 0755, true);
        @copy($localPublic, $cachedFile);
        return response()->file($localPublic, [
            'Cache-Control' => 'public, max-age=604800, immutable',
        ]);
    }

    // 3. Tarik dari Cloudflare R2, simpan ke public/media/ agar request berikutnya instan
    if (\Illuminate\Support\Facades\Storage::disk('s3')->exists($path)) {
        $file = \Illuminate\Support\Facades\Storage::disk('s3')->get($path);
        @mkdir(dirname($cachedFile), 0755, true);
        @file_put_contents($cachedFile, $file);

        $mime = \Illuminate\Support\Facades\Storage::disk('s3')->mimeType($path) ?? 'image/jpeg';
        return response($file, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=604800, immutable',
        ]);
    }

    abort(404);
})->where('path', '.*')->name('media.show');