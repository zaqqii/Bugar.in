@php
    $authUser = auth()->user();
    $userInitials = collect(explode(' ', $authUser?->name ?? 'U'))
        ->map(fn($w) => strtoupper(substr($w, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

<header class="admin-header">
    <div class="admin-header__left">
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
        <button class="admin-header__notification" aria-label="Notifications">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <span class="admin-header__notification-dot"></span>
        </button>

        <a href="#" class="btn btn--primary btn--sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Buat Event
        </a>

        <div class="admin-header__profile" role="button" title="{{ $authUser?->name }}" aria-haspopup="true"
            aria-expanded="false">
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
        </div>

        {{-- Tombol Logout Host --}}
        <form method="POST" action="{{ route('penyedia-komunitas.logout') }}" class="m-0">
            @csrf
            <button type="submit" id="btn-logout-penyedia"
                class="flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 text-xs font-semibold hover:border-red-200 hover:text-red-600 hover:bg-red-50 transition-all duration-200 cursor-pointer shadow-sm"
                title="Keluar dari Portal Host">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span class="hidden sm:inline">Keluar</span>
            </button>
        </form>
    </div>
</header>