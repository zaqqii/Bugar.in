<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lengkapi Profil Akun — Bugar.in</title>
    <meta name="description" content="Lengkapi informasi profil akun Google kamu untuk mulai bergabung dengan komunitas olahraga Bugar.in.">

    @vite(['resources/css/app.css', 'resources/css/auth-user.css', 'resources/js/app.js'])
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
        .google-complete-wrapper {
            height: 100vh;
            min-height: 100vh;
            background: #f0f7ff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1.5rem 1rem;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow: hidden;
        }
        .google-complete-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.08);
            border: 1px solid #e2e8f0;
            max-width: 540px;
            width: 100%;
            max-height: calc(100vh - 3rem);
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: 2.25rem 2rem 2.25rem 2.25rem;
            box-sizing: border-box;
        }
        /* Custom sleek scrollbar near the card */
        .google-complete-card::-webkit-scrollbar {
            width: 6px;
        }
        .google-complete-card::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 999px;
            margin: 14px 0;
        }
        .google-complete-card::-webkit-scrollbar-thumb {
            background: #93c5fd;
            border-radius: 999px;
        }
        .google-complete-card::-webkit-scrollbar-thumb:hover {
            background: #2563eb;
        }
        .google-complete-card {
            scrollbar-width: thin;
            scrollbar-color: #93c5fd #f1f5f9;
        }
        @media (max-width: 640px) {
            .google-complete-wrapper {
                padding: 1rem 0.75rem;
            }
            .google-complete-card {
                padding: 1.5rem 1.25rem;
                max-height: calc(100vh - 2rem);
            }
        }
        .google-verified-box {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 14px;
            margin-bottom: 1.5rem;
        }
        .google-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid #2563eb;
            object-fit: cover;
        }
        .google-avatar-fallback {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #2563eb;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.25rem;
        }
        .google-verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.72rem;
            font-weight: 700;
            color: #10b981;
            background: #d1fae5;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body class="auth-user-body">

