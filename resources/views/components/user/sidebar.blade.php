<aside id="user-sidebar"
    class="sidebar-glass fixed top-0 bottom-0 left-0 z-40 flex flex-col overflow-y-auto overflow-x-hidden custom-scrollbar transition-all duration-300 ease-in-out w-[260px]"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    aria-label="Sidebar Navigasi Pengguna">

    {{-- Ambient glow biru ringan di sidebar --}}
    <div class="absolute -top-8 -left-8 w-40 h-40 rounded-full bg-gradient-to-br from-blue-200/30 to-cyan-100/20 blur-2xl pointer-events-none" aria-hidden="true"></div>
    <div class="absolute bottom-1/3 -right-8 w-32 h-32 rounded-full bg-gradient-to-l from-blue-100/25 to-sky-50/15 blur-2xl pointer-events-none" aria-hidden="true"></div>

    {{-- Brand Header --}}
    <div class="relative z-10 flex items-center justify-between px-5 py-4 border-b border-slate-200/80">
        <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center shadow-sm shadow-blue-300/40 group-hover:scale-105 transition-transform duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-white">
                    <path d="M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9l1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z"/>
                </svg>
            </div>
            <div>
                <span class="font-extrabold text-lg text-slate-900 tracking-tight leading-none block">
                    Bugar<span class="text-blue-600">.in</span>
                </span>
                <span class="text-[11px] font-medium text-slate-400 block mt-0.5">Ruang Pengguna</span>
            </div>
        </a>

        {{-- Mobile close --}}
        <button @click="sidebarOpen = false"
                class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-blue-50 transition-colors"
                aria-label="Tutup Sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="relative z-10 flex-1 py-4 px-3 flex flex-col gap-0.5">

        {{-- Section: Eksplorasi --}}
        <div class="px-3 pt-2 pb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
            Eksplorasi Olahraga
        </div>

        {{-- 1. Komunitas & Kegiatan --}}
        <button type="button"
           @click="setSection('komunitas')"
           :class="activeSection === 'komunitas' ? 'nav-item--active' : ''"
           class="nav-item w-full"
           id="nav-komunitas">
            <span class="nav-icon" :class="activeSection === 'komunitas' ? 'nav-icon--active' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM7 10a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                </svg>
            </span>
            <span class="flex-1 leading-snug">Komunitas &amp; Kegiatan</span>
        </button>

        {{-- 2. Eksplorasi Event --}}
        <button type="button"
           @click="setSection('events')"
           :class="activeSection === 'events' ? 'nav-item--active' : ''"
           class="nav-item w-full"
           id="nav-events">
            <span class="nav-icon" :class="activeSection === 'events' ? 'nav-icon--active' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </span>
            <span class="flex-1 leading-snug">Eksplorasi Event</span>
            {{-- Dot indicator jika ada event --}}
            <span class="w-2 h-2 rounded-full bg-orange-400 flex-shrink-0" aria-label="Ada event mendatang"></span>
        </button>

        {{-- 3. Jadwal & Agenda --}}
        <button type="button"
           @click="setSection('agenda')"
           :class="activeSection === 'agenda' ? 'nav-item--active' : ''"
           class="nav-item w-full"
           id="nav-agenda">
            <span class="nav-icon" :class="activeSection === 'agenda' ? 'nav-icon--active' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 9h16M5 5h14a1 1 0 011 1v13H4V6a1 1 0 011-1zm3 8h3m-3 3h6"/>
                </svg>
            </span>
            <span class="flex-1 leading-snug">Jadwal &amp; Agenda</span>
        </button>

        {{-- 4. Leaderboard --}}
        <button type="button"
           @click="setSection('leaderboard')"
           :class="activeSection === 'leaderboard' ? 'nav-item--active' : ''"
           class="nav-item w-full"
           id="nav-leaderboard">
            <span class="nav-icon" :class="activeSection === 'leaderboard' ? 'nav-icon--active' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </span>
            <span class="flex-1 leading-snug">Leaderboard Overall</span>
        </button>

        {{-- Section divider: Kebugaran --}}
        <div class="px-3 pt-5 pb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
            Kebugaran &amp; Aktivitas
        </div>

        {{-- 5. Penghitung BMI --}}
        <button type="button"
           @click="setSection('bmi')"
           :class="activeSection === 'bmi' ? 'nav-item--active' : ''"
           class="nav-item w-full"
           id="nav-bmi">
            <span class="nav-icon" :class="activeSection === 'bmi' ? 'nav-icon--active' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 10h16M10 13h4M10 16h4M6 19h12a1 1 0 001-1V6a1 1 0 00-1-1H6a1 1 0 00-1 1v12a1 1 0 001 1z"/>
                </svg>
            </span>
            <span class="flex-1 leading-snug">Penghitung BMI</span>
        </button>

        {{-- 6. Streak & Poin --}}
        <button type="button"
           @click="setSection('streak')"
           :class="activeSection === 'streak' ? 'nav-item--active' : ''"
           class="nav-item w-full"
           id="nav-streak">
            <span class="nav-icon" :class="activeSection === 'streak' ? 'nav-icon--active' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                </svg>
            </span>
            <span class="flex-1 leading-snug">Streak &amp; Poin Olahraga</span>
        </button>

        {{-- 7. Menu Edukasi --}}
        <button type="button"
           @click="setSection('edukasi')"
           :class="activeSection === 'edukasi' ? 'nav-item--active' : ''"
           class="nav-item w-full"
           id="nav-edukasi">
            <span class="nav-icon" :class="activeSection === 'edukasi' ? 'nav-icon--active' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </span>
            <span class="flex-1 leading-snug">Menu Edukasi</span>
        </button>

        {{-- 8. Riwayat Aktivitas --}}
        <button type="button"
           @click="setSection('riwayat')"
           :class="activeSection === 'riwayat' ? 'nav-item--active' : ''"
           class="nav-item w-full"
           id="nav-riwayat">
            <span class="nav-icon" :class="activeSection === 'riwayat' ? 'nav-icon--active' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <span class="flex-1 leading-snug">Riwayat Aktivitas</span>
        </button>
    </nav>

    {{-- Footer Sidebar --}}
    <div class="relative z-10 px-5 py-4 border-t border-slate-200/70 mt-auto">
        <a href="{{ route('landing') }}" class="flex items-center gap-2.5 text-xs text-slate-500 hover:text-blue-600 font-medium transition-colors group">
            <span class="w-7 h-7 rounded-lg bg-slate-100 group-hover:bg-blue-50 flex items-center justify-center transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </span>
            <span>Kembali ke Beranda</span>
        </a>
    </div>
</aside>
