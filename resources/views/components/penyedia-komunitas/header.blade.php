@php
    $authUser = auth()->user();
    $userInitials = collect(explode(' ', $authUser?->name ?? 'U'))
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

<header class="admin-header">
    <div class="admin-header__left flex items-center gap-3">
        {{-- Mobile Hamburger Toggle Button --}}
        <button type="button"
                @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors focus:outline-none"
                aria-label="Buka Menu Navigasi">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        {{-- Search Input --}}
        <div class="admin-header__search">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input type="text" placeholder="Cari data internal host..." aria-label="Search">
        </div>
    </div>

    <div class="admin-header__actions">
        {{-- Notification Bell --}}
        <button class="admin-header__notification" aria-label="Notifications">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <span class="admin-header__notification-dot"></span>
        </button>

        {{-- Tombol Buat Event --}}
        <a href="{{ route('penyedia-komunitas.events.create') }}" class="btn btn--primary btn--sm inline-flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Buat Event</span>
        </a>

        {{-- Avatar Profil (Mengarah ke Halaman Akun) --}}
        <a href="{{ route('penyedia-komunitas.akun.index') }}"
           class="admin-header__profile block hover:opacity-90 transition-opacity"
           title="Pengaturan Akun ({{ $authUser?->name }})"
           aria-label="Pengaturan Akun">
            @if($authUser?->avatar)
                <img src="{{ asset('storage/' . $authUser->avatar) }}" alt="{{ $authUser->name }}"
                    class="admin-header__avatar">
            @else
                <span class="flex items-center justify-center w-full h-full
                                 bg-gradient-to-br from-primary-500 to-primary-700
                                 text-white text-xs font-bold rounded-full">
                    {{ $userInitials }}
                </span>
            @endif
        </a>
    </div>
</header>