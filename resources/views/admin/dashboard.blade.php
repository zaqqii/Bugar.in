@extends('layouts.admin')

@section('title', 'Dashboard Console Ops — Bugar.in')

@section('content')
<div class="space-y-8">

    {{-- 1. Hero Header Console Banner --}}
    <div class="relative overflow-hidden rounded-3xl border border-slate-800 bg-gradient-to-r from-slate-900 via-slate-900/90 to-blue-950/40 p-6 sm:p-8 shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-bold mb-3 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Platform Operations &bull; Status Normal</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight">
                    Selamat bertugas, {{ auth()->user()->name ?? 'Administrator' }}! 👋
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1.5 max-w-2xl leading-relaxed">
                    Pantau seluruh metrik operasional komunitas olahraga, verifikasi izin penyelenggara, serta pastikan integritas ekosistem Bugar.in berjalan aman.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="#verifikasi" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Tinjau Verifikasi ({{ $stats['pending_verifications'] }})</span>
                </a>
                <a href="{{ route('landing') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-semibold text-xs border border-slate-700 transition-all flex items-center gap-2">
                    <span>Pratinjau Publik &nearr;</span>
                </a>
            </div>
        </div>
    </div>

    {{-- 2. KPI / Platform Metrics Tiles --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        {{-- Card 1: Total Pengguna --}}
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm hover:border-slate-700 transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pengguna Aktif</span>
                <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20 flex items-center justify-center text-sm">
                    👥
                </div>
            </div>
            <div class="text-3xl font-extrabold text-white tracking-tight">
                {{ number_format($stats['total_users']) }}
            </div>
            <div class="mt-2 flex items-center gap-2 text-xs text-slate-400">
                <span class="text-emerald-400 font-bold font-mono">+{{ $stats['new_users_this_month'] }}</span>
                <span>pendaftar baru bulan ini</span>
            </div>
        </div>

        {{-- Card 2: Komunitas & Verifikasi --}}
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm hover:border-slate-700 transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Komunitas</span>
                <div class="w-9 h-9 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 flex items-center justify-center text-sm">
                    🏟️
                </div>
            </div>
            <div class="text-3xl font-extrabold text-white tracking-tight">
                {{ number_format($stats['total_communities']) }}
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-emerald-400 font-semibold">{{ $stats['verified_communities'] }} Terverifikasi</span>
                @if($stats['pending_verifications'] > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        {{ $stats['pending_verifications'] }} Menunggu
                    </span>
                @else
                    <span class="text-slate-400">0 Pending</span>
                @endif
            </div>
        </div>

        {{-- Card 3: Event & Kompetisi --}}
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm hover:border-slate-700 transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Event Olahraga</span>
                <div class="w-9 h-9 rounded-xl bg-orange-500/10 text-orange-400 border border-orange-500/20 flex items-center justify-center text-sm">
                    🏆
                </div>
            </div>
            <div class="text-3xl font-extrabold text-white tracking-tight">
                {{ number_format($stats['total_events']) }}
            </div>
            <div class="mt-2 flex items-center gap-2 text-xs text-slate-400">
                <span class="text-orange-400 font-bold font-mono">{{ $stats['active_events'] }}</span>
                <span>event aktif / terbuka</span>
            </div>
        </div>

        {{-- Card 4: Sesi & Partisipasi --}}
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm hover:border-slate-700 transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Partisipasi Sesi</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-sm">
                    ⚡
                </div>
            </div>
            <div class="text-3xl font-extrabold text-white tracking-tight">
                {{ number_format($stats['total_participations']) }}
            </div>
            <div class="mt-2 flex items-center gap-2 text-xs text-slate-400">
                <span class="text-slate-300 font-semibold font-mono">{{ $stats['total_schedules'] }}</span>
                <span>sesi jadwal latihan aktif</span>
            </div>
        </div>
    </div>

    {{-- 3. Antrean Verifikasi Komunitas (#verifikasi) --}}
    <section id="verifikasi" class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-800">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full {{ $stats['pending_verifications'] > 0 ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400' }}"></span>
                    <h2 class="text-base sm:text-lg font-bold text-white tracking-tight">Antrean Permohonan Verifikasi Komunitas</h2>
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    Verifikasi memberikan hak bagi penyelenggara komunitas untuk menerbitkan event berbayar dan memperoleh lencana resmi.
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $stats['pending_verifications'] > 0 ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' }} self-start sm:self-auto">
                {{ $stats['pending_verifications'] }} Permohonan Perlu Tindakan
            </span>
        </div>

        @if($pendingCommunities->isEmpty())
            <div class="py-12 px-4 text-center max-w-md mx-auto">
                <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-2xl mx-auto mb-3">
                    ✓
                </div>
                <h4 class="text-sm font-bold text-white mb-1">Semua Antrean Bersih</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Tidak ada pengajuan verifikasi komunitas yang sedang menunggu persetujuan saat ini.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead>
                        <tr class="border-b border-slate-800 text-[11px] uppercase tracking-wider text-slate-400 bg-slate-950/40">
                            <th class="py-3 px-4 rounded-l-xl">Nama Komunitas</th>
                            <th class="py-3 px-4">Cabor &bull; Kota</th>
                            <th class="py-3 px-4">Pengelola (Owner)</th>
                            <th class="py-3 px-4">Skema Verifikasi</th>
                            <th class="py-3 px-4">Diajukan</th>
                            <th class="py-3 px-4 text-right rounded-r-xl">Aksi Persetujuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($pendingCommunities as $comm)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-white">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 border border-blue-500/30 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($comm->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="block truncate max-w-xs">{{ $comm->name }}</span>
                                            <span class="text-[10px] text-slate-400 font-normal">{{ $comm->slug }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-blue-300 border border-slate-700">
                                        {{ ucfirst($comm->sport_category ?? 'Umum') }}
                                    </span>
                                    <span class="text-slate-400 block text-[11px] mt-0.5">{{ $comm->city ?? 'Indonesia' }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-medium text-slate-200 block">{{ $comm->owner?->name ?? 'User' }}</span>
                                    <span class="text-slate-400 text-[11px] font-mono">{{ $comm->owner?->email ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($comm->verification_type === 'paid')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                            Berbayar &bull; Rp {{ number_format($comm->verification_fee ?? 99000, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                            Kriteria Followers ({{ $comm->followers_count }} Followers)
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-400 font-mono text-[11px]">
                                    {{ $comm->verification_requested_at ? $comm->verification_requested_at->format('d M Y, H:i') : $comm->created_at->format('d M Y') }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        {{-- Tombol Setujui --}}
                                        <form method="POST" action="{{ route('admin.communities.verify', $comm->id) }}" onsubmit="return confirm('Setujui verifikasi untuk komunitas {{ $comm->name }}?');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-colors shadow-2xs">
                                                ✓ Setujui
                                            </button>
                                        </form>

                                        {{-- Tombol Tolak --}}
                                        <form method="POST" action="{{ route('admin.communities.reject', $comm->id) }}" onsubmit="return confirm('Tolak permohonan verifikasi ini?');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-red-500/20 text-slate-300 hover:text-red-300 border border-slate-700 hover:border-red-500/40 font-bold text-xs transition-colors">
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- 4. Grid Dua Kolom: Komunitas Terbaru & Event Terbaru --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Kolom Kiri: Komunitas Terbaru (7 Kolom) --}}
        <div id="komunitas" class="lg:col-span-7 p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <h3 class="text-sm sm:text-base font-bold text-white tracking-tight">Klub &amp; Komunitas Terdaftar Terbaru</h3>
                </div>
                <span class="text-xs font-mono text-slate-400">Total: {{ $stats['total_communities'] }}</span>
            </div>

            <div class="space-y-3">
                @forelse($recentCommunities as $rc)
                    <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800/80 hover:border-slate-700 transition-all flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-slate-800 text-slate-200 border border-slate-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                {{ strtoupper(substr($rc->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-bold text-white truncate">{{ $rc->name }}</h4>
                                    @if($rc->isVerified())
                                        <span class="text-blue-400 text-xs" title="Terverifikasi">✓</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-400 truncate mt-0.5">
                                    {{ ucfirst($rc->sport_category) }} &bull; {{ $rc->city ?? 'Indonesia' }} &bull; Oleh: {{ $rc->owner?->name ?? 'Pengguna' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded {{ $rc->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
                                {{ $rc->is_active ? 'Aktif' : 'Non-aktif' }}
                            </span>

                            <form method="POST" action="{{ route('admin.communities.toggle-status', $rc->id) }}">
                                @csrf
                                <button type="submit"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                                        title="{{ $rc->is_active ? 'Non-aktifkan komunitas' : 'Aktifkan komunitas' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-400">Belum ada komunitas terdaftar.</div>
                @endforelse
            </div>
        </div>

        {{-- Kolom Kanan: Event & Turnamen Terbaru (5 Kolom) --}}
        <div id="events" class="lg:col-span-5 p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                    <h3 class="text-sm sm:text-base font-bold text-white tracking-tight">Event &amp; Turnamen Terbaru</h3>
                </div>
                <span class="text-xs font-mono text-slate-400">Total: {{ $stats['total_events'] }}</span>
            </div>

            <div class="space-y-3">
                @forelse($recentEvents as $re)
                    <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800/80 hover:border-slate-700 transition-all space-y-1.5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-orange-500/20 text-orange-300 border border-orange-500/30">
                                {{ ucfirst($re->type ?? 'Event') }}
                            </span>
                            <span class="text-xs font-bold {{ $re->registration_fee > 0 ? 'text-amber-400' : 'text-emerald-400' }}">
                                {{ $re->registration_fee > 0 ? 'Rp ' . number_format($re->registration_fee, 0, ',', '.') : 'Gratis' }}
                            </span>
                        </div>
                        <h4 class="text-xs font-bold text-white truncate">{{ $re->title }}</h4>
                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span class="truncate">{{ $re->community?->name ?? 'Komunitas' }} &bull; {{ $re->location }}</span>
                            <span class="font-mono text-slate-400">{{ \Carbon\Carbon::parse($re->event_date)->translatedFormat('d M') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-400">Belum ada event terdaftar.</div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- 5. Grid Dua Kolom: Pengguna Terbaru & Distribusi Cabang Olahraga --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Kolom Kiri: Basis Pengguna Terbaru (#pengguna) --}}
        <div id="pengguna" class="lg:col-span-7 p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                    <h3 class="text-sm sm:text-base font-bold text-white tracking-tight">Akun Pengguna Terdaftar Baru</h3>
                </div>
                <span class="text-xs font-mono text-slate-400">Total: {{ $stats['total_users'] + $stats['total_community_owners'] }} akun</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @forelse($recentUsers as $ru)
                    <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800/80 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-slate-800 to-slate-700 text-slate-200 border border-slate-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                            {{ strtoupper(substr($ru->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-1">
                                <h4 class="text-xs font-bold text-white truncate">{{ $ru->name }}</h4>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider {{ $ru->role === 'community_owner' ? 'bg-amber-500/20 text-amber-300' : ($ru->role === 'admin' ? 'bg-red-500/20 text-red-300' : 'bg-blue-500/20 text-blue-300') }}">
                                    {{ $ru->role === 'community_owner' ? 'Owner' : ($ru->role === 'admin' ? 'Admin' : 'User') }}
                                </span>
                            </div>
                            <p class="text-[10px] text-slate-400 font-mono truncate mt-0.5">{{ $ru->email }}</p>
                        </div>
                    </div>
                @empty
                    <div class="sm:col-span-2 p-6 text-center text-xs text-slate-400">Belum ada akun pengguna.</div>
                @endforelse
            </div>
        </div>

        {{-- Kolom Kanan: Distribusi Cabang Olahraga --}}
        <div class="lg:col-span-5 p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <h3 class="text-sm sm:text-base font-bold text-white tracking-tight">Kategori Cabor Terpopuler</h3>
                </div>
                <span class="text-xs font-mono text-slate-400">Komunitas</span>
            </div>

            <div class="space-y-3.5">
                @php
                    $maxSportCount = $sportDistribution->max('count') ?: 1;
                @endphp
                @forelse($sportDistribution as $sp)
                    @php
                        $percentage = round(($sp->count / $maxSportCount) * 100);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-bold text-slate-200 capitalize">{{ $sp->sport_category }}</span>
                            <span class="font-mono text-slate-400">{{ $sp->count }} klub</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-cyan-400"
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-400">Belum ada data cabang olahraga.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
