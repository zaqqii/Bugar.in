@php
    $authUser = auth()->user();
    $initials = collect(explode(' ', $authUser?->name ?? 'User'))
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->take(2)->implode('');
    $totalPoints = $authUser?->total_points ?? 0;
@endphp

<header class="glass-nav sticky top-0 z-20 flex items-center justify-between gap-4 px-5 sm:px-8 h-[68px]">

    {{-- Left: Toggle + Page Context --}}
    <div class="flex items-center gap-3.5">
        <button @click="sidebarOpen = !sidebarOpen" id="btn-sidebar-toggle"
            class="flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 bg-white hover:bg-blue-50 text-slate-500 hover:text-blue-600 hover:border-blue-200 transition-all duration-150 cursor-pointer shadow-xs active:scale-95"
            aria-label="Toggle sidebar"
            title="Buka / Tutup Menu">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Brand mark di header (visible saat sidebar tertutup di mobile) --}}
        <div class="lg:hidden flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-white">
                    <path d="M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9l1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z"/>
                </svg>
            </div>
            <span class="font-bold text-sm text-slate-900">Bugar<span class="text-blue-600">.in</span></span>
        </div>

        {{-- Desktop: Section context --}}
        <div class="hidden lg:block">
            <p class="text-[11px] font-medium text-slate-400 leading-none">Ruang aktivitas & kebugaran</p>
        </div>
    </div>

    {{-- Right: Points + User Profile + Logout --}}
    <div class="flex items-center gap-3">

        {{-- Poin Badge --}}
        <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-500" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            <span class="text-xs font-bold text-blue-700">{{ number_format($totalPoints, 0, ',', '.') }} pts</span>
        </div>

        {{-- Active status --}}
        <div class="hidden md:flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-emerald-50 border border-emerald-100">
            <span class="active-dot"></span>
            <span class="text-[11px] font-semibold text-emerald-700">Aktif</span>
        </div>

        {{-- User Profile Pill --}}
        <a href="{{ route('user.profile.edit') }}"
           class="flex items-center gap-2.5 pl-2 pr-3.5 py-1.5 rounded-xl bg-white border border-slate-200 shadow-xs hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-200 group cursor-pointer"
           title="Edit Profil Saya"
           id="header-profile-link">
            <div class="w-8 h-8 rounded-lg overflow-hidden bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center text-white font-bold text-xs shadow-xs flex-shrink-0">
                @if($authUser?->avatar)
                    <img src="{{ \Illuminate\Support\Str::startsWith($authUser->avatar, ['http://', 'https://']) ? $authUser->avatar : \Illuminate\Support\Facades\Storage::url($authUser->avatar) }}" alt="{{ $authUser->name }}" class="w-full h-full object-cover" onerror="this.style.display='none'">
                @else
                    {{ $initials }}
                @endif
            </div>
            <div class="text-left hidden sm:block">
                <div class="text-xs font-bold text-slate-800 max-w-[110px] truncate leading-tight group-hover:text-blue-600 transition-colors">
                    {{ $authUser?->name ?? 'Pengguna Bugar.in' }}
                </div>
                <div class="text-[10px] text-slate-400 font-medium leading-tight truncate max-w-[110px]">
                    {{ '@' . ($authUser?->username ?? \Illuminate\Support\Str::before($authUser?->email ?? 'member', '@')) }}
                </div>
            </div>
        </a>

        {{-- Logout --}}
        <form method="POST" action="{{ route('user.logout') }}" class="m-0">
            @csrf
            <button type="submit" id="btn-user-logout"
                class="flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 bg-white text-slate-500 hover:border-red-200 hover:text-red-500 hover:bg-red-50 transition-all duration-150 cursor-pointer shadow-xs active:scale-95"
                title="Keluar dari Akun">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>
    </div>
</header>
