<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F0F7FF]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Jelajahi Komunitas Olahraga Lokal — Bugar.in</title>
    <meta name="description" content="Temukan dan bergabung dengan komunitas olahraga lokal terdekat: futsal, basket, badminton, running, yoga, dan lainnya.">

    <!-- Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts (Tailwind via Vite with custom design tokens) -->
    @vite(['resources/css/app.css', 'resources/css/user-dashboard.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased flex flex-col justify-between bg-[#F0F7FF] relative overflow-x-hidden"
      x-data="{
          searchQuery: '',
          selectedCity: '',
          selectedCategories: [],
          toggleCategory(cat) {
              const idx = this.selectedCategories.indexOf(cat);
              if (idx > -1) {
                  this.selectedCategories.splice(idx, 1);
              } else {
                  this.selectedCategories.push(cat);
              }
          },
          resetFilters() {
              this.searchQuery = '';
              this.selectedCity = '';
              this.selectedCategories = [];
          }
      }"
      x-cloak>

    {{-- Ambient subtle gradient orbs (Royal Blue + Cyan, Ice Blue background) --}}
    <div class="fixed -top-32 -right-32 w-[520px] h-[520px] rounded-full bg-gradient-to-br from-blue-200/25 via-cyan-100/20 to-transparent blur-3xl pointer-events-none z-0" aria-hidden="true"></div>
    <div class="fixed top-1/2 -left-28 w-[440px] h-[440px] rounded-full bg-gradient-to-tr from-blue-100/20 via-sky-50/20 to-transparent blur-3xl pointer-events-none z-0" aria-hidden="true"></div>

    <div class="relative z-10 flex flex-col min-h-screen">
        @unless(isset($isDashboard) && $isDashboard)
        <!-- ==============================================================
             1. NAVBAR UTAMA
             ============================================================== -->
        <header class="glass-nav sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20 gap-4">
                    <!-- Logo & Nav Links -->
                    <div class="flex items-center gap-8">
                        <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center text-white shadow-md shadow-blue-500/25 group-hover:scale-105 transition-transform duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                    <path d="M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9l1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z"/>
                                </svg>
                            </div>
                            <span class="font-extrabold text-xl text-slate-900 tracking-tight">
                                Bugar<span class="text-blue-600">.in</span>
                            </span>
                        </a>

                        <nav class="hidden lg:flex items-center gap-7">
                            <a href="{{ route('user.explore') }}" class="text-sm font-bold text-blue-600 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                Jelajah Komunitas
                            </a>
                            <a href="{{ route('user.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Dashboard Saya</a>
                            <a href="{{ route('landing') }}" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Beranda</a>
                        </nav>
                    </div>

                    <!-- Right Action Bar -->
                    <div class="flex items-center gap-3.5">
                        <div class="hidden md:block relative w-64">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                            </svg>
                            <input type="text" x-model="searchQuery" placeholder="Cari nama komunitas..." class="w-full pl-10 pr-4 py-2 bg-slate-50/80 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                        </div>

                        <!-- Dropdown Notifikasi -->
                        <div class="relative" x-data="{ openNotif: false, hasUnread: true }">
                            <button @click="openNotif = !openNotif" class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 relative cursor-pointer transition-all shadow-2xs">
                                <span x-show="hasUnread" class="absolute top-2 right-2 w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </button>

                            <div x-show="openNotif" @click.away="openNotif = false" x-cloak class="absolute right-0 mt-2 w-80 bg-white border border-slate-200/90 rounded-2xl shadow-xl p-4 z-50 text-xs space-y-3">
                                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                    <span class="font-bold text-slate-900">Notifikasi</span>
                                    <button type="button" @click="hasUnread = false" class="text-[10px] text-blue-600 font-semibold cursor-pointer hover:underline">Tandai dibaca</button>
                                </div>
                                <div class="space-y-2">
                                    <div class="p-3 bg-blue-50/60 rounded-xl text-slate-700 border border-blue-100/50">
                                        <p class="font-bold text-slate-900">Selamat Datang di Bugar.in! 🎉</p>
                                        <p class="text-[11px] text-slate-500 mt-1">Jelajahi komunitas olahraga lokal dan temukan teman latihan baru di kotamu.</p>
                                        <span class="text-[10px] text-slate-400 mt-1.5 block">Hari ini</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Profile Link -->
                        <a href="{{ route('user.profile.edit') }}" class="flex items-center gap-2 pl-2 border-l border-slate-200 hover:opacity-85 transition-opacity cursor-pointer group" title="Edit Profil">
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 text-white font-bold flex items-center justify-center text-xs shadow-xs">
                                {{ strtoupper(substr(Auth::user()->name ?? 'R', 0, 1)) }}
                            </div>
                            <span class="hidden sm:inline text-xs font-bold text-slate-700 group-hover:text-blue-600 transition-colors">{{ Auth::user()->name ?? 'User' }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </header>
        @endunless

        <!-- ==============================================================
             2. MAIN CONTENT AREA
             ============================================================== -->
        <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
            
            <!-- Hero Header: Human & Editorial -->
            <div class="mb-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-5">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200/80 text-blue-700 text-xs font-bold mb-3 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                            <span>Eksplorasi Komunitas Lokal</span>
                        </div>
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                            Temukan Ritme &amp; Rekan Olahragamu
                        </h1>
                        <p class="text-slate-600 text-sm sm:text-base mt-2 leading-relaxed">
                            Bergabunglah dalam sesi lari pagi, fun match badminton, futsal, renang, atau yoga bersama pegiat olahraga di sekitarmu.
                        </p>
                    </div>

                    {{-- Highlight Sesi Aktif --}}
                    <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-white border border-slate-200/90 shadow-xs self-start md:self-auto">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center text-white shadow-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-500 block leading-tight">Aktivitas Pekan Ini</span>
                            <span class="text-sm font-extrabold text-slate-900 leading-tight">{{ $totalActiveSessions ?? 0 }} Sesi Terjadwal</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Category Selector Pills -->
                <div class="mt-6 flex items-center gap-2 overflow-x-auto pb-2 custom-scrollbar">
                    <button type="button"
                            @click="selectedCategories = []"
                            :class="selectedCategories.length === 0 ? 'bg-blue-600 text-white shadow-xs shadow-blue-500/25' : 'bg-white text-slate-600 hover:bg-blue-50 hover:text-blue-600 border border-slate-200'"
                            class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap cursor-pointer">
                        ✨ Semua Kategori
                    </button>
                    @foreach($sportCategories ?? [] as $cat)
                        @php $catLower = strtolower(trim($cat)); @endphp
                        <button type="button"
                                @click="toggleCategory('{{ $catLower }}')"
                                :class="selectedCategories.includes('{{ $catLower }}') ? 'bg-blue-600 text-white shadow-xs shadow-blue-500/25' : 'bg-white text-slate-600 hover:bg-blue-50 hover:text-blue-600 border border-slate-200'"
                                class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap cursor-pointer">
                            {{ ucfirst($cat) }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Flash Message Alerts -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 font-bold p-1">✕</button>
                </div>
            @endif

            @if(session('info'))
                <div class="mb-6 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('info') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-blue-600 hover:text-blue-800 font-bold p-1">✕</button>
                </div>
            @endif

            <!-- ==============================================================
                 3. MAIN GRID: SIDEBAR FILTER & COMMUNITY CARDS
                 ============================================================== -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
                
                <!-- FILTER ASIDE -->
                <aside class="lg:col-span-1 space-y-6">
                    <div class="glass-card rounded-2xl p-5 space-y-5">
                        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                </svg>
                                <span class="font-bold text-slate-900 text-sm">Filter Komunitas</span>
                            </div>
                            <button type="button" @click="resetFilters()" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline cursor-pointer">
                                Reset
                            </button>
                        </div>

                        <!-- Search keyword -->
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Pencarian</label>
                            <div class="relative">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                                </svg>
                                <input type="text" x-model="searchQuery" placeholder="Nama komunitas atau kota..." class="glass-input w-full pl-8 pr-3 py-2 text-xs rounded-xl text-slate-700">
                            </div>
                        </div>

                        <!-- Kota / Wilayah Filter -->
                        <div class="space-y-1.5 pt-3 border-t border-slate-100">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Kota / Wilayah</label>
                            <select x-model="selectedCity" class="glass-input w-full px-3 py-2 text-xs rounded-xl text-slate-700 font-medium">
                                <option value="">Semua Wilayah</option>
                                @foreach($cities ?? [] as $city)
                                    <option value="{{ strtolower($city) }}">{{ ucfirst($city) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Kategori Olahraga Checklist -->
                        <div class="space-y-2 pt-3 border-t border-slate-100">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Kategori Olahraga</label>
                            <div class="space-y-1.5 text-xs max-h-56 overflow-y-auto custom-scrollbar pr-1">
                                @foreach($sportCategories ?? [] as $cat)
                                    @php $catLower = strtolower(trim($cat)); @endphp
                                    <label class="flex items-center justify-between p-1.5 rounded-lg hover:bg-blue-50/50 cursor-pointer transition-colors text-slate-700">
                                        <span class="flex items-center gap-2.5">
                                            <input type="checkbox"
                                                   value="{{ $catLower }}"
                                                   x-model="selectedCategories"
                                                   class="rounded text-blue-600 focus:ring-blue-500 w-3.5 h-3.5 cursor-pointer">
                                            <span class="font-medium">{{ ucfirst($cat) }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Bantuan / Host Baru -->
                        <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-100 text-xs space-y-1.5">
                            <span class="font-bold text-blue-900 block">Punya Komunitas?</span>
                            <p class="text-[11px] text-blue-700 leading-relaxed">Daftarkan komunitasmu dan undang atlet lokal untuk bergabung.</p>
                            <a href="{{ route('auth.penyedia-komunitas.register') }}" class="inline-block text-[11px] font-bold text-blue-600 hover:text-blue-800 hover:underline pt-0.5">Daftar sebagai Penyelenggara →</a>
                        </div>
                    </div>
                </aside>

                <!-- COMMUNITY CARDS GRID -->
                <div class="lg:col-span-3 space-y-6">
                    
                    <!-- Result Count Bar -->
                    <div class="glass-card rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-bold text-slate-700">
                                Menampilkan {{ $communities->firstItem() ?? 0 }} - {{ $communities->lastItem() ?? 0 }} dari total {{ $communities->total() }} Komunitas Aktif
                            </span>
                        </div>
                        <span class="text-[11px] text-slate-400 font-medium">Bugar.in Verified Communities</span>
                    </div>

                    @if($communities->isEmpty())
                        <div class="glass-card rounded-2xl p-12 text-center max-w-md mx-auto">
                            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4 border border-blue-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                </svg>
                            </div>
                            <h4 class="text-base font-bold text-slate-900 mb-1">Belum Ada Komunitas Terdaftar</h4>
                            <p class="text-xs text-slate-500 leading-relaxed">Komunitas olahraga baru akan muncul di sini setelah didaftarkan oleh host.</p>
                        </div>
                    @else
                        <!-- Cards Grid (3 Columns on Desktop) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            @foreach($communities as $comm)
                                @php
                                    $commName = strtolower($comm->name ?? '');
                                    $commCat = strtolower(trim($comm->sport_category ?? ''));
                                    $commCity = strtolower($comm->city ?? '');
                                    
                                    $sportCoverClass = match($commCat) {
                                        'futsal'                => 'sport-cover-futsal',
                                        'basket', 'basketball'  => 'sport-cover-basket',
                                        'badminton'             => 'sport-cover-badminton',
                                        'lari', 'running'       => 'sport-cover-lari',
                                        'renang', 'swimming'    => 'sport-cover-renang',
                                        'yoga', 'pilates'       => 'sport-cover-yoga',
                                        default                 => 'sport-cover-default',
                                    };

                                    $hasCustomCover = !empty($comm->cover_image);
                                    $isMember = $comm->members()->where('user_id', auth()->id())->exists();
                                @endphp

                                <div x-show="
                                    (!searchQuery || '{{ $commName }}'.includes(searchQuery.toLowerCase()) || '{{ $commCat }}'.includes(searchQuery.toLowerCase()) || '{{ $commCity }}'.includes(searchQuery.toLowerCase())) &&
                                    (!selectedCity || '{{ $commCity }}' === selectedCity) &&
                                    (selectedCategories.length === 0 || selectedCategories.includes('{{ $commCat }}'))
                                " class="community-card flex flex-col justify-between">
                                    
                                    <div>
                                        <!-- Cover Image & Sport Category Badge -->
                                        <div class="relative h-40 overflow-hidden {{ $sportCoverClass }}">
                                            @if($hasCustomCover)
                                                <img src="{{ \Illuminate\Support\Str::startsWith($comm->cover_image, ['http://', 'https://']) ? $comm->cover_image : \Illuminate\Support\Facades\Storage::url($comm->cover_image) }}"
                                                     alt="{{ $comm->name }}"
                                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                                     loading="lazy"
                                                     onerror="this.style.display='none'">
                                            @endif

                                            <!-- Category Badge (Glass Dark) -->
                                            <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold tracking-wide">
                                                {{ ucfirst($comm->sport_category ?? 'Umum') }}
                                            </span>

                                            <!-- Status Slot Badge -->
                                            <span class="absolute top-3 right-3 inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-500 text-white text-[10px] font-bold shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                                Open Slot
                                            </span>

                                            <!-- Location Bar on Bottom of Cover -->
                                            <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-center justify-between text-white text-[11px] font-medium bg-slate-950/65 backdrop-blur-xs px-2.5 py-1.5 rounded-xl border border-white/10">
                                                <div class="flex items-center gap-1.5 truncate">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-cyan-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    <span class="truncate">{{ $comm->city ?? $comm->location ?? 'Indonesia' }}</span>
                                                </div>
                                                <span class="text-[10px] text-white/80 font-bold shrink-0">Bugar.in</span>
                                            </div>
                                        </div>

                                        <!-- Card Content -->
                                        <div class="p-4 space-y-2.5">
                                            <h3 class="font-bold text-slate-900 text-base leading-snug line-clamp-1 group-hover:text-blue-600 transition-colors">
                                                {{ $comm->name }}
                                            </h3>
                                            <p class="text-slate-500 text-xs line-clamp-2 leading-relaxed">
                                                {{ $comm->description ?? 'Komunitas olahraga aktif yang rutin berkumpul untuk latihan bersama dan membangun kebugaran.' }}
                                            </p>
                                            
                                            <!-- Next Session Pill -->
                                            <div class="pt-2 border-t border-slate-100 flex items-center gap-2 text-[11px] text-slate-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="truncate">
                                                    @if($comm->schedules && $comm->schedules->isNotEmpty())
                                                        {{ ucfirst($comm->schedules->first()->day_of_week) }} · {{ substr($comm->schedules->first()->start_time, 0, 5) }} WIB
                                                    @else
                                                        Jadwal Rutin Mingguan
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card Footer: Members count & Action CTA -->
                                    <div class="p-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2 mt-auto">
                                        <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span>{{ number_format($comm->members_count ?? $comm->members()->count() ?? 0) }} Member</span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            @if($isMember)
                                                <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold">
                                                    ✓ Tergabung
                                                </span>
                                            @else
                                                <form action="{{ route('user.communities.join', $comm->id) }}" method="POST" class="m-0">
                                                    @csrf
                                                    <button type="submit" class="btn-primary text-xs px-3.5 py-1.5">
                                                        Gabung
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination Links -->
                        <div class="pt-6 border-t border-slate-200/80">
                            {{ $communities->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </main>

        <!-- ==============================================================
             4. FOOTER
             ============================================================== -->
        @unless(isset($isDashboard) && $isDashboard)
            <footer class="bg-white/80 backdrop-blur-md border-t border-slate-200/80 py-6 mt-16 text-xs text-slate-500">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold text-slate-900">Bugar<span class="text-blue-600">.in</span></span>
                        <span>© 2026 Platform Komunitas Olahraga Lokal Indonesia.</span>
                    </div>
                    <div class="flex items-center gap-6 font-medium">
                        <a href="{{ route('landing') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
                        <a href="{{ route('user.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard Pengguna</a>
                        <a href="{{ route('user.profile.edit') }}" class="hover:text-blue-600 transition-colors">Pengaturan Profil</a>
                    </div>
                </div>
            </footer>
        @endunless
    </div>

</body>
</html>