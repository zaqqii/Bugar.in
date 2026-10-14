<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PenyediaKomunitasAuthController extends Controller
{
    /** Daftar cabang olahraga yang tersedia */
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
     * Tampilkan halaman form daftar / masuk penyedia komunitas.
     */
    public function showForm(string $mode = 'register')
    {
        // Jika sudah login sebagai community_owner, langsung ke dashboard
        if (Auth::check() && Auth::user()->role === 'community_owner') {
            return redirect()->route('penyedia-komunitas.dashboard');
        }

        return view('auth.penyedia-komunitas.form-login-register', [
            'initialMode'     => $mode,
            'sportCategories' => self::SPORT_CATEGORIES,
        ]);
    }

    /**
     * Proses pendaftaran penyedia komunitas baru.
     * Membuat User (role: community_owner) + Community secara atomik.
     */
    public function register(Request $request)
    {
        $request->validate([
            'nama_komunitas'    => ['required', 'string', 'max:255'],
            'sport_category'    => ['required', 'string', 'in:' . implode(',', array_keys(self::SPORT_CATEGORIES))],
            'kota'              => ['required', 'string', 'max:255'],
            'nama_host'         => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', 'max:255', 'unique:users,email'],
            'whatsapp'          => ['required', 'string', 'max:20'],
            'password'          => ['required', Password::min(8)],
            'terms'             => ['accepted'],
        ], [
            'nama_komunitas.required'   => 'Nama komunitas / klub wajib diisi.',
            'sport_category.required'   => 'Cabang olahraga utama wajib dipilih.',
            'sport_category.in'         => 'Cabang olahraga tidak valid.',
            'kota.required'             => 'Kota / wilayah basecamp wajib diisi.',
            'nama_host.required'        => 'Nama penanggung jawab / host wajib diisi.',
            'email.required'            => 'Email resmi komunitas wajib diisi.',
            'email.unique'              => 'Email ini sudah terdaftar, silakan masuk.',
            'email.email'               => 'Format email tidak valid.',
            'whatsapp.required'         => 'Nomor WhatsApp host wajib diisi.',
            'password.required'         => 'Kata sandi wajib diisi.',
            'password.min'              => 'Kata sandi minimal 8 karakter.',
            'terms.accepted'            => 'Kamu harus menyetujui Pakta Integritas Olahraga Bugar.in.',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Buat user dengan role community_owner
            $user = User::create([
                'name'     => $request->nama_host,
                'email'    => $request->email,
                'phone'    => $request->whatsapp,
                'password' => Hash::make($request->password),
                'role'     => 'community_owner',
            ]);

            // 2. Buat komunitas dan hubungkan ke user
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
                'contact_email'       => $request->email,
                'contact_phone'       => $request->whatsapp,
                'verification_status' => 'pending',
                'is_active'           => true,
                'is_public'           => true,
            ]);
        });

        return redirect()->route('auth.penyedia-komunitas.login')
            ->with('success', 'Pendaftaran komunitas berhasil! Silakan masuk menggunakan email dan kata sandi akun host kamu.')
            ->with('registered_email', $request->email);
    }

    /**
     * Proses login penyedia komunitas.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Email penyelenggara / host wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $credentials = [
            'email'    => $request->email,
            'password' => $request->password,
        ];

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Pastikan yang login adalah community_owner
            if (Auth::user()->role !== 'community_owner') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun ini bukan akun Penyedia Komunitas. Silakan gunakan halaman login yang sesuai.',
                ])->withInput($request->only('email', 'form_type'));
            }

            return redirect()->intended(route('penyedia-komunitas.dashboard'))
                ->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang kamu masukkan salah.',
        ])->withInput($request->only('email', 'form_type'));
    }

    /**
     * Logout penyedia komunitas.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing')
            ->with('success', 'Kamu berhasil keluar dari portal Penyedia Komunitas.');
    }
}
