@extends('layouts.penyedia-komunitas')

@section('title', 'Komunitas - Penyedia Komunitas')

@section('content')
@php
    $user = auth()->user();
    $community = \App\Models\Community::where('owner_id', $user?->id)->first();
    $isVerified = $community?->isVerified() ?? false;

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

<div class="max-w-6xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-bold tracking-wide uppercase mb-2">
                <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                Manajemen Komunitas
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Komunitas Saya</h1>
            <p class="text-slate-500 text-sm mt-1">Pratinjau kartu komunitas, status verifikasi publik, dan pengelolaan profil.</p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('penyedia-komunitas.akun.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-500/20 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Edit Cover & Profil</span>
            </a>
            @if($community)
                <a href="{{ route('user.communities.show', $community->id) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition">
                    <span>Halaman Publik</span>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            @endif
        </div>
    </div>

    @if(!$community)
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-sm">
            <h3 class="text-lg font-bold text-slate-800">Komunitas Belum Ditemukan</h3>
            <p class="text-slate-500 text-sm mt-1">Akun Anda belum memiliki profil komunitas terdaftar.</p>
        </div>
    @else
        {{-- Hero Preview Card --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
            {{-- Cover Banner --}}
            <div class="relative h-56 sm:h-72 w-full overflow-hidden bg-slate-100">
                @if($coverUrl)
                    <img src="{{ $coverUrl }}" alt="{{ $community->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-r {{ $gradientClass }} flex items-center justify-center text-white">
                        <span class="text-lg font-bold text-white/80 uppercase tracking-widest">{{ $community->sport_category }}</span>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-black/20"></div>

                {{-- Badges on Cover --}}
                <div class="absolute top-4 left-4 flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full bg-slate-900/70 backdrop-blur-md text-white text-xs font-extrabold uppercase tracking-wider">
                        {{ $community->sport_category }}
                    </span>
                    @if($community->city)
                        <span class="px-3 py-1 rounded-full bg-slate-900/70 backdrop-blur-md text-white text-xs font-semibold flex items-center gap-1">
                            <svg class="w-3 h-3 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            {{ $community->city }}
                        </span>
                    @endif
                </div>

                <div class="absolute top-4 right-4">
                    @if($isVerified)
                        <span class="px-3 py-1 rounded-full bg-emerald-500/90 backdrop-blur-md text-white text-xs font-bold flex items-center gap-1.5 shadow-sm">
                            <svg class="w-3.5 h-3.5 text-white fill-current" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Terverifikasi Resmi
                        </span>
                    @else
                        <a href="{{ route('penyedia-komunitas.akun.index', ['tab' => 'verifikasi']) }}"
                           class="px-3 py-1 rounded-full bg-amber-500/90 hover:bg-amber-600 backdrop-blur-md text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition">
                            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                            Belum Verifikasi • Klik Verifikasi
                        </a>
                    @endif
                </div>

                {{-- Bottom Overlay Info on Cover --}}
                <div class="absolute bottom-6 left-6 right-6 flex items-end justify-between text-white">
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-2xl border-2 border-white shadow-lg overflow-hidden bg-white shrink-0">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="{{ $community->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-primary-600 flex items-center justify-center font-extrabold text-2xl text-white">
                                    {{ strtoupper(substr($community->name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-2xl font-extrabold text-white drop-shadow-sm flex items-center gap-2">
                                {{ $community->name }}
                                @if($isVerified)
                                    <svg class="w-5 h-5 text-cyan-400 fill-current inline-block" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </h2>
                            <p class="text-xs text-white/80 mt-0.5">
                                Kontak PIC: {{ $community->contact_phone ?? '-' }}
                                @if($community->instagram)
                                    • IG: &#64;{{ $community->instagram }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats Bar --}}
            <div class="p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 bg-slate-50/50 border-t border-slate-100">
                <div class="p-4 rounded-xl bg-white border border-slate-200/80">
                    <span class="text-xs text-slate-500 font-semibold block">Pengikut Komunitas</span>
                    <span class="text-xl font-extrabold text-slate-900 mt-1 block">{{ $community->followers_count ?? 0 }}</span>
                </div>
                <div class="p-4 rounded-xl bg-white border border-slate-200/80">
                    <span class="text-xs text-slate-500 font-semibold block">Total Anggota</span>
                    <span class="text-xl font-extrabold text-slate-900 mt-1 block">{{ $community->members_count ?? 0 }}</span>
                </div>
                <div class="p-4 rounded-xl bg-white border border-slate-200/80">
                    <span class="text-xs text-slate-500 font-semibold block">Rating Komunitas</span>
                    <span class="text-xl font-extrabold text-amber-600 mt-1 block flex items-center gap-1">
                        ★ {{ number_format($community->average_rating ?? 0, 1) }}
                    </span>
                </div>
                <div class="p-4 rounded-xl bg-white border border-slate-200/80">
                    <span class="text-xs text-slate-500 font-semibold block">Status Event Berbayar</span>
                    <span class="text-sm font-bold {{ $isVerified ? 'text-emerald-600' : 'text-amber-600' }} mt-1 block">
                        {{ $isVerified ? '✓ Terbuka Penuh' : '🔒 Butuh Verifikasi' }}
                    </span>
                </div>
            </div>

            {{-- Description & Details --}}
            <div class="p-6 sm:p-8 space-y-4">
                <h3 class="text-sm font-bold text-slate-800">Tentang Komunitas</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    {{ $community->description ?: 'Belum ada deskripsi yang ditambahkan. Tambahkan cerita dan jadwal latihan komunitasmu di menu Edit Profil.' }}
                </p>
            </div>
        </div>
    @endif
</div>
@endsection
