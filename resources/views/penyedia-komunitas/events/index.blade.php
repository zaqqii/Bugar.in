@extends('layouts.penyedia-komunitas')

@section('title', 'Daftar Event - Penyedia Komunitas')

@section('content')

    {{-- EMPTY STATE JIKA KOMUNITAS BELUM DIBUAT --}}
    @if(!$community)
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-20 h-20 rounded-2xl bg-primary-50 flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="w-10 h-10 text-primary-500">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 mb-2">Komunitas Belum Dibuat</h2>
            <p class="text-slate-500 max-w-md mb-8">
                Kamu belum memiliki data komunitas olahraga. Buat komunitas terlebih dahulu untuk mulai mempublikasikan event.
            </p>
            <a href="{{ route('penyedia-komunitas.dashboard') }}" class="btn btn--primary">
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

        {{-- PAGE HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-bold tracking-wide uppercase mb-2">
                    <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                    PORTAL HOST &bull; {{ strtoupper($community->sport_category) }} &bull; {{ strtoupper($community->name) }}
                </div>
                <h1 class="text-xl font-extrabold text-slate-900">
                    Kelola Event Komunitas
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Pantau seluruh agenda kegiatan, kuota peserta, dan publikasikan event baru untuk komunitas olahraga Anda.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('penyedia-komunitas.events.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-700 via-blue-600 to-cyan-500 hover:from-blue-800 hover:via-blue-700 hover:to-cyan-600 text-white text-sm font-bold shadow-lg shadow-blue-500/20 transition-all duration-200 hover:-translate-y-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Buat Event Baru</span>
                </a>
            </div>
        </div>

        {{-- METRIC SUMMARY CARDS --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            {{-- Card 1: Total Event --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Event</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-slate-900">{{ number_format($counts['total']) }}</div>
                <div class="text-xs text-slate-400 mt-1">Seluruh agenda kegiatan</div>
            </div>

            {{-- Card 2: Pendaftaran Dibuka --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Buka Registrasi</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-slate-900">{{ number_format($counts['published']) }}</div>
                <div class="text-xs text-emerald-600 font-semibold mt-1">Siap menerima peserta</div>
            </div>

            {{-- Card 3: Berlangsung --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Berlangsung</span>
                    <div class="w-9 h-9 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-slate-900">{{ number_format($counts['ongoing']) }}</div>
                <div class="text-xs text-slate-400 mt-1">Event aktif saat ini</div>
            </div>

            {{-- Card 4: Total Peserta --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Peserta</span>
                    <div class="w-9 h-9 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-slate-900">{{ number_format($counts['total_participants']) }}</div>
                <div class="text-xs text-slate-400 mt-1">Pendaftar terdaftar</div>
            </div>
        </div>

        {{-- FILTER & SEARCH TOOLBAR --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6 shadow-sm flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            {{-- Status Filter Tabs --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-2 lg:pb-0 scrollbar-none">
                <a href="{{ route('penyedia-komunitas.events.index', array_merge(request()->except(['page', 'status']), ['status' => 'all'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap inline-flex items-center gap-2 {{ $selectedStatus === 'all' ? 'bg-primary-600 text-white shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                    <span>Semua</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $selectedStatus === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600' }}">{{ $counts['total'] }}</span>
                </a>

                <a href="{{ route('penyedia-komunitas.events.index', array_merge(request()->except(['page', 'status']), ['status' => 'published'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap inline-flex items-center gap-2 {{ $selectedStatus === 'published' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                    <span>Pendaftaran Dibuka</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $selectedStatus === 'published' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600' }}">{{ $counts['published'] }}</span>
                </a>

                <a href="{{ route('penyedia-komunitas.events.index', array_merge(request()->except(['page', 'status']), ['status' => 'ongoing'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap inline-flex items-center gap-2 {{ $selectedStatus === 'ongoing' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                    <span>Berlangsung</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $selectedStatus === 'ongoing' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600' }}">{{ $counts['ongoing'] }}</span>
                </a>

                <a href="{{ route('penyedia-komunitas.events.index', array_merge(request()->except(['page', 'status']), ['status' => 'completed'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap inline-flex items-center gap-2 {{ $selectedStatus === 'completed' ? 'bg-slate-800 text-white shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                    <span>Selesai</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $selectedStatus === 'completed' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600' }}">{{ $counts['completed'] }}</span>
                </a>

                <a href="{{ route('penyedia-komunitas.events.index', array_merge(request()->except(['page', 'status']), ['status' => 'draft'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap inline-flex items-center gap-2 {{ $selectedStatus === 'draft' ? 'bg-purple-600 text-white shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                    <span>Draft</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $selectedStatus === 'draft' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600' }}">{{ $counts['draft'] }}</span>
                </a>
            </div>

            {{-- Search Bar --}}
            <form method="GET" action="{{ route('penyedia-komunitas.events.index') }}" class="flex items-center gap-2 m-0">
                @if($selectedStatus !== 'all')
                    <input type="hidden" name="status" value="{{ $selectedStatus }}">
                @endif
                <div class="relative w-full sm:w-72">
                    <input type="text"
                           name="q"
                           value="{{ $search }}"
                           placeholder="Cari nama event atau lokasi..."
                           class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                </div>
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                    Cari
                </button>
                @if($search !== '')
                    <a href="{{ route('penyedia-komunitas.events.index', ['status' => $selectedStatus]) }}"
                       class="px-2.5 py-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 text-xs font-semibold transition-colors"
                       title="Hapus filter pencarian">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- EVENTS TABLE CONTAINER --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            @if($events->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-primary-50 flex items-center justify-center mb-4 text-primary-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                        </svg>
                    </div>

                    @if($search !== '' || $selectedStatus !== 'all')
                        <h3 class="text-base font-bold text-slate-800">Tidak ada event yang sesuai</h3>
                        <p class="text-slate-500 text-xs mt-1 max-w-sm">
                            Tidak ditemukan agenda dengan status atau kata kunci yang Anda masukkan. Coba gunakan filter lain.
                        </p>
                        <a href="{{ route('penyedia-komunitas.events.index') }}"
                           class="mt-4 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                            Tampilkan Semua Event
                        </a>
                    @else
                        <h3 class="text-base font-bold text-slate-800">Belum ada event yang dibuat</h3>
                        <p class="text-slate-500 text-xs mt-1 max-w-sm">
                            Komunitas Anda belum memiliki agenda event. Publikasikan event pertamamu untuk mulai menjaring peserta.
                        </p>
                        <a href="{{ route('penyedia-komunitas.events.create') }}"
                           class="mt-4 px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold shadow-md shadow-primary-500/20 transition-all">
                            + Buat Event Pertama
                        </a>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-6">Informasi Event</th>
                                <th class="py-3.5 px-6">Tanggal &amp; Waktu</th>
                                <th class="py-3.5 px-6">Lokasi</th>
                                <th class="py-3.5 px-6">Biaya &amp; Kuota</th>
                                <th class="py-3.5 px-6">Status</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @php
                                $typeLabels = [
                                    'fun_match' => 'Fun Match',
                                    'tournament' => 'Turnamen',
                                    'gathering' => 'Gathering',
                                    'workshop' => 'Workshop',
                                    'conference' => 'Conference',
                                    'other' => 'Lainnya',
                                ];

                                $statusConfig = [
                                    'published' => ['label' => 'Pendaftaran Dibuka', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500'],
                                    'ongoing' => ['label' => 'Sedang Berlangsung', 'bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'dot' => 'bg-blue-500'],
                                    'registration_closed' => ['label' => 'Pendaftaran Ditutup', 'bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'dot' => 'bg-amber-500'],
                                    'completed' => ['label' => 'Selesai', 'bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'dot' => 'bg-slate-400'],
                                    'draft' => ['label' => 'Draft', 'bg' => 'bg-purple-50 text-purple-700 border-purple-200', 'dot' => 'bg-purple-500'],
                                    'cancelled' => ['label' => 'Dibatalkan', 'bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'dot' => 'bg-rose-500'],
                                ];
                            @endphp

                            @foreach($events as $event)
                                @php
                                    $filled = $event->participants_count;
                                    $capacity = $event->capacity;
                                    $isFull = $event->isFull();
                                    $percentage = $event->fillPercentage();
                                    $status = $statusConfig[$event->status] ?? ['label' => ucfirst($event->status), 'bg' => 'bg-slate-50 text-slate-700 border-slate-200', 'dot' => 'bg-slate-400'];
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    {{-- Kolom 1: Informasi Event --}}
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3.5">
                                            @if($event->cover_image)
                                                <img src="{{ \Illuminate\Support\Str::startsWith($event->cover_image, ['http://', 'https://']) ? $event->cover_image : \Illuminate\Support\Facades\Storage::url($event->cover_image) }}"
                                                     alt="{{ $event->title }}"
                                                     class="w-14 h-14 rounded-xl object-cover border border-slate-200 shrink-0 shadow-sm"
                                                     onerror="this.style.display='none'">
                                            @else
                                                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary-50 to-primary-100 border border-primary-200/50 flex items-center justify-center text-primary-500 shrink-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                                    </svg>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 uppercase tracking-wide">
                                                        {{ $typeLabels[$event->type] ?? ucfirst($event->type) }}
                                                    </span>
                                                    <span class="text-[11px] text-slate-400 font-medium">
                                                        {{ ucfirst($community->sport_category) }}
                                                    </span>
                                                </div>
                                                <strong class="text-sm font-bold text-slate-900 block line-clamp-1 hover:text-primary-600 transition-colors">
                                                    {{ $event->title }}
                                                </strong>
                                                <p class="text-xs text-slate-400 line-clamp-1 mt-0.5">
                                                    {{ Str::limit($event->description, 50) }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kolom 2: Tanggal & Waktu --}}
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <div class="flex items-center gap-2 text-xs font-bold text-slate-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ $event->event_date?->translatedFormat('d M Y') ?? '-' }}</span>
                                        </div>
                                        <div class="text-xs text-slate-500 mt-1 flex items-center gap-1.5 pl-6">
                                            <span>{{ substr($event->start_time, 0, 5) }}</span>
                                            @if($event->end_time)
                                                <span>- {{ substr($event->end_time, 0, 5) }}</span>
                                            @endif
                                            <span>WIB</span>
                                        </div>
                                        @if($event->registration_close_at)
                                            <div class="text-[10px] text-slate-400 pl-6 mt-0.5">
                                                Batas: {{ $event->registration_close_at->translatedFormat('d M Y') }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Kolom 3: Lokasi --}}
                                    <td class="py-4 px-6">
                                        <div class="flex items-start gap-1.5 text-xs text-slate-700 max-w-[200px]">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="line-clamp-2 leading-tight">{{ $event->location }}</span>
                                        </div>
                                        @if($event->maps_url)
                                            <a href="{{ $event->maps_url }}" target="_blank" rel="noopener" class="text-[11px] text-primary-600 hover:text-primary-700 font-semibold inline-flex items-center gap-1 pl-5.5 mt-1">
                                                <span>Buka Maps</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                            </a>
                                        @endif
                                    </td>

                                    {{-- Kolom 4: Biaya & Kuota --}}
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <div class="mb-2">
                                            @if($event->registration_fee == 0)
                                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
                                                    Gratis (Free)
                                                </span>
                                            @else
                                                <span class="text-xs font-extrabold text-slate-800">
                                                    Rp {{ number_format($event->registration_fee, 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="w-36">
                                            <div class="flex items-center justify-between text-[11px] mb-1">
                                                <span class="font-bold text-slate-700">
                                                    {{ $filled }} / {{ $capacity ?? '∞' }}
                                                </span>
                                                <span class="font-semibold {{ $isFull ? 'text-red-500' : 'text-primary-600' }}">
                                                    {{ $isFull ? 'Penuh' : ($capacity ? $percentage . '%' : 'Unlimited') }}
                                                </span>
                                            </div>
                                            @if($capacity)
                                                <div class="w-full h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-300 {{ $isFull ? 'bg-red-500' : 'bg-primary-500' }}"
                                                         style="width: {{ min(100, $percentage) }}%;"></div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Kolom 5: Status --}}
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $status['bg'] }}">
                                            <span class="w-2 h-2 rounded-full {{ $status['dot'] }}"></span>
                                            {{ $status['label'] }}
                                        </span>
                                    </td>
                                    {{-- Kolom 6: Aksi --}}
                                    <td class="py-4 px-6 text-right whitespace-nowrap">
                                        <a href="{{ route('penyedia-komunitas.events.show', $event) }}"
                                        class="inline-flex rounded-lg px-4 py-2 text-sm font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100">
                                            Kelola
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                @if($events->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50/40">
                        {{ $events->links() }}
                    </div>
                @endif
            @endif
        </div>

    @endif

@endsection
