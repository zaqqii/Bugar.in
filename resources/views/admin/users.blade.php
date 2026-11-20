@extends('layouts.admin')

@section('title', 'Data Pengguna — Console Ops Bugar.in')

@section('content')
<div class="space-y-8" 
     x-data="adminUsersHandler()" 
     x-init="init()">

    {{-- 1. Header & Breadcrumb --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1.5">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors">Console Ops</a>
                <span class="text-slate-600">&bull;</span>
                <span class="text-cyan-400">Data Pengguna</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Pusat Data &amp; Akun Pengguna
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl">
                Kelola basis seluruh pengguna platform Bugar.in, pantau keterlibatan komunitas, status keanggotaan, dan keaktifan akun tanpa reload halaman.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-slate-300 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                <span>Database Terhubung</span>
            </span>
        </div>
    </div>

    {{-- 2. Card Metrik Ringkasan Data Pengguna --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        {{-- Card 1: Total Pengguna --}}
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm hover:border-slate-700 transition-all relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-500/5 rounded-full blur-xl group-hover:bg-blue-500/10 transition-all pointer-events-none"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Akun Pengguna</span>
                <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20 flex items-center justify-center text-sm">
                    👥
                </div>
            </div>
            <div class="text-3xl font-extrabold text-white tracking-tight">
                {{ number_format($stats['total_users']) }}
            </div>
            <div class="mt-2 flex items-center gap-2 text-xs text-slate-400">
                <span class="text-emerald-400 font-bold font-mono">+{{ $stats['new_this_month'] }}</span>
                <span>pendaftar baru bulan ini</span>
            </div>
        </div>

        {{-- Card 2: Pengguna Reguler --}}
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm hover:border-slate-700 transition-all relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-cyan-500/5 rounded-full blur-xl group-hover:bg-cyan-500/10 transition-all pointer-events-none"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pengguna Reguler</span>
                <div class="w-9 h-9 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 flex items-center justify-center text-sm">
                    🏃
                </div>
            </div>
            <div class="text-3xl font-extrabold text-white tracking-tight">
                {{ number_format($stats['regular_users']) }}
            </div>
            <div class="mt-2 flex items-center gap-2 text-xs text-slate-400">
                <span class="text-cyan-400 font-bold font-mono">{{ $stats['total_users'] > 0 ? round(($stats['regular_users'] / $stats['total_users']) * 100) : 0 }}%</span>
                <span>dari seluruh total akun</span>
            </div>
        </div>

        {{-- Card 3: Tergabung Komunitas --}}
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm hover:border-slate-700 transition-all relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-500/5 rounded-full blur-xl group-hover:bg-indigo-500/10 transition-all pointer-events-none"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tergabung Komunitas</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center text-sm">
                    🏟️
                </div>
            </div>
            <div class="text-3xl font-extrabold text-white tracking-tight">
                {{ number_format($stats['users_in_communities']) }}
            </div>
            <div class="mt-2 flex items-center gap-2 text-xs text-slate-400">
                <span class="text-emerald-400 font-bold font-mono">
                    {{ $stats['regular_users'] > 0 ? round(($stats['users_in_communities'] / $stats['regular_users']) * 100) : 0 }}%
                </span>
                <span>member aktif berkomunitas</span>
            </div>
        </div>

        {{-- Card 4: Akumulasi Poin Gerak --}}
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm hover:border-slate-700 transition-all relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/5 rounded-full blur-xl group-hover:bg-amber-500/10 transition-all pointer-events-none"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Poin Gerak</span>
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center text-sm">
                    ⚡
                </div>
            </div>
            <div class="text-3xl font-extrabold text-white tracking-tight">
                {{ number_format($stats['total_points_distributed']) }} <span class="text-sm font-semibold text-amber-400">PTS</span>
            </div>
            <div class="mt-2 flex items-center gap-2 text-xs text-slate-400">
                <span class="text-amber-300 font-bold font-mono">{{ $stats['active_users'] }}</span>
                <span>akun pengguna berstatus aktif</span>
            </div>
        </div>
    </div>

    {{-- 3. Visualisasi Grafik: Pertumbuhan Pendaftar & Distribusi Peran --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Grafik Tren Pendaftaran (2 Kolom) --}}
        <div class="lg:col-span-2 p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-white tracking-tight flex items-center gap-2">
                        <span>📈 Tren Pendaftaran Pengguna Baru</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Periode tren registrasi berpusat pada musim puncak akhir tahun (Nov – Des)</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-slate-800 border border-slate-700 text-[11px] font-mono text-cyan-300">
                    Pusat Musim: Nov – Des
                </span>
            </div>
            <div class="relative w-full h-[230px]">
                <canvas id="userRegistrationChart"></canvas>
            </div>
        </div>

        {{-- Grafik Distribusi Peran (1 Kolom) --}}
        <div class="p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-white tracking-tight flex items-center gap-2">
                        <span>🍩 Komposisi Peran Akun</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Proporsi user, pengelola komunitas &amp; admin</p>
                </div>
            </div>
            <div class="relative w-full h-[180px] flex items-center justify-center">
                <canvas id="userRoleChart"></canvas>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-800/80 grid grid-cols-3 gap-2 text-center text-xs">
                <div class="p-2 rounded-xl bg-slate-950/60 border border-slate-800">
                    <span class="block text-[10px] text-cyan-400 font-bold uppercase">User</span>
                    <span class="font-extrabold text-white font-mono">{{ $roleDistribution['user'] }}</span>
                </div>
                <div class="p-2 rounded-xl bg-slate-950/60 border border-slate-800">
                    <span class="block text-[10px] text-indigo-400 font-bold uppercase">Penyedia</span>
                    <span class="font-extrabold text-white font-mono">{{ $roleDistribution['community_owner'] }}</span>
                </div>
                <div class="p-2 rounded-xl bg-slate-950/60 border border-slate-800">
                    <span class="block text-[10px] text-amber-400 font-bold uppercase">Admin</span>
                    <span class="font-extrabold text-white font-mono">{{ $roleDistribution['admin'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Filter & Pencarian Pengguna --}}
    <div class="p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-white tracking-tight flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <span>Filter &amp; Pencarian Pengguna</span>
            </h3>

            {{-- Indikator Tombol Reset --}}
            <button type="button" 
                    x-show="hasActiveFilters" 
                    @click="resetFilters()" 
                    class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition-colors flex items-center gap-1 cursor-pointer"
                    style="display: none;">
                <span>Reset Filter</span>
                <span class="text-slate-500">&times;</span>
            </button>
        </div>

        <form @submit.prevent="fetchUsers()" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                {{-- Input Pencarian Keyword (Auto Debounce 350ms) --}}
                <div class="sm:col-span-2 lg:col-span-2 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input type="text" 
                           x-model="search" 
                           @input.debounce.350ms="fetchUsers()" 
                           placeholder="Cari nama, @username, email, telepon..."
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all">
                </div>

                {{-- Filter Role --}}
                <div>
                    <select x-model="role" 
                            @change="fetchUsers()" 
                            class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-300 focus:outline-none focus:border-cyan-500 transition-all">
                        <option value="">Semua Peran</option>
                        <option value="user">User Reguler</option>
                        <option value="community_owner">Penyedia Komunitas</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>

                {{-- Filter Status Akun --}}
                <div>
                    <select x-model="status" 
                            @change="fetchUsers()" 
                            class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-300 focus:outline-none focus:border-cyan-500 transition-all">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>

                {{-- Filter Keterlibatan Komunitas --}}
                <div>
                    <select x-model="community_status" 
                            @change="fetchUsers()" 
                            class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-300 focus:outline-none focus:border-cyan-500 transition-all">
                        <option value="">Status Komunitas</option>
                        <option value="has_community">Sudah Join Komunitas</option>
                        <option value="no_community">Belum Ada Komunitas</option>
                    </select>
                </div>

                {{-- Urutkan --}}
                <div>
                    <select x-model="sort" 
                            @change="fetchUsers()" 
                            class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-300 focus:outline-none focus:border-cyan-500 transition-all">
                        <option value="latest">Terbaru Daftar</option>
                        <option value="oldest">Terlama</option>
                        <option value="points_high">Poin Tertinggi</option>
                        <option value="points_low">Poin Terendah</option>
                        <option value="name_asc">Nama A - Z</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                {{-- Filter Spesifik Komunitas --}}
                <div class="flex items-center gap-2 max-w-sm w-full">
                    <span class="text-xs text-slate-400 whitespace-nowrap">Filter Klub:</span>
                    <select x-model="community_id" 
                            @change="fetchUsers()" 
                            class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-300 focus:outline-none focus:border-cyan-500 transition-all">
                        <option value="">Semua Klub &amp; Komunitas</option>
                        @foreach($communitiesList as $comm)
                            <option value="{{ $comm->id }}">{{ $comm->name }} ({{ $comm->sport_category }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-md shadow-cyan-500/20 transition-all flex items-center gap-2 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                        </svg>
                        <span>Cari</span>
                    </button>
                    <button type="button" 
                            x-show="hasActiveFilters" 
                            @click="resetFilters()" 
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs border border-slate-700 transition-all cursor-pointer"
                            style="display: none;">
                        Reset
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- 5. Tabel Data Pengguna Wrapper (AJAX Reactive Container) --}}
    <div class="relative rounded-2xl bg-slate-900 border border-slate-800 shadow-sm overflow-hidden" 
         id="usersTableWrapper"
         @click="handleTableClick($event)">

        {{-- Shimmer / Loading State Overlay saat Fetch AJAX berlangsung --}}
        <div x-show="loading" 
             x-transition:enter="transition-opacity ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 z-20 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center"
             style="display: none;">
            <div class="inline-flex items-center gap-2.5 text-xs font-semibold text-white drop-shadow-md">
                <svg class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Memuat data pengguna...</span>
            </div>
        </div>

        {{-- Isi Tabel (Menggunakan Blade @fragment 'users-table' langsung di file ini tanpa folder partials) --}}
        <div id="usersTableContainer">
            @fragment('users-table')
                {{-- Table Header Bar --}}
                <div class="p-5 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-950/40">
                    <div>
                        <h3 class="text-sm font-bold text-white tracking-tight flex items-center gap-2">
                            <span>Daftar Pengguna Platform</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                                {{ $users->total() }} Akun
                            </span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Kelola data akun pengguna, penyedia komunitas, dan administrator
                        </p>
                    </div>
                </div>

                {{-- Table Content --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800">
                            <tr>
                                <th scope="col" class="py-3.5 px-4">Pengguna</th>
                                <th scope="col" class="py-3.5 px-4">Kontak</th>
                                <th scope="col" class="py-3.5 px-4 text-center">Status Akun</th>
                                <th scope="col" class="py-3.5 px-4">Bergabung</th>
                                <th scope="col" class="py-3.5 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            @forelse($users as $user)
                                @php
                                    $userInitials = collect(explode(' ', $user->name ?? 'User'))
                                        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                                        ->take(2)
                                        ->implode('');
                                @endphp
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    {{-- 1. Pengguna --}}
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl overflow-hidden bg-gradient-to-br from-blue-600 via-indigo-600 to-cyan-500 flex items-center justify-center text-white font-extrabold text-xs shadow-xs flex-shrink-0">
                                                @if($user->avatar)
                                                    <img src="{{ \Illuminate\Support\Str::startsWith($user->avatar, ['http://', 'https://']) ? $user->avatar : \Illuminate\Support\Facades\Storage::url($user->avatar) }}" 
                                                         alt="{{ $user->name }}" 
                                                         class="w-full h-full object-cover"
                                                         onerror="this.style.display='none'">
                                                @else
                                                    {{ $userInitials }}
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-bold text-white text-xs truncate max-w-[200px]">
                                                    {{ $user->name }}
                                                </div>
                                                <div class="text-[11px] font-mono text-cyan-400 truncate max-w-[200px]">
                                                    {{ '@' . ($user->username ?? \Illuminate\Support\Str::before($user->email, '@')) }}
                                                </div>
                                                <div class="mt-1">
                                                    @if($user->role === 'admin')
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                                            Admin
                                                        </span>
                                                    @elseif($user->role === 'community_owner')
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                                            Penyedia
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-blue-500/10 text-blue-300 border border-blue-500/20">
                                                            User
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 2. Kontak --}}
                                    <td class="py-4 px-4">
                                        <div class="space-y-0.5">
                                            <div class="text-xs text-slate-300 font-medium truncate max-w-[200px]" title="{{ $user->email }}">
                                                {{ $user->email }}
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono">
                                                {{ $user->phone ?? '-' }}
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 3. Status Akun --}}
                                    <td class="py-4 px-4 text-center">
                                        @if($user->is_active)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                                <span>Aktif</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-red-500/10 text-red-400 border border-red-500/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                                <span>Nonaktif</span>
                                            </span>
                                        @endif
                                    </td>

                                    {{-- 4. Tanggal Daftar --}}
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <div class="text-xs text-slate-300">
                                            {{ $user->created_at ? $user->created_at->isoFormat('D MMM YYYY') : '-' }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-mono">
                                            {{ $user->created_at ? $user->created_at->format('H:i') . ' WIB' : '' }}
                                        </div>
                                    </td>

                                    {{-- 5. Aksi --}}
                                    <td class="py-4 px-4 text-center">
                                        <div class="inline-flex items-center gap-1.5">
                                            {{-- Tombol Detail Pop-up yang disesuaikan per Role --}}
                                            <button type="button" 
                                                    @click="openProfileModal({{ json_encode($user) }})"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-300 hover:text-white transition-all cursor-pointer text-xs font-semibold"
                                                    title="Lihat Rincian Detail">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span>Detail</span>
                                            </button>

                                            {{-- Tombol Toggle Status (Aktif/Nonaktif) --}}
                                            @if($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('admin.users.toggle-status', $user->id) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status akun pengguna ini?');">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="p-2 rounded-xl transition-all cursor-pointer {{ $user->is_active ? 'bg-slate-800 hover:bg-red-500/20 text-slate-400 hover:text-red-400' : 'bg-slate-800 hover:bg-emerald-500/20 text-slate-400 hover:text-emerald-400' }}"
                                                            title="{{ $user->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                                                        @if($user->is_active)
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                            </svg>
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        @endif
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-400">
                                        <div class="max-w-xs mx-auto space-y-2">
                                            <div class="text-3xl">🔍</div>
                                            <div class="text-sm font-bold text-white">Tidak ada data pengguna ditemukan</div>
                                            <p class="text-xs text-slate-400">
                                                Coba sesuaikan kata kunci pencarian atau bersihkan filter yang sedang aktif.
                                            </p>
                                            <div class="pt-2">
                                                <button type="button" 
                                                        @click="resetFilters()" 
                                                        class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs inline-block shadow-sm transition-all cursor-pointer">
                                                    Reset Semua Filter
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Bar --}}
                <div class="p-5 border-t border-slate-800 bg-slate-950/40 pagination-container">
                    {{ $users->links('vendor.pagination.admin-dark') }}
                </div>
            @endfragment
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- MODAL POP-UP RINCIAN DETAIL (Disesuaikan Cerdas Berdasarkan Role) --}}
    {{-- ============================================================== --}}
    <div x-show="profileModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;" 
         aria-labelledby="modal-profile-title" 
         role="dialog" 
         aria-modal="true">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Backdrop --}}
            <div x-show="profileModalOpen" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 @click="profileModalOpen = false" 
                 class="fixed inset-0 transition-opacity bg-slate-950/80 backdrop-blur-xs" 
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel --}}
            <div x-show="profileModalOpen" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 flex items-center justify-center text-lg">
                            <template x-if="activeUserProfile && activeUserProfile.role === 'admin'">
                                <span>🛡️</span>
                            </template>
                            <template x-if="activeUserProfile && activeUserProfile.role === 'community_owner'">
                                <span>🏟️</span>
                            </template>
                            <template x-if="activeUserProfile && activeUserProfile.role === 'user'">
                                <span>🏃</span>
                            </template>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-white" id="modal-profile-title">
                                <template x-if="activeUserProfile && activeUserProfile.role === 'admin'">
                                    <span>Profil Administrator Console</span>
                                </template>
                                <template x-if="activeUserProfile && activeUserProfile.role === 'community_owner'">
                                    <span>Profil Penyelenggara Komunitas</span>
                                </template>
                                <template x-if="activeUserProfile && activeUserProfile.role === 'user'">
                                    <span>Profil Pengguna Bugar.in</span>
                                </template>
                            </h3>
                            <p class="text-xs text-slate-400">
                                Rincian data akun dan spesifikasi profil sesuai peran pengguna
                            </p>
                        </div>
                    </div>
                    <button @click="profileModalOpen = false" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer">
                        ✕
                    </button>
                </div>

                {{-- Modal Body --}}
                <template x-if="activeUserProfile">
                    <div class="mt-4 space-y-4 max-h-[70vh] overflow-y-auto pr-1">
                        {{-- Kartu Profil Utama --}}
                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-950 border border-slate-800/80">
                            <div class="w-14 h-14 rounded-2xl overflow-hidden bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center text-white font-extrabold text-lg flex-shrink-0 shadow-sm relative">
                                <template x-if="getAvatarUrl(activeUserProfile)">
                                    <img :src="getAvatarUrl(activeUserProfile)" 
                                         :alt="activeUserProfile.name" 
                                         class="w-full h-full object-cover"
                                         onerror="this.style.display='none'">
                                </template>
                                <template x-if="!getAvatarUrl(activeUserProfile)">
                                    <span x-text="activeUserProfile.name ? activeUserProfile.name.charAt(0).toUpperCase() : 'U'"></span>
                                </template>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <h4 class="text-sm font-extrabold text-white truncate" x-text="activeUserProfile.name"></h4>
                                    <div>
                                        <template x-if="activeUserProfile.role === 'admin'">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                                Admin
                                            </span>
                                        </template>
                                        <template x-if="activeUserProfile.role === 'community_owner'">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                                Penyedia
                                            </span>
                                        </template>
                                        <template x-if="activeUserProfile.role === 'user'">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-blue-500/10 text-blue-300 border border-blue-500/20">
                                                User
                                            </span>
                                        </template>
                                    </div>
                                </div>
                                <div class="text-xs text-cyan-400 font-mono mt-0.5" x-text="'@' + (activeUserProfile.username || 'member')"></div>
                                <div class="text-xs text-slate-400 mt-0.5" x-text="activeUserProfile.email"></div>
                            </div>
                        </div>

                        {{-- ============================================================== --}}
                        {{-- 1. DATA KHUSUS PENGGUNA BIASA (ROLE === 'user') --}}
                        {{-- ============================================================== --}}
                        <template x-if="activeUserProfile.role === 'user'">
                            <div class="space-y-4">
                                {{-- 4 Grid Metrik: Poin, Target, Kebugaran, Sparring --}}
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800/80">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Poin Gerak</span>
                                        <span class="font-mono text-amber-300 font-extrabold text-sm mt-0.5 block flex items-center gap-1">
                                            <span>⚡</span>
                                            <span x-text="(activeUserProfile.total_points || 0) + ' PTS'"></span>
                                        </span>
                                    </div>
                                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800/80">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Target Mingguan</span>
                                        <span class="font-bold text-emerald-400 text-xs mt-0.5 block" x-text="(activeUserProfile.weekly_target || '3-4') + ' Sesi/Mgg'"></span>
                                    </div>
                                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800/80">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Level Kebugaran</span>
                                        <span class="font-bold text-cyan-300 text-xs mt-0.5 block capitalize" x-text="activeUserProfile.fitness_level || 'Menengah'"></span>
                                    </div>
                                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800/80">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Status Sparring</span>
                                        <span class="font-bold text-xs mt-0.5 block" :class="activeUserProfile.sparring_open ? 'text-indigo-400' : 'text-slate-400'" x-text="activeUserProfile.sparring_open ? 'Terbuka' : 'Tertutup'"></span>
                                    </div>
                                </div>

                                {{-- Kontak & Identitas Fisik --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                                    <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/60">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">WhatsApp / HP</span>
                                        <span class="font-mono text-white mt-0.5 block" x-text="activeUserProfile.phone || '-'"></span>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/60">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Jenis Kelamin</span>
                                        <span class="text-white mt-0.5 block capitalize" x-text="activeUserProfile.gender === 'male' ? 'Laki-laki' : (activeUserProfile.gender === 'female' ? 'Perempuan' : '-')"></span>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/60">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Tanggal Lahir</span>
                                        <span class="text-white mt-0.5 block font-mono" x-text="activeUserProfile.date_of_birth ? new Date(activeUserProfile.date_of_birth).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'"></span>
                                    </div>
                                </div>

                                {{-- Cabang Olahraga Minat --}}
                                <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800/60">
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold mb-2">Cabang Olahraga Minat</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-if="activeUserProfile.sports && Array.isArray(activeUserProfile.sports) && activeUserProfile.sports.length > 0">
                                            <template x-for="sport in activeUserProfile.sports" :key="sport">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-900 border border-slate-800 text-cyan-300">
                                                    <span>🏅</span>
                                                    <span class="capitalize" x-text="sport"></span>
                                                </span>
                                            </template>
                                        </template>
                                        <template x-if="!activeUserProfile.sports || !Array.isArray(activeUserProfile.sports) || activeUserProfile.sports.length === 0">
                                            <span class="text-xs text-slate-500 italic">Belum memilih cabang olahraga favorit.</span>
                                        </template>
                                    </div>
                                </div>

                                {{-- Bio Profil --}}
                                <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800/60">
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold mb-1">Bio Pengguna</span>
                                    <p class="text-xs text-slate-300 leading-relaxed italic" x-text="activeUserProfile.bio || 'Belum ada deskripsi bio profil.'"></p>
                                </div>

                                {{-- Komunitas yang Diikuti --}}
                                <div class="space-y-2 pt-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-white flex items-center gap-1.5">
                                            <span>🏟️ Komunitas yang Diikuti</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/10 text-cyan-300 border border-cyan-500/20" 
                                                  x-text="(activeUserProfile.joined_communities ? activeUserProfile.joined_communities.length : 0) + ' Komunitas'"></span>
                                        </span>
                                    </div>

                                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                        <template x-if="activeUserProfile.joined_communities && activeUserProfile.joined_communities.length > 0">
                                            <div class="space-y-2">
                                                <template x-for="comm in activeUserProfile.joined_communities" :key="comm.id">
                                                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800/80 flex items-center justify-between gap-3">
                                                        <div class="flex items-center gap-2.5 min-w-0">
                                                            <div class="w-8 h-8 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center text-cyan-400 font-bold text-xs flex-shrink-0">
                                                                ⚽
                                                            </div>
                                                            <div class="min-w-0">
                                                                <div class="text-xs font-bold text-white truncate" x-text="comm.name"></div>
                                                                <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                                                                    <span class="text-cyan-400" x-text="comm.sport_category || 'Umum'"></span>
                                                                    <span>&bull;</span>
                                                                    <span x-text="comm.city || 'Indonesia'"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="text-right flex-shrink-0">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                                <span x-text="comm.pivot && comm.pivot.status ? comm.pivot.status.toUpperCase() : 'ACTIVE'"></span>
                                                            </span>
                                                            <div class="text-[10px] text-slate-400 mt-0.5 font-mono">
                                                                <span x-text="comm.pivot && comm.pivot.joined_at ? new Date(comm.pivot.joined_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : 'Terdaftar'"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!activeUserProfile.joined_communities || activeUserProfile.joined_communities.length === 0">
                                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800/60 text-center text-slate-500 text-xs">
                                                Pengguna ini belum bergabung dengan komunitas olahraga manapun.
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- ============================================================== --}}
                        {{-- 2. DATA KHUSUS PENYEDIA KOMUNITAS (ROLE === 'community_owner') --}}
                        {{-- ============================================================== --}}
                        <template x-if="activeUserProfile.role === 'community_owner'">
                            <div class="space-y-4">
                                {{-- Info Penyelenggara --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                                    <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/60">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Kontak WhatsApp</span>
                                        <span class="font-mono text-white mt-0.5 block" x-text="activeUserProfile.phone || '-'"></span>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/60">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Terdaftar Sejak</span>
                                        <span class="font-mono text-indigo-300 mt-0.5 block" x-text="activeUserProfile.created_at ? new Date(activeUserProfile.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'"></span>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/60">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Status Penyelenggara</span>
                                        <span class="font-bold text-xs mt-0.5 block" :class="activeUserProfile.is_active ? 'text-emerald-400' : 'text-red-400'" x-text="activeUserProfile.is_active ? 'Aktif' : 'Nonaktif'"></span>
                                    </div>
                                </div>

                                {{-- Komunitas yang Dikelola --}}
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-white flex items-center gap-1.5">
                                            <span>👑 Komunitas yang Dikelola</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20" 
                                                  x-text="(activeUserProfile.owned_communities ? activeUserProfile.owned_communities.length : 0) + ' Komunitas'"></span>
                                        </span>
                                    </div>

                                    <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                        <template x-if="activeUserProfile.owned_communities && activeUserProfile.owned_communities.length > 0">
                                            <div class="space-y-2.5">
                                                <template x-for="comm in activeUserProfile.owned_communities" :key="comm.id">
                                                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800/80 hover:border-slate-700 transition-all space-y-2">
                                                        <div class="flex items-start justify-between gap-3">
                                                            <div class="min-w-0">
                                                                <h5 class="text-xs font-extrabold text-white truncate" x-text="comm.name"></h5>
                                                                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-400">
                                                                    <span class="text-indigo-400 font-semibold" x-text="comm.sport_category || 'Olahraga'"></span>
                                                                    <span>&bull;</span>
                                                                    <span x-text="comm.city || 'Indonesia'"></span>
                                                                </div>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <template x-if="comm.verification_status === 'verified'">
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                                                        ✓ Terverifikasi
                                                                    </span>
                                                                </template>
                                                                <template x-if="comm.verification_status === 'pending'">
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-300 border border-amber-500/30">
                                                                        ⏳ Menunggu
                                                                    </span>
                                                                </template>
                                                                <template x-if="comm.verification_status === 'rejected'">
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-500/10 text-red-400 border border-red-500/30">
                                                                        ✕ Ditolak
                                                                    </span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="pt-2 border-t border-slate-800/60 flex items-center justify-between text-[11px] text-slate-400">
                                                            <div class="flex items-center gap-3">
                                                                <span>👥 <strong class="text-slate-300 font-mono" x-text="comm.members_count || 0"></strong> Anggota</span>
                                                                <span>⭐ <strong class="text-slate-300 font-mono" x-text="comm.followers_count || 0"></strong> Pengikut</span>
                                                            </div>
                                                            <span class="text-[10px] font-bold" :class="comm.is_active ? 'text-emerald-400' : 'text-slate-500'" x-text="comm.is_active ? 'Komunitas Aktif' : 'Nonaktif'"></span>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!activeUserProfile.owned_communities || activeUserProfile.owned_communities.length === 0">
                                            <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800/60 text-center text-slate-500 text-xs">
                                                Penyedia ini belum mendaftarkan klub atau komunitas olahraga.
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- ============================================================== --}}
                        {{-- 3. DATA KHUSUS ADMINISTRATOR (ROLE === 'admin') --}}
                        {{-- ============================================================== --}}
                        <template x-if="activeUserProfile.role === 'admin'">
                            <div class="space-y-4">
                                {{-- Hak Akses Administrator --}}
                                <div class="p-4 rounded-2xl bg-amber-500/5 border border-amber-500/20 space-y-2">
                                    <div class="flex items-center gap-2 text-xs font-bold text-amber-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                        </svg>
                                        <span>Akses Tata Kelola Platform &amp; Integritas Operasional</span>
                                    </div>
                                    <p class="text-xs text-slate-300 leading-relaxed">
                                        Akun ini memiliki hak akses penuh dalam mengawasi operasional platform Bugar.in, menyetujui verifikasi komunitas, serta mengelola keamanan data.
                                    </p>
                                </div>

                                {{-- Detail Keamanan & Akun Admin --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                                    <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/60">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Email Terverifikasi</span>
                                        <span class="font-mono text-emerald-400 font-bold mt-0.5 block flex items-center gap-1">
                                            <span>✓</span> Terverifikasi Resmi
                                        </span>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/60">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Tanggal Penunjukan</span>
                                        <span class="font-mono text-white mt-0.5 block" x-text="activeUserProfile.created_at ? new Date(activeUserProfile.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-'"></span>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/60">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Tingkat Hak Akses</span>
                                        <span class="font-mono text-amber-300 font-bold mt-0.5 block">Console Ops Superadmin</span>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/60">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Kontak Telepon</span>
                                        <span class="font-mono text-white mt-0.5 block" x-text="activeUserProfile.phone || 'Internal Staff'"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Modal Footer --}}
                <div class="mt-6 pt-4 border-t border-slate-800 text-right">
                    <button type="button" @click="profileModalOpen = false" class="px-5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition-all cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
{{-- Load Chart.js for data visualization --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function adminUsersHandler() {
    return {
        // State Filter
        search: @json(request('search', '')),
        role: @json(request('role', '')),
        status: @json(request('status', '')),
        community_status: @json(request('community_status', '')),
        community_id: @json(request('community_id', '')),
        sort: @json(request('sort', 'latest')),
        
        // UI State
        loading: false,
        totalUsers: {{ $users->total() }},
        
        // Modals State
        profileModalOpen: false,
        activeUserProfile: null,

        init() {
            // Listen to browser Back/Forward navigation
            window.addEventListener('popstate', (e) => {
                this.fetchUsers(window.location.href, false);
            });
        },

        // Helper cek keberadaan filter aktif
        get hasActiveFilters() {
            return this.search !== '' || 
                   this.role !== '' || 
                   this.status !== '' || 
                   this.community_status !== '' || 
                   this.community_id !== '' || 
                   this.sort !== 'latest';
        },

        // Fetch Data Pengguna secara Asynchronous via AJAX
        async fetchUsers(targetUrl = null, updateHistory = true) {
            this.loading = true;

            try {
                let url;
                if (targetUrl) {
                    url = new URL(targetUrl, window.location.origin);
                } else {
                    url = new URL('{{ route('admin.users.index') }}', window.location.origin);
                    if (this.search) url.searchParams.set('search', this.search);
                    if (this.role) url.searchParams.set('role', this.role);
                    if (this.status) url.searchParams.set('status', this.status);
                    if (this.community_status) url.searchParams.set('community_status', this.community_status);
                    if (this.community_id) url.searchParams.set('community_id', this.community_id);
                    if (this.sort && this.sort !== 'latest') url.searchParams.set('sort', this.sort);
                }

                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) throw new Error('Gagal memuat data pengguna');

                const data = await response.json();
                
                // Update HTML tabel
                const tableContainer = document.getElementById('usersTableContainer');
                if (tableContainer && data.html) {
                    tableContainer.innerHTML = data.html;
                    
                    // Inisialisasi kembali directive Alpine di DOM baru
                    if (window.Alpine) {
                        window.Alpine.initTree(tableContainer);
                    }
                }

                if (data.total !== undefined) {
                    this.totalUsers = data.total;
                }

                // Update URL pada address bar browser tanpa reload
                if (updateHistory) {
                    window.history.pushState({}, '', url.toString());
                }

            } catch (error) {
                console.error('AJAX Error:', error);
            } finally {
                this.loading = false;
            }
        },

        // Reset semua filter & load ulang
        resetFilters() {
            this.search = '';
            this.role = '';
            this.status = '';
            this.community_status = '';
            this.community_id = '';
            this.sort = 'latest';
            this.fetchUsers();
        },

        // Event delegation untuk menangani klik link pagination secara AJAX
        handleTableClick(e) {
            const link = e.target.closest('.pagination-container a');
            if (link && link.href) {
                e.preventDefault();
                this.fetchUsers(link.href);
            }
        },

        getAvatarUrl(user) {
            if (!user) return null;
            if (user.avatar_url) return user.avatar_url;
            if (!user.avatar) return null;
            if (user.avatar.startsWith('http://') || user.avatar.startsWith('https://')) {
                return user.avatar;
            }
            return '/storage/' + user.avatar.replace(/^\/?(storage\/)?/, '');
        },

        openProfileModal(user) {
            this.activeUserProfile = user;
            this.profileModalOpen = true;
        }
    };
}

// Inisialisasi Chart.js untuk grafik
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        return;
    }

    // 1. Chart Pendaftaran Pengguna 6 Bulan Terakhir
    const regCtx = document.getElementById('userRegistrationChart');
    if (regCtx) {
        const regLabels = @json($registrationChart['labels']);
        const regData = @json($registrationChart['data']);

        new Chart(regCtx, {
            type: 'line',
            data: {
                labels: regLabels,
                datasets: [{
                    label: 'Pengguna Baru',
                    data: regData,
                    borderColor: '#06b6d4',
                    backgroundColor: 'rgba(6, 182, 212, 0.12)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 3,
                    pointBackgroundColor: regLabels.map(l => {
                        const low = l.toLowerCase();
                        if (low.includes('nov')) return '#38bdf8';
                        if (low.includes('des')) return '#818cf8';
                        return '#06b6d4';
                    }),
                    pointBorderColor: '#0f172a',
                    pointBorderWidth: 2,
                    pointRadius: regLabels.map(l => {
                        const low = l.toLowerCase();
                        return (low.includes('nov') || low.includes('des')) ? 6 : 4;
                    }),
                    pointHoverRadius: regLabels.map(l => {
                        const low = l.toLowerCase();
                        return (low.includes('nov') || low.includes('des')) ? 8 : 6;
                    }),
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#38bdf8',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(51, 65, 85, 0.3)' },
                        ticks: { color: '#94a3b8', font: { size: 10, family: 'Plus Jakarta Sans' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(51, 65, 85, 0.3)' },
                        ticks: { 
                            precision: 0, 
                            color: '#94a3b8', 
                            font: { size: 10, family: 'Plus Jakarta Sans' } 
                        }
                    }
                }
            }
        });
    }

    // 2. Chart Komposisi Peran Akun (Doughnut)
    const roleCtx = document.getElementById('userRoleChart');
    if (roleCtx) {
        const roleData = @json(array_values($roleDistribution));

        new Chart(roleCtx, {
            type: 'doughnut',
            data: {
                labels: ['User Reguler', 'Penyedia Komunitas', 'Admin'],
                datasets: [{
                    data: roleData,
                    backgroundColor: [
                        '#06b6d4', // Cyan for User
                        '#6366f1', // Indigo for Community Owner
                        '#f59e0b'  // Amber for Admin
                    ],
                    borderColor: '#0f172a',
                    borderWidth: 3,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#f8fafc',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10
                    }
                }
            }
        });
    }
});
</script>
@endpush
