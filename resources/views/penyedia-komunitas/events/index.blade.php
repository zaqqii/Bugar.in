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

        {{-- PAGE HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-bold tracking-wide uppercase mb-2">
                    <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                    PORTAL HOST &bull; {{ strtoupper($community->sport_category ?? 'Olahraga') }} &bull; {{ strtoupper($community->name) }}
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
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Draft Disimpan</span>
                    <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-slate-900">{{ number_format($counts['draft']) }}</div>
                <div class="text-xs text-slate-400 mt-1">Belum dipublikasikan</div>
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
            @php
                $isSecondaryActive = in_array($selectedStatus, ['registration_closed', 'ongoing', 'completed', 'cancelled']);
            @endphp

            {{-- Status Filter Cluster (Priority tabs + secondary dropdown, no horizontal scroll) --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Tab: Semua --}}
                <a href="{{ route('penyedia-komunitas.events.index', array_merge(request()->except(['page', 'status']), ['status' => 'all'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 {{ $selectedStatus === 'all' ? 'bg-primary-600 text-white shadow-sm shadow-primary-500/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-slate-200/60' }}">
                    <span>Semua</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $selectedStatus === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600' }}">{{ $counts['total'] }}</span>
                </a>

                {{-- Tab: Pendaftaran Dibuka --}}
                <a href="{{ route('penyedia-komunitas.events.index', array_merge(request()->except(['page', 'status']), ['status' => 'published'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 {{ $selectedStatus === 'published' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-500/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-slate-200/60' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $selectedStatus === 'published' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                    <span>Buka Registrasi</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $selectedStatus === 'published' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600' }}">{{ $counts['published'] }}</span>
                </a>

                {{-- Tab: Draft --}}
                <a href="{{ route('penyedia-komunitas.events.index', array_merge(request()->except(['page', 'status']), ['status' => 'draft'])) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 {{ $selectedStatus === 'draft' ? 'bg-purple-600 text-white shadow-sm shadow-purple-500/20' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-slate-200/60' }}">
                    <span>Draft</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $selectedStatus === 'draft' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-600' }}">{{ $counts['draft'] }}</span>
                </a>

                {{-- Dropdown: Status Lainnya (Titik Tiga / Menu Tambahan) --}}
                <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false">
                    @if($isSecondaryActive)
                        @php
                            $activeSecondaryConfig = [
                                'registration_closed' => ['label' => 'Ditutup', 'full_label' => 'Pendaftaran Ditutup', 'bg' => 'bg-amber-600 text-white shadow-sm shadow-amber-500/20', 'dot' => 'bg-white', 'count_bg' => 'bg-white/20 text-white'],
                                'ongoing' => ['label' => 'Berlangsung', 'full_label' => 'Sedang Berlangsung', 'bg' => 'bg-blue-600 text-white shadow-sm shadow-blue-500/20', 'dot' => 'bg-white', 'count_bg' => 'bg-white/20 text-white'],
                                'completed' => ['label' => 'Selesai', 'full_label' => 'Selesai', 'bg' => 'bg-slate-800 text-white shadow-sm shadow-slate-700/20', 'dot' => 'bg-white', 'count_bg' => 'bg-white/20 text-white'],
                                'cancelled' => ['label' => 'Dibatalkan', 'full_label' => 'Dibatalkan', 'bg' => 'bg-rose-600 text-white shadow-sm shadow-rose-500/20', 'dot' => 'bg-white', 'count_bg' => 'bg-white/20 text-white'],
                            ][$selectedStatus] ?? ['label' => 'Lainnya', 'full_label' => 'Status Lainnya', 'bg' => 'bg-primary-600 text-white', 'dot' => 'bg-white', 'count_bg' => 'bg-white/20 text-white'];
                        @endphp
                        <button type="button"
                                @click="open = !open"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 {{ $activeSecondaryConfig['bg'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $activeSecondaryConfig['dot'] }}"></span>
                            <span>{{ $activeSecondaryConfig['label'] }}</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $activeSecondaryConfig['count_bg'] }}">
                                {{ $counts[$selectedStatus] ?? 0 }}
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    @else
                        <button type="button"
                                @click="open = !open"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center gap-2 border border-slate-200/80 bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-800">
                            <span class="inline-flex items-center gap-0.5 text-slate-400 font-extrabold text-sm leading-none">
                                <span class="w-1 h-1 rounded-full bg-slate-400"></span>
                                <span class="w-1 h-1 rounded-full bg-slate-400"></span>
                                <span class="w-1 h-1 rounded-full bg-slate-400"></span>
                            </span>
                            <span>Lainnya</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    @endif

                    {{-- Dropdown Popover --}}
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         class="absolute left-0 mt-2 w-64 bg-white rounded-2xl shadow-xl shadow-slate-900/10 border border-slate-100 p-2 z-30"
                         style="display: none;">
                        
                        <div class="px-3 py-2 text-[10px] font-extrabold tracking-wider text-slate-400 uppercase flex items-center justify-between border-b border-slate-100 mb-1">
                            <span>Status Lainnya</span>
                            <span class="text-[9px] font-bold text-slate-400 lowercase">4 opsi</span>
                        </div>

                        <div class="space-y-1">
                            {{-- Pendaftaran Ditutup --}}
                            <a href="{{ route('penyedia-komunitas.events.index', array_merge(request()->except(['page', 'status']), ['status' => 'registration_closed'])) }}"
                               class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ $selectedStatus === 'registration_closed' ? 'bg-amber-50 text-amber-900 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                                    <span>Pendaftaran Ditutup</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $selectedStatus === 'registration_closed' ? 'bg-amber-200/80 text-amber-900 font-bold' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $counts['registration_closed'] }}
                                    </span>
                                    @if($selectedStatus === 'registration_closed')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                            </a>

                            {{-- Sedang Berlangsung --}}
                            <a href="{{ route('penyedia-komunitas.events.index', array_merge(request()->except(['page', 'status']), ['status' => 'ongoing'])) }}"
                               class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ $selectedStatus === 'ongoing' ? 'bg-blue-50 text-blue-900 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                                    <span>Sedang Berlangsung</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $selectedStatus === 'ongoing' ? 'bg-blue-200/80 text-blue-900 font-bold' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $counts['ongoing'] }}
                                    </span>
                                    @if($selectedStatus === 'ongoing')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                            </a>

                            {{-- Selesai --}}
                            <a href="{{ route('penyedia-komunitas.events.index', array_merge(request()->except(['page', 'status']), ['status' => 'completed'])) }}"
                               class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ $selectedStatus === 'completed' ? 'bg-slate-100 text-slate-900 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2 h-2 rounded-full bg-slate-400 shrink-0"></span>
                                    <span>Selesai</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $selectedStatus === 'completed' ? 'bg-slate-200 text-slate-900 font-bold' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $counts['completed'] }}
                                    </span>
                                    @if($selectedStatus === 'completed')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-800" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                            </a>

                            {{-- Dibatalkan --}}
                            <a href="{{ route('penyedia-komunitas.events.index', array_merge(request()->except(['page', 'status']), ['status' => 'cancelled'])) }}"
                               class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ $selectedStatus === 'cancelled' ? 'bg-rose-50 text-rose-900 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                                    <span>Dibatalkan</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $selectedStatus === 'cancelled' ? 'bg-rose-200/80 text-rose-900 font-bold' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $counts['cancelled'] }}
                                    </span>
                                    @if($selectedStatus === 'cancelled')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-rose-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                            </a>
                        </div>

                        @if($isSecondaryActive)
                            <div class="mt-2 pt-2 border-t border-slate-100">
                                <a href="{{ route('penyedia-komunitas.events.index', array_merge(request()->except(['page', 'status']), ['status' => 'all'])) }}"
                                   class="w-full px-3 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 text-center block text-[11px] font-bold transition-colors">
                                    Hapus Filter Status
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Search Bar --}}
            <form method="GET" action="{{ route('penyedia-komunitas.events.index') }}" class="flex items-center gap-2 m-0 w-full sm:w-auto">
                @if($selectedStatus !== 'all')
                    <input type="hidden" name="status" value="{{ $selectedStatus }}">
                @endif
                <div class="relative w-full sm:w-64 md:w-72">
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
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors shrink-0">
                    Cari
                </button>
                @if($search !== '')
                    <a href="{{ route('penyedia-komunitas.events.index', ['status' => $selectedStatus]) }}"
                       class="px-2.5 py-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 text-xs font-semibold transition-colors shrink-0"
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
                    <table class="w-full text-left border-collapse table-auto">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 px-6 min-w-[280px] max-w-[340px]">Informasi Event</th>
                                <th class="py-3.5 px-6 whitespace-nowrap min-w-[170px]">Tanggal &amp; Waktu</th>
                                <th class="py-3.5 px-6 min-w-[180px] max-w-[220px]">Lokasi</th>
                                <th class="py-3.5 px-6 whitespace-nowrap min-w-[170px]">Biaya &amp; Kuota</th>
                                <th class="py-3.5 px-6 whitespace-nowrap min-w-[140px]">Status</th>
                                <th class="py-3.5 px-6 whitespace-nowrap text-right min-w-[160px]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @php
                                $typeLabels = [
                                    'fun_match' => 'Fun Match',
                                    'tournament' => 'Turnamen',
                                    'gathering' => 'Gathering',
                                    'workshop' => 'Workshop',
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
                                    $filled = (int) $event->participants_count;
                                    $capacity = $event->capacity;
                                    $isFull = $event->isFull();
                                    $percentage = $event->fillPercentage();
                                    $status = $statusConfig[$event->status] ?? ['label' => ucfirst($event->status), 'bg' => 'bg-slate-50 text-slate-700 border-slate-200', 'dot' => 'bg-slate-400'];
                                    
                                    // Bersihkan cuplikan deskripsi dari separator rundown
                                    $cleanDesc = explode("\n\n--- RUNDOWN ACARA ---\n", $event->description ?? '')[0];
                                    $cleanDesc = Str::limit(trim(strip_tags($cleanDesc)), 60);
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    {{-- Kolom 1: Informasi Event --}}
                                    <td class="py-4 px-6 min-w-[280px] max-w-[340px]">
                                        <div class="flex items-start gap-3.5">
                                            @if($event->cover_image)
                                                <img src="{{ \Illuminate\Support\Str::startsWith($event->cover_image, ['http://', 'https://']) ? $event->cover_image : \Illuminate\Support\Facades\Storage::url($event->cover_image) }}"
                                                     alt="{{ $event->title }}"
                                                     class="w-14 h-14 rounded-xl object-cover border border-slate-200 shrink-0 shadow-sm mt-0.5"
                                                     onerror="this.style.display='none'">
                                            @else
                                                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary-50 to-primary-100 border border-primary-200/50 flex items-center justify-center text-primary-500 shrink-0 mt-0.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                                    </svg>
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 uppercase tracking-wide">
                                                        {{ $typeLabels[$event->type] ?? ucfirst($event->type) }}
                                                    </span>
                                                    <span class="text-[11px] text-slate-400 font-medium">
                                                        {{ ucfirst($community->sport_category ?? 'Olahraga') }}
                                                    </span>
                                                </div>
                                                <strong class="text-sm font-bold text-slate-900 block line-clamp-2 hover:text-primary-600 transition-colors break-words leading-snug" title="{{ $event->title }}">
                                                    {{ $event->title }}
                                                </strong>
                                                <p class="text-xs text-slate-400 line-clamp-1 mt-1 break-words">
                                                    {{ $cleanDesc ?: 'Tidak ada deskripsi singkat.' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kolom 2: Tanggal & Waktu --}}
                                    <td class="py-4 px-6 whitespace-nowrap min-w-[170px]">
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
                                                Batas RSVP: {{ $event->registration_close_at->translatedFormat('d M Y') }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Kolom 3: Lokasi --}}
                                    <td class="py-4 px-6 min-w-[180px] max-w-[220px]">
                                        <div class="flex items-start gap-1.5 text-xs text-slate-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="line-clamp-2 leading-snug break-words" title="{{ $event->location }}">{{ $event->location }}</span>
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
                                    <td class="py-4 px-6 whitespace-nowrap min-w-[170px]">
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
                                                    {{ $filled }} / {{ $capacity ?? '∞' }} Peserta
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
                                    <td class="py-4 px-6 whitespace-nowrap min-w-[140px]">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $status['bg'] }}">
                                            <span class="w-2 h-2 rounded-full {{ $status['dot'] }}"></span>
                                            {{ $status['label'] }}
                                        </span>
                                    </td>

                                    {{-- Kolom 6: Aksi Sesuai Status --}}
                                    <td class="py-4 px-6 whitespace-nowrap text-right min-w-[160px]">
                                        <div class="flex items-center justify-end gap-1.5">

                                            {{-- Tombol Edit --}}
                                            @if($event->canBeEdited())
                                                <a href="{{ route('penyedia-komunitas.events.edit', $event) }}"
                                                   class="p-2 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                                   title="Edit Event">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                    </svg>
                                                </a>
                                            @endif

                                            {{-- Aksi Publikasikan (Khusus Draft) --}}
                                            @if($event->canBePublished())
                                                <form method="POST" action="{{ route('penyedia-komunitas.events.publish', $event) }}" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-bold transition-colors inline-flex items-center gap-1"
                                                            title="Publikasikan Event">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        <span>Publikasikan</span>
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Aksi Tutup Pendaftaran (Khusus Published) --}}
                                            @if($event->canCloseRegistration())
                                                <form method="POST" action="{{ route('penyedia-komunitas.events.close-registration', $event) }}" class="inline"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menutup pendaftaran untuk event ini?')">
                                                    @csrf
                                                    <button type="submit"
                                                            class="px-2.5 py-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 text-xs font-bold transition-colors inline-flex items-center gap-1"
                                                            title="Tutup Pendaftaran">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                        </svg>
                                                        <span>Tutup</span>
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Aksi Batalkan Event --}}
                                            @if($event->canBeCancelled() && $event->status !== 'draft')
                                                <form method="POST" action="{{ route('penyedia-komunitas.events.cancel', $event) }}" class="inline"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin MEMBATALKAN event &quot;{{ $event->title }}&quot;? Notifikasi pembatalan akan otomatis dikirimkan ke seluruh peserta.')">
                                                    @csrf
                                                    <button type="submit"
                                                            class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs font-bold transition-colors inline-flex items-center gap-1"
                                                            title="Batalkan Event">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                        </svg>
                                                        <span>Batalkan</span>
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Aksi Hapus (Hanya Draft atau jika belum ada peserta) --}}
                                            @if($event->canBeDeleted())
                                                <form method="POST" action="{{ route('penyedia-komunitas.events.destroy', $event) }}" class="inline"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS event &quot;{{ $event->title }}&quot;?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="p-2 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                                            title="Hapus Event">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif

                                            @if(!$event->canBeEdited() && !$event->canBeCancelled() && !$event->canBeDeleted())
                                                <span class="text-xs text-slate-400 italic">Tidak ada aksi</span>
                                            @endif

                                        </div>
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
