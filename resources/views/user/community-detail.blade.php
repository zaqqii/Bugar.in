@extends('layouts.user')

@section('title', ($community->name ?? 'Detail Komunitas') . ' — Bugar.in')

@section('content')
@php
    $commCategory = strtolower($community->sport_category ?? 'default');
    $categoryGradients = [
        'futsal'     => 'from-blue-900 via-blue-800 to-blue-600',
        'basket'     => 'from-amber-900 via-orange-700 to-amber-600',
        'basketball' => 'from-amber-900 via-orange-700 to-amber-600',
        'badminton'  => 'from-emerald-900 via-teal-700 to-emerald-600',
        'lari'       => 'from-blue-900 via-indigo-800 to-cyan-600',
        'running'    => 'from-blue-900 via-indigo-800 to-cyan-600',
        'renang'     => 'from-sky-900 via-cyan-700 to-blue-600',
        'swimming'   => 'from-sky-900 via-cyan-700 to-blue-600',
        'yoga'       => 'from-fuchsia-900 via-pink-700 to-rose-600',
        'sepeda'     => 'from-blue-900 via-teal-700 to-emerald-600',
        'cycling'    => 'from-blue-900 via-teal-700 to-emerald-600',
        'default'    => 'from-blue-900 via-blue-700 to-cyan-600',
    ];
    $fallbackGradient = $categoryGradients[$commCategory] ?? $categoryGradients['default'];

    $coverUrl = null;
    if (!empty($community->cover_image)) {
        $coverUrl = \Illuminate\Support\Str::startsWith($community->cover_image, ['http://', 'https://'])
            ? $community->cover_image
            : (file_exists(public_path('media/' . $community->cover_image))
                ? asset('media/' . $community->cover_image)
                : \Illuminate\Support\Facades\Storage::url($community->cover_image));
    }

    $logoUrl = null;
    if (!empty($community->logo)) {
        $logoUrl = \Illuminate\Support\Str::startsWith($community->logo, ['http://', 'https://'])
            ? $community->logo
            : (file_exists(public_path('media/' . $community->logo))
                ? asset('media/' . $community->logo)
                : \Illuminate\Support\Facades\Storage::url($community->logo));
    }

    $owner = $community->owner ?? null;
    $ownerInitials = collect(explode(' ', $owner?->name ?? 'Koordinator Komunitas'))
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->take(2)->implode('');

    $commInitials = collect(explode(' ', $community->name ?? 'Komunitas'))
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->take(3)->implode('');

    $isPublic = $community->is_public ?? true;
    $isMemberUser = $isMember ?? false;
    $isFollowerUser = $isFollower ?? false;
@endphp

