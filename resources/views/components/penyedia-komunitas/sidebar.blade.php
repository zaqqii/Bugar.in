@php
    $mainMenuItems = [
        [
            'label' => 'Dashboard',
            'route' => 'penyedia-komunitas.dashboard',
            'active_pattern' => 'penyedia-komunitas.dashboard',
            'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25a2.25 2.25 0 01-2.25-2.25V6z" />',
        ],
        [
            'label' => 'Komunitas',
            'route' => 'penyedia-komunitas.komunitas.index',
            'active_pattern' => 'penyedia-komunitas.komunitas.*',
            'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />',
        ],
        [
            'label' => 'Jadwal',
            'route' => 'penyedia-komunitas.jadwal.index',
            'active_pattern' => 'penyedia-komunitas.jadwal.*',
            'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />',
        ],
        [
            'label' => 'Event',
            'route' => 'penyedia-komunitas.events.index',
            'active_pattern' => 'penyedia-komunitas.events.*',
            'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />',
        ],
        [
            'label' => 'Statistik',
            'route' => 'penyedia-komunitas.statistik.index',
            'active_pattern' => 'penyedia-komunitas.statistik.*',
            'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />',
        ],
    ];

    $bottomMenuItems = [
        [
            'label' => 'Akun',
            'route' => 'penyedia-komunitas.akun.index',
            'active_pattern' => 'penyedia-komunitas.akun.*',
            'icon_svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />',
        ],
    ];
@endphp

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed top-0 bottom-0 left-0 z-40 w-[260px] bg-white border-r border-gray-200 flex flex-col justify-between overflow-y-auto transition-transform duration-300 ease-in-out shadow-lg lg:shadow-none"
       aria-label="Sidebar Navigation">

    {{-- Top Section: Brand & Navigation --}}
    <div class="flex flex-col">
        {{-- Brand Logo & Public Link --}}
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5 font-extrabold text-xl text-primary-600 tracking-tight">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-white">
                            <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
                        </svg>
                    </div>
                    <span>Bugar.in</span>
                </div>

                {{-- Close Button on Mobile --}}
                <button type="button"
                        @click="sidebarOpen = false"
                        class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                        aria-label="Tutup Menu Navigasi">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Satu-satunya tautan ke halaman publik --}}
            <div class="mt-3">
                <a href="{{ route('landing') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-primary-600 transition-colors group">
                    <span>Lihat halaman publik</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400 group-hover:text-primary-500 group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>
            </div>
        </div>

        {{-- Main Navigation (Items 1 - 5) --}}
        <nav class="py-4 flex flex-col">
            @foreach($mainMenuItems as $item)
                @php
                    $isActive = request()->routeIs($item['active_pattern']);
                @endphp
                <a href="{{ route($item['route']) }}"
                   @if($isActive) aria-current="page" @endif
                   class="flex items-center gap-3 px-6 py-3 font-medium text-sm transition-all border-l-4 {{ $isActive ? 'bg-primary-50 text-primary-700 border-primary-500 font-semibold' : 'text-gray-600 border-transparent hover:bg-gray-50 hover:text-primary-600 group' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                         class="w-5 h-5 shrink-0 {{ $isActive ? 'text-primary-600' : 'text-gray-400 group-hover:text-primary-500 transition-colors' }}">
                        {!! $item['icon_svg'] !!}
                    </svg>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Bottom Section: Divider, Akun, Keluar (Items 6 - 7) --}}
    <div class="pt-2 pb-4">
        {{-- Garis Pemisah --}}
        <div class="my-2 mx-6 border-t border-gray-100"></div>

        <nav class="flex flex-col">
            {{-- 6. Akun --}}
            @foreach($bottomMenuItems as $item)
                @php
                    $isActive = request()->routeIs($item['active_pattern']);
                @endphp
                <a href="{{ route($item['route']) }}"
                   @if($isActive) aria-current="page" @endif
                   class="flex items-center gap-3 px-6 py-3 font-medium text-sm transition-all border-l-4 {{ $isActive ? 'bg-primary-50 text-primary-700 border-primary-500 font-semibold' : 'text-gray-600 border-transparent hover:bg-gray-50 hover:text-primary-600 group' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                         class="w-5 h-5 shrink-0 {{ $isActive ? 'text-primary-600' : 'text-gray-400 group-hover:text-primary-500 transition-colors' }}">
                        {!! $item['icon_svg'] !!}
                    </svg>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach

            {{-- 7. Keluar --}}
            <form method="POST" action="{{ route('penyedia-komunitas.logout') }}" class="m-0">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-6 py-3 text-red-600 font-medium text-sm transition-all border-l-4 border-transparent hover:bg-red-50 hover:border-red-500 hover:text-red-700 group text-left cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                         class="w-5 h-5 shrink-0 text-red-500 group-hover:text-red-600 transition-colors">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </nav>
    </div>
</aside>
