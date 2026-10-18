<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Penyedia Komunitas - Bugar.in')</title>

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/css/penyedia-komunitas.css', 'resources/js/app.js'])

    {{-- Page-specific head --}}
    @stack('head')
</head>
<body x-data="{ sidebarOpen: false }" class="bg-gray-50 text-gray-800 font-sans antialiased">

    {{-- Mobile Sidebar Backdrop --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 lg:hidden"
         style="display: none;"
         aria-hidden="true"></div>

    <div class="flex min-h-screen">
        {{-- Sidebar Component --}}
        <x-penyedia-komunitas.sidebar />

        {{-- Main Content Area --}}
        <main class="flex-1 flex flex-col lg:ml-[260px] ml-0 min-w-0 transition-all duration-300">
            {{-- Header Component --}}
            <x-penyedia-komunitas.header />

            {{-- Content --}}
            <div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-x-hidden">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Page-specific scripts --}}
    @stack('scripts')
</body>
</html>
