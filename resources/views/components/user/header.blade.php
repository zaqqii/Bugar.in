@php
    $authUser = auth()->user();
    $initials = collect(explode(' ', $authUser?->name ?? 'User'))
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->take(2)->implode('');
@endphp

<header class="sticky top-0 z-20 flex items-center justify-between gap-6 px-6 sm:px-8 h-20 bg-gradient-to-r from-white/95 via-rose-50/50 to-white/90 backdrop-blur-md border-b border-rose-200/80 shadow-xs transition-all">

    {{-- Left: Sidebar Toggle + Title --}}
    <div class="flex items-center gap-4">
        <button @click="sidebarOpen = !sidebarOpen" id="btn-sidebar-toggle"
            class="flex items-center justify-center w-10 h-10 rounded-xl border border-stone-200/90 bg-white hover:bg-rose-50 text-stone-600 hover:text-rose-600 hover:border-rose-200 transition-all duration-200 cursor-pointer shadow-xs active:scale-95"
            aria-label="Toggle sidebar"
            title="Buka / Tutup Menu">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-base sm:text-lg font-bold text-stone-900 tracking-tight leading-tight">
                    Dashboard Pengguna
                </h1>
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                    Aktif
                </span>
            </div>
            <p class="text-xs text-stone-500 hidden sm:block mt-0.5">
                Ruang aktivitas, eksplorasi komunitas &amp; pantauan kebugaran
            </p>
        </div>
    </div>

    {{-- Right: User Info & Logout Button (Spacious & Clean) --}}
    <div class="flex items-center gap-4">

        {{-- User Profile Pill --}}
        <div class="flex items-center gap-3 pl-2 pr-3.5 py-1.5 rounded-xl bg-white border border-rose-100/80 shadow-xs">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-red-500 flex items-center justify-center text-white font-bold text-xs shadow-xs">
                {{ $initials }}
            </div>
            <div class="text-left hidden sm:block">
                <div class="text-xs font-bold text-stone-800 max-w-[130px] truncate leading-tight">
                    {{ $authUser?->name ?? 'Pengguna Bugar.in' }}
                </div>
                <div class="text-[11px] text-stone-400 font-medium">
                    {{ $authUser?->email ?? 'Member' }}
                </div>
            </div>
        </div>

        {{-- Logout Form --}}
        <form method="POST" action="{{ route('user.logout') }}" class="m-0">
            @csrf
            <button type="submit" id="btn-user-logout"
                class="flex items-center gap-2 px-3.5 py-2 rounded-xl border border-stone-200/90 bg-white text-stone-600 text-xs font-semibold hover:border-rose-200 hover:text-rose-600 hover:bg-rose-50/70 transition-all duration-200 cursor-pointer shadow-xs active:scale-95"
                title="Keluar dari Akun">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</header>
