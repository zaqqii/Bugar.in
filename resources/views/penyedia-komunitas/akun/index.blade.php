@extends('layouts.penyedia-komunitas')

@section('title', 'Pengaturan Akun & Komunitas - Penyedia Komunitas')

@section('content')
@php
    $authUser = auth()->user();
    $isVerified = $community?->isVerified() ?? false;

    // Helper URLs
    $coverUrl = null;
    if (!empty($community?->cover_image)) {
        $coverUrl = \Illuminate\Support\Str::startsWith($community->cover_image, ['http://', 'https://'])
            ? $community->cover_image
            : (file_exists(public_path('media/' . $community->cover_image))
                ? asset('media/' . $community->cover_image)
                : \Illuminate\Support\Facades\Storage::url($community->cover_image));
    }

    $logoUrl = null;
    if (!empty($community?->logo)) {
        $logoUrl = \Illuminate\Support\Str::startsWith($community->logo, ['http://', 'https://'])
            ? $community->logo
            : (file_exists(public_path('media/' . $community->logo))
                ? asset('media/' . $community->logo)
                : \Illuminate\Support\Facades\Storage::url($community->logo));
    }

    $avatarUrl = null;
    if (!empty($user?->avatar)) {
        $avatarUrl = \Illuminate\Support\Str::startsWith($user->avatar, ['http://', 'https://'])
            ? $user->avatar
            : (file_exists(public_path('media/' . $user->avatar))
                ? asset('media/' . $user->avatar)
                : \Illuminate\Support\Facades\Storage::url($user->avatar));
    }

    $gradientClass = match(strtolower($community?->sport_category ?? 'default')) {
        'futsal' => 'from-blue-900 via-blue-800 to-blue-600',
        'basket' => 'from-amber-900 via-amber-800 to-orange-600',
        'badminton' => 'from-emerald-900 via-emerald-800 to-emerald-600',
        'lari', 'running' => 'from-purple-900 via-purple-800 to-purple-600',
        'renang', 'swimming' => 'from-sky-900 via-sky-800 to-sky-500',
        'yoga' => 'from-fuchsia-900 via-fuchsia-800 to-fuchsia-600',
        default => 'from-blue-900 via-blue-800 to-cyan-600',
    };
@endphp

