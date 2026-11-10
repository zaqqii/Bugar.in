@php
    $currentRoute = request()->route()?->getName() ?? '';
    $authUser = auth()->user();
    $adminInitials = collect(explode(' ', $authUser?->name ?? 'A'))
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed top-0 bottom-0 left-0 z-40 w-[270px] bg-slate-900 border-r border-slate-800 flex flex-col justify-between overflow-y-auto transition-transform duration-300 ease-in-out shadow-2xl lg:shadow-none custom-scrollbar"
       aria-label="Admin Navigation Console">

    {{-- Top Section: Brand & Nav Links --}}
    <div class="flex flex-col">
        {{-- Brand / Header Sidebar --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800/80 bg-slate-950/40">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 via-indigo-600 to-cyan-500 flex items-center justify-center text-white shadow-md shadow-blue-500/20 group-hover:scale-105 transition-transform duration-200">
                    <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-extrabold text-base text-white tracking-tight leading-none">Bugar<span class="text-blue-400">.in</span></span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-blue-500/20 text-blue-300 border border-blue-500/30">Console</span>
                    </div>
                    <span class="text-[11px] font-medium text-slate-400 block mt-0.5 tracking-normal">Pusat Tata Kelola Ops</span>
                </div>
            </a>

            {{-- Mobile Close Button --}}
            <button @click="sidebarOpen = false"
                    class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
                    aria-label="Tutup Menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Navigasi Menu Utama --}}
        <nav class="p-4 space-y-6">
            {{-- Kelompok: Utama --}}
            <div>
                <span class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">
                    Operasional Platform
                </span>
                <div class="space-y-1">
                    {{-- 1. Dashboard Overview --}}
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600/20 text-blue-300 border border-blue-500/30 shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                        <span class="{{ request()->routeIs('admin.dashboard') ? 'text-blue-400' : 'text-slate-400' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25a2.25 2.25 0 01-2.25-2.25V6z" />
                            </svg>
                        </span>
                        <span class="flex-1">Ringkasan Sistem</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" title="Sistem Aktif"></span>
                    </a>

                    {{-- 2. Verifikasi Komunitas --}}
                    <a href="{{ route('admin.dashboard') }}#verifikasi"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800/60 transition-all duration-150">
                        <span class="text-amber-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <span class="flex-1">Verifikasi Komunitas</span>
                        @if(isset($stats['pending_verifications']) && $stats['pending_verifications'] > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                {{ $stats['pending_verifications'] }}
                            </span>
                        @endif
                    </a>

                    {{-- 3. Monitoring Komunitas --}}
                    <a href="{{ route('admin.dashboard') }}#komunitas"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800/60 transition-all duration-150">
                        <span class="text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </span>
                        <span class="flex-1">Klub &amp; Komunitas</span>
                    </a>

                    {{-- 4. Pengguna & Role --}}
                    <a href="{{ route('admin.dashboard') }}#pengguna"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800/60 transition-all duration-150">
                        <span class="text-cyan-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </span>
                        <span class="flex-1">Basis Pengguna</span>
                    </a>

                    {{-- 5. Event & Kegiatan --}}
                    <a href="{{ route('admin.dashboard') }}#events"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800/60 transition-all duration-150">
                        <span class="text-orange-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                            </svg>
                        </span>
                        <span class="flex-1">Event &amp; Turnamen</span>
                    </a>
                </div>
            </div>

            {{-- Kelompok: Sistem & Audit --}}
            <div>
                <span class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">
                    Integritas &amp; Keamanan
                </span>
                <div class="space-y-1">
                    <div class="px-3.5 py-2 rounded-xl bg-slate-950/40 border border-slate-800 text-[11px] text-slate-400 space-y-1.5">
                        <div class="flex items-center justify-between text-slate-300 font-semibold">
                            <span>Status Server</span>
                            <span class="text-emerald-400 font-mono">ONLINE</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Framework</span>
                            <span class="text-slate-400 font-mono">Laravel 11</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Lingkungan</span>
                            <span class="text-slate-400 font-mono">{{ app()->environment() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    {{-- Bottom Section: Administrator Account & Logout --}}
    <div class="p-4 border-t border-slate-800/80 bg-slate-950/60">
        <div class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-slate-900 border border-slate-800">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white font-extrabold text-xs shadow-xs flex-shrink-0">
                    {{ $adminInitials }}
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-white truncate leading-tight">
                        {{ $authUser->name ?? 'Administrator' }}
                    </div>
                    <span class="text-[10px] font-mono text-cyan-400 block truncate">
                        {{ $authUser->email ?? 'admin@bugar.in' }}
                    </span>
                </div>
            </div>

            {{-- Tombol Logout --}}
            <form method="POST" action="{{ route('admin.logout') }}" class="flex-shrink-0">
                @csrf
                <button type="submit"
                        class="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors"
                        title="Keluar dari Console Admin">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

