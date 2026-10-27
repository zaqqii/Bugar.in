<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Streak;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    /** Daftar cabang olahraga yang tersedia untuk komunitas */
    private const SPORT_CATEGORIES = [
        'lari'       => 'Lari / Running',
        'sepeda'     => 'Sepeda / Cycling',
        'renang'     => 'Renang / Swimming',
        'badminton'  => 'Badminton',
        'basket'     => 'Basket / Basketball',
        'futsal'     => 'Futsal / Football',
        'padel'      => 'Padel',
        'tenis'      => 'Tenis',
        'yoga'       => 'Yoga / Pilates',
        'voli'       => 'Voli / Volleyball',
        'hiking'     => 'Hiking / Outdoor',
        'gym'        => 'Gym / Fitness',
        'martial'    => 'Bela Diri',
        'other'      => 'Olahraga Lainnya',
    ];

    /**
     * Redirect pengguna ke halaman otorisasi Google.
     */
    public function redirect(Request $request)
    {
        $role = $request->query('role', 'user');
        if (!in_array($role, ['user', 'community_owner'])) {
            $role = 'user';
        }

        // Simpan role target di session untuk diproses saat callback
        session(['google_target_role' => $role]);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback dari Google OAuth.
     */
    public function callback(Request $request)
    {
        $targetRole = session('google_target_role', 'user');

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $e) {
            Log::warning('Google OAuth state mismatch (InvalidStateException), mencoba fallback ke stateless: ' . $e->getMessage());
            try {
                $googleUser = Socialite::driver('google')->stateless()->user();
            } catch (\Throwable $ex) {
                Log::error('Google OAuth Stateless Exception: ' . $ex->getMessage(), [
                    'trace' => $ex->getTraceAsString(),
                ]);
                return $this->redirectOnError($targetRole, 'Gagal mengautentikasi akun Google kamu. Silakan coba kembali.');
            }
        } catch (\Throwable $e) {
            Log::error('Google OAuth Callback Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->redirectOnError($targetRole, 'Gagal menghubungkan dengan Google. Pastikan koneksi internet stabil dan coba lagi.');
        }

        // Cek data Google user
        if (!$googleUser || !$googleUser->getEmail()) {
            return $this->redirectOnError($targetRole, 'Tidak dapat membaca email dari akun Google kamu. Pastikan izin profil diberikan.');
        }

        // 1. Cek apakah user sudah terdaftar berdasarkan google_id atau email
        $hasGoogleIdColumn = Schema::hasColumn('users', 'google_id');

        $user = null;
        if ($hasGoogleIdColumn && $googleUser->getId()) {
            $user = User::where('google_id', $googleUser->getId())->first();
        }

        if (!$user && $googleUser->getEmail()) {
            $user = User::where('email', $googleUser->getEmail())->first();
        }

        // KASUS A: Akun SUDAH ADA di database -> Langsung login
        if ($user) {
            // Update data avatar dan google_id jika belum terhubung
            $updates = [];
            if ($hasGoogleIdColumn && empty($user->google_id)) {
                $updates['google_id'] = $googleUser->getId();
            }
            if (empty($user->avatar) && $googleUser->getAvatar()) {
                $updates['avatar'] = $googleUser->getAvatar();
            }
            if (!empty($updates)) {
                $user->update($updates);
            }

            Auth::login($user, remember: true);
            $request->session()->regenerate();
            session()->forget(['google_target_role', 'google_auth_pending']);

            if ($user->role === 'community_owner') {
                return redirect()->route('penyedia-komunitas.dashboard')
                    ->with('success', 'Selamat datang kembali di Portal Komunitas!');
            }

            return redirect()->route('user.dashboard')
                ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        // KASUS B: Akun BARU -> Simpan data Google di session dan arahkan ke Onboarding
        session([
            'google_auth_pending' => [
                'google_id' => $googleUser->getId(),
                'email'     => $googleUser->getEmail(),
                'name'      => $googleUser->getName(),
                'avatar'    => $googleUser->getAvatar(),
                'role'      => $targetRole,
            ]
        ]);

        if ($targetRole === 'community_owner') {
            return redirect()->route('auth.penyedia-komunitas.google-complete');
        }

        return redirect()->route('auth.user.google-complete');
    }

    /**
     * Tampilkan formulir pelengkap profil untuk Pengguna Biasa.
     */
    public function showUserCompleteForm()
    {
        $googleData = session('google_auth_pending');
        if (!$googleData) {
            return redirect()->route('auth.user.login')
                ->with('error', 'Sesi pendaftaran Google telah berakhir atau tidak ditemukan. Silakan klik tombol Google kembali.');
        }

        // Buat username suggestion dari nama atau email
        $baseUsername = Str::slug($googleData['name'] ?? explode('@', $googleData['email'])[0], '_');
        $suggestedUsername = $baseUsername;
        $counter = 1;
        while (User::where('username', $suggestedUsername)->exists()) {
            $suggestedUsername = $baseUsername . '_' . $counter++;
        }

        return view('auth.user.google-complete', [
            'googleData'        => $googleData,
            'suggestedUsername' => $suggestedUsername,
        ]);
    }

    /**
     * Simpan pendaftaran user baru via Google setelah melengkapi form.
     */
    public function storeUserComplete(Request $request)
    {
        $googleData = session('google_auth_pending');
        if (!$googleData) {
            return redirect()->route('auth.user.login')
                ->with('error', 'Sesi pendaftaran Google telah kedaluwarsa. Silakan masuk kembali dengan Google.');
        }

        $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username', 'regex:/^[a-zA-Z0-9_.]+$/'],
            'whatsapp' => ['required', 'string', 'max:20'],
            'terms'    => ['accepted'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username ini sudah digunakan, silakan pilih yang lain.',
            'username.regex'    => 'Username hanya boleh berisi huruf, angka, underscore (_), dan titik (.).',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi untuk tiket & notifikasi jadwal.',
            'terms.accepted'    => 'Anda harus menyetujui Syarat & Ketentuan Bugar.in.',
        ]);

        // Cek jika akun sudah terdaftar saat mengisi form
        $existingUser = User::where('email', $googleData['email'])->first();
        if ($existingUser) {
            if (Schema::hasColumn('users', 'google_id') && empty($existingUser->google_id)) {
                $existingUser->update(['google_id' => $googleData['google_id']]);
            }
            Auth::login($existingUser, remember: true);
            $request->session()->regenerate();
            session()->forget(['google_auth_pending', 'google_target_role']);
            return redirect()->route('user.dashboard')
                ->with('success', 'Selamat datang di Bugar.in, ' . $existingUser->name . '!');
        }

        $userData = [
            'name'              => $googleData['name'] ?? explode('@', $googleData['email'])[0],
            'username'          => strtolower($request->username),
            'email'             => $googleData['email'],
            'email_verified_at' => now(),
            'password'          => Hash::make(Str::random(32)),
            'phone'             => $request->whatsapp,
            'avatar'            => $googleData['avatar'] ?? null,
            'role'              => 'user',
            'total_points'      => 0,
            'is_active'         => true,
        ];

        if (Schema::hasColumn('users', 'google_id')) {
            $userData['google_id'] = $googleData['google_id'];
        }

        $user = User::create($userData);

        // Jika user memilih cabang olahraga, buat record streak awal
        if ($request->has('sports') && is_array($request->sports)) {
            foreach ($request->sports as $sport) {
                if (is_string($sport) && !empty($sport)) {
                    Streak::firstOrCreate(
                        ['user_id' => $user->id, 'sport_category' => $sport],
                        ['current_streak' => 0, 'longest_streak' => 0]
                    );
                }
            }
        }

        session()->forget(['google_auth_pending', 'google_target_role']);
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->route('user.dashboard')
            ->with('success', 'Akun berhasil dibuat! Selamat datang di Bugar.in, ' . $user->name . '.');
    }

    /**
     * Tampilkan formulir pelengkap profil untuk Penyedia Komunitas.
     */
    public function showPenyediaCompleteForm()
    {
        $googleData = session('google_auth_pending');
        if (!$googleData) {
            return redirect()->route('auth.penyedia-komunitas.login')
                ->with('error', 'Sesi pendaftaran Google telah berakhir atau tidak ditemukan. Silakan klik tombol Google kembali.');
        }

        return view('auth.penyedia-komunitas.google-complete', [
            'googleData'      => $googleData,
            'sportCategories' => self::SPORT_CATEGORIES,
        ]);
    }

    /**
     * Simpan pendaftaran penyedia komunitas baru via Google.
     */
    public function storePenyediaComplete(Request $request)
    {
        $googleData = session('google_auth_pending');
        if (!$googleData) {
            return redirect()->route('auth.penyedia-komunitas.login')
                ->with('error', 'Sesi pendaftaran Google telah kedaluwarsa. Silakan coba kembali.');
        }

        $request->validate([
            'nama_komunitas' => ['required', 'string', 'max:255'],
            'sport_category' => ['required', 'string', 'in:' . implode(',', array_keys(self::SPORT_CATEGORIES))],
            'kota'           => ['required', 'string', 'max:255'],
            'whatsapp'       => ['required', 'string', 'max:20'],
            'terms'          => ['accepted'],
        ], [
            'nama_komunitas.required' => 'Nama komunitas / klub wajib diisi.',
            'sport_category.required' => 'Cabang olahraga utama wajib dipilih.',
            'sport_category.in'       => 'Cabang olahraga tidak valid.',
            'kota.required'           => 'Kota / wilayah basecamp wajib diisi.',
            'whatsapp.required'       => 'Nomor WhatsApp host wajib diisi.',
            'terms.accepted'          => 'Anda harus menyetujui Pakta Integritas Olahraga Bugar.in.',
        ]);

        // Cek jika akun sudah terdaftar saat mengisi form
        $existingUser = User::where('email', $googleData['email'])->first();
        if ($existingUser) {
            if (Schema::hasColumn('users', 'google_id') && empty($existingUser->google_id)) {
                $existingUser->update(['google_id' => $googleData['google_id']]);
            }
            Auth::login($existingUser, remember: true);
            $request->session()->regenerate();
            session()->forget(['google_auth_pending', 'google_target_role']);
            return redirect()->route('penyedia-komunitas.dashboard')
                ->with('success', 'Selamat datang kembali di Portal Komunitas!');
        }

        $user = DB::transaction(function () use ($request, $googleData) {
            // Generate username unik untuk host jika belum ada
            $baseUsername = Str::slug($googleData['name'] ?? explode('@', $googleData['email'])[0], '_');
            $hostUsername = $baseUsername;
            $counter = 1;
            while (User::where('username', $hostUsername)->exists()) {
                $hostUsername = $baseUsername . '_' . $counter++;
            }

            $userData = [
                'name'              => $googleData['name'] ?? 'Host ' . $request->nama_komunitas,
                'username'          => $hostUsername,
                'email'             => $googleData['email'],
                'email_verified_at' => now(),
                'password'          => Hash::make(Str::random(32)),
                'phone'             => $request->whatsapp,
                'avatar'            => $googleData['avatar'] ?? null,
                'role'              => 'community_owner',
                'total_points'      => 0,
                'is_active'         => true,
            ];

            if (Schema::hasColumn('users', 'google_id')) {
                $userData['google_id'] = $googleData['google_id'];
            }

            $user = User::create($userData);

            $slug = Str::slug($request->nama_komunitas);
            $originalSlug = $slug;
            $i = 1;
            while (Community::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $i++;
            }

            Community::create([
                'owner_id'            => $user->id,
                'name'                => $request->nama_komunitas,
                'slug'                => $slug,
                'sport_category'      => $request->sport_category,
                'city'                => $request->kota,
                'contact_email'       => $googleData['email'],
                'contact_phone'       => $request->whatsapp,
                'verification_status' => 'verified',
                'verified_at'         => now(),
                'is_active'           => true,
                'is_public'           => true,
            ]);

            return $user;
        });

        session()->forget(['google_auth_pending', 'google_target_role']);
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->route('penyedia-komunitas.dashboard')
            ->with('success', 'Komunitas ' . $request->nama_komunitas . ' berhasil didaftarkan! Selamat datang di Portal Komunitas.');
    }

    /**
     * Helper redirect jika terjadi error pada flow callback.
     */
    protected function redirectOnError(string $role, string $message)
    {
        if ($role === 'community_owner') {
            return redirect()->route('auth.penyedia-komunitas.login')
                ->with('error', $message);
        }

        return redirect()->route('auth.user.login')
            ->with('error', $message);
    }
}