<div class="max-w-6xl mx-auto space-y-6 pb-16" x-data="{
    activeTab: (new URLSearchParams(window.location.search)).get('tab') || 'komunitas',
    paidModalOpen: false,
    selectedPaymentMethod: 'qris',
    coverPreview: '{{ $coverUrl }}',
    logoPreview: '{{ $logoUrl }}',
    avatarPreview: '{{ $avatarUrl }}',
    handleCoverChange(event) {
        const file = event.target.files[0];
        if (file) {
            this.coverPreview = URL.createObjectURL(file);
        }
    },
    handleLogoChange(event) {
        const file = event.target.files[0];
        if (file) {
            this.logoPreview = URL.createObjectURL(file);
        }
    },
    handleAvatarChange(event) {
        const file = event.target.files[0];
        if (file) {
            this.avatarPreview = URL.createObjectURL(file);
        }
    }
}">

    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-bold tracking-wide uppercase mb-2">
                <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                Pengaturan Host
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                Akun & Profil Komunitas
                @if($isVerified)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                        <svg class="w-3.5 h-3.5 text-emerald-500 fill-current" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Terverifikasi
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold border border-amber-200">
                        Belum Verifikasi
                    </span>
                @endif
            </h1>
            <p class="text-slate-500 text-sm mt-1">Kelola cover komunitas, nomor WhatsApp penanggung jawab, dan status verifikasi resmi.</p>
        </div>

        {{-- Action preview link --}}
        @if($community)
            <div class="flex items-center gap-2">
                <a href="{{ route('user.communities.show', $community->id) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-sm transition-colors">
                    <span>Lihat Halaman Publik</span>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        @endif
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if(session('success_auto_verify'))
        <div class="p-5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-medium shadow-lg shadow-emerald-500/20 flex items-start gap-3.5">
            <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-base">Verifikasi Otomatis Aktif!</h4>
                <p class="text-emerald-100 text-xs mt-0.5 leading-relaxed">{{ session('success_auto_verify') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm shadow-sm">
            <span class="font-bold block mb-1">Terdapat data yang perlu diperiksa kembali:</span>
            <ul class="list-disc list-inside text-xs text-red-700 space-y-0.5 font-normal">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- NAVIGATION TABS --}}
    <div class="border-b border-slate-200">
        <nav class="flex space-x-6" aria-label="Tabs">
            <button type="button"
                    @click="activeTab = 'komunitas'"
                    class="py-3 px-1 border-b-2 font-bold text-sm flex items-center gap-2 transition-all cursor-pointer"
                    :class="activeTab === 'komunitas' 
                        ? 'border-primary-600 text-primary-600' 
                        : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>Cover & Profil Komunitas</span>
            </button>

            <button type="button"
                    @click="activeTab = 'verifikasi'"
                    class="py-3 px-1 border-b-2 font-bold text-sm flex items-center gap-2 transition-all cursor-pointer relative"
                    :class="activeTab === 'verifikasi' 
                        ? 'border-primary-600 text-primary-600' 
                        : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Verifikasi Komunitas</span>
                @if(!$isVerified)
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                @endif
            </button>

            <button type="button"
                    @click="activeTab = 'penanggung_jawab'"
                    class="py-3 px-1 border-b-2 font-bold text-sm flex items-center gap-2 transition-all cursor-pointer"
                    :class="activeTab === 'penanggung_jawab' 
                        ? 'border-primary-600 text-primary-600' 
                        : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Profil Penanggung Jawab</span>
            </button>
        </nav>
    </div>

    {{-- ============================================================ --}}
    {{-- TAB 1: COVER & INFORMASI KOMUNITAS                            --}}
    {{-- ============================================================ --}}
    <div x-show="activeTab === 'komunitas'" class="space-y-6">
        @if(!$community)
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
                <p class="text-slate-500 text-sm">Anda belum memiliki komunitas terdaftar.</p>
            </div>
        @else
            <form action="{{ route('penyedia-komunitas.akun.update-community') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Card 1: Cover & Logo Komunitas --}}
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Visual Komunitas (Cover & Logo)</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Unggah gambar cover banner dan logo resmi untuk menarik lebih banyak pegiat olahraga.</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        {{-- Interactive Cover Banner Upload & Live Preview --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Foto Cover Komunitas (Banner Utama)
                            </label>
                            
                            <div class="relative w-full h-52 sm:h-64 rounded-2xl overflow-hidden border border-slate-200 group bg-slate-100">
                                {{-- Preview Image / Gradient Fallback --}}
                                <template x-if="coverPreview">
                                    <img :src="coverPreview" alt="Preview Cover" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                </template>
                                <template x-if="!coverPreview">
                                    <div class="w-full h-full bg-gradient-to-r {{ $gradientClass }} flex flex-col items-center justify-center text-white p-6 text-center">
                                        <svg class="w-12 h-12 text-white/60 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <p class="font-bold text-sm text-white/90">Belum ada cover banner kustom</p>
                                        <p class="text-xs text-white/70 mt-0.5">Sistem saat ini menggunakan fallback warna gradien cabang olahraga ({{ $community->sport_category }}).</p>
                                    </div>
                                </template>

                                {{-- Overlay Upload Button --}}
                                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                                    <label for="cover_image_input" class="cursor-pointer px-4 py-2.5 rounded-xl bg-white/90 hover:bg-white text-slate-800 text-xs font-bold shadow-md inline-flex items-center gap-2 transition-transform hover:scale-105">
                                        <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Ganti Foto Cover
                                    </label>
                                </div>

                                {{-- Badge Kategori di Pojok Kiri Cover --}}
                                <div class="absolute top-4 left-4 z-10 px-3 py-1 rounded-full bg-slate-900/60 backdrop-blur-md text-white text-xs font-bold uppercase tracking-wider">
                                    {{ $community->sport_category }}
                                </div>
                            </div>

                            <input type="file" id="cover_image_input" name="cover_image" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" @change="handleCoverChange($event)">

                            <div class="flex items-center justify-between text-xs text-slate-500 mt-2 px-1">
                                <span>Format: JPG, PNG, WEBP. Maksimal 3MB. Rekomendasi rasio 16:9 atau 1200x500 px.</span>
                                <label for="cover_image_input" class="text-primary-600 hover:text-primary-700 font-bold cursor-pointer inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Pilih File Cover
                                </label>
                            </div>
                        </div>

                        {{-- Logo Komunitas & Info Singkat --}}
                        <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center gap-5">
                            <div class="relative w-20 h-20 rounded-2xl border-2 border-slate-200 overflow-hidden bg-slate-50 shrink-0 group">
                                <template x-if="logoPreview">
                                    <img :src="logoPreview" alt="Logo Komunitas" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!logoPreview">
                                    <div class="w-full h-full flex items-center justify-center font-extrabold text-xl text-primary-600 bg-primary-50">
                                        {{ strtoupper(substr($community->name, 0, 2)) }}
                                    </div>
                                </template>
                                <label for="logo_input" class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer">
                                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    </svg>
                                </label>
                            </div>
                            <input type="file" id="logo_input" name="logo" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" @change="handleLogoChange($event)">

                            <div class="flex-1">
                                <h3 class="text-sm font-bold text-slate-800">Logo Komunitas (Avatar / Ikon)</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Ditampilkan pada kartu direktori jelajah komunitas, daftar ranking, dan avatar obrolan.</p>
                                <label for="logo_input" class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold cursor-pointer shadow-2xs">
                                    Ganti Logo
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Informasi Detail Komunitas & Nomor Penanggung Jawab --}}
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 sm:p-8 space-y-6">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Informasi & Kontak Komunitas</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Kelola identitas publik, kategori olahraga, dan nomor kontak resmi penanggung jawab.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Nama Komunitas --}}
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Nama Komunitas <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name', $community->name) }}"
                                   required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition">
                        </div>

                        {{-- Kategori Olahraga --}}
                        <div>
                            <label for="sport_category" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Kategori Olahraga Utama <span class="text-red-500">*</span>
                            </label>
                            <select id="sport_category" name="sport_category" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition bg-white">
                                @foreach($sportCategories as $key => $label)
                                    <option value="{{ $key }}" {{ old('sport_category', $community->sport_category) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kota Basecamp --}}
                        <div>
                            <label for="city" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Kota / Wilayah Basecamp <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="city" name="city" value="{{ old('city', $community->city) }}"
                                   placeholder="Contoh: Bandung, Jakarta Selatan" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition">
                        </div>

                        {{-- Nomor Penanggung Jawab / WhatsApp (HIGHLIGHTED FITUR) --}}
                        <div class="p-4 rounded-xl bg-primary-50/50 border border-primary-200/70 md:col-span-2 space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-primary-600"></span>
                                <label for="contact_phone" class="text-sm font-bold text-slate-800">
                                    Nomor Telepon / WhatsApp Penanggung Jawab (PIC) <span class="text-red-500">*</span>
                                </label>
                            </div>
                            <p class="text-xs text-slate-500">
                                Nomor ini digunakan oleh calon anggota, peserta event, dan pihak penyelenggara untuk menghubungi penanggung jawab komunitas.
                            </p>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-semibold text-sm">
                                    📱
                                </span>
                                <input type="text" id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $community->contact_phone) }}"
                                       placeholder="Contoh: 081234567890" required
                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-primary-300 bg-white text-slate-800 text-sm font-semibold focus:outline-none focus:border-primary-600 focus:ring-4 focus:ring-primary-500/15 transition shadow-2xs">
                            </div>
                        </div>

                        {{-- Email Kontak Komunitas --}}
                        <div>
                            <label for="contact_email" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Email Resmi Komunitas
                            </label>
                            <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $community->contact_email) }}"
                                   placeholder="komunitas@example.com"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition">
                        </div>

                        {{-- Alamat / Basecamp Lapangan --}}
                        <div>
                            <label for="location" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Alamat Lokasi / Venue Rutin
                            </label>
                            <input type="text" id="location" name="location" value="{{ old('location', $community->location) }}"
                                   placeholder="Contoh: GOR Saparua, Lapangan Tri Lomba Juang"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition">
                        </div>

                        {{-- Instagram --}}
                        <div>
                            <label for="instagram" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Akun Instagram Komunitas
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-sm pointer-events-none">
                                    @
                                </span>
                                <input type="text" id="instagram" name="instagram" value="{{ old('instagram', $community->instagram) }}"
                                       placeholder="username_komunitas"
                                       class="w-full pl-8 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition">
                            </div>
                        </div>

                        {{-- Website / Tautan Eksternal --}}
                        <div>
                            <label for="website" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Tautan Website / Linktree (Opsional)
                            </label>
                            <input type="url" id="website" name="website" value="{{ old('website', $community->website) }}"
                                   placeholder="https://..."
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition">
                        </div>

                        {{-- Deskripsi Komunitas --}}
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Cerita & Deskripsi Komunitas
                            </label>
                            <textarea id="description" name="description" rows="4"
                                      placeholder="Jelaskan visi komunitas, jadwal rutin, dan keterbukaan bagi anggota baru..."
                                      class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition">{{ old('description', $community->description) }}</textarea>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm shadow-md shadow-primary-500/20 inline-flex items-center gap-2 transition hover:-translate-y-0.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Perubahan Komunitas
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>

    {{-- ============================================================ --}}
    {{-- TAB 2: VERIFIKASI KOMUNITAS (2 OPSI: GRATIS 15 FOLLOWERS / BERBAYAR) --}}
    {{-- ============================================================ --}}
    <div x-show="activeTab === 'verifikasi'" class="space-y-6">

        {{-- STATUS CURRENT VERIFICATION HERO BANNER --}}
        @if($isVerified)
            <div class="bg-gradient-to-br from-emerald-600 via-teal-700 to-cyan-800 rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 relative z-10">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-emerald-100 text-xs font-bold uppercase tracking-wide">
                            <svg class="w-3.5 h-3.5 text-emerald-300 fill-current" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Status: Komunitas Resmi Terverifikasi
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                            Selamat! Komunitas Anda Sudah Terverifikasi
                        </h2>
                        <p class="text-emerald-100 text-sm max-w-xl">
                            Seluruh benefit eksklusif host telah aktif. Anda bebas menyelenggarakan event berbayar (tiket pendaftaran) dan jadwal kegiatan rutin.
                        </p>
                        @if($community?->verified_at)
                            <p class="text-xs text-white/70">
                                Diverifikasi sejak: {{ $community->verified_at->translatedFormat('d F Y, H:i') }} WIB
                                @if(!empty($community->verification_type))
                                    • Jalur: {{ $community->verification_type === 'paid' ? 'Verifikasi Instan' : '15+ Pengikut Komunitas' }}
                                @endif
                            </p>
                        @endif
                    </div>

                    <a href="{{ route('penyedia-komunitas.events.create') }}"
                       class="px-5 py-3 rounded-xl bg-white text-emerald-800 hover:bg-emerald-50 text-sm font-bold shadow-lg transition-all hover:scale-105 inline-flex items-center gap-2 shrink-0">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Buat Event Berbayar Sekarang
                    </a>
                </div>
            </div>
        @else
            <div class="bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 relative z-10">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-xs font-bold uppercase tracking-wide border border-amber-400/30">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                            Status: Belum Terverifikasi
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                            Buka Akses Pembuatan Event Berbayar
                        </h2>
                        <p class="text-slate-300 text-sm max-w-xl leading-relaxed">
                            Komunitas yang belum terverifikasi <strong class="text-white">hanya dapat membuat jadwal rutin dan event biasa (gratis)</strong>. Verifikasikan komunitas Anda untuk mulai menjual tiket event dan menjaring peserta berbayar!
                        </p>
                    </div>

                    <div class="px-4 py-3 rounded-xl bg-white/10 backdrop-blur-md border border-white/10 text-xs text-slate-200 space-y-1 shrink-0">
                        <div class="font-bold text-white flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Ketentuan Fitur:
                        </div>
                        <div>• Jadwal Rutin: <span class="text-emerald-400 font-semibold">Tersedia</span></div>
                        <div>• Event Biasa (Gratis): <span class="text-emerald-400 font-semibold">Tersedia</span></div>
                        <div>• Event Berbayar (Tiket): <span class="text-amber-400 font-semibold">Terkunci (Perlu Verifikasi)</span></div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 2 PILIHAN METODE VERIFIKASI (GRATIS vs BERBAYAR) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- OPSI 1: VERSI GRATIS (15 PENGIKUT) --}}
            <div class="bg-white rounded-2xl border-2 {{ $canClaimFree && !$isVerified ? 'border-emerald-500 ring-4 ring-emerald-500/10' : 'border-slate-200/90' }} p-6 sm:p-7 shadow-sm flex flex-col justify-between relative overflow-hidden">
                @if($canClaimFree && !$isVerified)
                    <div class="absolute top-0 right-0 bg-emerald-500 text-white text-[11px] font-extrabold uppercase px-3 py-1 rounded-bl-xl tracking-wider shadow-sm">
                        Syarat Terpenuhi!
                    </div>
                @endif

                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>

                    <div>
                        <div class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full mb-1">
                            Opsi 1 • 100% Gratis
                        </div>
                        <h3 class="text-xl font-extrabold text-slate-900">Verifikasi Lewat 15 Pengikut</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Kumpulkan minimal 15 pengikut di Bugar.in. Begitu mencapai 15 pengikut, komunitas Anda akan otomatis terverifikasi tanpa biaya sepeser pun.
                        </p>
                    </div>

                    {{-- PROGRESS FOLLOWER BAR --}}
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-600">Progress Pengikut Komunitas:</span>
                            <span class="text-emerald-700 font-extrabold text-sm">{{ $followersCount }} / {{ $followersTarget }}</span>
                        </div>
                        <div class="w-full h-3 rounded-full bg-slate-200 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all duration-500"
                                 style="width: {{ $followersProgress }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-500">
                            <span>{{ $followersProgress }}% tercapai</span>
                            @if($canClaimFree)
                                <span class="text-emerald-600 font-bold">Siap diverifikasi!</span>
                            @else
                                <span>Butuh <strong>{{ 15 - $followersCount }}</strong> pengikut lagi</span>
                            @endif
                        </div>
                    </div>

                    <ul class="text-xs text-slate-600 space-y-2 pt-2">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Gratis selamanya tanpa pungutan biaya admin</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Otomatis aktif saat follower mencapai 15 orang</span>
                        </li>
                    </ul>
                </div>

                {{-- Action Button --}}
                <div class="mt-6 pt-4 border-t border-slate-100">
                    @if($isVerified)
                        <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold text-center cursor-not-allowed">
                            Komunitas Sudah Terverifikasi
                        </button>
                    @elseif($canClaimFree)
                        <form action="{{ route('penyedia-komunitas.akun.verify') }}" method="POST">
                            @csrf
                            <input type="hidden" name="verification_type" value="free">
                            <button type="submit"
                                    class="w-full py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-sm shadow-lg shadow-emerald-500/20 inline-flex items-center justify-center gap-2 transition hover:-translate-y-0.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Klaim Verifikasi Gratis Sekarang
                            </button>
                        </form>
                    @else
                        <div class="flex items-center gap-2">
                            <button type="button" disabled class="flex-1 py-2.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold text-center cursor-not-allowed">
                                Belum Cukup 15 Pengikut
                            </button>
                            <a href="{{ route('user.communities.show', $community->id ?? 1) }}" target="_blank"
                               class="px-3 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition">
                                Bagikan Profil
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- OPSI 2: VERSI BERBAYAR (INSTAN / PREMIUM) --}}
            <div class="bg-white rounded-2xl border-2 border-primary-500/80 p-6 sm:p-7 shadow-sm flex flex-col justify-between relative overflow-hidden ring-4 ring-primary-500/5">
                <div class="absolute top-0 right-0 bg-gradient-to-l from-primary-600 to-cyan-600 text-white text-[11px] font-extrabold uppercase px-3 py-1 rounded-bl-xl tracking-wider shadow-sm">
                    Instan Langsung Aktif
                </div>

                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>

                    <div>
                        <div class="inline-flex items-center gap-1 text-xs font-bold text-primary-700 bg-primary-50 px-2.5 py-0.5 rounded-full mb-1">
                            Opsi 2 • Fast Track
                        </div>
                        <h3 class="text-xl font-extrabold text-slate-900">Verifikasi Berbayar (Instan)</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Tidak ingin menunggu sampai 15 pengikut? Verifikasi instan membuka seluruh benefit dalam hitungan detik.
                        </p>
                    </div>

                    {{-- PRICING CARD --}}
                    <div class="p-4 rounded-xl bg-gradient-to-br from-primary-50 to-blue-50/50 border border-primary-200/80 space-y-1">
                        <span class="text-xs text-slate-500 font-semibold">Biaya Verifikasi Sekali Bayar:</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-black text-slate-900">Rp 99.000</span>
                            <span class="text-xs text-slate-500 font-medium">/ selamanya</span>
                        </div>
                        <p class="text-[11px] text-primary-700 font-semibold mt-1">
                            ✓ Langsung aktif detik ini juga tanpa syarat follower
                        </p>
                    </div>

                    <ul class="text-xs text-slate-600 space-y-2 pt-2">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Dapat membuat event berbayar (jual tiket online)</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Lencana centang verifikasi resmi di katalog</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Dukungan pembayaran QRIS & Virtual Account</span>
                        </li>
                    </ul>
                </div>

                {{-- Action Button --}}
                <div class="mt-6 pt-4 border-t border-slate-100">
                    @if($isVerified)
                        <button type="button" disabled class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold text-center cursor-not-allowed">
                            Komunitas Sudah Terverifikasi
                        </button>
                    @else
                        <button type="button"
                                @click="paidModalOpen = true"
                                class="w-full py-3 rounded-xl bg-gradient-to-r from-blue-700 via-primary-600 to-cyan-500 hover:from-blue-800 hover:via-primary-700 hover:to-cyan-600 text-white font-bold text-sm shadow-lg shadow-primary-500/25 inline-flex items-center justify-center gap-2 transition hover:-translate-y-0.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Pilih Verifikasi Instan (Rp 99.000)
                        </button>
                    @endif
                </div>
            </div>

        </div>

        {{-- BENEFIT COMPARISON TABLE --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-sm">
            <h3 class="text-base font-bold text-slate-800 mb-1">Perbandingan Fitur Komunitas</h3>
            <p class="text-xs text-slate-500 mb-4">Pelajari batasan fitur antara komunitas biasa dengan komunitas resmi terverifikasi.</p>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/70">
                            <th class="py-3 px-4 font-bold text-slate-700">Fitur Platform</th>
                            <th class="py-3 px-4 font-bold text-slate-500 text-center">Komunitas Biasa</th>
                            <th class="py-3 px-4 font-bold text-primary-700 text-center bg-primary-50/50">Komunitas Terverifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="py-3 px-4 font-medium text-slate-800">Buat Jadwal Rutin Mingguan</td>
                            <td class="py-3 px-4 text-center text-emerald-600 font-bold">✓ Gratis</td>
                            <td class="py-3 px-4 text-center text-emerald-600 font-bold bg-primary-50/20">✓ Gratis</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-medium text-slate-800">Buat Event Biasa (Gratis)</td>
                            <td class="py-3 px-4 text-center text-emerald-600 font-bold">✓ Gratis</td>
                            <td class="py-3 px-4 text-center text-emerald-600 font-bold bg-primary-50/20">✓ Gratis</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-medium text-slate-800">Buat Event Berbayar (Jual Tiket)</td>
                            <td class="py-3 px-4 text-center text-red-500 font-bold">✗ Terkunci</td>
                            <td class="py-3 px-4 text-center text-emerald-600 font-bold bg-primary-50/20">✓ Terbuka Penuh</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-medium text-slate-800">Lencana Verified Resmi</td>
                            <td class="py-3 px-4 text-center text-slate-400 font-bold">-</td>
                            <td class="py-3 px-4 text-center text-primary-600 font-bold bg-primary-50/20">✓ Lencana Centang</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-medium text-slate-800">Prioritas Hasil Pencarian</td>
                            <td class="py-3 px-4 text-center text-slate-400 font-bold">Standar</td>
                            <td class="py-3 px-4 text-center text-primary-600 font-bold bg-primary-50/20">✓ Prioritas Tinggi</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- TAB 3: PROFIL PENANGGUNG JAWAB (PIC)                          --}}
    {{-- ============================================================ --}}
    <div x-show="activeTab === 'penanggung_jawab'" class="space-y-6">
        <form action="{{ route('penyedia-komunitas.akun.update-profile') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-6 sm:p-8 space-y-6">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Profil Penanggung Jawab Komunitas (Host)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Kelola identitas penanggung jawab resmi, nomor telepon, dan kredensial kontak host.</p>
                </div>

                {{-- Avatar PIC --}}
                <div class="flex items-center gap-5 pt-2">
                    <div class="relative w-16 h-16 rounded-full border-2 border-slate-200 overflow-hidden bg-slate-100 group shrink-0">
                        <template x-if="avatarPreview">
                            <img :src="avatarPreview" alt="Avatar Host" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!avatarPreview">
                            <div class="w-full h-full flex items-center justify-center font-bold text-lg text-primary-700 bg-primary-50">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        </template>
                        <label for="avatar_input" class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            </svg>
                        </label>
                    </div>
                    <input type="file" id="avatar_input" name="avatar" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden" @change="handleAvatarChange($event)">

                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Foto Profil Penanggung Jawab</h4>
                        <p class="text-xs text-slate-500">Maksimal 2MB (JPG, PNG, atau WEBP).</p>
                        <label for="avatar_input" class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold cursor-pointer">
                            Pilih Foto Avatar
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t border-slate-100">
                    {{-- Nama Penanggung Jawab --}}
                    <div>
                        <label for="pic_name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Nama Lengkap Penanggung Jawab <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="pic_name" name="name" value="{{ old('name', $user->name) }}"
                               required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition">
                    </div>

                    {{-- Nomor WhatsApp Penanggung Jawab --}}
                    <div>
                        <label for="pic_phone" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Nomor WhatsApp / HP Penanggung Jawab <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="pic_phone" name="phone" value="{{ old('phone', $user->phone) }}"
                               required placeholder="Contoh: 081234567890"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition">
                        <p class="text-[11px] text-slate-400 mt-1">Nomor ini juga otomatis terhubung dengan kontak komunitas Anda.</p>
                    </div>

                    {{-- Email Akun Host --}}
                    <div class="md:col-span-2">
                        <label for="pic_email" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Alamat Email Akun Host <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="pic_email" name="email" value="{{ old('email', $user->email) }}"
                               required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition">
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm shadow-md shadow-primary-500/20 inline-flex items-center gap-2 transition hover:-translate-y-0.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Data Penanggung Jawab
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ============================================================ --}}
    {{-- MODAL VERIFIKASI INSTAN (BERBAYAR)                           --}}
    {{-- ============================================================ --}}
    <div x-show="paidModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">

        <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative"
             @click.outside="paidModalOpen = false">

            {{-- Close Button --}}
            <button type="button" @click="paidModalOpen = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center font-bold">
                    💳
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Verifikasi Instan Komunitas</h3>
                    <p class="text-xs text-slate-500">Konfirmasi aktivasi verifikasi berbayar resmi Bugar.in.</p>
                </div>
            </div>

            <form action="{{ route('penyedia-komunitas.akun.verify') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="verification_type" value="paid">

                {{-- Order Summary --}}
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <div class="flex justify-between text-xs text-slate-600">
                        <span>Komunitas</span>
                        <span class="font-bold text-slate-800">{{ $community->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-600">
                        <span>Paket</span>
                        <span class="font-bold text-primary-700">Verifikasi Instan Lifetime</span>
                    </div>
                    <div class="pt-2 border-t border-slate-200 flex justify-between items-baseline">
                        <span class="text-xs font-bold text-slate-700">Total Biaya:</span>
                        <span class="text-xl font-extrabold text-slate-900">Rp 99.000</span>
                    </div>
                </div>

                {{-- Payment Method Selection --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Pilih Metode Pembayaran
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="p-3 rounded-xl border cursor-pointer flex items-center gap-2 transition"
                               :class="selectedPaymentMethod === 'qris' ? 'border-primary-500 bg-primary-50/50 ring-2 ring-primary-500/20' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="payment_method" value="qris" x-model="selectedPaymentMethod" class="sr-only">
                            <span class="text-sm">⚡</span>
                            <div class="text-left">
                                <span class="block text-xs font-bold text-slate-800">QRIS Instan</span>
                                <span class="block text-[10px] text-slate-400">Gopay, OVO, Shopee</span>
                            </div>
                        </label>

                        <label class="p-3 rounded-xl border cursor-pointer flex items-center gap-2 transition"
                               :class="selectedPaymentMethod === 'bca' ? 'border-primary-500 bg-primary-50/50 ring-2 ring-primary-500/20' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="payment_method" value="bca" x-model="selectedPaymentMethod" class="sr-only">
                            <span class="text-sm">🏦</span>
                            <div class="text-left">
                                <span class="block text-xs font-bold text-slate-800">BCA Virtual</span>
                                <span class="block text-[10px] text-slate-400">Transfer Otomatis</span>
                            </div>
                        </label>

                        <label class="p-3 rounded-xl border cursor-pointer flex items-center gap-2 transition"
                               :class="selectedPaymentMethod === 'mandiri' ? 'border-primary-500 bg-primary-50/50 ring-2 ring-primary-500/20' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="payment_method" value="mandiri" x-model="selectedPaymentMethod" class="sr-only">
                            <span class="text-sm">🏦</span>
                            <div class="text-left">
                                <span class="block text-xs font-bold text-slate-800">Mandiri VA</span>
                                <span class="block text-[10px] text-slate-400">Livin' Mandiri</span>
                            </div>
                        </label>

                        <label class="p-3 rounded-xl border cursor-pointer flex items-center gap-2 transition"
                               :class="selectedPaymentMethod === 'bri' ? 'border-primary-500 bg-primary-50/50 ring-2 ring-primary-500/20' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="payment_method" value="bri" x-model="selectedPaymentMethod" class="sr-only">
                            <span class="text-sm">🏦</span>
                            <div class="text-left">
                                <span class="block text-xs font-bold text-slate-800">BRI BRIVA</span>
                                <span class="block text-[10px] text-slate-400">BRImo & ATM</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-3">
                    <button type="button" @click="paidModalOpen = false"
                            class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-blue-700 to-cyan-600 hover:from-blue-800 hover:to-cyan-700 text-white text-xs font-bold shadow-md transition cursor-pointer">
                        Konfirmasi Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
