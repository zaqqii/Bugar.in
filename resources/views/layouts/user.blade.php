<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Dashboard Pengguna Bugar.in — Platform komunitas olahraga lokal Indonesia">
    <title>@yield('title', 'Dashboard - Bugar.in')</title>
    @vite(['resources/css/app.css', 'resources/css/user-dashboard.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body x-data="{
        sidebarOpen: window.innerWidth >= 1024,
        activeSection: 'komunitas',
        setSection(s) {
            this.activeSection = s;
            if (window.innerWidth < 1024) {
                this.sidebarOpen = false;
            }
        }
      }"
      @resize.window="if (window.innerWidth < 1024 && sidebarOpen) { sidebarOpen = false; }"
      class="bg-[#F0F7FF] text-slate-800 font-sans antialiased min-h-screen relative overflow-x-hidden">

    {{-- Subtle ambient orbs — biru dingin, bukan rose --}}
    <div class="fixed -top-32 -right-32 w-[500px] h-[500px] rounded-full bg-gradient-to-br from-blue-200/20 via-cyan-100/15 to-transparent blur-3xl pointer-events-none z-0" aria-hidden="true"></div>
    <div class="fixed top-1/3 -left-24 w-[420px] h-[420px] rounded-full bg-gradient-to-tr from-blue-100/20 via-sky-50/15 to-transparent blur-3xl pointer-events-none z-0" aria-hidden="true"></div>
    <div class="fixed -bottom-24 right-1/4 w-[460px] h-[460px] rounded-full bg-gradient-to-t from-cyan-100/20 via-blue-50/10 to-transparent blur-3xl pointer-events-none z-0" aria-hidden="true"></div>

    <div class="relative z-10 flex min-h-screen">
        {{-- Sidebar Component --}}
        <x-user.sidebar />

        {{-- Mobile Backdrop --}}
        <div x-show="sidebarOpen"
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-30 bg-slate-900/25 backdrop-blur-xs lg:hidden"
             style="display:none;"></div>

        {{-- Main Area --}}
        <main class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out"
              :class="sidebarOpen ? 'lg:ml-[260px]' : 'lg:ml-0'">
            {{-- Header Component --}}
            <x-user.header />

            {{-- Main Page Content --}}
            <div class="flex-1 px-5 sm:px-8 py-6 overflow-x-hidden">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
