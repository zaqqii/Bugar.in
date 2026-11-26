<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar & Masuk — Bugar.in</title>
    <meta name="description" content="Daftarkan dirimu di Bugar.in dan temukan komunitas olahraga favoritmu.">

    @vite(['resources/css/app.css', 'resources/css/auth-user.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="auth-user-body">

{{-- ============================================================
     WRAPPER dengan Alpine.js — mode: 'register' | 'login'
     ============================================================ --}}
<div class="auth-user-page"
     x-data="authUserPage()"
     x-init="init()">

    {{-- ============================================================
         LEFT PANEL — Form Area (slides)
         ============================================================ --}}
    <div class="auth-panel-left">

        {{-- Header --}}
        <header class="auth-panel-header">
            <a href="{{ route('landing') }}" class="auth-brand" aria-label="Kembali ke Bugar.in">
                <div class="auth-brand-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9l1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z"/>
                    </svg>
                </div>
                <span class="auth-brand-name">Bugar.in</span>
                <span class="auth-brand-sub">Platform Komunitas &amp; Event Olahraga</span>
            </a>

            <div class="auth-header-actions">
                <button
                    class="auth-header-btn"
                    :class="{ 'auth-header-btn--pill-active': mode === 'register' }"
                    @click="switchTo('register')"
                    id="btn-header-daftar">
                    Daftar Akun Baru
                </button>
                <button
                    class="auth-header-btn"
                    :class="{ 'auth-header-btn--pill-active': mode === 'login' }"
                    @click="switchTo('login')"
                    id="btn-header-masuk">
                    Masuk
                </button>
            </div>
        </header>

        {{-- Slide Container --}}
        <div class="auth-slides-wrapper">
            <div class="auth-slides-track"
                 :style="{ transform: mode === 'register' ? 'translateX(0%)' : 'translateX(-50%)' }">

                {{-- ================================================
                     SLIDE 1 — DAFTAR (Register) — mode register = 0%
                     ================================================ --}}
                <div class="auth-slide" id="slide-register" aria-label="Form Daftar">
                    <div class="auth-form-scroll">

                        <div class="auth-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            REGISTRASI PENGGUNA BARU
                        </div>

                        <h1 class="auth-form-title">
                            Mulai Langkah Sehatmu,<br>
                            <span class="auth-form-title--accent">Gabung Ribuan Atlet Lokal</span>
                        </h1>
                        <p class="auth-form-desc">
                            Daftarkan dirimu untuk menemukan komunitas lari, padel, sepeda, dan ikuti berbagai event olahraga seru di kotamu.
                        </p>

                        {{-- Error alert register (hanya non-field error) --}}
                        @if(session('error'))
                            <div class="auth-error-alert" role="alert">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                {{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('auth.user.register.post') }}" class="auth-form" id="form-register" @submit="handleSubmit($event, 'register')" novalidate>
                            @csrf
                            <input type="hidden" name="form_type" value="register">

                            {{-- Row 1: Nama & Username --}}
                            <div class="auth-form-row">
                                <div class="auth-field">
                                    <label class="auth-field-label" for="reg-nama">Nama Lengkap</label>
                                    <div class="auth-field-wrap">
                                        <svg class="auth-field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <input
                                            type="text"
                                            id="reg-nama"
                                            name="nama"
                                            class="auth-field-input"
                                            :class="{ 'is-error': errors.nama }"
                                            placeholder="Cth. Dimas Krispatia"
                                            autocomplete="name"
                                            value="{{ old('nama') }}"
                                            @keydown="blockDigitsOnName($event)"
                                            @input="handleNameInput($event)"
                                            required>
                                    </div>
                                    <p class="auth-field-error" x-show="errors.nama" x-cloak x-text="errors.nama"></p>
                                </div>
                                <div class="auth-field">
                                    <label class="auth-field-label" for="reg-username">Username</label>
                                    <div class="auth-field-wrap">
                                        <svg class="auth-field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <input
                                            type="text"
                                            id="reg-username"
                                            name="username"
                                            class="auth-field-input"
                                            :class="{ 'is-error': errors.username }"
                                            placeholder="dimas_k"
                                            autocomplete="username"
                                            value="{{ old('username') }}"
                                            @input="clearError('username')"
                                            required>
                                    </div>
                                    <p class="auth-field-error" x-show="errors.username" x-cloak x-text="errors.username"></p>
                                </div>
                            </div>

                            {{-- Row 2: Email & WhatsApp --}}
                            <div class="auth-form-row">
                                <div class="auth-field">
                                    <label class="auth-field-label" for="reg-email">Alamat Email</label>
                                    <div class="auth-field-wrap">
                                        <svg class="auth-field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <input
                                            type="email"
                                            id="reg-email"
                                            name="email"
                                            class="auth-field-input"
                                            :class="{ 'is-error': errors.email }"
                                            placeholder="nama@email.com"
                                            autocomplete="email"
                                            value="{{ old('form_type') === 'register' ? old('email') : '' }}"
                                            @input="clearError('email')"
                                            required>
                                    </div>
                                    <p class="auth-field-error" x-show="errors.email" x-cloak x-text="errors.email"></p>
                                </div>
                                <div class="auth-field">
                                    <label class="auth-field-label" for="reg-wa">Nomor WhatsApp / HP</label>
                                    <div class="auth-field-wrap">
                                        <svg class="auth-field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        <input
                                            type="tel"
                                            id="reg-wa"
                                            name="whatsapp"
                                            class="auth-field-input"
                                            :class="{ 'is-error': errors.whatsapp }"
                                            placeholder="+62 812-xxxx-xxxx"
                                            autocomplete="tel"
                                            value="{{ old('whatsapp') }}"
                                            @input="clearError('whatsapp')"
                                            required>
                                    </div>
                                    <p class="auth-field-error" x-show="errors.whatsapp" x-cloak x-text="errors.whatsapp"></p>
                                </div>
                            </div>

                            {{-- Row 3: Kata Sandi & Konfirmasi --}}
                            <div class="auth-form-row">
                                <div class="auth-field">
                                    <label class="auth-field-label" for="reg-password">Kata Sandi</label>
                                    <div class="auth-field-wrap">
                                        <svg class="auth-field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <input
                                            :type="showPassword ? 'text' : 'password'"
                                            id="reg-password"
                                            name="password"
                                            class="auth-field-input auth-field-input--pad-right"
                                            :class="{ 'is-error': errors.password }"
                                            placeholder="Minimal 8 karakter"
                                            autocomplete="new-password"
                                            @input="clearError('password')"
                                            required>
                                        <button
                                            type="button"
                                            class="auth-field-toggle"
                                            @click="showPassword = !showPassword"
                                            :aria-label="showPassword ? 'Sembunyikan sandi' : 'Tampilkan sandi'"
                                            id="toggle-reg-password">
                                            <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="display:none">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="auth-field-error" x-show="errors.password" x-cloak x-text="errors.password"></p>
                                </div>
                                <div class="auth-field">
                                    <label class="auth-field-label" for="reg-password-confirm">Ulangi Sandi</label>
                                    <div class="auth-field-wrap">
                                        <svg class="auth-field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <input
                                            :type="showPasswordConfirm ? 'text' : 'password'"
                                            id="reg-password-confirm"
                                            name="password_confirmation"
                                            class="auth-field-input auth-field-input--pad-right"
                                            :class="{ 'is-error': errors.password_confirmation }"
                                            placeholder="Ulangi sandi"
                                            autocomplete="new-password"
                                            @input="clearError('password_confirmation')"
                                            required>
                                        <button
                                            type="button"
                                            class="auth-field-toggle"
                                            @click="showPasswordConfirm = !showPasswordConfirm"
                                            :aria-label="showPasswordConfirm ? 'Sembunyikan sandi' : 'Tampilkan sandi'"
                                            id="toggle-reg-password-confirm">
                                            <svg x-show="!showPasswordConfirm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg x-show="showPasswordConfirm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="display:none">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="auth-field-error" x-show="errors.password_confirmation" x-cloak x-text="errors.password_confirmation"></p>
                                </div>
                            </div>

                            {{-- Terms & Conditions --}}
                            <div class="auth-terms">
                                <label class="auth-terms-label" for="reg-terms">
                                    <div class="auth-checkbox-wrap">
                                        <input type="checkbox" id="reg-terms" name="terms" class="auth-checkbox" {{ old('terms') ? 'checked' : '' }} @change="clearError('terms')" required>
                                        <div class="auth-checkbox-custom" :class="{ 'is-error': errors.terms }" aria-hidden="true">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <span>Saya menyetujui <button type="button" class="auth-link-btn" @click.stop.prevent="openLegalModal('terms')">Syarat &amp; Ketentuan</button> serta <button type="button" class="auth-link-btn" @click.stop.prevent="openLegalModal('privacy')">Kebijakan Privasi</button> Bugar.in untuk rekomendasi jadwal olahraga.</span>
                                </label>
                                <p class="auth-field-error" x-show="errors.terms" x-cloak x-text="errors.terms"></p>
                            </div>

                            {{-- CTA --}}
                            <button
                                type="submit"
                                class="auth-submit-btn"
                                id="btn-daftar-sekarang"
                                :disabled="isTransitioning || isSubmitting"
                                :class="{ 'is-disabled': isTransitioning || isSubmitting }">
                                <span x-text="isSubmitting && mode === 'register' ? 'Memproses Pendaftaran...' : 'Daftar Sekarang'">Daftar Sekarang</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>

                            {{-- Divider --}}
                            <div class="auth-divider">
                                <span>atau daftar dengan</span>
                            </div>

                            {{-- Social Buttons (Hanya Google) --}}
                            <div class="auth-social-grid">
                                <a href="{{ route('auth.google.redirect', ['role' => 'user']) }}" class="auth-social-btn" id="btn-reg-google" style="text-decoration:none;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                    </svg>
                                    Google
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ================================================
                     SLIDE 2 — MASUK (Login) — mode login = -50%
                     ================================================ --}}
                <div class="auth-slide" id="slide-login" aria-label="Form Masuk">
                    <div class="auth-form-scroll">

                        <div class="auth-badge auth-badge--blue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z"/>
                            </svg>
                            MASUK KE AKUN ANDA
                        </div>

                        <h1 class="auth-form-title">
                            Selamat Datang,<br>
                            <span class="auth-form-title--accent">Kembali ke Komunitas!</span>
                        </h1>
                        <p class="auth-form-desc">
                            Masuk untuk melanjutkan perjalanan olahraga bersama ribuan member aktif di Bugar.in.
                        </p>

                        {{-- Error alert login (hanya error social non-field) --}}
                        @if($errors->has('social'))
                            <div class="auth-error-alert" role="alert">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                {{ $errors->first('social') }}
                            </div>
                        @endif

                        {{-- Success alert login --}}
                        @if(session('success'))
                            <div class="auth-success-alert" role="alert">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ session('success') }}
                            </div>
                        @endif
                        <div class="auth-success-alert" role="status" x-show="postRegisterBanner" x-cloak>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Akun berhasil dibuat! Silakan masuk menggunakan kata sandi yang sudah didaftarkan.</span>
                        </div>

                        <form method="POST" action="{{ route('auth.user.login.post') }}" class="auth-form" id="form-login" @submit="handleSubmit($event, 'login')" novalidate>
                            @csrf
                            <input type="hidden" name="form_type" value="login">

                            {{-- Email / Username --}}
                            <div class="auth-field auth-field--full">
                                <label class="auth-field-label" for="login-email">Alamat Email / Username</label>
                                <div class="auth-field-wrap">
                                    <svg class="auth-field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <input
                                        type="text"
                                        id="login-email"
                                        name="login"
                                        x-model="loginInput"
                                        class="auth-field-input"
                                        :class="{ 'is-error': errors.login }"
                                        placeholder="nama@email.com atau username"
                                        autocomplete="username"
                                        value="{{ old('login') }}"
                                        @input="clearError('login'); clearError('login_credentials')"
                                        required>
                                </div>
                                <p class="auth-field-error" x-show="errors.login" x-cloak x-text="errors.login"></p>
                            </div>

                            {{-- Password --}}
                            <div class="auth-field auth-field--full">
                                <div class="auth-field-label-row">
                                    <label class="auth-field-label" for="login-password">Kata Sandi</label>
                                    <a href="#" class="auth-field-link" id="link-lupa-sandi">Lupa kata sandi?</a>
                                </div>
                                <div class="auth-field-wrap">
                                    <svg class="auth-field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <input
                                        :type="showLoginPassword ? 'text' : 'password'"
                                        id="login-password"
                                        name="password"
                                        x-model="loginPassword"
                                        class="auth-field-input auth-field-input--pad-right"
                                        :class="{ 'is-error': errors.login_password || errors.login_credentials }"
                                        placeholder="Masukkan kata sandi"
                                        autocomplete="current-password"
                                        @input="clearError('login_password'); clearError('login_credentials')"
                                        required>
                                    <button
                                        type="button"
                                        class="auth-field-toggle"
                                        @click="showLoginPassword = !showLoginPassword"
                                        :aria-label="showLoginPassword ? 'Sembunyikan sandi' : 'Tampilkan sandi'"
                                        id="toggle-login-password">
                                        <svg x-show="!showLoginPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showLoginPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" style="display:none">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                                <p class="auth-field-error" x-show="errors.login_password" x-cloak x-text="errors.login_password"></p>
                            </div>

                            {{-- Remember me --}}
                            <div class="auth-remember">
                                <label class="auth-terms-label" for="login-remember">
                                    <div class="auth-checkbox-wrap">
                                        <input type="checkbox" id="login-remember" name="remember" class="auth-checkbox">
                                        <div class="auth-checkbox-custom" aria-hidden="true">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <span>Ingat saya di perangkat ini</span>
                                </label>
                            </div>

                            {{-- CTA --}}
                            <button
                                type="submit"
                                class="auth-submit-btn"
                                id="btn-masuk-sekarang"
                                :disabled="isTransitioning || isSubmitting"
                                :class="{ 'is-disabled': isTransitioning || isSubmitting }">
                                <span x-text="isSubmitting && mode === 'login' ? 'Memverifikasi Akun...' : 'Masuk Sekarang'">Masuk Sekarang</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>

                            {{-- Divider --}}
                            <div class="auth-divider">
                                <span>atau masuk dengan</span>
                            </div>

                            {{-- Social Buttons --}}
                            <div class="auth-social-grid auth-social-grid--login">
                                <a href="{{ route('auth.google.redirect', ['role' => 'user']) }}" class="auth-social-btn" id="btn-login-google" style="text-decoration:none;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                    </svg>
                                    Google
                                </a>
                            </div>

                            {{-- Switch to Register --}}
                            <p class="auth-switch-text">
                                Belum punya akun?
                                <button type="button" class="auth-switch-link" @click="switchTo('register')" id="link-ke-daftar">Daftar sekarang</button>
                            </p>
                        </form>
                    </div>
                </div>

            </div>{{-- .auth-slides-track --}}
        </div>{{-- .auth-slides-wrapper --}}

    </div>{{-- .auth-panel-left --}}

    {{-- ============================================================
         RIGHT PANEL — Visual / Promo
         ============================================================ --}}
    <div class="auth-panel-right" aria-hidden="true">
        <div class="auth-panel-right__overlay"></div>
        <div class="auth-panel-right__content">

            {{-- Badge top --}}
            <div class="auth-promo-badge">
                <span class="auth-promo-badge-dot"></span>
                1.250+ Komunitas Aktif &amp; Terverifikasi
            </div>
            <div class="auth-promo-location">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Jabodetabek • Bali • SBY • BDG
            </div>

            {{-- Heading --}}
            <p class="auth-promo-hashtag">#BANGUNKEBIASAANSEHAT</p>
            <h2 class="auth-promo-heading">
                Temukan ritme larimu,<br>
                bertumbuh bersama<br>
                komunitas lokal.
            </h2>

            {{-- Stats --}}
            <div class="auth-stats-grid">
                <div class="auth-stat">
                    <div class="auth-stat-icon auth-stat-icon--blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9l1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="auth-stat-num">84.2K+</div>
                        <div class="auth-stat-label">PARTISIPAN</div>
                    </div>
                </div>
                <div class="auth-stat">
                    <div class="auth-stat-icon auth-stat-icon--teal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="auth-stat-num">1.250+</div>
                        <div class="auth-stat-label">KOMUNITAS</div>
                    </div>
                </div>
                <div class="auth-stat">
                    <div class="auth-stat-icon auth-stat-icon--orange">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="auth-stat-num">4.9 / 5</div>
                        <div class="auth-stat-label">KEPUASAN</div>
                    </div>
                </div>
            </div>

            {{-- Testimonial Card --}}
            <div class="auth-testimonial">
                <div class="auth-testimonial-header">
                    <div class="auth-testimonial-avatar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="auth-testimonial-name">Maya Kartika</div>
                        <div class="auth-testimonial-role">Member Jakarta Running Brigade</div>
                    </div>
                    <div class="auth-testimonial-stars" aria-label="Rating 5 bintang">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#f59e0b" viewBox="0 0 24 24"><path d="M12 2L9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#f59e0b" viewBox="0 0 24 24"><path d="M12 2L9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#f59e0b" viewBox="0 0 24 24"><path d="M12 2L9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#f59e0b" viewBox="0 0 24 24"><path d="M12 2L9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#f59e0b" viewBox="0 0 24 24"><path d="M12 2L9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2z"/></svg>
                    </div>
                </div>
                <blockquote class="auth-testimonial-quote">
                    "Sudah ikut 24 fun match &amp; sunday morning run via Bugar.in!<br>
                    Sangat mudah cari jadwal komunitas yang match sama target pace lari saya."
                </blockquote>
                <div class="auth-testimonial-footer">
                    <div class="auth-testimonial-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.95 11.95 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Tiket &amp; Slot Terkonfirmasi
                    </div>
                    <span class="auth-testimonial-meta">Pace 5:45 • Half Marathon Finisher</span>
                </div>
            </div>

        </div>{{-- .auth-panel-right__content --}}
    </div>{{-- .auth-panel-right --}}

    {{-- ============================================================
         MODAL RINGKAS: SYARAT & KETENTUAN / KEBIJAKAN PRIVASI (Tanpa Scroll)
         ============================================================ --}}
    <div class="auth-modal-backdrop"
         x-show="legalModal !== null"
         x-cloak
         @click.self="legalModal = null"
         @keydown.escape.window="legalModal = null"
         role="dialog"
         aria-modal="true"
         aria-labelledby="legal-modal-title">
        <div class="auth-modal-card">
            <div class="auth-modal-header">
                <h3 class="auth-modal-title" id="legal-modal-title"
                    x-text="legalModal === 'privacy' ? 'Kebijakan Privasi Bugar.in' : 'Syarat & Ketentuan Bugar.in'"></h3>
                <button type="button" class="auth-modal-close" @click="legalModal = null" aria-label="Tutup modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <ul class="auth-modal-list" x-show="legalModal === 'terms'">
                <li>Pengguna mendaftar dengan identitas asli serta menjaga kerahasiaan akun dan kata sandi.</li>
                <li>Menjunjung sportivitas, hadir tepat waktu pada jadwal olahraga yang diikuti, dan mematuhi aturan komunitas.</li>
                <li>Bugar.in berhak membatasi fitur pendaftaran jika terjadi penyalahgunaan atau pelanggaran berulang.</li>
            </ul>

            <ul class="auth-modal-list" x-show="legalModal === 'privacy'">
                <li>Nama, email, dan nomor WhatsApp digunakan untuk verifikasi akun serta pengingat jadwal olahraga.</li>
                <li>Preferensi olahraga hanya dipakai untuk merekomendasikan komunitas dan event yang relevan.</li>
                <li>Bugar.in tidak menjual atau membagikan data pribadi pengguna kepada pihak ketiga tanpa izin.</li>
            </ul>

            <button type="button" class="auth-modal-btn" @click="legalModal = null">Mengerti</button>
        </div>
    </div>

    {{-- ============================================================
         POP-UP TANDA SUKSES DAFTAR -> OTOMATIS PINDAH KE HALAMAN MASUK
         ============================================================ --}}
    <div class="auth-modal-backdrop"
         x-show="showRegisterSuccess"
         x-cloak
         role="status"
         aria-live="polite">
        <div class="auth-success-modal-card">
            <div class="auth-success-badge-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="auth-success-modal-title">Pendaftaran Berhasil!</h3>
            <p class="auth-success-modal-desc">
                <span x-text="registeredSuccessText || 'Akun kamu telah berhasil dibuat.'"></span>
                <br>Mengalihkan ke halaman Masuk...
            </p>
            <div class="auth-success-progress-track">
                <div class="auth-success-progress-fill"></div>
            </div>
            <button type="button" class="auth-modal-btn auth-modal-btn--success" @click="completeRegisterRedirect()">
                Masuk Sekarang
            </button>
        </div>
    </div>

