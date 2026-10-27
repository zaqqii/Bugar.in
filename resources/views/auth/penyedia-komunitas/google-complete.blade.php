<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftarkan Komunitas via Google — Bugar.in</title>
    <meta name="description" content="Lengkapi informasi klub olahraga kamu untuk membuka portal penyelenggara komunitas di Bugar.in.">

    @vite(['resources/css/app.css', 'resources/css/auth-penyedia-komunitas.css', 'resources/js/app.js'])
    <style>
        html, body {
            min-height: 100%;
            margin: 0;
            padding: 0;
        }
        @media (min-height: 500px) {
            html, body {
                height: 100%;
                overflow: hidden;
            }
        }
        .google-host-wrapper {
            height: 100vh;
            min-height: 100vh;
            background: #fff5f5;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1.5rem 1rem;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow: hidden;
        }
        .google-host-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(225, 29, 72, 0.08);
            border: 1px solid #ffe4e6;
            max-width: 580px;
            width: 100%;
            max-height: calc(100vh - 3rem);
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: 2.25rem 2rem 2.25rem 2.25rem;
            box-sizing: border-box;
        }
        /* Custom sleek scrollbar near the host card */
        .google-host-card::-webkit-scrollbar {
            width: 6px;
        }
        .google-host-card::-webkit-scrollbar-track {
            background: #fff1f2;
            border-radius: 999px;
            margin: 14px 0;
        }
        .google-host-card::-webkit-scrollbar-thumb {
            background: #fca5a5;
            border-radius: 999px;
        }
        .google-host-card::-webkit-scrollbar-thumb:hover {
            background: #e11d48;
        }
        .google-host-card {
            scrollbar-width: thin;
            scrollbar-color: #fca5a5 #fff1f2;
        }
        @media (max-width: 640px) {
            .google-host-wrapper {
                padding: 1rem 0.75rem;
            }
            .google-host-card {
                padding: 1.5rem 1.25rem;
                max-height: calc(100vh - 2rem);
            }
        }
        .google-host-verified-box {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            border-radius: 14px;
            margin-bottom: 1.5rem;
        }
        .google-host-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid #e11d48;
            object-fit: cover;
        }
        .google-host-avatar-fallback {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e11d48;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.25rem;
        }
        .google-host-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.72rem;
            font-weight: 700;
            color: #e11d48;
            background: #ffe4e6;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body class="auth-host-body">

