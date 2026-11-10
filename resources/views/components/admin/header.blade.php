@php
    $authUser = auth()->user();
    $adminInitials = collect(explode(' ', $authUser?->name ?? 'A'))
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

<header class="sticky top-0 z-30 flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8 bg-slate-900/90 backdrop-blur-md border-b border-slate-800 text-white">
    <div class="flex items-center gap-3 sm:gap-4">
        {{-- Mobile Hamburger Button --}}
        <button type="button"
                @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors focus:outline-none"
                aria-label="Buka Menu Navigasi">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        {{-- Title / Breadcrumb --}}
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="text-xs sm:text-sm font-bold text-white tracking-tight">Console Ops</span>
            <span class="text-slate-600 hidden sm:inline">&bull;</span>
            <span class="text-xs text-slate-400 font-medium hidden sm:inline">Pusat Pengawasan Bugar.in</span>
        </div>
    </div>

    {{-- Actions & Profile --}}
    <div class="flex items-center gap-3">
        {{-- Link Cepat ke Landing / Public Page --}}
        <a href="{{ route('landing') }}"
           target="_blank"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 transition-all shadow-2xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            <span class="hidden sm:inline">Buka Portal Pengguna</span>
        </a>

        {{-- Admin User Badge --}}
        <div class="flex items-center gap-2.5 pl-3 border-l border-slate-800">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white font-extrabold text-xs shadow-xs">
                {{ $adminInitials }}
            </div>
            <div class="hidden md:block text-left">
                <div class="text-xs font-bold text-slate-200 leading-tight">
                    {{ $authUser->name ?? 'Admin Ops' }}
                </div>
                <span class="text-[10px] text-slate-400 block font-mono">Role: Administrator</span>
            </div>
        </div>

        {{-- Logout Icon Button --}}
        <form method="POST" action="{{ route('admin.logout') }}" class="inline">
            @csrf
            <button type="submit"
                    class="p-2 rounded-xl text-slate-400 hover:text-red-400 hover:bg-red-500/10 border border-transparent hover:border-red-500/20 transition-all"
                    title="Keluar dari Console">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                </svg>
            </button>
        </form>
    </div>
</header>

