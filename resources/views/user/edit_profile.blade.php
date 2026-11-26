<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f8faff]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Edit Profil — Bugar.in</title>
    <meta name="description" content="Kelola informasi akun dan preferensi olahraga Anda di Bugar.in.">

    <!-- Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN standalone guarantee) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        cyan: {
                            50: '#ecfeff',
                            100: '#cffafe',
                            200: '#a5f3fc',
                            300: '#67e8f9',
                            400: '#22d3ee',
                            500: '#06b6d4',
                            600: '#0891b2',
                            700: '#0e7490',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased flex flex-col justify-between"
      x-data="editProfileApp()"
      x-cloak>

    @php
        $avatarUrl = $user->avatar_url;
        $userInitials = collect(explode(' ', $user->name ?? 'User'))
            ->map(fn($w) => strtoupper(substr($w, 0, 1)))
            ->take(2)->implode('');
    @endphp

    <!-- ==============================================================
         MAIN CONTENT AREA
         ============================================================== -->
    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">

        <!-- Tombol Kembali + Breadcrumb Nav -->
        <div class="flex items-center justify-between gap-3 mb-5 sm:mb-6">
            <a href="{{ route('user.dashboard') }}"
               class="inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs sm:text-sm font-semibold text-slate-700 shadow-2xs hover:border-blue-400 hover:text-blue-600 transition-all active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali</span>
            </a>

            <!-- Breadcrumb Nav -->
            <nav class="flex items-center gap-1.5 text-xs sm:text-sm text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('user.dashboard') }}" class="text-slate-500 hover:text-blue-600 font-medium transition-colors hidden sm:inline">
                    Dashboard
                </a>
                <span class="text-slate-300 hidden sm:inline">/</span>
                <span class="font-bold text-slate-900">Edit Profil</span>
            </nav>
        </div>

        <!-- Flash Success Notification -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Validation Errors Notification -->
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold shadow-xs">
                <div class="font-bold text-sm mb-1.5 flex items-center gap-1.5 text-rose-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>Gagal menyimpan perubahan:</span>
                </div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ==============================================================
             SINGLE FORM ENCLOSING EVERYTHING (Avatar, Inputs, Actions)
             ============================================================== -->
        <form method="POST"
              action="{{ route('user.profile.update') }}"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- ==============================================================
                 2. HERO PROFILE BANNER CARD
                 ============================================================== -->
            <div class="rounded-2xl sm:rounded-3xl overflow-hidden bg-white border border-slate-200/80 shadow-xs mb-6 sm:mb-8">
                <!-- Top Gradient Wave Banner -->
                <div class="relative h-36 sm:h-48 md:h-52 bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 overflow-hidden">
                    <svg class="absolute inset-0 w-full h-full object-cover opacity-20 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 300" preserveAspectRatio="none">
                        <path d="M0,0 C300,120 600,-40 900,100 C1050,170 1150,110 1200,90 L1200,300 L0,300 Z" fill="#ffffff"/>
                        <path d="M0,80 C200,200 500,60 800,160 C1000,220 1150,140 1200,120 L1200,300 L0,300 Z" fill="#ffffff" opacity="0.3"/>
                    </svg>
                    <div class="absolute -right-16 -top-16 w-80 h-80 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                    <div class="absolute left-1/3 bottom-0 w-64 h-64 rounded-full bg-cyan-300/20 blur-3xl pointer-events-none"></div>

                    <!-- Top Right Badge -->
                    <div class="absolute top-3 right-3 sm:top-5 sm:right-6">
                        @if($user->email_verified_at)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-[10px] sm:text-[11px] font-bold tracking-wider uppercase shadow-xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-300 animate-pulse"></span>
                                AKUN TERVERIFIKASI
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-[10px] sm:text-[11px] font-bold tracking-wider uppercase shadow-xs">
                                AKUN MEMBER
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Profile Info Bar (Under Banner) -->
                <div class="px-4 sm:px-8 pb-5 sm:pb-6 pt-3 relative">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 sm:gap-6">
                        
                        <!-- Left: Avatar + Details -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-end gap-3.5 sm:gap-5">
                            
                            <!-- Avatar Overlapping Banner -->
                            <div class="relative -mt-14 sm:-mt-20 flex-shrink-0">
                                <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-white shadow-md overflow-hidden bg-gradient-to-br from-blue-600 to-cyan-500 relative flex items-center justify-center text-white">
                                    
                                    <!-- Dynamic Avatar Image (Shown if user uploaded or selects a file) -->
                                    <template x-if="avatarPreview">
                                        <img :src="avatarPreview"
                                             alt="Foto Profil"
                                             class="w-full h-full object-cover"
                                             x-on:error="avatarPreview = null">
                                    </template>

                                    <!-- Clean Initials Fallback (Shown when user has NOT uploaded any photo or image error) -->
                                    <template x-if="!avatarPreview">
                                        <div class="flex items-center justify-center w-full h-full font-extrabold text-2xl sm:text-3xl tracking-tight select-none">
                                            {{ $userInitials }}
                                        </div>
                                    </template>

                                    <!-- Hidden File Input for Avatar (Inside the form!) -->
                                    <input type="file"
                                           id="avatar-input"
                                           name="avatar"
                                           accept="image/*"
                                           @change="handleAvatarChange"
                                           class="hidden">
                                </div>

                                <!-- Camera Button (Trigger Upload) -->
                                <button type="button"
                                        @click="document.getElementById('avatar-input').click()"
                                        class="absolute bottom-0 right-0 sm:bottom-1 sm:right-1 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-md border-2 border-white hover:bg-blue-700 transition-all cursor-pointer active:scale-95"
                                        title="Ubah Foto Profil">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Name, Badges & Meta -->
                            <div class="space-y-1 sm:space-y-1.5 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight truncate"
                                        x-text="form.name || '{{ addslashes($user->name) }}'">
                                        {{ $user->name }}
                                    </h1>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-cyan-50 border border-cyan-200/80 text-cyan-700 text-[11px] sm:text-xs font-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-cyan-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Member Terverifikasi
                                    </span>
                                </div>

                                <!-- Meta Details row -->
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs text-slate-500 font-medium">
                                    <span class="font-semibold text-blue-600 truncate" x-text="form.username ? '@' + form.username : '{{ !empty($user->username) ? '@' . $user->username : '@' . strtolower(str_replace(' ', '', $user->name)) }}'">
                                        {{ !empty($user->username) ? '@' . $user->username : '@' . strtolower(str_replace(' ', '', $user->name)) }}
                                    </span>
                                    <span class="text-slate-300">•</span>
                                    <span class="inline-flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Bergabung {{ $user->created_at ? $user->created_at->translatedFormat('M Y') : '2026' }}
                                    </span>
                                </div>
                            </div>

                        </div>

                        <!-- Right: TOTAL POIN Card -->
                        <div class="flex flex-row sm:flex-col items-center sm:items-end justify-between sm:justify-center pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                            <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase sm:mb-1.5">TOTAL POIN</span>
                            <div class="rounded-xl sm:rounded-2xl border border-amber-200/90 bg-gradient-to-r from-amber-50 via-orange-50/60 to-amber-50 px-3.5 py-1.5 sm:px-4 sm:py-2 flex items-center gap-2.5 shadow-2xs">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-amber-500/15 text-amber-600 flex items-center justify-center font-bold text-sm">
                                    ⚡
                                </div>
                                <div>
                                    <div class="flex items-baseline gap-1">
                                        <span class="font-black text-base sm:text-lg text-slate-900 font-mono tracking-tight leading-none">
                                            {{ number_format($user->total_points ?? 0, 0, ',', '.') }}
                                        </span>
                                        <span class="text-[11px] sm:text-xs font-bold text-amber-600 uppercase">PTS</span>
                                    </div>
                                    <span class="hidden sm:block text-[9px] font-medium text-slate-500 leading-tight">Poin Tersimpan</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ==============================================================
                 3. FORM CONTENT AREA
                 ============================================================== -->
            <div class="space-y-6 sm:space-y-8">
                    
                <!-- CARD 1: INFORMASI AKUN -->
                <div class="rounded-2xl sm:rounded-3xl border border-slate-200/80 bg-white p-4 sm:p-7 md:p-8 shadow-xs">
                    
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-5 sm:pb-6 mb-5 sm:mb-6 border-b border-slate-100">
                        <div class="flex items-center gap-3 sm:gap-3.5">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Informasi Akun</h2>
                                <p class="text-[11px] sm:text-xs text-slate-500">Identitas personal dan rincian kontak utama akun Anda</p>
                            </div>
                        </div>
                    </div>

                        <!-- Fields Grid -->
                        <div class="space-y-5">
                            
                            <!-- Row 1: Nama Lengkap & Username Publik -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <!-- Nama Lengkap -->
                                <div>
                                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <input type="text"
                                               name="name"
                                               id="name"
                                               x-model="form.name"
                                               required
                                               class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all outline-none">
                                    </div>
                                </div>

                                <!-- Username Publik -->
                                <div>
                                    <label for="username" class="block text-xs font-bold text-slate-700 mb-1.5">Username Publik</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold text-sm">
                                            @
                                        </div>
                                        <input type="text"
                                               name="username"
                                               id="username"
                                               x-model="form.username"
                                               placeholder="username_anda"
                                               class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all outline-none">
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2: Email Akun & Nomor WhatsApp -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <!-- Email Akun -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label for="email" class="block text-xs font-bold text-slate-700">Email Akun</label>
                                        @if($user->email_verified_at)
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-cyan-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                                Terverifikasi
                                            </span>
                                        @endif
                                    </div>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <input type="email"
                                               id="email"
                                               value="{{ $user->email }}"
                                               readonly
                                               disabled
                                               class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-100 border border-slate-200 text-sm font-semibold text-slate-500 cursor-not-allowed select-none outline-none">
                                    </div>
                                    <p class="mt-1 text-[11px] text-slate-400">Email akun bersifat tetap dan tidak dapat diubah.</p>
                                </div>

                                <!-- Nomor WhatsApp / Telepon -->
                                <div>
                                    <label for="phone" class="block text-xs font-bold text-slate-700 mb-1.5">Nomor WhatsApp / Telepon</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                        </div>
                                        <input type="tel"
                                               name="phone"
                                               id="phone"
                                               x-model="form.phone"
                                               placeholder="+62 812-xxxx-xxxx"
                                               class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all outline-none">
                                    </div>
                                </div>
                            </div>

                            <!-- Row 3: Bio -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="bio" class="block text-xs font-bold text-slate-700">Bio</label>
                                    <span class="text-xs font-semibold text-slate-400"
                                          :class="bioCount > 150 ? 'text-amber-600 font-bold' : ''"
                                          x-text="`${bioCount} / 160 Karakter`">
                                    </span>
                                </div>
                                <textarea name="bio"
                                          id="bio"
                                          x-model="form.bio"
                                          maxlength="160"
                                          rows="3"
                                          class="w-full px-4 py-3 rounded-2xl bg-slate-50/70 border border-slate-200 text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all outline-none resize-none leading-relaxed"
                                          placeholder="Tuliskan bio atau deskripsi profil kebugaran Anda..."></textarea>
                                <p class="text-xs text-blue-600 font-medium mt-1.5 flex items-center gap-1">
                                    Bio ini tampil pada leaderboard komunitas, tim estafet, dan profil penantang Anda.
                                </p>
                            </div>

                        </div>
                    </div>

                    <!-- CARD 2: PREFERENSI OLAHRAGA -->
                    <div class="rounded-2xl sm:rounded-3xl border border-slate-200/80 bg-white p-4 sm:p-7 md:p-8 shadow-xs">
                        
                        <!-- Header -->
                        <div class="flex items-center justify-between pb-5 sm:pb-6 mb-5 sm:mb-6 border-b border-slate-100">
                            <div class="flex items-center gap-3 sm:gap-3.5">
                                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Preferensi Olahraga</h2>
                                    <p class="text-[11px] sm:text-xs text-slate-500">Personalisasi rekomendasi komunitas dan intensitas kegiatan Anda</p>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="space-y-6">
                            
                            <!-- Hidden inputs: data preferensi dikirim ke ProfileController@update -->
                            <input type="hidden" name="preferences_submitted" value="1">
                            <template x-for="sport in form.sports" :key="sport">
                                <input type="hidden" name="sports[]" :value="sport">
                            </template>
                            <input type="hidden" name="fitness_level" :value="form.fitness_level">
                            <input type="hidden" name="weekly_target" :value="form.weekly_target">
                            <input type="hidden" name="sparring_open" value="{{ $user->sparring_open ? 1 : 0 }}">

                            <!-- 1. Cabang Olahraga Favorit (Multi-Select Pills dengan Pilihan Lengkap) -->
                            <div>
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700">
                                            Cabang Olahraga Favorit <span class="text-slate-400 font-normal">(Bisa pilih lebih dari satu)</span>
                                        </label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700"
                                              x-text="`${form.sports.length} cabor dipilih`">
                                        </span>
                                        <button type="button"
                                                x-show="form.sports.length > 0"
                                                @click="form.sports = []"
                                                class="text-xs text-rose-500 hover:text-rose-700 font-semibold cursor-pointer">
                                            Reset
                                        </button>
                                    </div>
                                </div>

                                    <!-- Category Tabs -->
                                    <div class="flex items-center gap-1 overflow-x-auto pb-1 max-w-full no-scrollbar">
                                        <template x-for="cat in sportCategories" :key="cat">
                                            <button type="button"
                                                    @click="selectedSportCategory = cat"
                                                    :class="selectedSportCategory === cat
                                                        ? 'bg-blue-600 text-white font-bold'
                                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                                                    class="px-2.5 py-1 rounded-full text-[11px] whitespace-nowrap transition-colors cursor-pointer">
                                                <span x-text="cat"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Sport Pills Grid -->
                                <div class="flex flex-wrap gap-2 max-h-64 overflow-y-auto p-1 border border-slate-100 rounded-2xl bg-slate-50/40">
                                    <template x-for="sport in filteredSports" :key="sport.id">
                                        <button type="button"
                                                @click="toggleSport(sport.id)"
                                                :class="form.sports.includes(sport.id)
                                                    ? 'bg-blue-600 text-white border-blue-600 shadow-xs shadow-blue-300 font-bold'
                                                    : 'bg-white text-slate-700 border-slate-200 hover:border-blue-300 hover:text-blue-600 font-semibold'"
                                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full border text-xs transition-all duration-150 cursor-pointer select-none active:scale-95">
                                            <span x-text="sport.icon"></span>
                                            <span x-text="sport.label"></span>
                                            <span x-show="form.sports.includes(sport.id)" class="text-xs font-extrabold ml-0.5">✓</span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- 2. Dropdowns: Level Kebugaran & Target Frekuensi -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mt-4 mb-2">Level Kebugaran Saat Ini</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </div>
                                        <select x-model="form.fitness_level"
                                                class="w-full pl-10 pr-8 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all outline-none appearance-none cursor-pointer">
                                            <option value="beginner">Beginner / Pemula</option>
                                            <option value="intermediate">Intermediate / Rekreasional Aktif</option>
                                            <option value="advanced">Advanced / Kompetitif</option>
                                            <option value="elite">Elite / Semi-Pro</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mt-4 mb-2">Target Frekuensi Mingguan</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <select x-model="form.weekly_target"
                                                class="w-full pl-10 pr-8 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all outline-none appearance-none cursor-pointer">
                                            <option value="1-2">1-2 Sesi per minggu (Santai)</option>
                                            <option value="3-4">3-4 Sesi per minggu (Target Reguler)</option>
                                            <option value="5-6">5-6 Sesi per minggu (Disiplin)</option>
                                            <option value="7">Setiap Hari (High Volume)</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                </div>

            <!-- ==============================================================
                 3. BOTTOM ACTION BAR
                 ============================================================== -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-xs flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3 sm:gap-4 mt-6 sm:mt-8">
                
                <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Perubahan data profil akan langsung diperbarui di akun Anda.</span>
                </div>

                <div class="flex items-stretch sm:items-center gap-2.5 w-full sm:w-auto justify-end">
                    <a href="{{ route('user.dashboard') }}"
                       class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors cursor-pointer text-center flex items-center justify-center">
                        Kembali
                    </a>

                    <button type="submit"
                            class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xs transition-all cursor-pointer active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>

            </div>

        </form>

    </main>

    <!-- ==============================================================
         ALPINE.JS LOGIC SCRIPT
         ============================================================== -->
    <script>
        function editProfileApp() {
            const rawSports = @json($availableSports ?? \App\Models\User::availableSports());
            const initialSports = @json(old('sports', $user->sports ?? []));

            const aliasMap = {
                'running': 'lari',
                'cycling': 'sepeda',
                'swimming': 'renang',
                'strength': 'gym',
                'fitness': 'gym',
                'basketball': 'basket',
                'trail': 'hiking',
                'outdoor': 'hiking',
                'martial': 'bela_diri',
                'martial_arts': 'bela_diri',
                'tennis': 'tenis',
                'table_tennis': 'tenis_meja',
                'football': 'sepakbola',
                'soccer': 'sepakbola',
                'volleyball': 'voli',
            };

            const normalizedSports = initialSports.map(s => aliasMap[s] || s);

            return {
                avatarPreview: @json($avatarUrl),

                form: {
                    name: @json(old('name', $user->name ?? '')),
                    username: @json(old('username', $user->username ?? '')),
                    email: @json(old('email', $user->email ?? '')),
                    phone: @json(old('phone', $user->phone ?? '')),
                    bio: @json(old('bio', $user->bio ?? '')),
                    sports: normalizedSports,
                    fitness_level: @json(old('fitness_level', $user->fitness_level ?? 'intermediate')),
                    weekly_target: @json(old('weekly_target', $user->weekly_target ?? '3-4')),
                    sparring_open: @json((bool) old('sparring_open', $user->sparring_open ?? true))
                },

                // Daftar Cabor Favorit Lengkap
                rawSports: rawSports,
                searchSport: '',
                selectedSportCategory: 'Semua',
                sportCategories: ['Semua', 'Cardio', 'Raket', 'Tim', 'Strength & Fitness', 'Outdoor & Combat', 'Akurasi & Lainnya'],

                get sportsList() {
                    return this.rawSports;
                },

                get filteredSports() {
                    return this.rawSports.filter(sport => {
                        const matchesCat = this.selectedSportCategory === 'Semua' || sport.category === this.selectedSportCategory;
                        const query = this.searchSport.toLowerCase().trim();
                        const matchesQuery = !query ||
                            sport.label.toLowerCase().includes(query) ||
                            sport.id.toLowerCase().includes(query) ||
                            (sport.aliases && sport.aliases.some(a => a.toLowerCase().includes(query)));
                        return matchesCat && matchesQuery;
                    });
                },

                get bioCount() {
                    return (this.form.bio || '').length;
                },

                toggleSport(id) {
                    if (this.form.sports.includes(id)) {
                        this.form.sports = this.form.sports.filter(s => s !== id);
                    } else {
                        this.form.sports.push(id);
                    }
                },

                // Handle Preview Foto Avatar dari Komputer
                handleAvatarChange(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.avatarPreview = URL.createObjectURL(file);
                    }
                }
            }
        }
    </script>
</body>
</html>
