<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Console Ops Admin — Pusat Tata Kelola & Integritas Platform Bugar.in">
    <title>@yield('title', 'Console Admin — Bugar.in')</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/css/user-dashboard.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
      @resize.window="if (window.innerWidth < 1024 && sidebarOpen) { sidebarOpen = false; }"
      class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen relative overflow-x-hidden selection:bg-blue-500 selection:text-white">

    {{-- Dark Ambient Glows --}}
    <div class="fixed -top-40 -right-40 w-[500px] h-[500px] rounded-full bg-blue-600/10 blur-3xl pointer-events-none z-0" aria-hidden="true"></div>
    <div class="fixed top-1/2 -left-40 w-[450px] h-[450px] rounded-full bg-cyan-600/10 blur-3xl pointer-events-none z-0" aria-hidden="true"></div>

    <div class="relative z-10 flex min-h-screen">
        {{-- Sidebar Component --}}
        <x-admin.sidebar />

        {{-- Mobile Backdrop --}}
        <div x-show="sidebarOpen"
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-30 bg-slate-950/80 backdrop-blur-xs lg:hidden"
             style="display:none;"
             aria-hidden="true"></div>

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300 lg:ml-[270px]">
            {{-- Header Component --}}
            <x-admin.header />

            {{-- Main Body --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-8 max-w-7xl w-full mx-auto">
                {{-- Flash Notifications --}}
                @if(session('success'))
                    <div role="status" class="flex items-start gap-3 rounded-2xl bg-emerald-950/60 border border-emerald-500/30 p-4 text-xs font-semibold text-emerald-300 shadow-lg">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-500 text-slate-950 flex items-center justify-center text-xs font-black">✓</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div role="alert" class="flex items-start gap-3 rounded-2xl bg-red-950/60 border border-red-500/30 p-4 text-xs font-semibold text-red-300 shadow-lg">
                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-red-500 text-white flex items-center justify-center text-xs font-black">✕</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>

            {{-- Footer Console --}}
            <footer class="py-4 px-6 border-t border-slate-900 text-center text-xs text-slate-400 bg-slate-950/50">
                <span>&copy; {{ date('Y') }} Bugar.in Console Ops &bull; Pusat Pengawasan &amp; Regulasi Komunitas Olahraga</span>
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