<div class="google-host-wrapper" x-data="{ isSubmitting: false }">
    <div class="google-host-card">

        {{-- Header Brand Host --}}
        <div style="display: flex; align-items: center; justify-content: center; gap: 0.625rem; margin-bottom: 1.5rem;">
            <div class="auth-host-brand-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9l1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z"/>
                </svg>
            </div>
            <div>
                <span class="auth-host-brand-name" style="font-size: 1.35rem;">Bugar.in</span>
                <span style="display: block; font-size: 0.75rem; color: #e11d48; font-weight: 700; text-transform: uppercase;">Portal Host Komunitas</span>
            </div>
        </div>

        <h1 class="auth-host-form-title" style="text-align: center; font-size: 1.4rem; margin-bottom: 0.5rem;">
            Daftarkan <span class="auth-host-form-title--accent">Klub Komunitasmu</span>
        </h1>
        <p class="auth-host-form-desc" style="text-align: center; margin-bottom: 1.5rem;">
            Akun Google kamu telah terverifikasi sebagai Host. Lengkapi detail komunitas untuk membuka portal jadwal & tiket.
        </p>

        {{-- Box Akun Google Host Terverifikasi --}}
        <div class="google-host-verified-box">
            @if(!empty($googleData['avatar']))
                <img src="{{ $googleData['avatar'] }}" alt="{{ $googleData['name'] }}" class="google-host-avatar">
            @else
                <div class="google-host-avatar-fallback">{{ strtoupper(substr($googleData['name'] ?? 'H', 0, 1)) }}</div>
            @endif
            <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $googleData['name'] }} (Host)
                </div>
                <div style="font-size: 0.8rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $googleData['email'] }}
                </div>
                <span class="google-host-badge">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Host Resmi Terverifikasi Google
                </span>
            </div>
        </div>

        {{-- Error alert --}}
        @if($errors->any())
            <div class="auth-host-alert-error" role="alert" style="margin-bottom: 1.25rem;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p>{{ $errors->first() }}</p>
            </div>
        @endif

        {{-- Form Pelengkap Komunitas --}}
        <form method="POST" action="{{ route('auth.penyedia-komunitas.google-complete.post') }}" @submit="isSubmitting = true">
            @csrf

            <div class="auth-host-field-grid">

                {{-- Nama Komunitas --}}
                <div class="auth-host-field-grid--full">
                    <label class="auth-host-label" for="comp-nama-komunitas">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Nama Komunitas / Klub Olahraga
                    </label>
                    <input
                        id="comp-nama-komunitas"
                        name="nama_komunitas"
                        type="text"
                        class="auth-host-input {{ $errors->has('nama_komunitas') ? 'is-error' : '' }}"
                        placeholder="Eks: Jakarta Sunday Runners"
                        value="{{ old('nama_komunitas') }}"
                        required>
                </div>

                {{-- Cabang Olahraga --}}
                <div>
                    <label class="auth-host-label" for="comp-sport-category">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                        </svg>
                        Cabang Olahraga Utama
                    </label>
                    <select
                        id="comp-sport-category"
                        name="sport_category"
                        class="auth-host-select {{ $errors->has('sport_category') ? 'is-error' : '' }}"
                        required>
                        <option value="" disabled {{ old('sport_category') ? '' : 'selected' }}>Pilih Cabor</option>
                        @foreach($sportCategories as $key => $label)
                            <option value="{{ $key }}" {{ old('sport_category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Kota Basecamp --}}
                <div>
                    <label class="auth-host-label" for="comp-kota">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Kota / Wilayah Basecamp
                    </label>
                    <input
                        id="comp-kota"
                        name="kota"
                        type="text"
                        class="auth-host-input {{ $errors->has('kota') ? 'is-error' : '' }}"
                        placeholder="Eks: Jakarta Selatan, DKI"
                        value="{{ old('kota') }}"
                        required>
                </div>

                {{-- WhatsApp Host --}}
                <div class="auth-host-field-grid--full">
                    <label class="auth-host-label" for="comp-whatsapp">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Nomor WhatsApp Host / Penanggung Jawab
                    </label>
                    <input
                        id="comp-whatsapp"
                        name="whatsapp"
                        type="tel"
                        class="auth-host-input {{ $errors->has('whatsapp') ? 'is-error' : '' }}"
                        placeholder="+62 812-xxxx-xxxx"
                        value="{{ old('whatsapp') }}"
                        required>
                </div>

            </div>

            {{-- Pakta Integritas Checkbox --}}
            <div class="auth-host-checkbox-row mt-4">
                <input
                    id="comp-terms"
                    name="terms"
                    type="checkbox"
                    class="auth-host-checkbox"
                    value="1"
                    required
                    checked>
                <label class="auth-host-checkbox-label" for="comp-terms">
                    Saya menyatakan komunitas ini beroperasi aktif dan menyetujui
                    <a href="#">Pakta Integritas Olahraga Bugar.in</a>
                    serta Kebijakan Privasi Penyelenggara.
                </label>
            </div>

            {{-- Submit CTA --}}
            <button
                type="submit"
                class="auth-host-btn-submit"
                :disabled="isSubmitting"
                :class="{ 'is-disabled': isSubmitting }">
                <span x-text="isSubmitting ? 'Mengaktifkan Komunitas...' : 'Aktifkan Komunitas & Buka Portal'">Aktifkan Komunitas &amp; Buka Portal</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>

    </div>
</div>

</body>
</html>