<div class="google-complete-wrapper" x-data="{
    isSubmitting: false,
    selectedSports: ['lari'],
    sports: [
        { id: 'lari', label: 'Lari / Running', icon: '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'14\' height=\'14\' fill=\'currentColor\' viewBox=\'0 0 24 24\'><path d=\'M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9l1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z\'/></svg>' },
        { id: 'padel', label: 'Padel', icon: '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'14\' height=\'14\' fill=\'currentColor\' viewBox=\'0 0 24 24\'><circle cx=\'12\' cy=\'12\' r=\'10\'/></svg>' },
        { id: 'sepeda', label: 'Sepeda / Cycling', icon: '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'14\' height=\'14\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\' stroke-width=\'2\'><circle cx=\'5\' cy=\'18\' r=\'3\'/><circle cx=\'19\' cy=\'18\' r=\'3\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M5 18l4-10 6 0 2 5M9 8h10l-3 7\'/></svg>' },
        { id: 'tenis', label: 'Tenis', icon: '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'14\' height=\'14\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\' stroke-width=\'2\'><circle cx=\'12\' cy=\'12\' r=\'10\'/><path stroke-linecap=\'round\' d=\'M4.93 4.93c4.15 4.15 4.15 9.9 0 14.14M19.07 4.93c-4.15 4.15-4.15 9.9 0 14.14\'/></svg>' },
        { id: 'badminton', label: 'Badminton', icon: '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'14\' height=\'14\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3 21l18-18M9 3l3 3-9 9-3-3 9-9z\'/></svg>' }
    ],
    toggleSport(id) {
        const idx = this.selectedSports.indexOf(id);
        if (idx === -1) {
            this.selectedSports.push(id);
        } else {
            this.selectedSports.splice(idx, 1);
        }
    }
}">
    <div class="google-complete-card">

        {{-- Header Brand --}}
        <div style="display: flex; align-items: center; justify-content: center; gap: 0.625rem; margin-bottom: 1.5rem;">
            <div class="auth-brand-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9l1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z"/>
                </svg>
            </div>
            <span class="auth-brand-name" style="font-size: 1.35rem;">Bugar.in</span>
        </div>

        <h1 class="auth-form-title" style="text-align: center; font-size: 1.4rem; margin-bottom: 0.5rem;">
            Satu Langkah Lagi,<br>
            <span class="auth-form-title--accent">Lengkapi Profil Olahragamu</span>
        </h1>
        <p class="auth-form-desc" style="text-align: center; margin-bottom: 1.5rem;">
            Akun Google kamu telah terhubung. Tentukan username dan nomor WhatsApp kamu untuk memulai.
        </p>

        {{-- Box Akun Google Terverifikasi --}}
        <div class="google-verified-box">
            @if(!empty($googleData['avatar']))
                <img src="{{ $googleData['avatar'] }}" alt="{{ $googleData['name'] }}" class="google-avatar">
            @else
                <div class="google-avatar-fallback">{{ strtoupper(substr($googleData['name'] ?? 'U', 0, 1)) }}</div>
            @endif
            <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $googleData['name'] }}
                </div>
                <div style="font-size: 0.8rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $googleData['email'] }}
                </div>
                <span class="google-verified-badge">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Terverifikasi via Google
                </span>
            </div>
        </div>

        {{-- Error Alert --}}
        @if($errors->any())
            <div class="auth-error-alert" role="alert" style="margin-bottom: 1.25rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Form Pelengkap Profil --}}
        <form method="POST" action="{{ route('auth.user.google-complete.post') }}" class="auth-form" @submit="isSubmitting = true">
            @csrf

            {{-- Username --}}
            <div class="auth-field auth-field--full" style="margin-bottom: 1rem;">
                <label class="auth-field-label" for="comp-username">Pilih Username</label>
                <div class="auth-field-wrap">
                    <svg class="auth-field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <input
                        type="text"
                        id="comp-username"
                        name="username"
                        class="auth-field-input {{ $errors->has('username') ? 'is-error' : '' }}"
                        placeholder="cth. dimas_k"
                        value="{{ old('username', $suggestedUsername) }}"
                        required>
                </div>
                <span class="auth-field-hint" style="margin-top: 4px; display: block;">Hanya huruf, angka, underscore (_), dan titik.</span>
            </div>

            {{-- WhatsApp --}}
            <div class="auth-field auth-field--full" style="margin-bottom: 1rem;">
                <label class="auth-field-label" for="comp-wa">Nomor WhatsApp / HP Aktif</label>
                <div class="auth-field-wrap">
                    <svg class="auth-field-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <input
                        type="tel"
                        id="comp-wa"
                        name="whatsapp"
                        class="auth-field-input {{ $errors->has('whatsapp') ? 'is-error' : '' }}"
                        placeholder="+62 812-xxxx-xxxx"
                        value="{{ old('whatsapp') }}"
                        required>
                </div>
                <span class="auth-field-hint" style="margin-top: 4px; display: block;">Digunakan untuk konfirmasi tiket dan reminder sesi olahraga.</span>
            </div>

            {{-- Minat Olahraga --}}
            <div class="auth-field auth-field--full" style="margin-bottom: 1.25rem;">
                <label class="auth-field-label">Minat Cabang Olahraga Utama</label>
                <div class="auth-sports-grid" style="margin-top: 0.5rem;">
                    <template x-for="sport in sports" :key="sport.id">
                        <button
                            type="button"
                            class="auth-sport-chip"
                            :class="{ 'active': selectedSports.includes(sport.id) }"
                            @click="toggleSport(sport.id)">
                            <span x-html="sport.icon" aria-hidden="true"></span>
                            <span x-text="sport.label"></span>
                        </button>
                    </template>
                </div>
                <template x-for="s in selectedSports" :key="s">
                    <input type="hidden" name="sports[]" :value="s">
                </template>
            </div>

            {{-- Terms & Conditions --}}
            <div class="auth-terms" style="margin-bottom: 1.5rem;">
                <label class="auth-terms-label" for="comp-terms">
                    <div class="auth-checkbox-wrap">
                        <input type="checkbox" id="comp-terms" name="terms" class="auth-checkbox" required checked>
                        <div class="auth-checkbox-custom" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <span>Saya menyetujui <a href="#" class="auth-link">Syarat &amp; Ketentuan</a> serta Kebijakan Privasi Bugar.in.</span>
                </label>
            </div>

            {{-- Submit CTA --}}
            <button
                type="submit"
                class="auth-submit-btn"
                :disabled="isSubmitting"
                :class="{ 'is-disabled': isSubmitting }">
                <span x-text="isSubmitting ? 'Menyimpan Profil...' : 'Simpan & Masuk ke Bugar.in'">Simpan &amp; Masuk ke Bugar.in</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>

    </div>
</div>

</body>
</html>
