<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserAuthController extends Controller
{
    /**
     * Tampilkan halaman form daftar / masuk user.
     */
    public function showForm(string $mode = 'register')
    {
        // Jika sudah login, langsung ke dashboard
        if (Auth::check() && Auth::user()->role === 'user') {
            return redirect()->route('user.dashboard');
        }

        return view('auth.user.form-login-register', ['initialMode' => $mode]);
    }

    /**
     * Proses pendaftaran user baru.
     */
    public function register(Request $request)
    {
        $request->validate([
            'nama'      => ['required', 'string', 'max:255', 'unique:users,name'],
            'username'  => ['required', 'string', 'max:50', 'unique:users,username', 'regex:/^[a-zA-Z0-9_.]+$/'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'whatsapp'  => ['required', 'string', 'max:20'],
            'password'  => ['required', 'confirmed', Password::min(8)],
            'terms'     => ['accepted'],
        ], [
            'nama.required'      => 'Nama lengkap wajib diisi.',
            'nama.unique'        => 'Nama ini sudah digunakan, silakan pilih nama lain.',
            'username.required'  => 'Username wajib diisi.',
            'username.unique'    => 'Username ini sudah dipakai.',
            'username.regex'     => 'Username hanya boleh berisi huruf, angka, garis bawah (_), dan titik (.).',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.unique'       => 'Email ini sudah terdaftar, silakan masuk.',
            'whatsapp.required'  => 'Nomor WhatsApp wajib diisi.',
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min'       => 'Kata sandi minimal 8 karakter.',
            'terms.accepted'     => 'Kamu harus menyetujui Syarat & Ketentuan.',
        ]);

        $user = User::create([
            'name'     => $request->nama,
            'username' => strtolower($request->username),
            'email'    => $request->email,
            'phone'    => $request->whatsapp,
            'password' => Hash::make($request->password),
            'role'     => 'user',
        ]);

        return redirect()->route('auth.user.login')
            ->with('success', 'Pendaftaran berhasil! Silakan masuk menggunakan email dan kata sandi kamu.');
    }

    /**
     * Proses login user.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required'    => 'Email atau Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // Cek apakah input berupa email atau username
        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField => $request->login,
            'password'  => $request->password,
        ];

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Pastikan yang login adalah user biasa
            if (Auth::user()->role !== 'user') {
                Auth::logout();
                return back()->withErrors([
                    'login' => 'Akun ini bukan akun pengguna biasa.',
                ])->withInput($request->only('login'));
            }

            return redirect()->intended(route('user.dashboard'))
                ->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'login' => 'Email/Username atau kata sandi yang kamu masukkan salah.',
        ])->withInput($request->only('login'));
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing')
            ->with('success', 'Kamu berhasil keluar dari Bugar.in.');
    }
}
