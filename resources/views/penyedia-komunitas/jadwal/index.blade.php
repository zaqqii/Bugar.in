@extends('layouts.penyedia-komunitas')

@section('title', 'Kelola Jadwal Sesi - Penyedia Komunitas')

@section('content')

    {{-- EMPTY STATE JIKA KOMUNITAS BELUM DIBUAT --}}
    @if(!$community)
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-20 h-20 rounded-2xl bg-blue-50 flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="w-10 h-10 text-blue-600">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 mb-2">Komunitas Belum Dibuat</h2>
            <p class="text-slate-500 max-w-md mb-8 text-sm">
                Kamu belum memiliki data komunitas olahraga. Buat komunitas terlebih dahulu untuk mulai mengatur jadwal latihan.
            </p>
            <a href="{{ route('penyedia-komunitas.dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white font-bold text-sm shadow-md hover:bg-blue-700 transition">
                Kembali ke Dashboard
            </a>
        </div>
    @else

        {{-- FLASH SUCCESS MESSAGE --}}
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shadow-sm shrink-0">
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

        {{-- FLASH ERROR MESSAGE --}}
        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center shadow-sm shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        {{-- FLASH INFO MESSAGE --}}
        @if(session('info'))
            <div class="mb-6 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-800 text-sm font-semibold flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-blue-500 text-white flex items-center justify-center shadow-sm shrink-0">
                        ℹ️
                    </div>
                    <span>{{ session('info') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-blue-500 hover:text-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        {{-- PAGE HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold tracking-wide uppercase mb-2">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    JADWAL SESI BERTANGGAL &bull; {{ strtoupper($community->sport_category ?? 'Olahraga') }} &bull; {{ strtoupper($community->name) }}
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Kelola Jadwal Latihan
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Setiap baris jadwal merupakan satu pertemuan bertanggal lengkap dengan tingkat level, kuota peserta, dan pengamanan kehadiran.
                </p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('penyedia-komunitas.jadwal.blocks') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-xs">
                    <span>🛡️</span>
                    <span>Daftar Blokir Anggota</span>
                </a>
                <a href="{{ route('penyedia-komunitas.jadwal.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-700 via-blue-600 to-cyan-500 hover:from-blue-800 hover:via-blue-700 hover:to-cyan-600 text-white text-sm font-bold shadow-lg shadow-blue-500/20 transition-all duration-200 hover:-translate-y-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Jadwal</span>
                </a>
            </div>
        </div>

        {{-- METRIC SUMMARY CARDS --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            {{-- Card 1: Sesi Mendatang --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sesi Mendatang</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                        📅
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ number_format($counts['upcoming']) }}</span>
                    <span class="text-xs text-slate-400 font-medium">dari {{ number_format($counts['total']) }} sesi</span>
                </div>
            </div>

            {{-- Card 2: Total Peserta Aktif --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Peserta Terdaftar</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ number_format($counts['total_participants']) }}</span>
                    <span class="text-xs text-slate-400 font-medium">partisipasi aktif</span>
                </div>
            </div>

            {{-- Card 3: Jadwal Streak Aktif --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Fitur Streak</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                        🔥
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ number_format($counts['streak_active']) }}</span>
                    <span class="text-xs text-slate-400 font-medium">sesi ber-streak</span>
                </div>
            </div>

            {{-- Card 4: Riwayat Selesai --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Riwayat Selesai</span>
                    <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-sm">
                        ✓
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ number_format($counts['history']) }}</span>
                    <span class="text-xs text-slate-400 font-medium">sesi terlaksana</span>
                </div>
            </div>
        </div>

        {{-- TAB NAVIGASI: MENDATANG vs RIWAYAT --}}
        <div class="flex items-center gap-2 border-b border-slate-200 mb-6">
            <a href="{{ route('penyedia-komunitas.jadwal.index', array_merge(request()->query(), ['tab' => 'upcoming'])) }}"
               class="px-5 py-3 text-sm font-bold border-b-2 transition flex items-center gap-2 {{ $selectedTab === 'upcoming' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <span>Sesi Mendatang</span>
                <span class="px-2 py-0.5 rounded-full text-xs {{ $selectedTab === 'upcoming' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ $counts['upcoming'] }}
                </span>
            </a>
            <a href="{{ route('penyedia-komunitas.jadwal.index', array_merge(request()->query(), ['tab' => 'history'])) }}"
               class="px-5 py-3 text-sm font-bold border-b-2 transition flex items-center gap-2 {{ $selectedTab === 'history' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <span>Riwayat Selesai</span>
                <span class="px-2 py-0.5 rounded-full text-xs {{ $selectedTab === 'history' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ $counts['history'] }}
                </span>
            </a>
        </div>

        {{-- FILTER TOOLBAR --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm mb-6">
            <form method="GET" action="{{ route('penyedia-komunitas.jadwal.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <input type="hidden" name="tab" value="{{ $selectedTab }}">

                <div class="flex flex-wrap items-center gap-3">
                    {{-- Filter Level --}}
                    <div>
                        <label for="filter-level" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Level</label>
                        <select id="filter-level" name="level" onchange="this.form.submit()"
                                class="px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 bg-slate-50 text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="all" {{ $selectedLevel === 'all' ? 'selected' : '' }}>Semua Level</option>
                            <option value="all_levels" {{ $selectedLevel === 'all_levels' ? 'selected' : '' }}>Semua level (Bebas)</option>
                            <option value="beginner" {{ $selectedLevel === 'beginner' ? 'selected' : '' }}>Pemula</option>
                            <option value="intermediate" {{ $selectedLevel === 'intermediate' ? 'selected' : '' }}>Menengah</option>
                            <option value="advanced" {{ $selectedLevel === 'advanced' ? 'selected' : '' }}>Mahir</option>
                        </select>
                    </div>

                    {{-- Filter Status --}}
                    <div>
                        <label for="filter-status" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status</label>
                        <select id="filter-status" name="status" onchange="this.form.submit()"
                                class="px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 bg-slate-50 text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="active" {{ $selectedStatus === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ $selectedStatus === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                            <option value="open" {{ $selectedStatus === 'open' ? 'selected' : '' }}>Slot Terbuka</option>
                            <option value="almost_full" {{ $selectedStatus === 'almost_full' ? 'selected' : '' }}>Hampir Penuh (&ge;80%)</option>
                            <option value="full" {{ $selectedStatus === 'full' ? 'selected' : '' }}>Penuh</option>
                            <option value="cancelled" {{ $selectedStatus === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                </div>

                @if($selectedLevel !== 'all' || $selectedStatus !== 'all')
                    <div class="flex items-center">
                        <a href="{{ route('penyedia-komunitas.jadwal.index', ['tab' => $selectedTab]) }}"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Reset Filter</span>
                        </a>
                    </div>
                @endif
            </form>
        </div>

        {{-- DAFTAR JADWAL --}}
        @if($schedules->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-sm">
                <div class="w-16 h-16 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800 mb-1">
                    @if($selectedLevel !== 'all' || $selectedStatus !== 'all')
                        Tidak ada sesi yang cocok dengan filter
                    @else
                        {{ $selectedTab === 'upcoming' ? 'Belum Ada Sesi Jadwal Mendatang' : 'Belum Ada Riwayat Sesi Selesai' }}
                    @endif
                </h3>
                <p class="text-slate-500 text-xs max-w-sm mx-auto mb-6">
                    @if($selectedLevel !== 'all' || $selectedStatus !== 'all')
                        Coba sesuaikan filter level atau status untuk melihat jadwal lainnya.
                    @else
                        {{ $selectedTab === 'upcoming' ? 'Mulai buat sesi jadwal baru agar anggota komunitas Anda dapat mendaftar dan berlatih bersama.' : 'Sesi jadwal yang telah selesai akan secara otomatis tercatat di tab riwayat ini.' }}
                    @endif
                </p>
                @if($selectedTab === 'upcoming')
                    <a href="{{ route('penyedia-komunitas.jadwal.create') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold shadow-md hover:bg-blue-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Buat Jadwal Baru</span>
                    </a>
                @endif
            </div>
        @else
            @php $totalSchedules = $schedules->count(); @endphp
            <div class="space-y-6" x-data="{
                expanded: false,
                deleteModalOpen: false,
                deleteUrl: '',
                deleteTitle: '',
                confirmDelete(url, title) {
                    this.deleteUrl = url;
                    this.deleteTitle = title;
                    this.deleteModalOpen = true;
                }
            }">
                {{-- Satu Grid Datar: 3 Kolom (3x3 default, sisanya x-show) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($schedules as $i => $schedule)
                        @php
                            $regCount = $schedule->registered_participants_count ?? $schedule->activeParticipantsCount();
                            $capacity = $schedule->capacity;
                            $percent = $capacity ? (int) min(100, round(($regCount / $capacity) * 100)) : 0;
                            $isFinished = $schedule->isFinished();
                            $isCancelled = $schedule->isCancelled();
                            $schDateFormatted = $schedule->date ? $schedule->date->locale('id')->isoFormat('dddd, D MMMM Y') : 'Tanggal Belum Ditentukan';
                            $isToday = $schedule->date ? $schedule->date->isToday() : false;
                        @endphp
                        <div @if($i >= 9) x-show="expanded" x-transition style="display:none;" @endif
                             class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:border-slate-300 transition-all flex flex-col justify-between {{ !$schedule->is_active || $isCancelled ? 'opacity-75 bg-slate-50/60' : '' }}">
                            <div class="space-y-3">
                                {{-- Date Label Header di tiap kartu --}}
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold {{ $isToday ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700' }}">
                                        <span>📅</span>
                                        <span>{{ $schDateFormatted }}</span>
                                        @if($isToday)
                                            <span class="ml-1 text-[10px] uppercase font-extrabold text-blue-200">(Hari Ini)</span>
                                        @endif
                                    </span>
                                </div>

                                {{-- Top Row: Time & Badges --}}
                                <div class="flex items-start justify-between gap-2 flex-wrap">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>{{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }} WIB</span>
                                    </div>

                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        {{-- Level Badge --}}
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $schedule->levelBadgeClass() }}">
                                            {{ $schedule->levelLabel() }}
                                        </span>

                                        {{-- Seri Badge --}}
                                        @if($schedule->series_id)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 text-[10px] font-bold">
                                                🔗 Seri
                                            </span>
                                        @endif

                                        {{-- Streak Badge --}}
                                        @if($schedule->streak_enabled)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold">
                                                🔥 Streak
                                            </span>
                                        @endif

                                        {{-- Dibatalkan / Selesai / Status Badge --}}
                                        @if($isCancelled)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 text-[11px] font-bold">
                                                Dibatalkan
                                            </span>
                                        @elseif($isFinished)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[11px] font-bold">
                                                Selesai
                                            </span>
                                        @elseif(!$schedule->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[11px] font-semibold">
                                                Nonaktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-semibold border border-emerald-200">
                                                Aktif
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Title & Sport --}}
                                <div>
                                    <div class="inline-block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">
                                        {{ $community->sport_category ?? 'Olahraga' }}
                                    </div>
                                    <h3 class="font-extrabold text-slate-900 text-base line-clamp-2 break-words" title="{{ $schedule->title }}">
                                        {{ $schedule->title }}
                                    </h3>
                                </div>

                                {{-- Description --}}
                                @if($schedule->description)
                                    <p class="text-xs text-slate-500 line-clamp-2 break-words">
                                        {{ $schedule->description }}
                                    </p>
                                @endif

                                {{-- Location --}}
                                <div class="flex items-center gap-1.5 text-xs text-slate-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                    <span class="truncate">{{ $schedule->location }}</span>
                                </div>

                                {{-- Quota / Kapasitas Progress --}}
                                <div class="pt-2">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="font-bold text-slate-600">Peserta Terdaftar</span>
                                        <span class="font-bold {{ $capacity && $regCount >= $capacity ? 'text-rose-600' : 'text-slate-900' }}">
                                            {{ $regCount }} {{ $capacity ? '/ ' . $capacity . ' peserta' : 'peserta (Tanpa batas)' }}
                                        </span>
                                    </div>
                                    @if($capacity)
                                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-300 {{ $percent >= 100 ? 'bg-rose-500' : ($percent >= 80 ? 'bg-amber-500' : 'bg-blue-600') }}"
                                                 style="width: {{ $percent }}%"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Bottom Actions Toolbar --}}
                            <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between gap-2 flex-wrap text-xs">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('penyedia-komunitas.jadwal.show', $schedule) }}"
                                       class="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold transition">
                                        Detail & Peserta
                                    </a>

                                    {{-- Tombol Check-in QR (Hanya aktif untuk sesi hari ini) --}}
                                    @php
                                        $tz = config('app.timezone', 'Asia/Jakarta');
                                        $isTodaySess = $schedule->date && \Carbon\Carbon::parse($schedule->date, $tz)->isToday();
                                    @endphp
                                    @if($isTodaySess && !$isCancelled)
                                        <a href="{{ route('penyedia-komunitas.jadwal.checkin', $schedule) }}"
                                           class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition flex items-center gap-1 shadow-xs">
                                            <span>📷 Check-in</span>
                                        </a>
                                    @else
                                        <span title="{{ $isCancelled ? 'Sesi dibatalkan' : ($isFinished ? 'Sesi telah selesai' : 'Check-in hanya aktif pada hari sesi berlangsung') }}"
                                              class="px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-400 font-medium cursor-not-allowed text-[11px] inline-flex items-center">
                                            📷 Check-in
                                        </span>
                                    @endif

                                    @if(!$isCancelled && !$isFinished)
                                        <a href="{{ route('penyedia-komunitas.jadwal.edit', $schedule) }}"
                                           class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold transition">
                                            Edit
                                        </a>
                                    @endif
                                </div>

                                <div class="flex items-center gap-1.5">
                                    {{-- Toggle Aktif / Nonaktif --}}
                                    @if(!$isCancelled && !$isFinished)
                                        <form method="POST" action="{{ route('penyedia-komunitas.jadwal.toggle-active', $schedule) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    title="{{ $schedule->is_active ? 'Nonaktifkan Sesi' : 'Aktifkan Sesi' }}"
                                                    class="p-1.5 rounded-lg border border-slate-200 text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">
                                                @if($schedule->is_active)
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z" />
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                @endif
                                            </button>
                                        </form>

                                        {{-- Tombol Batalkan Sesi --}}
                                        <form method="POST" action="{{ route('penyedia-komunitas.jadwal.cancel', $schedule) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan sesi latihan ini? Seluruh peserta terdaftar akan dibatalkan.')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    title="Batalkan Sesi Ini"
                                                    class="p-1.5 rounded-lg border border-amber-200 text-amber-700 hover:bg-amber-50 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            </button>
                                        </form>

                                        {{-- Tombol Batalkan Seluruh Seri --}}
                                        @if($schedule->series_id)
                                            <form method="POST" action="{{ route('penyedia-komunitas.jadwal.cancel-series', $schedule) }}" onsubmit="return confirm('Batalkan seluruh sesi mendatang dalam seri ini?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        title="Batalkan Seluruh Seri Mendatang"
                                                        class="px-2 py-1.5 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 font-bold text-[10px] hover:bg-amber-100 transition">
                                                    Batal Seri
                                                </button>
                                            </form>
                                        @endif
                                    @endif

                                    {{-- Tombol Hapus --}}
                                    @if($regCount === 0 && !$isCancelled)
                                        <button type="button"
                                                @click="confirmDelete('{{ route('penyedia-komunitas.jadwal.destroy', $schedule) }}', '{{ addslashes($schedule->title) }}')"
                                                title="Hapus Jadwal"
                                                class="p-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Tombol Lihat Semua jika lebih dari 9 sesi (3x3) --}}
                @if($totalSchedules > 9)
                    <div class="mt-4 flex justify-center">
                        <button type="button"
                                @click="expanded = !expanded"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition-all duration-200 group shadow-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                            <span x-text="expanded ? 'Sembunyikan' : 'Lihat Semua {{ $totalSchedules }} Sesi'"></span>
                        </button>
                    </div>
                @endif
            </div>

                {{-- Modal Konfirmasi Hapus --}}
                <div x-show="deleteModalOpen"
                     x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4"
                         @click.away="deleteModalOpen = false">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Hapus Sesi Jadwal?</h3>
                            <p class="text-xs text-slate-500 mt-1">
                                Jadwal <strong x-text="deleteTitle"></strong> akan dihapus permanen. Jadwal yang memiliki peserta aktif tidak dapat dihapus.
                            </p>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="deleteModalOpen = false"
                                    class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                                Batal
                            </button>
                            <form :action="deleteUrl" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md">
                                    Ya, Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endsection
