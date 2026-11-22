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
                    Selamat bertugas, {{ auth()->user()->name ?? 'Administrator' }}
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
                <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
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
                <div class="w-9 h-9 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
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
                <div class="w-9 h-9 rounded-xl bg-orange-500/10 text-orange-400 border border-orange-500/20 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.004 0A6.75 6.75 0 0015 7.5V6a2.25 2.25 0 00-2.25-2.25h-1.5A2.25 2.25 0 009 6v1.5a6.75 6.75 0 002.625 5.375" />
                    </svg>
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
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
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
                <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
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
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-colors shadow-2xs inline-flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                                <span>Setujui</span>
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
                                        <span class="text-blue-400 text-xs inline-flex" title="Terverifikasi">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                        </span>
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

    {{-- 5. Laporan Pengguna Terbaru (#laporan) --}}
    <section id="laporan" class="p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-800">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <a href="{{ route('admin.reports.index') }}" class="group inline-flex items-center gap-2 hover:text-white transition-colors">
                    <h2 class="text-base sm:text-lg font-bold text-white tracking-tight group-hover:text-rose-400 transition-colors">
                        Laporan pengguna terbaru
                    </h2>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 group-hover:text-rose-400 group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>

            <a href="{{ route('admin.reports.index') }}"
               class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold border border-slate-700 transition-all inline-flex items-center gap-1.5 self-start sm:self-auto">
                <span>Lihat semua laporan</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>

        @if($recentReports->isEmpty())
            <div class="py-8 px-4 text-center max-w-md mx-auto">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center mx-auto mb-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <h4 class="text-xs font-bold text-white mb-0.5">Tidak ada laporan aktif</h4>
                <p class="text-[11px] text-slate-400 leading-relaxed">
                    Belum ada laporan pelanggaran yang masuk dari pengguna.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead>
                        <tr class="border-b border-slate-800 text-[10px] uppercase tracking-wider text-slate-400 bg-slate-950/40">
                            <th class="py-3 px-4 rounded-l-xl">ID</th>
                            <th class="py-3 px-4">Alasan laporan</th>
                            <th class="py-3 px-4">Terlapor</th>
                            <th class="py-3 px-4">Pelapor</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4 text-right rounded-r-xl">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($recentReports as $rep)
                            @php
                                $targetName = '-';
                                $targetType = class_basename($rep->reportable_type);
                                if ($rep->reportable) {
                                    $targetName = $rep->reportable->name ?? ($rep->reportable->title ?? ('#' . $rep->reportable_id));
                                }
                            @endphp
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-rose-400">
                                    #{{ $rep->id }}
                                </td>
                                <td class="py-3 px-4 max-w-xs">
                                    <div class="font-bold text-white text-xs truncate">{{ $rep->reason }}</div>
                                    <div class="text-[11px] text-slate-400 truncate mt-0.5">
                                        {{ $rep->description ?: 'Tanpa keterangan tambahan' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold uppercase tracking-wider bg-slate-800 text-slate-300 mb-0.5 inline-block">
                                        {{ $targetType === 'Community' ? 'Komunitas' : ($targetType === 'User' ? 'Pengguna' : $targetType) }}
                                    </span>
                                    <div class="font-semibold text-white truncate max-w-[130px]">{{ $targetName }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-slate-200 truncate max-w-[120px]">{{ $rep->reporter->name ?? 'User dihapus' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $rep->status_badge_class }}">
                                        {{ $rep->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-400 font-mono text-[11px]">
                                    {{ $rep->created_at->diffForHumans() }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('admin.reports.show', $rep->id) }}"
                                       class="px-3 py-1.5 rounded-xl bg-blue-600/10 hover:bg-blue-600 text-blue-400 hover:text-white border border-blue-500/20 font-bold text-xs transition-all inline-flex items-center gap-1.5 shadow-2xs">
                                        <span>Periksa</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- 6. Grid Dua Kolom: Pengguna Terbaru & Distribusi Cabang Olahraga --}}
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

    {{-- 6. Audit Log & Recent Activity Panel (AD-07 SPA / AJAX) --}}
    <section id="recent-activity"
             x-data="{
                 filterAction: 'all',
                 isLoading: false,
                 async fetchLogs(url = null) {
                     this.isLoading = true;
                     const targetUrl = new URL(url || '{{ route('admin.audit-logs.recent-activity') }}', window.location.origin);
                     if (this.filterAction !== 'all') {
                         targetUrl.searchParams.set('action', this.filterAction);
                     }
                     try {
                         const response = await fetch(targetUrl, {
                             headers: {
                                 'X-Requested-With': 'XMLHttpRequest',
                                 'Accept': 'text/html'
                             }
                         });
                         if (response.ok) {
                             const html = await response.text();
                             this.$refs.logContainer.innerHTML = html;
                         }
                     } catch (e) {
                         console.error('Gagal mengambil data log audit:', e);
                     } finally {
                         this.isLoading = false;
                     }
                 }
             }"
             class="p-5 sm:p-6 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
                    <h2 class="text-base sm:text-lg font-extrabold text-white tracking-tight">Aktivitas dan log audit terbaru</h2>
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    Jejak rekaman aktivitas administratif terkini pada platform Bugar.in.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Filter Kategori Aksi Cepat (SPA / AJAX) --}}
                <div class="inline-flex rounded-xl bg-slate-950 p-1 border border-slate-800 text-xs">
                    <button type="button"
                            @click="filterAction = 'all'; fetchLogs()"
                            :class="filterAction === 'all' ? 'bg-cyan-600 text-white shadow-xs font-bold' : 'text-slate-400 hover:text-white'"
                            class="px-2.5 py-1 rounded-lg transition-all cursor-pointer">
                        Semua
                    </button>
                    <button type="button"
                            @click="filterAction = 'community'; fetchLogs()"
                            :class="filterAction === 'community' ? 'bg-cyan-600 text-white shadow-xs font-bold' : 'text-slate-400 hover:text-white'"
                            class="px-2.5 py-1 rounded-lg transition-all cursor-pointer">
                        Komunitas
                    </button>
                    <button type="button"
                            @click="filterAction = 'report'; fetchLogs()"
                            :class="filterAction === 'report' ? 'bg-cyan-600 text-white shadow-xs font-bold' : 'text-slate-400 hover:text-white'"
                            class="px-2.5 py-1 rounded-lg transition-all cursor-pointer">
                        Laporan
                    </button>
                    <button type="button"
                            @click="filterAction = 'user'; fetchLogs()"
                            :class="filterAction === 'user' ? 'bg-cyan-600 text-white shadow-xs font-bold' : 'text-slate-400 hover:text-white'"
                            class="px-2.5 py-1 rounded-lg transition-all cursor-pointer">
                        Pengguna
                    </button>
                </div>

                <a href="{{ route('admin.audit-logs.index') }}"
                   class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition-all inline-flex items-center gap-1.5">
                    <span>Lihat log lengkap</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>

        {{-- Log Container dengan Animasi Loading --}}
        <div class="relative min-h-[160px]">
            <div x-show="isLoading"
                 class="absolute inset-0 bg-slate-900/70 backdrop-blur-2xs rounded-2xl flex items-center justify-center z-10"
                 style="display: none;">
                <div class="flex items-center gap-2 text-xs font-mono text-cyan-400">
                    <svg class="animate-spin h-4 w-4 text-cyan-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Memperbarui data...</span>
                </div>
            </div>

            <div x-ref="logContainer" class="space-y-2.5">
                @include('admin.audit-logs._list', ['auditLogs' => $auditLogs])
            </div>
        </div>
    </section>

</div>
@endsection