<div x-data="{
    activeTab: 'tentang',
    copiedShare: false,
    shareCommunity() {
        if (navigator.share) {
            navigator.share({
                title: '{{ addslashes($community->name ?? 'Komunitas Bugar.in') }}',
                text: 'Ayo gabung dan berolahraga bersama di {{ addslashes($community->name ?? 'Bugar.in') }}!',
                url: window.location.href,
            }).catch(() => {});
        } else {
            navigator.clipboard.writeText(window.location.href);
            this.copiedShare = true;
            setTimeout(() => { this.copiedShare = false; }, 2500);
        }
    }
}" class="max-w-7xl mx-auto space-y-6">

    {{-- Breadcrumb Navigasi Atas --}}
    <div class="flex flex-wrap items-center justify-between gap-3 text-xs sm:text-sm">
        <a href="{{ route('user.dashboard') }}"
           class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white border border-slate-200/90 text-slate-700 hover:text-blue-600 hover:border-blue-300 transition-all font-semibold shadow-2xs group active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 group-hover:text-blue-600 group-hover:-translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Dashboard</span>
        </a>

        <nav class="flex items-center gap-2 text-slate-500 text-xs" aria-label="Breadcrumb">
            <a href="{{ route('user.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-slate-400">Komunitas</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
            <span class="font-bold text-slate-900 truncate max-w-[200px]">{{ $community->name ?? 'Detail Komunitas' }}</span>
        </nav>
    </div>

    {{-- ==============================================================
         1. HERO SECTION & HEADER CARD
         ============================================================== --}}
    <div class="rounded-3xl overflow-hidden bg-white border border-slate-200/80 shadow-xs relative">
        
        {{-- Banner Cover Image --}}
        <div class="relative h-52 sm:h-72 lg:h-80 bg-gradient-to-r {{ $fallbackGradient }} overflow-hidden">
            @if($coverUrl)
                <img src="{{ $coverUrl }}"
                     alt="{{ $community->name ?? 'Cover Komunitas' }}"
                     class="w-full h-full object-cover"
                     onerror="this.style.display='none'">
            @endif

            {{-- Subtle Ambient Decorative Waves if no image / overlay --}}
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/20 to-transparent pointer-events-none"></div>
        </div>

        {{-- Header Info Container (Overlapping Banner) --}}
        <div class="px-5 sm:px-8 pb-6 pt-3 relative">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">

                {{-- Left: Logo + Nama + Meta Info --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-end gap-5">
                    
                    {{-- Logo Avatar (Square Rounded-2xl dengan Border Putih) --}}
                    <div class="relative -mt-16 sm:-mt-20 flex-shrink-0">
                        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl border-4 border-white shadow-md bg-white overflow-hidden flex items-center justify-center p-1 relative">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}"
                                     alt="{{ $community->name ?? 'Logo' }}"
                                     class="w-full h-full object-cover rounded-xl"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            @endif
                            <div class="w-full h-full rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 text-white font-extrabold text-2xl flex items-center justify-center tracking-tight {{ $logoUrl ? 'hidden' : 'flex' }}">
                                {{ $commInitials }}
                            </div>
                        </div>
                    </div>

                    {{-- Community Details --}}
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                                {{ $community->name ?? 'Jakarta Sunday Runners' }}
                            </h1>
                            
                            {{-- Sport Category Pill --}}
                            <span class="px-3 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-600 border border-blue-200">
                                {{ ucfirst($community->sport_category ?? 'Lari') }}
                            </span>

                            @if(($community->verification_status ?? 'verified') === 'verified')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200/80 shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Terverifikasi
                                </span>
                            @endif
                        </div>

                        {{-- Subline: Kategori, Kota/Provinsi, Tanggal Berdiri --}}
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 font-medium">
                            <span class="inline-flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span>{{ ucfirst($community->sport_category ?? 'Olahraga') }}</span>
                            </span>

                            @if($community->city || $community->province)
                                <span class="inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>{{ $community->city ?? 'Jakarta' }}{{ $community->province ? ', ' . $community->province : '' }}</span>
                                </span>
                            @endif

                            <span class="inline-flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Est. {{ $community->created_at ? $community->created_at->translatedFormat('M Y') : 'Jan 2022' }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Right: Action Buttons Group (Gabung, Ikuti, Kontak/Sosmed) --}}
                <div class="flex flex-wrap items-center gap-2.5">
                    
                    {{-- 1. Tombol Gabung Komunitas (Cek Status is_public) --}}
                    @if($isMemberUser)
                        <button type="button"
                                disabled
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs sm:text-sm font-bold shadow-2xs cursor-default">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span>Anggota Aktif</span>
                        </button>
                    @elseif($isPublic)
                        <form method="POST" action="{{ route('user.communities.join', $community->id ?? 1) }}" class="m-0">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold shadow-sm transition-all duration-200 cursor-pointer active:scale-95">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                <span>Gabung Komunitas</span>
                            </button>
                        </form>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-500 text-xs font-bold border border-slate-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>Komunitas Privat</span>
                        </span>
                    @endif

                    {{-- 2. Tombol Ikuti (Follow / Unfollow) --}}
                    @if($isFollowerUser)
                        <form method="POST" action="{{ route('user.communities.unfollow', $community->id ?? 1) }}" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 text-xs sm:text-sm font-bold shadow-2xs hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 transition-all cursor-pointer">
                                <span>✓ Mengikuti</span>
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('user.communities.follow', $community->id ?? 1) }}" class="m-0">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:text-blue-600 hover:border-blue-300 text-xs sm:text-sm font-bold shadow-2xs transition-all cursor-pointer active:scale-95">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 group-hover:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                                <span>Ikuti</span>
                            </button>
                        </form>
                    @endif

                    {{-- 3. Deretan Ikon Sosial Media & Kontak --}}
                    <div class="flex items-center gap-1.5 border-l border-slate-200 pl-2.5">
                        
                        {{-- Instagram --}}
                        @if($community->instagram)
                            <a href="https://instagram.com/{{ ltrim($community->instagram, '@') }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               title="Instagram {{ $community->instagram }}"
                               class="w-9 h-9 rounded-xl bg-slate-50 hover:bg-pink-50 text-slate-600 hover:text-pink-600 border border-slate-200/80 hover:border-pink-200 flex items-center justify-center transition-all shadow-2xs">
                                <svg class="w-4 h-4 fill-currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </a>
                        @endif

                        {{-- Website --}}
                        @if($community->website)
                            <a href="{{ \Illuminate\Support\Str::startsWith($community->website, ['http://', 'https://']) ? $community->website : 'https://' . $community->website }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               title="Website Resmi"
                               class="w-9 h-9 rounded-xl bg-slate-50 hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-200/80 hover:border-blue-200 flex items-center justify-center transition-all shadow-2xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                            </a>
                        @endif

                        {{-- Email Kontak --}}
                        @if($community->contact_email)
                            <a href="mailto:{{ $community->contact_email }}"
                               title="Email: {{ $community->contact_email }}"
                               class="w-9 h-9 rounded-xl bg-slate-50 hover:bg-cyan-50 text-slate-600 hover:text-cyan-600 border border-slate-200/80 hover:border-cyan-200 flex items-center justify-center transition-all shadow-2xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </a>
                        @endif

                        {{-- WhatsApp / Telepon --}}
                        @if($community->contact_phone)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $community->contact_phone) }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               title="WhatsApp: {{ $community->contact_phone }}"
                               class="w-9 h-9 rounded-xl bg-slate-50 hover:bg-emerald-50 text-slate-600 hover:text-emerald-600 border border-slate-200/80 hover:border-emerald-200 flex items-center justify-center transition-all shadow-2xs">
                                <svg class="w-4 h-4 fill-currentColor" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.158.572 4.183 1.571 5.932l-1.667 6.096 6.275-1.646c1.704.931 3.652 1.458 5.72 1.458 6.627 0 12-5.373 12-12s-5.373-12-12-12z"/>
                                </svg>
                            </a>
                        @endif

                        {{-- Tombol Bagikan Komunitas --}}
                        <button type="button"
                                @click="shareCommunity()"
                                title="Bagikan Komunitas"
                                class="w-9 h-9 rounded-xl bg-slate-50 hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-200/80 hover:border-blue-200 flex items-center justify-center transition-all shadow-2xs cursor-pointer relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            
                            {{-- Tooltip feedback tersalin --}}
                            <span x-show="copiedShare"
                                  x-transition
                                  class="absolute -top-8 right-0 bg-slate-900 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-md whitespace-nowrap">
                                Link Tersalin! ✓
                            </span>
                        </button>
                    </div>

                </div>

            </div>

            {{-- Stat Row (Members Count, Events, Rating, Leaderboard) --}}
            <div class="mt-6 pt-5 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-4 gap-4">
                
                {{-- Stat 1: Anggota Aktif --}}
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-lg sm:text-xl font-extrabold text-slate-900 leading-tight">
                            {{ number_format($community->members_count ?? 2450) }}
                        </div>
                        <div class="text-xs text-slate-400 font-medium">Anggota Aktif</div>
                    </div>
                </div>

                {{-- Stat 2: Event Selesai --}}
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-lg sm:text-xl font-extrabold text-slate-900 leading-tight">
                            {{ number_format($community->events_count ?? ($community->events?->count() ?? 158)) }}
                        </div>
                        <div class="text-xs text-slate-400 font-medium">Event Selesai</div>
                    </div>
                </div>

                {{-- Stat 3: Rating Komunitas --}}
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-amber-400" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-lg sm:text-xl font-extrabold text-slate-900 leading-tight flex items-baseline gap-1">
                            <span>{{ number_format($community->average_rating ?? 4.9, 1) }}</span>
                            <span class="text-xs text-slate-400 font-normal">/ 5.0</span>
                        </div>
                        <div class="text-xs text-slate-400 font-medium">Rating Komunitas</div>
                    </div>
                </div>

                {{-- Stat 4: Leaderboard Rank --}}
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-lg sm:text-xl font-extrabold text-blue-600 leading-tight flex items-center gap-1.5">
                            <span>#2</span>
                            <span class="text-[11px] font-bold text-blue-700 bg-blue-100/60 px-1.5 py-0.2 rounded-md">Nasional</span>
                        </div>
                        <div class="text-xs text-slate-400 font-medium">Leaderboard {{ ucfirst($community->sport_category ?? 'Run') }}</div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    {{-- ==============================================================
         2. NAVIGATION TABS (Alpine.js State)
         ============================================================== --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        
        {{-- Tab 1: Tentang Komunitas (Default Active) --}}
        <button type="button"
                @click="activeTab = 'tentang'"
                :class="activeTab === 'tentang'
                    ? 'bg-blue-600 text-white font-bold shadow-xs'
                    : 'bg-white text-slate-600 hover:text-blue-600 hover:bg-blue-50/50 border border-slate-200/80 font-semibold'"
                class="px-5 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-150 cursor-pointer whitespace-nowrap active:scale-95">
            Tentang Komunitas
        </button>

        {{-- Tab 2: Event --}}
        <button type="button"
                @click="activeTab = 'event'"
                :class="activeTab === 'event'
                    ? 'bg-blue-600 text-white font-bold shadow-xs'
                    : 'bg-white text-slate-600 hover:text-blue-600 hover:bg-blue-50/50 border border-slate-200/80 font-semibold'"
                class="px-5 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-150 cursor-pointer whitespace-nowrap active:scale-95">
            Event &amp; Kegiatan
        </button>

        {{-- Tab 3: Anggota --}}
        <button type="button"
                @click="activeTab = 'anggota'"
                :class="activeTab === 'anggota'
                    ? 'bg-blue-600 text-white font-bold shadow-xs'
                    : 'bg-white text-slate-600 hover:text-blue-600 hover:bg-blue-50/50 border border-slate-200/80 font-semibold'"
                class="px-5 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-150 cursor-pointer whitespace-nowrap active:scale-95">
            Anggota &amp; Koordinator
        </button>

        {{-- Tab 4: Galeri --}}
        <button type="button"
                @click="activeTab = 'galeri'"
                :class="activeTab === 'galeri'
                    ? 'bg-blue-600 text-white font-bold shadow-xs'
                    : 'bg-white text-slate-600 hover:text-blue-600 hover:bg-blue-50/50 border border-slate-200/80 font-semibold'"
                class="px-5 py-2.5 rounded-xl text-xs sm:text-sm transition-all duration-150 cursor-pointer whitespace-nowrap active:scale-95">
            Galeri Foto
        </button>

    </div>

    {{-- ==============================================================
         3. 2-COLUMN MAIN CONTENT LAYOUT
         ============================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {{-- ==========================================================
             KOLOM KIRI (8 COLS): Konten Utama Berdasarkan Tab Aktif
             ========================================================== --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- ------------------------------------------------------
                 TAB 1: TENTANG KOMUNITAS (Default Active)
                 ------------------------------------------------------ --}}
            <div x-show="activeTab === 'tentang'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="space-y-6">
                
                {{-- Card Deskripsi Komunitas --}}
                <div class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-xs">
                    <div class="flex items-center gap-2.5 mb-4">
                        <span class="w-1.5 h-6 rounded-full bg-blue-600"></span>
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                            Tentang Komunitas
                        </h3>
                    </div>

                    <div class="text-xs sm:text-sm text-slate-600 leading-relaxed font-normal space-y-3">
                        @if($community->description)
                            <p class="whitespace-pre-line">{{ $community->description }}</p>
                        @else
                            <p>
                                {{ $community->name ?? 'Jakarta Sunday Runners (JSR)' }} adalah ekosistem olahraga terbuka yang berbasis di {{ $community->location ?? 'Gelora Bung Karno' }}, {{ $community->city ?? 'Jakarta' }}. Kami percaya bahwa setiap langkah aktif berharga—mulai dari pemula yang baru pertama kali mencoba hingga atlet rekreasional yang konsisten meningkatkan performa tubuh.
                            </p>
                            <p class="text-slate-500">
                                Didirikan dengan semangat inklusivitas, sportivitas, dan gaya hidup aktif perkotaan yang berkelanjutan. Bergabung bersama kami tanpa biaya keanggotaan formal, cukup datang dengan perlengkapan olahraga dan tekad untuk terus bergerak sehat bersama.
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Card Event Mendatang dari Komunitas --}}
                <div class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-5">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-1.5 h-6 rounded-full bg-blue-600"></span>
                            <div>
                                <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight leading-tight">
                                    Event Mendatang dari Komunitas
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Kegiatan khusus, tantangan bulanan, dan program latihan bersama.</p>
                            </div>
                        </div>

                        <button type="button"
                                @click="activeTab = 'event'"
                                class="text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors whitespace-nowrap hidden sm:inline-flex items-center gap-1 cursor-pointer">
                            <span>Lihat Kalender Penuh</span>
                            <span>&rarr;</span>
                        </button>
                    </div>

                    {{-- List Card Event Sederhana (Dengan Relasi Database atau Dummy Fallback) --}}
                    <div class="space-y-4">
                        
                        {{-- Event 1: 15K Long Run --}}
                        <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/70 border border-slate-200/70 hover:border-blue-300 hover:bg-white hover:shadow-xs transition-all duration-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-start gap-4">
                                {{-- Thumbnail Event --}}
                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden bg-gradient-to-br from-blue-600 to-cyan-500 flex-shrink-0 relative">
                                    <img src="https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?auto=format&fit=crop&w=400&q=80"
                                         alt="15K Long Run"
                                         class="w-full h-full object-cover">
                                    <span class="absolute top-1.5 left-1.5 px-2 py-0.5 rounded-md bg-slate-950/80 text-white text-[9px] font-extrabold uppercase tracking-wide">
                                        15K RACE
                                    </span>
                                </div>

                                {{-- Event Details --}}
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2 text-xs font-semibold text-blue-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Minggu Depan • 05:30 WIB</span>
                                    </div>

                                    <h4 class="text-sm sm:text-base font-extrabold text-slate-900 leading-snug">
                                        {{ $community->name ?? 'JSR' }} Road to Jakarta Marathon 15K Long Run
                                    </h4>

                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span>GBK Plaza Timur</span>
                                        </span>
                                        <span class="text-slate-300">•</span>
                                        <span class="inline-flex items-center gap-1 text-cyan-700 font-semibold">
                                            <span>Free Guided Pacer</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Quota & Action Button --}}
                            <div class="flex flex-col items-start md:items-end justify-center gap-2 flex-shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                                <div class="w-full md:w-40">
                                    <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500 mb-1">
                                        <span>Sisa Kuota:</span>
                                        <span class="text-blue-600 font-bold">45 / 60 Peserta</span>
                                    </div>
                                    <div class="w-full h-1.5 rounded-full bg-slate-200 overflow-hidden">
                                        <div class="h-full rounded-full bg-blue-600" style="width: 75%;"></div>
                                    </div>
                                </div>

                                <button type="button"
                                        class="w-full md:w-auto px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-2xs transition-all active:scale-95 cursor-pointer text-center">
                                    Daftar Kegiatan
                                </button>
                            </div>
                        </div>

                        {{-- Event 2: Breakfast Gathering --}}
                        <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/70 border border-slate-200/70 hover:border-blue-300 hover:bg-white hover:shadow-xs transition-all duration-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex items-start gap-4">
                                {{-- Thumbnail Event --}}
                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden bg-gradient-to-br from-emerald-600 to-teal-500 flex-shrink-0 relative">
                                    <img src="https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=400&q=80"
                                         alt="Breakfast Gathering"
                                         class="w-full h-full object-cover">
                                    <span class="absolute top-1.5 left-1.5 px-2 py-0.5 rounded-md bg-slate-950/80 text-white text-[9px] font-extrabold uppercase tracking-wide">
                                        CASUAL 5K
                                    </span>
                                </div>

                                {{-- Event Details --}}
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Dua Minggu Lagi • 06:15 WIB</span>
                                    </div>

                                    <h4 class="text-sm sm:text-base font-extrabold text-slate-900 leading-snug">
                                        Beginner 5K &amp; Breakfast Gathering
                                    </h4>

                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                        <span class="inline-flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span>Hutan Kota GBK</span>
                                        </span>
                                        <span class="text-slate-300">•</span>
                                        <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold">
                                            <span>Sarapan Bersama</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Quota & Action Button --}}
                            <div class="flex flex-col items-start md:items-end justify-center gap-2 flex-shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                                <div class="w-full md:w-40">
                                    <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500 mb-1">
                                        <span>Sisa Kuota:</span>
                                        <span class="text-rose-600 font-bold">28 / 30 Peserta</span>
                                    </div>
                                    <div class="w-full h-1.5 rounded-full bg-slate-200 overflow-hidden">
                                        <div class="h-full rounded-full bg-rose-500" style="width: 93%;"></div>
                                    </div>
                                </div>

                                <button type="button"
                                        class="w-full md:w-auto px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-2xs transition-all active:scale-95 cursor-pointer text-center">
                                    Daftar Kegiatan
                                </button>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            {{-- ------------------------------------------------------
                 TAB 2: EVENT LENGKAP
                 ------------------------------------------------------ --}}
            <div x-show="activeTab === 'event'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
                
                <div class="flex items-center gap-2.5">
                    <span class="w-1.5 h-6 rounded-full bg-blue-600"></span>
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                            Semua Event &amp; Jadwal Kegiatan
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Daftar agenda latihan dan kompetisi komunitas.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Event Card 1 --}}
                    <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/60 hover:bg-white hover:shadow-xs transition-all flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-700">RACE RUNNING</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-2">JSR Road to Jakarta Marathon 15K</h4>
                            <p class="text-xs text-slate-500 mt-1">Latihan simulasi pace jarak menengah dengan pacer bersertifikasi.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="font-bold text-blue-600">Minggu, 05:30 WIB</span>
                            <button type="button" class="btn-primary text-xs py-1.5 px-3 rounded-lg">Ikuti Event</button>
                        </div>
                    </div>

                    {{-- Event Card 2 --}}
                    <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/60 hover:bg-white hover:shadow-xs transition-all flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">GATHERING</span>
                            <h4 class="text-sm font-bold text-slate-900 mt-2">Beginner 5K &amp; Recovery Breakfast</h4>
                            <p class="text-xs text-slate-500 mt-1">Lari santai khusus pemula mengelilingi jalur rindang Hutan Kota.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="font-bold text-emerald-600">Sabtu, 06:15 WIB</span>
                            <button type="button" class="btn-primary text-xs py-1.5 px-3 rounded-lg">Ikuti Event</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ------------------------------------------------------
                 TAB 3: ANGGOTA KOMUNITAS
                 ------------------------------------------------------ --}}
            <div x-show="activeTab === 'anggota'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-1.5 h-6 rounded-full bg-blue-600"></span>
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                            Anggota &amp; Struktur Komunitas
                        </h3>
                    </div>
                    <span class="text-xs font-semibold text-slate-500">{{ number_format($community->members_count ?? 2450) }} Member</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5">
                    {{-- Koordinator / Owner --}}
                    <div class="p-3.5 rounded-2xl bg-blue-50/50 border border-blue-200/80 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 shadow-2xs">
                            {{ $ownerInitials }}
                        </div>
                        <div class="truncate">
                            <div class="text-xs font-bold text-slate-900 truncate">{{ $owner?->name ?? 'Dimas Suryo, S.Or.' }}</div>
                            <div class="text-[10px] font-bold text-blue-600">Founder &amp; Owner</div>
                        </div>
                    </div>

                    {{-- Member 1 --}}
                    <div class="p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">
                            AP
                        </div>
                        <div class="truncate">
                            <div class="text-xs font-bold text-slate-900 truncate">Aditya Pratama</div>
                            <div class="text-[10px] text-slate-400 font-medium">Core Member • Pace 5:12</div>
                        </div>
                    </div>

                    {{-- Member 2 --}}
                    <div class="p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">
                            SR
                        </div>
                        <div class="truncate">
                            <div class="text-xs font-bold text-slate-900 truncate">Siti Rahmawati</div>
                            <div class="text-[10px] text-slate-400 font-medium">Core Member • Pace 5:45</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ------------------------------------------------------
                 TAB 4: GALERI FOTO
                 ------------------------------------------------------ --}}
            <div x-show="activeTab === 'galeri'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="rounded-3xl bg-white border border-slate-200/80 p-6 sm:p-8 shadow-xs space-y-6">
                
                <div class="flex items-center gap-2.5">
                    <span class="w-1.5 h-6 rounded-full bg-blue-600"></span>
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                            Galeri Foto Sesi Olahraga
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Dokumentasi momen keceriaan dan keringat bersama.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5">
                    <div class="h-36 sm:h-44 rounded-2xl overflow-hidden bg-slate-100">
                        <img src="https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=500&q=80" alt="Moment 1" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                    </div>
                    <div class="h-36 sm:h-44 rounded-2xl overflow-hidden bg-slate-100">
                        <img src="https://images.unsplash.com/photo-1571008887538-b36bb32f4571?auto=format&fit=crop&w=500&q=80" alt="Moment 2" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                    </div>
                    <div class="h-36 sm:h-44 rounded-2xl overflow-hidden bg-slate-100">
                        <img src="https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?auto=format&fit=crop&w=500&q=80" alt="Moment 3" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                    </div>
                </div>
            </div>

        </div>

        {{-- ==========================================================
             KOLOM KANAN (4 COLS): Widget Sidebar Informasi Tambahan
             ========================================================== --}}
        <div class="lg:col-span-4 space-y-6">
            
            {{-- WIDGET 1: KOORDINATOR KOMUNITAS --}}
            <div class="rounded-3xl bg-white border border-slate-200/80 p-6 shadow-xs">
                
                <div class="flex items-center gap-2.5 mb-4">
                    <span class="w-1.5 h-5 rounded-full bg-blue-600"></span>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">
                        Koordinator Komunitas
                    </h3>
                </div>

                <div class="p-4 rounded-2xl bg-gradient-to-br from-blue-50/60 to-cyan-50/40 border border-blue-100/80 flex items-center gap-3.5">
                    {{-- Avatar Koordinator --}}
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 text-white font-extrabold text-sm flex items-center justify-center flex-shrink-0 shadow-xs border border-white">
                        @if($owner?->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($owner->avatar))
                            <img src="{{ asset('storage/' . $owner->avatar) }}" alt="{{ $owner->name }}" class="w-full h-full object-cover rounded-xl">
                        @else
                            {{ $ownerInitials }}
                        @endif
                    </div>

                    {{-- Nama & Kredensial --}}
                    <div class="min-w-0">
                        <div class="text-xs sm:text-sm font-extrabold text-slate-900 leading-snug truncate">
                            {{ $owner?->name ?? 'Dimas Suryo, S.Or.' }}
                        </div>
                        <div class="text-[11px] font-semibold text-blue-600">
                            {{ $owner?->isCommunityOwner() ? 'Head Coach & Founder' : 'Koordinator Komunitas' }}
                        </div>
                        <div class="text-[10px] text-slate-400 font-medium flex items-center gap-1 mt-0.5">
                            <span class="text-cyan-600 font-bold">✓</span> Certified Coach IAAF (dummy)
                        </div>
                    </div>
                </div>

            </div>

            {{-- WIDGET 2: LOKASI & BASECAMP (Teks Berikon Sesuai Instruksi, Tanpa Maps) --}}
            <div class="rounded-3xl bg-white border border-slate-200/80 p-6 shadow-xs space-y-4">
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-1.5 h-5 rounded-full bg-blue-600"></span>
                        <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">
                            Lokasi &amp; Basecamp
                        </h3>
                    </div>
                    <span class="text-[11px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100">
                        {{ $community->city ?? 'Jakarta' }}
                    </span>
                </div>

                {{-- Teks Berikon untuk Lokasi, Kota, dan Provinsi --}}
                <div class="space-y-3 p-4 rounded-2xl bg-slate-50/70 border border-slate-100 text-xs">
                    
                    {{-- Titik Basecamp --}}
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Basecamp Utama</span>
                            <span class="font-bold text-slate-800 text-xs sm:text-sm">
                                {{ $community->location ?? 'Gate 7 Gelora Bung Karno' }}
                            </span>
                        </div>
                    </div>

                    {{-- Kota & Provinsi --}}
                    <div class="flex items-start gap-3 pt-2.5 border-t border-slate-200/60">
                        <div class="w-8 h-8 rounded-lg bg-cyan-100 text-cyan-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Wilayah / Kota</span>
                            <span class="font-semibold text-slate-700">
                                {{ $community->city ?? 'Jakarta Pusat' }}{{ $community->province ? ', ' . $community->province : ', DKI Jakarta' }}
                            </span>
                        </div>
                    </div>

                    {{-- Panduan Arah --}}
                    <div class="pt-2 text-[11px] text-slate-500 leading-relaxed border-t border-slate-200/60">
                        Titik kumpul: Dekat patung atau gerbang utama venue. Akses mudah via transportasi umum (MRT, KRL, TransJakarta).
                    </div>
                </div>

                {{-- Action Link Petunjuk Arah --}}
                @php
                    $mapsQuery = urlencode(($community->location ?? 'Gate 7 GBK') . ' ' . ($community->city ?? 'Jakarta'));
                @endphp
                <a href="https://www.google.com/maps/search/?api=1&query={{ $mapsQuery }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 hover:text-blue-600 hover:border-blue-300 font-bold text-xs shadow-2xs transition-all active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    <span>Buka Petunjuk Arah di Maps</span>
                </a>

            </div>

            {{-- WIDGET 3: TOP MILEAGE BULAN INI (Top Members Dummy Data) --}}
            <div class="rounded-3xl bg-white border border-slate-200/80 p-6 shadow-xs space-y-4">
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-1.5 h-5 rounded-full bg-blue-600"></span>
                        <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">
                            Top Mileage Bulan Ini
                        </h3>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-500">
                        {{ now()->translatedFormat('M Y') }}
                    </span>
                </div>

                <div class="space-y-3">
                    {{-- Rank 1 --}}
                    <div class="p-3 rounded-2xl bg-amber-50/40 border border-amber-200/80 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-amber-500 text-white font-extrabold text-xs flex items-center justify-center shadow-2xs">1</span>
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-cyan-500 text-white font-bold text-xs flex items-center justify-center">
                                AP
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Aditya Pratama</div>
                                <div class="text-[10px] text-slate-400 font-medium">Pace Rata-rata 5:12</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-extrabold text-blue-600">184.2</div>
                            <div class="text-[9px] font-bold text-slate-400">KM</div>
                        </div>
                    </div>

                    {{-- Rank 2 --}}
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-slate-400 text-white font-extrabold text-xs flex items-center justify-center">2</span>
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-500 to-emerald-600 text-white font-bold text-xs flex items-center justify-center">
                                SR
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Siti Rahmawati</div>
                                <div class="text-[10px] text-slate-400 font-medium">Pace Rata-rata 5:45</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-extrabold text-blue-600">162.8</div>
                            <div class="text-[9px] font-bold text-slate-400">KM</div>
                        </div>
                    </div>

                    {{-- Rank 3 --}}
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-amber-700/80 text-white font-extrabold text-xs flex items-center justify-center">3</span>
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600 text-white font-bold text-xs flex items-center justify-center">
                                KW
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Kevin Wijaya</div>
                                <div class="text-[10px] text-slate-400 font-medium">Pace Rata-rata 6:00</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-extrabold text-blue-600">148.5</div>
                            <div class="text-[9px] font-bold text-slate-400">KM</div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- WIDGET 4: KOMUNITAS SERUPA --}}
            <div class="rounded-3xl bg-white border border-slate-200/80 p-6 shadow-xs space-y-4">
                
                <div class="flex items-center gap-2.5">
                    <span class="w-1.5 h-5 rounded-full bg-blue-600"></span>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">
                        Komunitas Serupa
                    </h3>
                </div>

                <div class="space-y-2.5">
                    
                    {{-- Item 1 --}}
                    <a href="{{ route('user.explore') }}"
                       class="p-3 rounded-2xl bg-slate-50/70 hover:bg-blue-50/50 border border-slate-100 hover:border-blue-200 flex items-center justify-between transition-all group">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 font-extrabold text-xs flex items-center justify-center flex-shrink-0">
                                SSRC
                            </div>
                            <div class="truncate">
                                <div class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition-colors truncate">
                                    Senayan Stride Club
                                </div>
                                <div class="text-[10px] text-slate-400 font-medium">
                                    Lari Subuh • 1.2K Anggota
                                </div>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-300 group-hover:text-blue-600 group-hover:translate-x-0.5 transition-all flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    {{-- Item 2 --}}
                    <a href="{{ route('user.explore') }}"
                       class="p-3 rounded-2xl bg-slate-50/70 hover:bg-blue-50/50 border border-slate-100 hover:border-blue-200 flex items-center justify-between transition-all group">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-cyan-100 text-cyan-700 font-extrabold text-xs flex items-center justify-center flex-shrink-0">
                                JRC
                            </div>
                            <div class="truncate">
                                <div class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition-colors truncate">
                                    Jakarta Road Cycling &amp; Run
                                </div>
                                <div class="text-[10px] text-slate-400 font-medium">
                                    Multisport • 3.4K Anggota
                                </div>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-300 group-hover:text-blue-600 group-hover:translate-x-0.5 transition-all flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>
@endsection