</div>{{-- .auth-user-page --}}

@php
    $isLoginSubmit = old('form_type') === 'login' || $errors->has('login');
    $passwordErr = (!$isLoginSubmit && $errors->has('password')) ? $errors->first('password') : '';
    $isConfirmErr = $passwordErr !== '' && stripos($passwordErr, 'konfirmasi') !== false;
    $loginErr = ($isLoginSubmit && $errors->has('login')) ? $errors->first('login') : '';
    $loginPasswordErr = ($isLoginSubmit && $errors->has('password')) ? $errors->first('password') : '';
    $isLoginCredErr = $loginErr !== '' && stripos($loginErr, 'wajib diisi') === false;

    $initialFieldErrors = [
        'nama' => !$isLoginSubmit ? $errors->first('nama') : '',
        'username' => !$isLoginSubmit ? $errors->first('username') : '',
        'email' => !$isLoginSubmit ? $errors->first('email') : '',
        'whatsapp' => !$isLoginSubmit ? $errors->first('whatsapp') : '',
        'password' => $isConfirmErr ? '' : $passwordErr,
        'password_confirmation' => $isConfirmErr ? $passwordErr : '',
        'terms' => !$isLoginSubmit ? $errors->first('terms') : '',
        'login' => $loginErr,
        'login_password' => $loginPasswordErr,
        'login_credentials' => $isLoginCredErr ? '1' : '',
    ];
