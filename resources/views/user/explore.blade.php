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
<body class="min-h-screen text-slate-800 antialiased flex flex-col justify-between" x-data="{ searchQuery: '', selectedCity: '' }" x-cloak>

    <!-- 1. NAVBAR UTAMA -->
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

                <!-- Right Action Bar (Tanpa tombol Buat Event) -->
                <div class="flex items-center gap-4">
                    <div class="hidden md:block relative w-64">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                        <input type="text" placeholder="Cari event, komunitas, atlet..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                    <button class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 relative">
                        <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-rose-500"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </button>
                    <!-- Indikator Akun User Biasa -->
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center text-xs">
                            R
                        </div>
                        <span class="hidden sm:inline text-xs font-bold text-slate-700">reva</span>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- 2. MAIN CONTENT -->
    <!-- 2. MAIN CONTENT -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full relative">
        
        <!-- Background Gradient Glow (Dibuat Lebih Besar & Biru Tua Lebih Pekat) -->
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
                    🔥 184 Sesi Aktif Pekan Ini
                </div>
            </div>

            <!-- Global Search & Sorting Bar -->
            <div class="mt-6 flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-grow w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <input type="text" x-model="searchQuery" placeholder="Cari nama komunitas, cabang olahraga, atau area (misal: Senayan, Padel, Lari)..." class="w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200/90 rounded-2xl text-xs shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <div class="bg-white border border-slate-200/90 px-4 py-3.5 rounded-2xl text-xs font-semibold text-slate-700 flex items-center gap-2 shadow-xs whitespace-nowrap cursor-pointer hover:bg-slate-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707v4.172a1 1 0 01-.553.894l-4 2A1 1 0 017 20.172v-8.172a1 1 0 00-.293-.707L.293 7.293A1 1 0 010 6.586V4z"/></svg>
                        <span>URUTKAN: Paling Populer</span>
                    </div>
                </div>
            </div>
        </div>

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
                            <label class="flex items-center justify-between text-slate-600 cursor-pointer"><span class="flex items-center gap-2"><input type="checkbox" class="rounded text-blue-600 w-4 h-4"> Lari & Marathon</span><span class="text-slate-400">14</span></label>
                            <label class="flex items-center justify-between text-slate-600 cursor-pointer"><span class="flex items-center gap-2"><input type="checkbox" class="rounded text-blue-600 w-4 h-4"> Padel & Tenis</span><span class="text-slate-400">8</span></label>
                            <label class="flex items-center justify-between text-slate-600 cursor-pointer"><span class="flex items-center gap-2"><input type="checkbox" class="rounded text-blue-600 w-4 h-4"> Badminton</span><span class="text-slate-400">11</span></label>
                            <label class="flex items-center justify-between text-slate-600 cursor-pointer"><span class="flex items-center gap-2"><input type="checkbox" class="rounded text-blue-600 w-4 h-4"> Sepeda & Roadbike</span><span class="text-slate-400">9</span></label>
                            <label class="flex items-center justify-between text-slate-600 cursor-pointer"><span class="flex items-center gap-2"><input type="checkbox" class="rounded text-blue-600 w-4 h-4"> Futsal & Mini Soccer</span><span class="text-slate-400">16</span></label>
                            <label class="flex items-center justify-between text-slate-600 cursor-pointer"><span class="flex items-center gap-2"><input type="checkbox" class="rounded text-blue-600 w-4 h-4"> Gym & Calisthenics</span><span class="text-slate-400">6</span></label>
                            <label class="flex items-center justify-between text-slate-600 cursor-pointer"><span class="flex items-center gap-2"><input type="checkbox" class="rounded text-blue-600 w-4 h-4"> Basket</span><span class="text-slate-400">5</span></label>
                        </div>
                    </div>

                    <!-- Kota / Wilayah -->
                    <div class="space-y-3 pt-4 border-t border-slate-100">
                        <label class="font-extrabold text-slate-800 uppercase tracking-wider block text-[11px]">Kota / Wilayah</label>
                        <select class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            <option>Semua Wilayah</option>
                            <option>Jakarta Selatan</option>
                            <option>Jakarta Pusat</option>
                            <option>Jakarta Barat</option>
                            <option>Bandung</option>
                        </select>
                    </div>

                    <!-- Hari Kegiatan -->
                    <div class="space-y-3 pt-4 border-t border-slate-100">
                        <label class="font-extrabold text-slate-800 uppercase tracking-wider block text-[11px]">Hari Kegiatan</label>
                        <div class="flex flex-wrap gap-1.5 text-xs">
                            <button class="px-3 py-1.5 rounded-lg bg-blue-600 text-white font-semibold">Bebas</button>
                            <button class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200">Weekend Only</button>
                            <button class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200">Weekday Malam</button>
                        </div>
                    </div>

                    <!-- Vibe & Kemahiran -->
                    <div class="space-y-3 pt-4 border-t border-slate-100">
                        <label class="font-extrabold text-slate-800 uppercase tracking-wider block text-[11px]">Vibe & Kemahiran</label>
                        <div class="space-y-2 text-xs text-slate-600">
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="vibe" class="text-blue-600" checked> Semua Level</label>
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="vibe" class="text-blue-600"> Fun & Santai</label>
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="vibe" class="text-blue-600"> Ramah Pemula (Beginner)</label>
                            <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="vibe" class="text-blue-600"> Kompetitif / Semi-Pro</label>
                        </div>
                    </div>

                    <!-- Tipe Kontribusi -->
                    <div class="space-y-3 pt-4 border-t border-slate-100">
                        <label class="font-extrabold text-slate-800 uppercase tracking-wider block text-[11px]">Tipe Kontribusi</label>
                        <div class="space-y-2 text-xs text-slate-600">
                            <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="rounded text-blue-600"> Gratis / Kas Rutin Saja</label>
                            <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="rounded text-blue-600"> Berbayar (Sewa Lapangan/Coach)</label>
                        </div>
                    </div>

                    <button class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 transition-all">
                        ✓ Terapkan Filter
                    </button>
                </div>
            </aside>

            <!-- 2. CARDS GRID KOMUNITAS (BANYAK DAN LENGKAP) -->
            <div class="lg:col-span-3 space-y-6">
                
                <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                    <span class="text-xs font-bold text-slate-700">Menampilkan 24 Komunitas Olahraga di Sekitarmu</span>
                    <div class="flex items-center gap-1">
                        <button class="p-1.5 rounded-lg bg-blue-50 text-blue-600"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7"/></svg></button>
                        <button class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-50"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg></button>
                    </div>
                </div>

                <!-- Grid Kartu Komunitas -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- Card 1 -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="relative h-36 bg-slate-100">
                            <img src="https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=600&q=80" alt="Run" class="w-full h-full object-cover">
                            <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold">Lari & Track</span>
                            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-emerald-500 text-white text-[10px] font-bold">Open Slot</span>
                            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-[11px] font-medium bg-slate-900/60 backdrop-blur-xs px-2.5 py-1 rounded-lg">
                                <span>📍 Senayan & GBK, Jaksel</span>
                            </div>
                        </div>
                        <div class="p-4 space-y-2">
                            <h3 class="font-bold text-slate-900 text-sm">South Jakarta Night Runners</h3>
                            <p class="text-slate-500 text-xs line-clamp-2">Lari santai melepas penat sepulang kantor bersama ratusan penggiat urban...</p>
                            <div class="pt-3 border-t border-slate-100 space-y-1 text-[11px] text-slate-600">
                                <div class="flex justify-between"><span>📅 Rutin:</span><span class="font-semibold">Sel & Kam • 19:30 WIB</span></div>
                                <div class="flex justify-between"><span>⭐ Level:</span><span class="font-semibold text-blue-600">Semua Level (Beginner Friendly)</span></div>
                            </div>
                        </div>
                        <div class="p-4 pt-0 flex items-center justify-between border-t border-slate-100 mt-2 pt-3">
                            <span class="text-[11px] font-extrabold text-slate-700">👥 +2,150 Atlet</span>
                            <div class="flex gap-2">
                                <a href="#" class="px-2.5 py-1.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-200">Detail</a>
                                <a href="#" class="px-3 py-1.5 bg-blue-600 text-white rounded-xl text-xs font-semibold shadow-xs hover:bg-blue-700">Gabung</a>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="relative h-36 bg-slate-100">
                            <img src="https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=600&q=80" alt="Padel" class="w-full h-full object-cover">
                            <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold">Padel Tennis</span>
                            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-amber-500 text-white text-[10px] font-bold">Sisa 4 Slot</span>
                            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-[11px] font-medium bg-slate-900/60 backdrop-blur-xs px-2.5 py-1 rounded-lg">
                                <span>📍 TB Simatupang, Jaksel</span>
                            </div>
                        </div>
                        <div class="p-4 space-y-2">
                            <h3 class="font-bold text-slate-900 text-sm">Jakarta Padel Masters</h3>
                            <p class="text-slate-500 text-xs line-clamp-2">Komunitas padel intensif untuk match doubles mingguan. Sesi warm-up teknik...</p>
                            <div class="pt-3 border-t border-slate-100 space-y-1 text-[11px] text-slate-600">
                                <div class="flex justify-between"><span>📅 Rutin:</span><span class="font-semibold">Sabtu • 08:00 - 11:00</span></div>
                                <div class="flex justify-between"><span>⭐ Level:</span><span class="font-semibold text-blue-600">Pemula - Menengah</span></div>
                            </div>
                        </div>
                        <div class="p-4 pt-0 flex items-center justify-between border-t border-slate-100 mt-2 pt-3">
                            <span class="text-[11px] font-extrabold text-slate-700">👥 +480 Member</span>
                            <div class="flex gap-2">
                                <a href="#" class="px-2.5 py-1.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-200">Detail</a>
                                <a href="#" class="px-3 py-1.5 bg-blue-600 text-white rounded-xl text-xs font-semibold shadow-xs hover:bg-blue-700">Gabung</a>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="relative h-36 bg-slate-100">
                            <img src="https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=600&q=80" alt="Badminton" class="w-full h-full object-cover">
                            <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold">Bulutangkis</span>
                            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-emerald-500 text-white text-[10px] font-bold">Open Slot</span>
                            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-[11px] font-medium bg-slate-900/60 backdrop-blur-xs px-2.5 py-1 rounded-lg">
                                <span>📍 Tebet, Jaksel</span>
                            </div>
                        </div>
                        <div class="p-4 space-y-2">
                            <h3 class="font-bold text-slate-900 text-sm">Tebet Badminton Club</h3>
                            <p class="text-slate-500 text-xs line-clamp-2">Sparing seru 3 lapangan karpet vinyl standar PBSI. Sistem rolling rally point...</p>
                            <div class="pt-3 border-t border-slate-100 space-y-1 text-[11px] text-slate-600">
                                <div class="flex justify-between"><span>📅 Rutin:</span><span class="font-semibold">Rabu • 20:00 - 23:00</span></div>
                                <div class="flex justify-between"><span>⭐ Level:</span><span class="font-semibold text-blue-600">Pemula - Lanjutan</span></div>
                            </div>
                        </div>
                        <div class="p-4 pt-0 flex items-center justify-between border-t border-slate-100 mt-2 pt-3">
                            <span class="text-[11px] font-extrabold text-slate-700">👥 +890 Member</span>
                            <div class="flex gap-2">
                                <a href="#" class="px-2.5 py-1.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-200">Detail</a>
                                <a href="#" class="px-3 py-1.5 bg-blue-600 text-white rounded-xl text-xs font-semibold shadow-xs hover:bg-blue-700">Gabung</a>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4 -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="relative h-36 bg-slate-100">
                            <img src="https://images.unsplash.com/photo-1517649763962-0c623266d102?auto=format&fit=crop&w=600&q=80" alt="Roadbike" class="w-full h-full object-cover">
                            <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold">Roadbike & Sepeda</span>
                            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-blue-500 text-white text-[10px] font-bold">Reguler</span>
                            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-[11px] font-medium bg-slate-900/60 backdrop-blur-xs px-2.5 py-1 rounded-lg">
                                <span>📍 SCBD Loop & Sudirman</span>
                            </div>
                        </div>
                        <div class="p-4 space-y-2">
                            <h3 class="font-bold text-slate-900 text-sm">Loopies Cycling Club</h3>
                            <p class="text-slate-500 text-xs line-clamp-2">Morning ride teratur di aspal mulus Sudirman-Thamrin. Terdapat grup tempo...</p>
                            <div class="pt-3 border-t border-slate-100 space-y-1 text-[11px] text-slate-600">
                                <div class="flex justify-between"><span>📅 Rutin:</span><span class="font-semibold">Sab & Min • 06:00 WIB</span></div>
                                <div class="flex justify-between"><span>⭐ Level:</span><span class="font-semibold text-blue-600">Menengah - Advance</span></div>
                            </div>
                        </div>
                        <div class="p-4 pt-0 flex items-center justify-between border-t border-slate-100 mt-2 pt-3">
                            <span class="text-[11px] font-extrabold text-slate-700">👥 +1,320 Member</span>
                            <div class="flex gap-2">
                                <a href="#" class="px-2.5 py-1.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-200">Detail</a>
                                <a href="#" class="px-3 py-1.5 bg-blue-600 text-white rounded-xl text-xs font-semibold shadow-xs hover:bg-blue-700">Gabung</a>
                            </div>
                        </div>
                    </div>

                    <!-- Card 5 -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="relative h-36 bg-slate-100">
                            <img src="https://images.unsplash.com/photo-1526232761682-d26e03ac148e?auto=format&fit=crop&w=600&q=80" alt="Futsal" class="w-full h-full object-cover">
                            <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold">Futsal</span>
                            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-amber-500 text-white text-[10px] font-bold">Sisa 2 Tim</span>
                            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-[11px] font-medium bg-slate-900/60 backdrop-blur-xs px-2.5 py-1 rounded-lg">
                                <span>📍 Cilandak Sport Center</span>
                            </div>
                        </div>
                        <div class="p-4 space-y-2">
                            <h3 class="font-bold text-slate-900 text-sm">Cilandak Futsal & Fun Match</h3>
                            <p class="text-slate-500 text-xs line-clamp-2">Fun match 5v5 rotasi 3 tim. Mengutamakan sportivitas tanpa tackling...</p>
                            <div class="pt-3 border-t border-slate-100 space-y-1 text-[11px] text-slate-600">
                                <div class="flex justify-between"><span>📅 Rutin:</span><span class="font-semibold">Jumat • 19:00 - 21:00</span></div>
                                <div class="flex justify-between"><span>⭐ Level:</span><span class="font-semibold text-blue-600">Fun & Santai</span></div>
                            </div>
                        </div>
                        <div class="p-4 pt-0 flex items-center justify-between border-t border-slate-100 mt-2 pt-3">
                            <span class="text-[11px] font-extrabold text-slate-700">👥 +640 Member</span>
                            <div class="flex gap-2">
                                <a href="#" class="px-2.5 py-1.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-200">Detail</a>
                                <a href="#" class="px-3 py-1.5 bg-blue-600 text-white rounded-xl text-xs font-semibold shadow-xs hover:bg-blue-700">Gabung</a>
                            </div>
                        </div>
                    </div>

                    <!-- Card 6 -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between hover:border-blue-300 hover:shadow-md transition-all">
                        <div class="relative h-36 bg-slate-100">
                            <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=600&q=80" alt="Calisthenics" class="w-full h-full object-cover">
                            <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-bold">Fitness & Calisthenics</span>
                            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-emerald-500 text-white text-[10px] font-bold">Semua Boleh Ikut</span>
                            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-[11px] font-medium bg-slate-900/60 backdrop-blur-xs px-2.5 py-1 rounded-lg">
                                <span>📍 Taman Suropati, Jakpus</span>
                            </div>
                        </div>
                        <div class="p-4 space-y-2">
                            <h3 class="font-bold text-slate-900 text-sm">Menteng Calisthenics Movement</h3>
                            <p class="text-slate-500 text-xs line-clamp-2">Latihan beban tubuh (bodyweight training), belajar teknik pull-up...</p>
                            <div class="pt-3 border-t border-slate-100 space-y-1 text-[11px] text-slate-600">
                                <div class="flex justify-between"><span>📅 Rutin:</span><span class="font-semibold">Minggu • 07:00 WIB</span></div>
                                <div class="flex justify-between"><span>⭐ Level:</span><span class="font-semibold text-blue-600">Pemula Sangat Disambut</span></div>
                            </div>
                        </div>
                        <div class="p-4 pt-0 flex items-center justify-between border-t border-slate-100 mt-2 pt-3">
                            <span class="text-[11px] font-extrabold text-slate-700">👥 +520 Member</span>
                            <div class="flex gap-2">
                                <a href="#" class="px-2.5 py-1.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-200">Detail</a>
                                <a href="#" class="px-3 py-1.5 bg-blue-600 text-white rounded-xl text-xs font-semibold shadow-xs hover:bg-blue-700">Gabung</a>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Pagination Bar -->
                <div class="flex flex-col sm:flex-row items-center justify-between pt-6 border-t border-slate-200/80 gap-4">
                    <span class="text-xs text-slate-500 font-medium">Menampilkan 1 - 6 dari 24 Komunitas</span>
                    <div class="flex items-center gap-1.5">
                        <button class="w-9 h-9 rounded-xl bg-blue-600 text-white text-xs font-bold shadow-sm">1</button>
                        <button class="w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50">2</button>
                        <button class="w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50">3</button>
                        <button class="w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50">4</button>
                        <button class="w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50">&rarr;</button>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- 3. FOOTER -->
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

</body>
</html>