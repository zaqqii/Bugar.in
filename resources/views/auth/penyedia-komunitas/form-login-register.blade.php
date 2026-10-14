<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar & Masuk Komunitas — Bugar.in</title>
    <meta name="description" content="Portal Penyelenggara & Komunitas Olahraga Bugar.in. Daftarkan komunitas olahraga kamu atau masuk ke portal host.">

    @vite(['resources/css/app.css', 'resources/css/auth-penyedia-komunitas.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="auth-host-body">

{{-- ============================================================
     WRAPPER Alpine.js — mode: 'register' | 'login'
     ============================================================ --}}
<div class="auth-host-page"
     x-data="{
        mode: '{{ old("form_type") === "login" ? "login" : (old("form_type") === "register" ? "register" : request("mode", $initialMode ?? "register")) }}',
        showPassReg: false,
        showPassLogin: false,
        switchTo(m) {
            this.mode = m;
            const url = new URL(window.location);
            url.searchParams.set('mode', m);
            window.history.pushState({}, '', url);
        },
        init() {
            @if(session('success') && !old('form_type'))
                // Setelah register berhasil, mulai di slide register sejenak lalu bergeser smooth ke slide login
                this.mode = 'register';
                setTimeout(() => {
                    this.switchTo('login');
                }, 100);
            @endif
        }
     }">

    {{-- ============================================================
         LEFT PANEL — 65% Form Area
         ============================================================ --}}
    <div class="auth-host-left">

        {{-- Header --}}
        <header class="auth-host-header">
            <a href="{{ route('landing') }}" class="auth-host-brand" aria-label="Kembali ke Bugar.in">
                <div class="auth-host-brand-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9l1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z"/>
                    </svg>
                </div>
                <div>
                    <span class="auth-host-brand-name">Bugar.in</span>
                    <span class="auth-host-brand-sub">Platform Komunitas &amp; Event Olahraga</span>
                </div>
            </a>

            {{-- Header Actions Switcher (Daftar Akun Baru kiri, Masuk kanan — persis User) --}}
            <div class="auth-header-actions" role="tablist">
                <button
                    type="button"
                    class="auth-header-btn"
                    :class="{ 'auth-header-btn--pill-active': mode === 'register' }"
                    @click="switchTo('register')"
                    id="btn-header-daftar"
                    role="tab"
                    :aria-selected="mode === 'register'">
                    Daftar Akun Baru
                </button>
                <button
                    type="button"
                    class="auth-header-btn"
                    :class="{ 'auth-header-btn--pill-active': mode === 'login' }"
                    @click="switchTo('login')"
                    id="btn-header-masuk"
                    role="tab"
                    :aria-selected="mode === 'login'">
                    Masuk
                </button>
            </div>
        </header>

        {{-- Slides Wrapper --}}
        <div class="auth-host-slides-wrapper">
            <div class="auth-host-slides-track"
                 :style="{ transform: mode === 'register' ? 'translateX(0%)' : 'translateX(-50%)' }">

                {{-- ================================================
                     SLIDE 1 — DAFTAR (Register)
                     ================================================ --}}
                <div class="auth-host-slide" id="slide-register" role="tabpanel" aria-labelledby="btn-header-daftar">
                    <div class="auth-host-form-scroll">

                        {{-- Badge --}}
                        <div class="auth-host-form-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            REGISTRASI KOMUNITAS BARU
                        </div>

                        <h1 class="auth-host-form-title">
                            Daftarkan <span class="auth-host-form-title--accent">Komunitas</span>mu
                        </h1>
                        <p class="auth-host-form-desc">
                            Buka portal penyelenggara untuk mengelola jadwal sesi, tiket event, absensi otomatis, dan verifikasi legalitas klub olahragamu.
                        </p>

                        {{-- Error alert register banner --}}
                        @if($errors->any() && old('form_type', 'register') === 'register')
                            <div class="auth-host-alert-error" role="alert">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p>{{ $errors->first() }}</p>
                            </div>
                        @endif

                        {{-- FORM REGISTER --}}
                        <form method="POST" action="{{ route('auth.penyedia-komunitas.register.post') }}" id="form-register">
                            @csrf
                            <input type="hidden" name="form_type" value="register">

                            <div class="auth-host-field-grid">

                                {{-- Nama Komunitas / Klub --}}
                                <div>
                                    <label class="auth-host-label" for="reg-nama-komunitas">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Nama Komunitas / Klub
                                    </label>
                                    <input
                                        id="reg-nama-komunitas"
                                        name="nama_komunitas"
                                        type="text"
                                        class="auth-host-input {{ $errors->has('nama_komunitas') ? 'is-error' : '' }}"
                                        placeholder="Eks: Jakarta Sunday Runners"
                                        value="{{ old('nama_komunitas') }}"
                                        autocomplete="organization"
                                        required>
                                    @error('nama_komunitas')
                                        <p class="auth-host-error-text">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Cabang Olahraga Utama --}}
                                <div>
                                    <label class="auth-host-label" for="reg-sport-category">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                                        </svg>
                                        Cabang Olahraga Utama
                                    </label>
                                    <select
                                        id="reg-sport-category"
                                        name="sport_category"
                                        class="auth-host-select {{ $errors->has('sport_category') ? 'is-error' : '' }}"
                                        required>
                                        <option value="" disabled {{ old('sport_category') ? '' : 'selected' }}>Pilih Cabor</option>
                                        @foreach($sportCategories as $key => $label)
                                            <option value="{{ $key }}" {{ old('sport_category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('sport_category')
                                        <p class="auth-host-error-text">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Kota / Wilayah Basecamp --}}
                                <div>
                                    <label class="auth-host-label" for="reg-kota">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Kota / Wilayah Basecamp
                                    </label>
                                    <input
                                        id="reg-kota"
                                        name="kota"
                                        type="text"
                                        class="auth-host-input {{ $errors->has('kota') ? 'is-error' : '' }}"
                                        placeholder="Eks: Jakarta Selatan, DKI"
                                        value="{{ old('kota') }}"
                                        required>
                                    @error('kota')
                                        <p class="auth-host-error-text">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Penanggung Jawab / Host (Ringkas & Rapi) --}}
                                <div>
                                    <label class="auth-host-label" for="reg-nama-host">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Penanggung Jawab / Host
                                    </label>
                                    <input
                                        id="reg-nama-host"
                                        name="nama_host"
                                        type="text"
                                        class="auth-host-input {{ $errors->has('nama_host') ? 'is-error' : '' }}"
                                        placeholder="Eks: Budi Santoso"
                                        value="{{ old('nama_host') }}"
                                        autocomplete="name"
                                        required>
                                    @error('nama_host')
                                        <p class="auth-host-error-text">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Email Resmi Komunitas --}}
                                <div>
                                    <label class="auth-host-label" for="reg-email">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        Email Resmi Komunitas
                                    </label>
                                    <input
                                        id="reg-email"
                                        name="email"
                                        type="email"
                                        class="auth-host-input {{ $errors->has('email') && old('form_type', 'register') === 'register' ? 'is-error' : '' }}"
                                        placeholder="host@namakomunitas.com"
                                        value="{{ old('email') }}"
                                        autocomplete="email"
                                        required>
                                    @if(old('form_type', 'register') === 'register')
                                        @error('email')
                                            <p class="auth-host-error-text">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                                {{-- Nomor WhatsApp Host --}}
                                <div>
                                    <label class="auth-host-label" for="reg-whatsapp">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        Nomor WhatsApp Host
                                    </label>
                                    <input
                                        id="reg-whatsapp"
                                        name="whatsapp"
                                        type="tel"
                                        class="auth-host-input {{ $errors->has('whatsapp') ? 'is-error' : '' }}"
                                        placeholder="+62 812-3456-7890"
                                        value="{{ old('whatsapp') }}"
                                        autocomplete="tel"
                                        required>
                                    @error('whatsapp')
                                        <p class="auth-host-error-text">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Kata Sandi --}}
                                <div class="auth-host-field-grid--full">
                                    <label class="auth-host-label" for="reg-password">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Kata Sandi Akun Host
                                    </label>
                                    <div class="auth-host-input-wrap">
                                        <input
                                            id="reg-password"
                                            name="password"
                                            :type="showPassReg ? 'text' : 'password'"
                                            class="auth-host-input {{ $errors->has('password') && old('form_type', 'register') === 'register' ? 'is-error' : '' }}"
                                            placeholder="Minimal 8 karakter, kombinasi huruf & angka"
                                            autocomplete="new-password"
                                            minlength="8"
                                            required>
                                        <button type="button" class="auth-host-eye-btn" @click="showPassReg = !showPassReg" aria-label="Toggle password visibility">
                                            <svg x-show="!showPassReg" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg x-show="showPassReg" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </button>
                                    </div>
                                    @if(old('form_type', 'register') === 'register')
                                        @error('password')
                                            <p class="auth-host-error-text">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                            </div>{{-- end field-grid --}}

                            {{-- Checkbox agreement --}}
                            <div class="auth-host-checkbox-row mt-4">
                                <input
                                    id="reg-terms"
                                    name="terms"
                                    type="checkbox"
                                    class="auth-host-checkbox {{ $errors->has('terms') ? 'outline outline-2 outline-red-400' : '' }}"
                                    value="1"
                                    required>
                                <label class="auth-host-checkbox-label" for="reg-terms">
                                    Saya menyatakan komunitas ini beroperasi aktif dan menyetujui
                                    <a href="#">Pakta Integritas Olahraga Bugar.in</a>
                                    serta Kebijakan Privasi Penyelenggara.
                                </label>
                            </div>
                            @error('terms')
                                <p class="auth-host-error-text mt-1">{{ $message }}</p>
                            @enderror

                            {{-- Submit --}}
                            <button type="submit" class="auth-host-btn-submit" id="btn-daftar-komunitas">
                                Daftarkan Komunitas Sekarang
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        </form>

                        {{-- Divider --}}
                        <div class="auth-host-divider">
                            <span>atau daftar dengan</span>
                        </div>

                        {{-- Social buttons --}}
                        <div class="auth-host-socials">
                            <a href="#" class="auth-host-social-btn">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                </svg>
                                Google
                            </a>
                            <a href="#" class="auth-host-social-btn">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#FC4C02">
                                    <path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0 4 13.828h4.172"/>
                                </svg>
                                Strava
                            </a>
                            <a href="#" class="auth-host-social-btn">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#000000">
                                    <path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701"/>
                                </svg>
                                Apple
                            </a>
                        </div>

                        <p class="auth-host-footer-link">
                            Sudah terdaftar sebagai Host?
                            <button type="button" class="auth-switch-link" @click="switchTo('login')">Masuk ke Portal Komunitas →</button>
                        </p>

                    </div>{{-- end form-scroll --}}
                </div>{{-- end slide register --}}


                {{-- ================================================
                     SLIDE 2 — MASUK (Login)
                     ================================================ --}}
                <div class="auth-host-slide" id="slide-login" role="tabpanel" aria-labelledby="btn-header-masuk">
                    <div class="auth-host-form-scroll">

                        {{-- Badge --}}
                        <div class="auth-host-form-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            PORTAL PENGELOLA KOMUNITAS
                        </div>

                        <h1 class="auth-host-form-title">
                            Masuk ke <span class="auth-host-form-title--accent">Komunitas</span>mu
                        </h1>
                        <p class="auth-host-form-desc">
                            Masuk ke portal pengelola untuk mengatur jadwal sesi, peserta event, dan absensi klub olahraga.
                        </p>

                        {{-- Error alert login banner --}}
                        @if($errors->any() && old('form_type') === 'login')
                            <div class="auth-host-alert-error" role="alert">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p>{{ $errors->first() }}</p>
                            </div>
                        @endif

                        {{-- Success flash login --}}
                        @if(session('success'))
                            <div class="auth-host-alert-success" role="alert">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <p>{{ session('success') }}</p>
                            </div>
                        @endif

                        {{-- FORM LOGIN --}}
                        <form method="POST" action="{{ route('auth.penyedia-komunitas.login.post') }}" id="form-login" class="mt-4">
                            @csrf
                            <input type="hidden" name="form_type" value="login">

                            {{-- Email --}}
                            <div class="mb-4">
                                <label class="auth-host-label" for="login-email">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Email Penyelenggara / Host
                                </label>
                                <div class="auth-host-input-wrap">
                                    <input
                                        id="login-email"
                                        name="email"
                                        type="email"
                                        class="auth-host-input {{ $errors->has('email') && old('form_type') === 'login' ? 'is-error' : '' }}"
                                        placeholder="host@namakomunitas.com"
                                        value="{{ old('email', session('registered_email')) }}"
                                        autocomplete="email"
                                        required>
                                </div>
                                @if(old('form_type') === 'login')
                                    @error('email')
                                        <p class="auth-host-error-text">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>

                            {{-- Password --}}
                            <div class="mb-3">
                                <div class="auth-host-remember-row">
                                    <label class="auth-host-label mb-0" for="login-password">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Password
                                    </label>
                                    <a href="#" class="auth-host-forgot-link">Lupa Password?</a>
                                </div>
                                <div class="auth-host-input-wrap">
                                    <input
                                        id="login-password"
                                        name="password"
                                        :type="showPassLogin ? 'text' : 'password'"
                                        class="auth-host-input {{ $errors->has('password') && old('form_type') === 'login' ? 'is-error' : '' }}"
                                        placeholder="••••••••••••"
                                        autocomplete="current-password"
                                        required>
                                    <button type="button" class="auth-host-eye-btn" @click="showPassLogin = !showPassLogin" aria-label="Toggle password visibility">
                                        <svg x-show="!showPassLogin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showPassLogin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                                @if(old('form_type') === 'login')
                                    @error('password')
                                        <p class="auth-host-error-text">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>

                            {{-- Remember me --}}
                            <label class="auth-host-remember-label mb-4">
                                <input type="checkbox" name="remember" class="auth-host-remember-check">
                                Ingat sesi saya di perangkat ini
                            </label>

                            {{-- Submit --}}
                            <button type="submit" class="auth-host-btn-submit" id="btn-masuk-host">
                                Masuk ke Portal Komunitas
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        </form>

                        {{-- Divider --}}
                        <div class="auth-host-divider">
                            <span>atau masuk dengan</span>
                        </div>

                        {{-- Social buttons --}}
                        <div class="auth-host-socials">
                            <a href="#" class="auth-host-social-btn">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                </svg>
                                Google
                            </a>
                            <a href="#" class="auth-host-social-btn">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#FC4C02">
                                    <path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0 4 13.828h4.172"/>
                                </svg>
                                Strava
                            </a>
                            <a href="#" class="auth-host-social-btn">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#000000">
                                    <path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701"/>
                                </svg>
                                Apple
                            </a>
                        </div>

                        <p class="auth-host-footer-link">
                            Ingin mendaftarkan klub olahraga baru?
                            <button type="button" class="auth-switch-link" @click="switchTo('register')">Daftar Komunitas Sekarang →</button>
                        </p>

                    </div>{{-- end form-scroll --}}
                </div>{{-- end slide login --}}

            </div>{{-- end slides-track --}}
        </div>{{-- end slides-wrapper --}}

    </div>{{-- end left panel --}}


    {{-- ============================================================
         RIGHT PANEL — 35% Visual / Promo Panel
         Design identical to User Auth (Image 2) with community copy
         ============================================================ --}}
    <div class="auth-host-right" aria-hidden="true">
        <div class="auth-host-right__overlay"></div>
        <div class="auth-host-right__content">

            {{-- Badge top --}}
            <div class="auth-promo-badge">
                <span class="auth-promo-badge-dot"></span>
                1.480+ Komunitas Terverifikasi
            </div>
            <div class="auth-promo-location">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Jabodetabek • Bali • SBY • BDG • YOG
            </div>

            {{-- Heading --}}
            <p class="auth-promo-hashtag">#HOSTKOMUNITAS</p>
            <h2 class="auth-promo-heading">
                Kembangkan klubmu,<br>
                kelola event seru,<br>
                jangkau ribuan pelari.
            </h2>

            {{-- Stats Grid (Vertical stacked wide cards matching Image 2) --}}
            <div class="auth-stats-grid">
                {{-- Stat 1: Partisipan --}}
                <div class="auth-stat">
                    <div class="auth-stat-icon auth-stat-icon--blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="auth-stat-num">420+</div>
                        <div class="auth-stat-label">PARTISIPAN / BULAN</div>
                    </div>
                </div>

                {{-- Stat 2: Kehadiran via QR --}}
                <div class="auth-stat">
                    <div class="auth-stat-icon auth-stat-icon--teal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.95 11.95 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="auth-stat-num">98.4%</div>
                        <div class="auth-stat-label">KEHADIRAN VIA QR CHECK-IN</div>
                    </div>
                </div>

                {{-- Stat 3: Biaya Registrasi Host --}}
                <div class="auth-stat">
                    <div class="auth-stat-icon auth-stat-icon--orange">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="auth-stat-num">Rp 0,–</div>
                        <div class="auth-stat-label">BIAYA REGISTRASI HOST</div>
                    </div>
                </div>
            </div>

            {{-- Testimonial Card --}}
            <div class="auth-testimonial">
                <div class="auth-testimonial-header">
                    <div class="auth-testimonial-avatar">
                        BS
                    </div>
                    <div>
                        <div class="auth-testimonial-name">Budi Santoso</div>
                        <div class="auth-testimonial-role">Founder Jakarta Sunday Runners</div>
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
                    "Mengelola 3.400 pelari aktif kini jauh lebih teratur. Sistem tiket dan absensi Bugar.in memangkas 90% waktu koordinasi manual kami."
                </blockquote>
                <div class="auth-testimonial-footer">
                    <div class="auth-testimonial-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.95 11.95 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Penyelenggara Terverifikasi
                    </div>
                    <span class="auth-testimonial-meta">ID: 2849-JSR</span>
                </div>
            </div>

        </div>{{-- .auth-host-right__content --}}
    </div>{{-- .auth-host-right --}}

</div>{{-- .auth-host-page --}}

</body>
</html>
