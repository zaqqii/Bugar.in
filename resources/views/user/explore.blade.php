<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#f8faff]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Jelajahi Komunitas Olahraga Lokal — Bugar.in</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased flex flex-col justify-between" x-data="{ searchQuery: '', selectedCity: '', selectedCategories: [] }" x-cloak>
    
    @unless(isset($isDashboard) && $isDashboard)
       <!-- 1. NAVBAR UTAMA -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4">
                <!-- Logo & Nav Links -->
                <div class="flex items-center gap-10">
                    <a href="{{ route('user.dashboard') }}" class="flex items-center gap-2.5 group">
                        <div class="w-10 h-10 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path d="M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9l1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z"/>
                            </svg>
                        </div>
                        <span class="font-extrabold text-xl text-blue-600 tracking-tight">
                            Bugar<span class="text-slate-900">.in</span>
                        </span>
                    </a>

                    <nav class="hidden lg:flex items-center gap-8">
                        <a href="{{ route('user.explore') }}" class="text-sm font-bold text-blue-600">Jelajah Komunitas</a>
                        <a href="#" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Jadwal & Event</a>
                        <a href="#" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">Leaderboard</a>
                    </nav>
                </div>

                <!-- Right Action Bar -->
                <div class="flex items-center gap-4">
                    <div class="hidden md:block relative w-64">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                        <input type="text" placeholder="Cari event, komunitas, atlet..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                    <div class="relative" x-data="{ openNotif: false, hasUnread: true }">
                        <!-- Tombol Lonceng -->
                        <button @click="openNotif = !openNotif" class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 relative cursor-pointer">
                            <!-- Titik Merah (Akan hilang jika hasUnread bernilai false) -->
                            <span x-show="hasUnread" class="absolute top-2 right-2 w-2 h-2 rounded-full bg-rose-500"></span>
                            
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </button>

                        <!-- Kotak Dropdown Notifikasi -->
                        <div x-show="openNotif" @click.away="openNotif = false" x-cloak class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-2xl shadow-lg p-4 z-50 text-xs space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                <span class="font-bold text-slate-900">Notifikasi</span>
                                <!-- Tombol Tandai Dibaca (Mengubah hasUnread menjadi false) -->
                                <span @click="hasUnread = false" class="text-[10px] text-blue-600 font-semibold cursor-pointer hover:underline">Tandai dibaca</span>
                            </div>
                            <div class="space-y-2">
                                <div class="p-2.5 bg-blue-50/60 rounded-xl text-slate-700">
                                    <p class="font-semibold text-slate-900">Selamat Datang di Bugar.in! 🎉</p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Jelajahi komunitas olahraga lokal favoritmu dan mulai bergabung.</p>
                                    <span class="text-[9px] text-slate-400 mt-1 block">Baru saja</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Indikator Akun User Biasa (Bisa diklik ke Edit Profil) -->
                    <a href="{{ route('user.profile.edit') }}" class="flex items-center gap-2 pl-2 border-l border-slate-200 hover:opacity-80 transition-opacity cursor-pointer" title="Edit Profil">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center text-xs">
                            {{ strtoupper(substr(Auth::user()->name ?? 'R', 0, 1)) }}
                        </div>
                        <span class="hidden sm:inline text-xs font-bold text-slate-700">{{ Auth::user()->name ?? 'reva' }}</span>
                    </a>
                </div>
            </div>
        </div>
    </header>
    @endunless

    <!-- 2. MAIN CONTENT -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full relative">
        
        <!-- Background Gradient Glow -->
        <div class="absolute -top-10 left-0 right-0 h-72 bg-gradient-to-r from-blue-700/25 via-indigo-600/20 to-blue-900/30 blur-[100px] -z-10 rounded-full pointer-events-none"></div>

        <!-- Header Banner & Title -->
        <div class="mb-8 relative">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-100/80 border border-blue-200 text-blue-800 text-xs font-bold mb-3 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                        <span>Jelajahi & Bertemu Komunitas</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Jelajahi Komunitas Olahraga Lokal
                    </h1>
                    <p class="text-slate-600 text-sm mt-1 max-w-3xl">
                        Temukan teman berolahraga baru di kotamu sesuai minat, level kemahiran, dan jadwal favoritmu.
                    </p>
                </div>
                <div class="self-start md:self-auto px-4 py-2 rounded-2xl bg-blue-900 text-white text-xs font-bold flex items-center gap-2 shadow-md shadow-blue-900/20">
                    🔥 {{ $totalActiveSessions ?? 0 }} Sesi Aktif Pekan Ini
                </div>
            </div>

            <!-- Global Search & Sorting Bar -->
            <div class="mt-6 flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-grow w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <input type="text" x-model="searchQuery" placeholder="Cari nama komunitas, cabang olahraga, atau area..." class="w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200/90 rounded-2xl text-xs shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Flash Message Notifikasi -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <span class="text-base">✅</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 font-bold">✕</button>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-6 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <span class="text-base">ℹ️</span>
                    <span>{{ session('info') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-blue-600 hover:text-blue-800 font-bold">✕</button>
            </div>
        @endif

        <!-- MAIN LAYOUT: SIDEBAR FILTER & CARDS GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
            
            <!-- 1. SIDEBAR FILTER KOMUNITAS -->
            <aside class="lg:col-span-1 space-y-6">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                            <span class="font-bold text-slate-900 text-sm">Filter Komunitas</span>
                        </div>
                        <a href="{{ route('user.explore') }}" class="text-xs font-semibold text-rose-500 hover:underline">Reset</a>
                    </div>

                    <!-- Kategori Olahraga -->
                    <div class="space-y-3">
                        <label class="font-extrabold text-slate-800 uppercase tracking-wider block text-[11px]">Kategori Olahraga</label>
                        <div class="space-y-2 text-xs">
                            @foreach($sportCategories ?? [] as $cat)
                                @php
                                    $catLower = strtolower($cat);
                                @endphp
                                <label class="flex items-center justify-between text-slate-600 cursor-pointer">
                                    <span class="flex items-center gap-2">
                                        <input type="checkbox" 
                                            value="{{ $catLower }}" 
                                            x-model="selectedCategories" 
                                            class="rounded text-blue-600 w-4 h-4 cursor-pointer"> 
                                        {{ ucfirst($cat) }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Kota / Wilayah -->
                    <div class="space-y-3 pt-4 border-t border-slate-100">
                        <label class="font-extrabold text-slate-800 uppercase tracking-wider block text-[11px]">Kota / Wilayah</label>
                        <select x-model="selectedCity" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            <option value="">Semua Wilayah</option>
                            @foreach($cities ?? [] as $city)
                                <option value="{{ strtolower($city) }}">{{ ucfirst($city) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </aside>

           <!-- 2. CARDS GRID KOMUNITAS (DINAMIS & PAGINATED) -->
            <div class="lg:col-span-3 space-y-6">
                
                <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                    <span class="text-xs font-bold text-slate-700">
                        Menampilkan {{ $communities->firstItem() ?? 0 }} - {{ $communities->lastItem() ?? 0 }} dari total {{ $communities->total() }} Komunitas Olahraga
                    </span>
                </div>

                @if($communities->isEmpty())
                    <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-xs">
                        <h4 class="text-base font-bold text-slate-900 mb-1">Belum Ada Komunitas Terdaftar</h4>
                        <p class="text-xs text-slate-500">Silakan jalankan seeder database terlebih dahulu untuk memunculkan data komunitas.</p>
                    </div>
                @else
                    <!-- Grid Kartu Komunitas (Maksimal 6 per halaman) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($communities as $comm)
                            @php
                                $commName = strtolower($comm->name ?? '');
                                $commCat = strtolower($comm->sport_category ?? '');
                                $commCity = strtolower($comm->city ?? '');
                            @endphp
                            <div x-show="
                                (!searchQuery || '{{ $commName }}'.includes(searchQuery.toLowerCase()) || '{{ $commCat }}'.includes(searchQuery.toLowerCase())) &&
                                (!selectedCity || '{{ $commCity }}' === selectedCity) &&
                                (selectedCategories.length === 0 || selectedCategories.includes('{{ $commCat }}'))
                            " class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between hover:border-blue-300 hover:shadow-md transition-all">
                                
                                <!-- Cover Image & Badge -->
                                <div class="relative h-36 bg-slate-100">
                                    <img src="{{ $comm->cover_image ?? 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=600&q=80' }}" alt="{{ $comm->name }}" class="w-full h-full object-cover">
                                    <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold">{{ $comm->sport_category }}</span>
                                    <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-emerald-500 text-white text-[10px] font-bold">Open Slot</span>
                                    <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-[11px] font-medium bg-slate-900/60 backdrop-blur-xs px-2.5 py-1 rounded-lg">
                                        <span>📍 {{ $comm->location ?? $comm->city ?? 'Indonesia' }}</span>
                                    </div>
                                </div>

                                <!-- Informasi Detail Komunitas -->
                                <div class="p-4 space-y-2">
                                    <h3 class="font-bold text-slate-900 text-sm">{{ $comm->name }}</h3>
                                    <p class="text-slate-500 text-xs line-clamp-2">{{ $comm->description ?? 'Komunitas olahraga aktif yang terbuka untuk anggota baru.' }}</p>
                                    
                                    <div class="pt-3 border-t border-slate-100 space-y-1 text-[11px] text-slate-600">
                                        <div class="flex justify-between">
                                            <span>📅 Sesi:</span>
                                            <span class="font-semibold">
                                                @if($comm->schedules && $comm->schedules->isNotEmpty())
                                                    {{ ucfirst($comm->schedules->first()->day_of_week) }} • {{ substr($comm->schedules->first()->start_time, 0, 5) }} WIB
                                                @else
                                                    Jadwal Rutin
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer Card: Jumlah Member & Tombol Aksi -->
                                <div class="p-4 pt-0 flex items-center justify-between border-t border-slate-100 mt-2 pt-3">
                                    <span class="text-[11px] font-extrabold text-slate-700">👥 +{{ number_format($comm->members_count ?? 0) }} Member</span>
                                    
                                    @php
                                        // Cek apakah user yang sedang login sudah bergabung di komunitas ini
                                        $isMember = $comm->members()->where('user_id', auth()->id())->exists();
                                    @endphp

                                    <div class="flex gap-2 items-center">
                                        <a href="#" class="px-2.5 py-1.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-200">Detail</a>
                                        
                                        @if($isMember)
                                            {{-- Jika sudah bergabung, tampilkan tombol status non-aktif --}}
                                            <button type="button" disabled class="px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-xl text-xs font-bold cursor-not-allowed">
                                                ✓ Sudah Bergabung
                                            </button>
                                        @else
                                            {{-- Jika belum, tampilkan form tombol Gabung --}}
                                            <form action="{{ route('user.communities.join', $comm->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-xl text-xs font-semibold shadow-xs hover:bg-blue-700 cursor-pointer">
                                                    Gabung
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Laravel Pagination Links -->
                <div class="pt-6 border-t border-slate-200/80">
                    {{ $communities->links() }}
                </div>

            </div>
        </div>
    </main>

    {{-- Footer --}}
    @unless(isset($isDashboard) && $isDashboard)
        <footer class="bg-white border-t border-slate-100 py-6 mt-16 text-xs text-slate-500">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="font-extrabold text-slate-900">Bugar.in</span>
                    <span>© 2026 Platform Komunitas Olahraga Indonesia.</span>
                </div>
                <div class="flex items-center gap-6">
                    <a href="#" class="hover:text-blue-600">Privasi</a>
                    <a href="#" class="hover:text-blue-600">Ketentuan</a>
                    <a href="#" class="hover:text-blue-600">Pusat Bantuan</a>
                </div>
            </div>
        </footer>
    @endunless

</body>
</html>