@endphp

<script>
    function authUserPage() {
        return {
            mode: @js(session('registered_success') ? 'register' : (old('form_type') === 'login' || $errors->has('login') || session('success') ? 'login' : (old('form_type') === 'register' || $errors->hasAny(['nama', 'username', 'email', 'whatsapp', 'password', 'terms']) ? 'register' : request('mode', $initialMode ?? 'register')))),
            showPassword: false,
            showPasswordConfirm: false,
            showLoginPassword: false,
            isTransitioning: false,
            isSubmitting: false,
            loginInput: @js(old('form_type') === 'login' || $errors->has('login') ? old('login', '') : ''),
            loginPassword: '',
            legalModal: null,
            showRegisterSuccess: @js((bool) session('registered_success')),
            registeredSuccessText: @js(session('registered_success', '')),
            registeredEmail: @js(session('registered_email', '')),
            postRegisterBanner: false,
            hasServerErrors: @js($errors->any()),
            _redirectTimer: null,
            errors: @js($initialFieldErrors),

            init() {
                const params = new URLSearchParams(window.location.search);
                if (!this.showRegisterSuccess && !this.hasServerErrors && params.get('mode') === 'login') {
                    this.mode = 'login';
                }
                if (this.showRegisterSuccess) {
                    this._redirectTimer = setTimeout(() => {
                        this.completeRegisterRedirect();
                    }, 2200);
                }
            },

            openLegalModal(type) {
                this.legalModal = type;
            },

            completeRegisterRedirect() {
                if (this._redirectTimer) {
                    clearTimeout(this._redirectTimer);
                    this._redirectTimer = null;
                }
                this.showRegisterSuccess = false;
                this.postRegisterBanner = true;
                this.switchTo('login', true);
            },

            clearError(field) {
                if (this.errors && Object.prototype.hasOwnProperty.call(this.errors, field)) {
                    this.errors[field] = '';
                }
            },

            blockDigitsOnName(event) {
                if (!event.ctrlKey && !event.metaKey && !event.altKey && /^[0-9]$/.test(event.key)) {
                    event.preventDefault();
                    this.errors.nama = 'Nama lengkap tidak boleh mengandung angka.';
                }
            },

            handleNameInput(event) {
                const val = event.target.value;
                if (/\d/.test(val)) {
                    event.target.value = val.replace(/\d+/g, '');
                    this.errors.nama = 'Nama lengkap tidak boleh mengandung angka.';
                } else {
                    this.clearError('nama');
                }
            },

            switchTo(newMode, preserveEmail = false) {
                if (this.isTransitioning || this.mode === newMode) return;
                this.mode = newMode;
                this.isTransitioning = true;

                if (!preserveEmail) {
                    this.clearLoginForm();
                } else {
                    this.loginInput = this.registeredEmail || '';
                    this.loginPassword = '';
                    this.showLoginPassword = false;
                    const emailEl = document.getElementById('login-email');
                    if (emailEl) emailEl.value = this.loginInput;
                    const passEl = document.getElementById('login-password');
                    if (passEl) {
                        passEl.value = '';
                        setTimeout(() => passEl.focus(), 450);
                    }
                }

                setTimeout(() => {
                    this.isTransitioning = false;
                }, 800);

                // Update URL without reload
                const url = new URL(window.location);
                url.searchParams.set('mode', newMode);
                window.history.pushState({}, '', url);
            },

            clearLoginForm() {
                this.loginInput = '';
                this.loginPassword = '';
                this.showLoginPassword = false;
                this.clearError('login');
                this.clearError('login_password');
                this.clearError('login_credentials');
                const formLogin = document.getElementById('form-login');
                if (formLogin) {
                    formLogin.reset();
                    const emailEl = document.getElementById('login-email');
                    if (emailEl) {
                        emailEl.value = '';
                        emailEl.defaultValue = '';
                    }
                    const passEl = document.getElementById('login-password');
                    if (passEl) {
                        passEl.value = '';
                        passEl.defaultValue = '';
                    }
                    const remEl = document.getElementById('login-remember');
                    if (remEl) remEl.checked = false;
                }
            },

            handleSubmit(event, type) {
                if (this.isSubmitting || this.isTransitioning) {
                    event.preventDefault();
                    return false;
                }

                if (type === 'register') {
                    const nama = (document.getElementById('reg-nama')?.value || '').trim();
                    const username = (document.getElementById('reg-username')?.value || '').trim();
                    const email = (document.getElementById('reg-email')?.value || '').trim();
                    const whatsapp = (document.getElementById('reg-wa')?.value || '').trim();
                    const password = document.getElementById('reg-password')?.value || '';
                    const passwordConfirm = document.getElementById('reg-password-confirm')?.value || '';
                    const termsChecked = Boolean(document.getElementById('reg-terms')?.checked);

                    const regErrors = {
                        nama: '',
                        username: '',
                        email: '',
                        whatsapp: '',
                        password: '',
                        password_confirmation: '',
                        terms: '',
                    };

                    if (!nama) {
                        regErrors.nama = 'Nama lengkap wajib diisi.';
                    } else if (/\d/.test(nama)) {
                        regErrors.nama = 'Nama lengkap tidak boleh mengandung angka.';
                    }

                    if (!username) {
                        regErrors.username = 'Username wajib diisi.';
                    } else if (!/^[a-zA-Z0-9_.]+$/.test(username)) {
                        regErrors.username = 'Username hanya boleh berisi huruf, angka, garis bawah (_), dan titik (.).';
                    }

                    if (!email) {
                        regErrors.email = 'Alamat email wajib diisi.';
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        regErrors.email = 'Format alamat email tidak valid.';
                    }

                    if (!whatsapp) {
                        regErrors.whatsapp = 'Nomor WhatsApp wajib diisi.';
                    } else if (whatsapp.replace(/\D/g, '').length < 8) {
                        regErrors.whatsapp = 'Nomor WhatsApp minimal 8 digit angka.';
                    }

                    if (!password) {
                        regErrors.password = 'Kata sandi wajib diisi.';
                    } else if (password.length < 8) {
                        regErrors.password = 'Kata sandi minimal 8 karakter.';
                    }

                    if (!passwordConfirm) {
                        regErrors.password_confirmation = 'Konfirmasi kata sandi wajib diisi.';
                    } else if (password && passwordConfirm !== password) {
                        regErrors.password_confirmation = 'Konfirmasi kata sandi tidak cocok.';
                    }

                    if (!termsChecked) {
                        regErrors.terms = 'Kamu harus menyetujui Syarat & Ketentuan.';
                    }

                    Object.assign(this.errors, regErrors);

                    const fieldOrder = [
                        ['nama', 'reg-nama'],
                        ['username', 'reg-username'],
                        ['email', 'reg-email'],
                        ['whatsapp', 'reg-wa'],
                        ['password', 'reg-password'],
                        ['password_confirmation', 'reg-password-confirm'],
                        ['terms', 'reg-terms'],
                    ];
                    const firstInvalid = fieldOrder.find(([key]) => Boolean(regErrors[key]));
                    if (firstInvalid) {
                        event.preventDefault();
                        document.getElementById(firstInvalid[1])?.focus();
                        return false;
                    }
                }

                if (type === 'login') {
                    this.errors.login = this.loginInput.trim() ? '' : 'Email atau Username wajib diisi.';
                    this.errors.login_password = this.loginPassword ? '' : 'Kata sandi wajib diisi.';
                    this.errors.login_credentials = '';

                    if (this.errors.login || this.errors.login_password) {
                        event.preventDefault();
                        const targetId = this.errors.login ? 'login-email' : 'login-password';
                        document.getElementById(targetId)?.focus();
                        return false;
                    }
                }

                this.isSubmitting = true;
                return true;
            }
        };
    }
</script>

</body>
</html>
