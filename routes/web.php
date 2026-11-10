<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Auth\PenyediaKomunitasAuthController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\SocialAuthController;
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

Route::get('/auth/{audience}/social/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->where(['audience' => 'user|penyedia-komunitas', 'provider' => 'google|apple'])
    ->name('auth.social.redirect');
Route::get('/auth/social/google/callback', [SocialAuthController::class, 'googleCallback'])
    ->name('auth.social.google.callback');
Route::post('/auth/social/apple/callback', [SocialAuthController::class, 'appleCallback'])
    ->name('auth.social.apple.callback');

// ============================================================
// ADMIN AUTH & CONSOLE ROUTES
// ============================================================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('/communities/{id}/verify', [AdminDashboardController::class, 'approveVerification'])->name('communities.verify');
        Route::post('/communities/{id}/reject', [AdminDashboardController::class, 'rejectVerification'])->name('communities.reject');
        Route::post('/communities/{id}/toggle-status', [AdminDashboardController::class, 'toggleCommunityStatus'])->name('communities.toggle-status');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    });
});

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
use App\Http\Controllers\PenyediaKomunitas\AkunController as PenyediaAkunController;
use App\Http\Controllers\PenyediaKomunitas\JadwalController as PenyediaJadwalController;

// ============================================================
// USER DASHBOARD & PROFILE ROUTES
// ============================================================
Route::prefix('user')->name('user.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/onboarding/sports', [ProfileController::class, 'showOnboardingSports'])->name('onboarding.sports');
    Route::post('/onboarding/sports', [ProfileController::class, 'saveOnboardingSports'])->name('onboarding.sports.store');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/explore', function () {
        $today = \Carbon\Carbon::now(config('app.timezone', 'Asia/Jakarta'))->toDateString();
        $joinedScheduleIds = auth()->check()
            ? \App\Models\Participant::where('user_id', auth()->id())
                ->where('participatable_type', \App\Models\Schedule::class)
                ->whereIn('status', ['registered', 'attended'])
                ->pluck('participatable_id')
                ->all()
            : [];
        return view('user.explore', [
            'communities' => Community::with(['schedules' => fn($q) => $q->where('is_active', true)->whereNull('cancelled_at')->whereNotIn('status', ['cancelled', 'completed'])->whereDate('date', '>=', $today)->orderBy('date')->orderBy('start_time')])->where('is_active', true)->paginate(6),
            'sportCategories' => Community::where('is_active', true)
                                ->pluck('sport_category')
                                ->map(fn($item) => trim($item))
                                ->unique(fn($item) => strtolower($item))
                                ->values(),
            'cities' => Community::distinct()->pluck('city'),
            'totalActiveSessions' => Schedule::where('is_active', true)->whereNull('cancelled_at')->whereNotIn('status', ['cancelled', 'completed'])->whereDate('date', '>=', $today)->count(),
            'joinedScheduleIds' => $joinedScheduleIds,
        ]);
    })->name('explore');
    Route::get('/communities/{id}', [CommunityController::class, 'show'])->name('communities.show');
    Route::post('/communities/{community}/follow', [DashboardController::class, 'followCommunity'])->name('communities.follow');
    Route::delete('/communities/{community}/unfollow', [DashboardController::class, 'unfollowCommunity'])->name('communities.unfollow');
    Route::post('/communities/{id}/join', [CommunityController::class, 'join'])->name('communities.join');
    Route::get('/schedules/{schedule}/availability', [DashboardController::class, 'scheduleAvailability'])->name('schedules.availability');
    Route::post('/schedules/{schedule}/join', [DashboardController::class, 'joinSchedule'])->name('schedules.join');
    Route::post('/events/{event}/join', [DashboardController::class, 'joinEvent'])->name('events.join');
    Route::post('/participation/cancel', [DashboardController::class, 'cancelParticipation'])->name('participation.cancel');
    Route::post('/schedules/{schedule}/checkin-token', [DashboardController::class, 'getCheckinToken'])->middleware('throttle:30,1')->name('schedules.checkin-token');
    Route::get('/schedules/{schedule}/checkin-status', [DashboardController::class, 'getCheckinStatus'])->middleware('throttle:60,1')->name('schedules.checkin-status');
    Route::post('/rewards/{reward}/redeem', [DashboardController::class, 'redeemReward'])->name('rewards.redeem');
    Route::post('/announcements/{announcement}/read', [DashboardController::class, 'readAnnouncement'])->name('announcements.read');
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
    Route::get('/jadwal', [PenyediaJadwalController::class, 'index'])->name('jadwal.index');
    Route::get('/jadwal/create', [PenyediaJadwalController::class, 'create'])->name('jadwal.create');
    Route::post('/jadwal', [PenyediaJadwalController::class, 'store'])->name('jadwal.store');
    Route::get('/jadwal/blokir', [PenyediaJadwalController::class, 'blocksIndex'])->name('jadwal.blocks');
    Route::delete('/jadwal/blokir/{block}', [PenyediaJadwalController::class, 'unblock'])->name('jadwal.unblock');
    Route::get('/jadwal/{schedule}', [PenyediaJadwalController::class, 'show'])->name('jadwal.show');
    Route::get('/jadwal/{schedule}/checkin', [PenyediaJadwalController::class, 'checkinView'])->name('jadwal.checkin');
    Route::get('/jadwal/{schedule}/checkin/participants', [PenyediaJadwalController::class, 'checkinParticipantsJson'])->name('jadwal.checkin.participants');
    Route::post('/jadwal/{schedule}/checkin/scan', [PenyediaJadwalController::class, 'checkinScan'])->middleware('throttle:60,1')->name('jadwal.checkin.scan');
    Route::post('/jadwal/{schedule}/checkin/manual/{participant}', [PenyediaJadwalController::class, 'checkinManual'])->middleware('throttle:60,1')->name('jadwal.checkin.manual');
    Route::get('/jadwal/{schedule}/edit', [PenyediaJadwalController::class, 'edit'])->name('jadwal.edit');
    Route::put('/jadwal/{schedule}', [PenyediaJadwalController::class, 'update'])->name('jadwal.update');
    Route::delete('/jadwal/{schedule}', [PenyediaJadwalController::class, 'destroy'])->name('jadwal.destroy');
    Route::patch('/jadwal/{schedule}/toggle-active', [PenyediaJadwalController::class, 'toggleActive'])->name('jadwal.toggle-active');
    Route::patch('/jadwal/{schedule}/toggle-streak', [PenyediaJadwalController::class, 'toggleStreak'])->name('jadwal.toggle-streak');
    Route::patch('/jadwal/{schedule}/cancel', [PenyediaJadwalController::class, 'cancel'])->name('jadwal.cancel');
    Route::patch('/jadwal/{schedule}/cancel-series', [PenyediaJadwalController::class, 'cancelSeries'])->name('jadwal.cancel-series');
    Route::delete('/jadwal/{schedule}/participants/{participant}', [PenyediaJadwalController::class, 'removeParticipant'])->name('jadwal.participants.remove');
    Route::post('/jadwal/{schedule}/participants/{participant}/block', [PenyediaJadwalController::class, 'blockParticipant'])->name('jadwal.participants.block');
    Route::get('/events', [PenyediaEventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [PenyediaEventController::class, 'create'])->name('events.create');
    Route::post('/events', [PenyediaEventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [PenyediaEventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [PenyediaEventController::class, 'update'])->name('events.update');
    Route::post('/events/{event}/publish', [PenyediaEventController::class, 'publish'])->name('events.publish');
    Route::post('/events/{event}/close-registration', [PenyediaEventController::class, 'closeRegistration'])->name('events.close-registration');
    Route::post('/events/{event}/cancel', [PenyediaEventController::class, 'cancel'])->name('events.cancel');
    Route::delete('/events/{event}', [PenyediaEventController::class, 'destroy'])->name('events.destroy');
    Route::view('/statistik', 'penyedia-komunitas.statistik.index')->name('statistik.index');
    Route::get('/akun', [PenyediaAkunController::class, 'index'])->name('akun.index');
    Route::put('/akun/profil', [PenyediaAkunController::class, 'updateProfile'])->name('akun.update-profile');
    Route::put('/akun/komunitas', [PenyediaAkunController::class, 'updateCommunity'])->name('akun.update-community');
    Route::post('/akun/verifikasi', [PenyediaAkunController::class, 'verify'])->name('akun.verify');
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
    try {
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
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::warning("Media streaming error for [{$path}]: " . $e->getMessage());
    }

    abort(404);
})->where('path', '.*')->name('media.show');
