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
        $avatarExists = $user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar);
        $userInitials = collect(explode(' ', $user->name ?? 'User'))
            ->map(fn($w) => strtoupper(substr($w, 0, 1)))
            ->take(2)->implode('');
    @endphp

    <!-- ==============================================================
         MAIN CONTENT AREA
         ============================================================== -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <!-- Tombol Kembali + Breadcrumb Nav -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <a href="{{ route('user.dashboard') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-700 shadow-2xs hover:border-blue-400 hover:text-blue-600 transition-all active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali ke Dashboard</span>
            </a>

            <!-- Breadcrumb Nav -->
            <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
                <a href="{{ route('user.dashboard') }}" class="inline-flex items-center gap-1.5 text-blue-600 font-medium hover:text-blue-700 hover:underline">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Akun Saya</span>
                </a>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
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
            <div class="rounded-3xl overflow-hidden bg-white border border-slate-200/80 shadow-xs mb-8">
                <!-- Top Gradient Wave Banner -->
                <div class="relative h-44 sm:h-52 bg-gradient-to-r from-blue-700 via-blue-600 to-sky-400 overflow-hidden">
                    <svg class="absolute inset-0 w-full h-full object-cover opacity-20 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 300" preserveAspectRatio="none">
                        <path d="M0,0 C300,120 600,-40 900,100 C1050,170 1150,110 1200,90 L1200,300 L0,300 Z" fill="#ffffff"/>
                        <path d="M0,80 C200,200 500,60 800,160 C1000,220 1150,140 1200,120 L1200,300 L0,300 Z" fill="#ffffff" opacity="0.3"/>
                    </svg>
                    <div class="absolute -right-16 -top-16 w-80 h-80 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                    <div class="absolute left-1/3 bottom-0 w-64 h-64 rounded-full bg-cyan-300/20 blur-3xl pointer-events-none"></div>

                    <!-- Top Right Badge -->
                    <div class="absolute top-4 right-5 sm:top-5 sm:right-6">
                        @if($user->email_verified_at)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-[11px] font-bold tracking-wider uppercase shadow-xs">
                                <span class="w-2 h-2 rounded-full bg-cyan-300 animate-pulse"></span>
                                AKUN TERVERIFIKASI
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-[11px] font-bold tracking-wider uppercase shadow-xs">
                                AKUN MEMBER
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Profile Info Bar (Under Banner) -->
                <div class="px-6 sm:px-8 pb-6 pt-3 relative">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                        
                        <!-- Left: Avatar + Details -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-end gap-5">
                            
                            <!-- Avatar Overlapping Banner -->
                            <div class="relative -mt-16 sm:-mt-20 flex-shrink-0">
                                <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-full border-4 border-white shadow-md overflow-hidden bg-gradient-to-br from-blue-600 to-cyan-500 relative flex items-center justify-center text-white">
                                    
                                    <!-- Dynamic Avatar Image (Shown ONLY if user uploaded or selects a file) -->
                                    <template x-if="avatarPreview">
                                        <img :src="avatarPreview"
                                             alt="Foto Profil"
                                             class="w-full h-full object-cover">
                                    </template>

                                    <!-- Clean Initials Fallback (Shown when user has NOT uploaded any photo) -->
                                    <template x-if="!avatarPreview">
                                        <div class="flex items-center justify-center w-full h-full font-extrabold text-3xl tracking-tight select-none">
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
                                        class="absolute bottom-1 right-1 w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-md border-2 border-white hover:bg-blue-700 transition-all cursor-pointer active:scale-95"
                                        title="Ubah Foto Profil">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Name, Badges & Meta -->
                            <div class="space-y-1.5">
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight"
                                        x-text="form.name || '{{ addslashes($user->name) }}'">
                                        {{ $user->name }}
                                    </h1>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-cyan-50 border border-cyan-200/80 text-cyan-700 text-xs font-semibold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-cyan-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Member Terverifikasi
                                    </span>
                                </div>

                                <!-- Meta Details row -->
                                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 font-medium">
                                    <span class="font-semibold text-blue-600" x-text="form.username ? '@' + form.username : '{{ !empty($user->username) ? '@' . $user->username : '@' . strtolower(str_replace(' ', '', $user->name)) }}'">
                                        {{ !empty($user->username) ? '@' . $user->username : '@' . strtolower(str_replace(' ', '', $user->name)) }}
                                    </span>
                                    <span class="text-slate-300">•</span>
                                    <span class="inline-flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Member aktif sejak {{ $user->created_at ? $user->created_at->translatedFormat('M Y') : '2026' }}
                                    </span>
                                </div>
                            </div>

                        </div>

                        <!-- Right: STATUS ATLETIK Card -->
                        <div class="flex flex-col sm:items-end">
                            <span class="text-[10px] font-bold tracking-wider text-slate-400 uppercase mb-1.5">STATUS ATLETIK</span>
                            <div class="rounded-2xl border border-cyan-200/80 bg-gradient-to-r from-cyan-50/80 to-blue-50/50 p-2.5 sm:px-4 sm:py-2 flex items-center gap-3 shadow-xs">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-cyan-500/15 text-cyan-600 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-cyan-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <span class="font-extrabold text-sm text-cyan-800">
                                        Level {{ max(1, floor(($user->total_points ?? 0) / 300) + 1) }} • {{ $user->total_points ?? 0 }} Poin
                                    </span>
                                </div>
                                <div class="w-7 h-7 rounded-lg bg-white text-cyan-500 flex items-center justify-center shadow-xs border border-cyan-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-cyan-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.527.82-1.124 1.906-1.515 2.894-.37.935-.615 1.848-.615 2.558 0 .195.016.386.046.57a3.504 3.504 0 01-1.39-1.282 1 1 0 00-1.637.954C5.356 10.45 6.7 13 9 13c1.722 0 3.04-1.235 3.65-2.73.313-.77.45-1.574.45-2.27 0-.964-.265-1.92-.705-2.947zm-1.87 9.87a4.975 4.975 0 01-1.525.577C8.425 13.14 8 13.528 8 14c0 .828.895 1.5 2 1.5s2-.672 2-1.5c0-.472-.425-.86-.975-1.073z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ==============================================================
                 3. FORM 2-COLUMN LAYOUT
                 ============================================================== -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- LEFT COLUMN (8 COLS): Informasi Akun & Preferensi -->
                <div class="lg:col-span-8 space-y-8">
                    
                    <!-- CARD 1: INFORMASI AKUN (Langkah 1 dari 3) -->
                    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-xs">
                        
                        <!-- Header -->
                        <div class="flex items-center justify-between pb-6 mb-6 border-b border-slate-100">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Informasi Akun</h2>
                                    <p class="text-xs text-slate-500">Identitas personal dan rincian kontak utama Anda di platform</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-xs font-semibold">
                                Langkah 1 dari 2
                            </span>
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
                                        <label for="email" class="block text-xs font-bold text-slate-700">Email Akun <span class="text-rose-500">*</span></label>
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
                                               name="email"
                                               id="email"
                                               x-model="form.email"
                                               required
                                               class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50/70 border border-slate-200 text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all outline-none">
                                    </div>
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

                    <!-- CARD 2: PREFERENSI OLAHRAGA & AKTIVITAS (Langkah 2 dari 3) -->
                    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-xs">
                        
                        <!-- Header -->
                        <div class="flex items-center justify-between pb-6 mb-6 border-b border-slate-100">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Preferensi Olahraga &amp; Aktivitas</h2>
                                    <p class="text-xs text-slate-500">Personalisasi feed rekomendasi event dan tantangan rute</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-xs font-semibold">
                                Langkah 2 dari 2
                            </span>
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
                            <input type="hidden" name="sparring_open" :value="form.sparring_open ? 1 : 0">

                            <!-- 1. Cabang Olahraga Favorit (Multi-Select Pills) -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-3">
                                    Cabang Olahraga Favorit (Pilih yang sering Anda ikuti)
                                </label>
                                <div class="flex flex-wrap gap-2.5">
                                    <template x-for="sport in sportsList" :key="sport.id">
                                        <button type="button"
                                                @click="toggleSport(sport.id)"
                                                :class="form.sports.includes(sport.id)
                                                    ? 'bg-blue-600 text-white border-blue-600 shadow-xs shadow-blue-300 font-bold'
                                                    : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:text-blue-600 font-semibold'"
                                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full border text-xs transition-all duration-150 cursor-pointer select-none active:scale-95">
                                            <span x-text="sport.icon"></span>
                                            <span x-text="sport.label"></span>
                                            <span x-show="form.sports.includes(sport.id)" class="text-xs font-extrabold">✓</span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- 2. Dropdowns: Level Kebugaran & Target Frekuensi -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Level Kebugaran Saat Ini</label>
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
                                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Target Frekuensi Mingguan</label>
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

                            <!-- 3. Preferensi Notifikasi & Komunitas (Toggle Box) -->
                            <div class="pt-2">
                                <label class="block text-xs font-bold text-slate-700 mb-2">Preferensi Notifikasi &amp; Komunitas</label>
                                <div class="rounded-2xl border border-cyan-100 bg-cyan-50/50 p-4 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-9 h-9 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-800">Undangan Komunitas &amp; Sparring Roster</h4>
                                            <p class="text-xs text-slate-500">Izinkan kapten klub mengirim tawaran gabung sesi latihan &amp; sparring</p>
                                        </div>
                                    </div>

                                    <!-- Switch Toggle with Alpine.js -->
                                    <button type="button"
                                            @click="form.sparring_open = !form.sparring_open"
                                            :class="form.sparring_open ? 'bg-cyan-500' : 'bg-slate-300'"
                                            class="w-12 h-6 rounded-full p-0.5 transition-colors duration-200 cursor-pointer flex-shrink-0 relative focus:outline-none"
                                            role="switch"
                                            :aria-checked="form.sparring_open">
                                        <span :class="form.sparring_open ? 'translate-x-6' : 'translate-x-0'"
                                              class="block w-5 h-5 rounded-full bg-white shadow-md transform transition-transform duration-200"></span>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- RIGHT COLUMN (4 COLS): Statistik, Status -->
                <div class="lg:col-span-4 space-y-6">
                    
                    <!-- CARD 2: STATISTIK BULAN INI -->
                    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xs">
                        
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-extrabold text-slate-900">Statistik Aktivitas</h3>
                            <span class="px-2 py-0.5 rounded-full bg-cyan-500 text-white text-[11px] font-bold">
                                Aktif
                            </span>
                        </div>

                        <!-- Metric Cards -->
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Poin</span>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-2xl sm:text-3xl font-extrabold text-blue-600 tracking-tight">{{ $user->total_points ?? 0 }}</span>
                                    <span class="text-xs font-bold text-slate-500">pts</span>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</span>
                                <div class="flex items-baseline gap-0.5">
                                    <span class="text-xl sm:text-2xl font-extrabold text-cyan-600 tracking-tight">Level {{ max(1, floor(($user->total_points ?? 0) / 300) + 1) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Mini Trend Chart -->
                        <div class="p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100">
                            <div class="flex items-center justify-between text-xs mb-2">
                                <span class="font-bold text-slate-600">Konsistensi Aktivitas</span>
                                <span class="font-extrabold text-cyan-600 text-[11px]">+18% tren</span>
                            </div>

                            <div class="h-14 w-full relative flex items-center">
                                <svg class="w-full h-full overflow-visible" viewBox="0 0 200 50" fill="none">
                                    <path d="M 10 40 Q 30 15, 60 35 T 110 25"
                                          stroke="#2563eb"
                                          stroke-width="2.5"
                                          stroke-linecap="round"
                                          fill="none"/>
                                    <path d="M 130 15 L 150 45 L 180 15"
                                          stroke="#06b6d4"
                                          stroke-width="2.5"
                                          stroke-linecap="round"
                                          stroke-linejoin="round"
                                          fill="none"/>
                                    <circle cx="180" cy="15" r="3.5" fill="#06b6d4" stroke="#ffffff" stroke-width="1.5"/>
                                </svg>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <!-- ==============================================================
                 4. BOTTOM STICKY ACTION BAR
                 ============================================================== -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 sm:px-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4 mt-8">
                
                <div class="flex items-center gap-2.5 text-xs text-slate-600 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-cyan-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Perubahan data profil akan langsung tampak di dashboard dan kartu kegiatan Anda.</span>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <a href="{{ route('user.dashboard') }}"
                       class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors cursor-pointer text-center">
                        Batal / Kembali
                    </a>

                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md shadow-blue-500/25 transition-all cursor-pointer active:scale-95">
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
            return {
                avatarPreview: @json($avatarExists ? asset('storage/' . $user->avatar) : null),

                form: {
                    name: @json(old('name', $user->name ?? '')),
                    username: @json(old('username', $user->username ?? '')),
                    email: @json(old('email', $user->email ?? '')),
                    phone: @json(old('phone', $user->phone ?? '')),
                    bio: @json(old('bio', $user->bio ?? '')),
                    sports: @json(old('sports', $user->sports ?? [])),
                    fitness_level: @json(old('fitness_level', $user->fitness_level ?? 'intermediate')),
                    weekly_target: @json(old('weekly_target', $user->weekly_target ?? '3-4')),
                    sparring_open: @json((bool) old('sparring_open', $user->sparring_open ?? true))
                },

                // Daftar Cabor Favorit
                sportsList: [
                    { id: 'running', label: 'Lari / Road Running', icon: '🏃' },
                    { id: 'padel', label: 'Padel Tennis', icon: '🎾' },
                    { id: 'cycling', label: 'Sepeda / Cycling', icon: '🚴' },
                    { id: 'strength', label: 'Strength Training', icon: '🏋️' },
                    { id: 'swimming', label: 'Renang', icon: '🏊' },
                    { id: 'basketball', label: 'Basket', icon: '🏀' },
                    { id: 'trail', label: 'Trail Run / Hiking', icon: '🧗' },
                ],

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