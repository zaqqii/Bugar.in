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

    <div class="flex min-h-screen">
        {{-- Sidebar Component --}}
        <x-penyedia-komunitas.sidebar />

        {{-- Main Content Area --}}
        <main class="flex-1 flex flex-col ml-[260px] min-w-0">
            {{-- Header Component --}}
            <x-penyedia-komunitas.header />

            {{-- Content --}}
            <div class="flex-1 p-8 overflow-x-hidden">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Page-specific scripts --}}
    @stack('scripts')
</body>
</html>
