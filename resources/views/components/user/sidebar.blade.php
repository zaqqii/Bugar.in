<aside id="user-sidebar"
    class="sidebar-glass fixed top-0 bottom-0 left-0 z-40 flex flex-col overflow-y-auto overflow-x-hidden custom-scrollbar transition-all duration-300 ease-in-out w-[260px]"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" aria-label="Sidebar Navigasi Pengguna">

    {{-- Subtle Decorative Blurred Color Glows in Sidebar --}}
    <div class="absolute -top-10 -left-10 w-44 h-44 rounded-full bg-gradient-to-br from-rose-400/25 to-red-300/15 blur-2xl pointer-events-none"
        aria-hidden="true"></div>
    <div class="absolute top-1/2 -right-12 w-36 h-36 rounded-full bg-gradient-to-l from-rose-300/20 to-pink-200/10 blur-2xl pointer-events-none"
        aria-hidden="true"></div>
    <div class="absolute -bottom-10 -left-8 w-40 h-40 rounded-full bg-gradient-to-tr from-red-300/20 to-rose-200/15 blur-2xl pointer-events-none"
        aria-hidden="true"></div>

    {{-- Brand Header (Glass with subtle tint) --}}
    <div
        class="relative z-10 flex items-center justify-between px-6 py-5 border-b border-rose-200/70 bg-white/70 backdrop-blur-md">
        <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
            <div
                class="w-9 h-9 rounded-xl bg-gradient-to-br from-rose-500 to-red-500 flex items-center justify-center shadow-sm shadow-rose-300 group-hover:scale-105 transition-transform duration-200 relative">
                {{-- Mini blur under logo --}}
                <div
                    class="absolute inset-0 rounded-xl bg-rose-500 blur-sm opacity-40 group-hover:opacity-70 transition-opacity">
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                    class="w-4 h-4 text-white relative z-10">
                    <path
                        d="M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9l1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z" />
                </svg>
            </div>
            <div>
                <span class="font-extrabold text-lg text-stone-900 tracking-tight flex items-center gap-1.5">
                    Bugar.in
                    <span
                        class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-rose-100/80 text-rose-700 border border-rose-200/80">User</span>
                </span>
                <div class="text-[11px] font-medium text-stone-500">Ruang Pengguna</div>
            </div>
        </a>

        {{-- Mobile close button --}}
        <button @click="sidebarOpen = false"
            class="lg:hidden p-1.5 rounded-lg text-stone-400 hover:text-stone-700 hover:bg-rose-50 transition-colors"
            aria-label="Tutup Sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Nav Items --}}
    <nav class="relative z-10 flex-1 py-4 px-3.5 flex flex-col gap-1">

        {{-- Section 1: Eksplorasi --}}
        <div class="px-3 pt-2 pb-1 text-[11px] font-bold text-stone-400 uppercase tracking-wider">
            Eksplorasi Olahraga
        </div>

        {{-- 1. Eksplorasi Komunitas & Kegiatan Olahraga --}}
        <button type="button" @click="setSection('komunitas')" :class="activeSection === 'komunitas'
               ? 'bg-gradient-to-r from-rose-100/90 via-rose-50/80 to-white/60 text-rose-700 font-semibold border-l-4 border-rose-500 shadow-xs'
               : 'text-stone-600 hover:bg-white/60 hover:text-rose-600 font-medium'"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all duration-150 group w-full text-left cursor-pointer relative overflow-hidden">
            {{-- Active soft color blur pill --}}
            <div x-show="activeSection === 'komunitas'"
                class="absolute -left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-rose-400/30 blur-md pointer-events-none"
                aria-hidden="true"></div>
            <span class="flex items-center justify-center w-7 h-7 rounded-lg transition-colors relative z-10"
                :class="activeSection === 'komunitas' ? 'bg-rose-200/70 text-rose-700' : 'bg-white/80 text-stone-500 group-hover:bg-rose-100/70 group-hover:text-rose-600 border border-stone-200/60'">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zM7 10a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
            </span>
            <span class="flex-1 leading-snug relative z-10">Komunitas &amp; Kegiatan</span>
        </button>

        {{-- 2. Eksplorasi Event --}}
        <button type="button" @click="setSection('events')" :class="activeSection === 'events'
               ? 'bg-gradient-to-r from-rose-100/90 via-rose-50/80 to-white/60 text-rose-700 font-semibold border-l-4 border-rose-500 shadow-xs'
               : 'text-stone-600 hover:bg-white/60 hover:text-rose-600 font-medium'"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all duration-150 group w-full text-left cursor-pointer relative overflow-hidden">
            <div x-show="activeSection === 'events'"
                class="absolute -left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-rose-400/30 blur-md pointer-events-none"
                aria-hidden="true"></div>
            <span class="flex items-center justify-center w-7 h-7 rounded-lg transition-colors relative z-10"
                :class="activeSection === 'events' ? 'bg-rose-200/70 text-rose-700' : 'bg-white/80 text-stone-500 group-hover:bg-rose-100/70 group-hover:text-rose-600 border border-stone-200/60'">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </span>
            <span class="flex-1 leading-snug relative z-10">Eksplorasi Event</span>
        </button>

        {{-- 3. Kalender Jadwal & Agenda Olahraga --}}
        <button type="button" @click="setSection('agenda')" :class="activeSection === 'agenda'
               ? 'bg-gradient-to-r from-rose-100/90 via-rose-50/80 to-white/60 text-rose-700 font-semibold border-l-4 border-rose-500 shadow-xs'
               : 'text-stone-600 hover:bg-white/60 hover:text-rose-600 font-medium'"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all duration-150 group w-full text-left cursor-pointer relative overflow-hidden">
            <div x-show="activeSection === 'agenda'"
                class="absolute -left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-rose-400/30 blur-md pointer-events-none"
                aria-hidden="true"></div>
            <span class="flex items-center justify-center w-7 h-7 rounded-lg transition-colors relative z-10"
                :class="activeSection === 'agenda' ? 'bg-rose-200/70 text-rose-700' : 'bg-white/80 text-stone-500 group-hover:bg-rose-100/70 group-hover:text-rose-600 border border-stone-200/60'">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7V3m8 4V3M4 9h16M5 5h14a1 1 0 011 1v13H4V6a1 1 0 011-1zm3 8h3m-3 3h6" />
                </svg>
            </span>
            <span class="flex-1 leading-snug relative z-10">Jadwal &amp; Agenda</span>
        </button>

        {{-- 4. Leaderboard User Overall --}}
        <button type="button" @click="setSection('leaderboard')" :class="activeSection === 'leaderboard'
               ? 'bg-gradient-to-r from-rose-100/90 via-rose-50/80 to-white/60 text-rose-700 font-semibold border-l-4 border-rose-500 shadow-xs'
               : 'text-stone-600 hover:bg-white/60 hover:text-rose-600 font-medium'"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all duration-150 group w-full text-left cursor-pointer relative overflow-hidden">
            <div x-show="activeSection === 'leaderboard'"
                class="absolute -left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-rose-400/30 blur-md pointer-events-none"
                aria-hidden="true"></div>
            <span class="flex items-center justify-center w-7 h-7 rounded-lg transition-colors relative z-10"
                :class="activeSection === 'leaderboard' ? 'bg-rose-200/70 text-rose-700' : 'bg-white/80 text-stone-500 group-hover:bg-rose-100/70 group-hover:text-rose-600 border border-stone-200/60'">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 21h8m-4-4v4m-5-16h10l2 3-7 8-7-8 2-3zm0 0 2 6m8-6-2 6m-3-6v8" />
                </svg>
            </span>
            <span class="flex-1 leading-snug relative z-10">Leaderboard Overall</span>
        </button>

        {{-- Section 2: Fitur Personal & Kesehatan --}}
        <div class="px-3 pt-4 pb-1 text-[11px] font-bold text-stone-400 uppercase tracking-wider">
            Kebugaran &amp; Aktivitas
        </div>

        {{-- 3. Penghitung BMI --}}
        <button type="button" @click="setSection('bmi')" :class="activeSection === 'bmi'
               ? 'bg-gradient-to-r from-rose-100/90 via-rose-50/80 to-white/60 text-rose-700 font-semibold border-l-4 border-rose-500 shadow-xs'
               : 'text-stone-600 hover:bg-white/60 hover:text-rose-600 font-medium'"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all duration-150 group w-full text-left cursor-pointer relative overflow-hidden">
            <div x-show="activeSection === 'bmi'"
                class="absolute -left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-rose-400/30 blur-md pointer-events-none"
                aria-hidden="true"></div>
            <span class="flex items-center justify-center w-7 h-7 rounded-lg transition-colors relative z-10"
                :class="activeSection === 'bmi' ? 'bg-rose-200/70 text-rose-700' : 'bg-white/80 text-stone-500 group-hover:bg-rose-100/70 group-hover:text-rose-600 border border-stone-200/60'">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </span>
            <span class="flex-1 leading-snug relative z-10">Penghitung BMI</span>
        </button>

        {{-- 4. Streak User --}}
        <button type="button" @click="setSection('streak')" :class="activeSection === 'streak'
               ? 'bg-gradient-to-r from-rose-100/90 via-rose-50/80 to-white/60 text-rose-700 font-semibold border-l-4 border-rose-500 shadow-xs'
               : 'text-stone-600 hover:bg-white/60 hover:text-rose-600 font-medium'"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all duration-150 group w-full text-left cursor-pointer relative overflow-hidden">
            <div x-show="activeSection === 'streak'"
                class="absolute -left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-rose-400/30 blur-md pointer-events-none"
                aria-hidden="true"></div>
            <span class="flex items-center justify-center w-7 h-7 rounded-lg transition-colors relative z-10"
                :class="activeSection === 'streak' ? 'bg-rose-200/70 text-rose-700' : 'bg-white/80 text-stone-500 group-hover:bg-rose-100/70 group-hover:text-rose-600 border border-stone-200/60'">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                </svg>
            </span>
            <span class="flex-1 leading-snug relative z-10">Streak User</span>
        </button>

        {{-- 5. Menu Edukasi --}}
        <button type="button" @click="setSection('edukasi')" :class="activeSection === 'edukasi'
               ? 'bg-gradient-to-r from-rose-100/90 via-rose-50/80 to-white/60 text-rose-700 font-semibold border-l-4 border-rose-500 shadow-xs'
               : 'text-stone-600 hover:bg-white/60 hover:text-rose-600 font-medium'"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all duration-150 group w-full text-left cursor-pointer relative overflow-hidden">
            <div x-show="activeSection === 'edukasi'"
                class="absolute -left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-rose-400/30 blur-md pointer-events-none"
                aria-hidden="true"></div>
            <span class="flex items-center justify-center w-7 h-7 rounded-lg transition-colors relative z-10"
                :class="activeSection === 'edukasi' ? 'bg-rose-200/70 text-rose-700' : 'bg-white/80 text-stone-500 group-hover:bg-rose-100/70 group-hover:text-rose-600 border border-stone-200/60'">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </span>
            <span class="flex-1 leading-snug relative z-10">Menu Edukasi</span>
        </button>

        {{-- 6. Riwayat Aktivitas User --}}
        <button type="button" @click="setSection('riwayat')" :class="activeSection === 'riwayat'
               ? 'bg-gradient-to-r from-rose-100/90 via-rose-50/80 to-white/60 text-rose-700 font-semibold border-l-4 border-rose-500 shadow-xs'
               : 'text-stone-600 hover:bg-white/60 hover:text-rose-600 font-medium'"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-all duration-150 group w-full text-left cursor-pointer relative overflow-hidden">
            <div x-show="activeSection === 'riwayat'"
                class="absolute -left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-rose-400/30 blur-md pointer-events-none"
                aria-hidden="true"></div>
            <span class="flex items-center justify-center w-7 h-7 rounded-lg transition-colors relative z-10"
                :class="activeSection === 'riwayat' ? 'bg-rose-200/70 text-rose-700' : 'bg-white/80 text-stone-500 group-hover:bg-rose-100/70 group-hover:text-rose-600 border border-stone-200/60'">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <span class="flex-1 leading-snug relative z-10">Riwayat Aktivitas</span>
        </button>
    </nav>

    {{-- Footer: Platform Public Link with soft tinted gradient --}}
    <div
        class="relative z-10 px-5 py-4 border-t border-rose-200/70 bg-gradient-to-r from-white/70 via-rose-50/60 to-red-50/40 backdrop-blur-md mt-auto">
        <a href="{{ route('landing') }}"
            class="flex items-center justify-between text-xs text-stone-600 hover:text-rose-600 font-medium transition-colors">
            <span class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-stone-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Beranda
            </span>
            <span class="text-[10px] text-stone-400">&rarr;</span>
        </a>
    </div>
</aside>