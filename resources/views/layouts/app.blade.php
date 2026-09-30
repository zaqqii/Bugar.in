<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO Meta --}}
    <title>@yield('title', 'Bugar.in — Platform Komunitas & Event Olahraga Indonesia')</title>
    <meta name="description" content="@yield('description', 'Temukan komunitas olahraga favoritmu, ikuti event seru, dan bangun gaya hidup aktif bersama ribuan member di Bugar.in.')">
    <meta name="keywords" content="komunitas olahraga, event olahraga, running, badminton, cycling, padel, basket, Indonesia">
    <meta property="og:title" content="@yield('title', 'Bugar.in')">
    <meta property="og:description" content="@yield('description', 'Platform komunitas & event olahraga terbesar di Indonesia.')">
    <meta property="og:type" content="website">

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Page-specific head --}}
    @stack('head')
</head>
<body x-data="{ mobileOpen: false, scrolled: false }"
      x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 10 })"
      @keydown.escape.window="mobileOpen = false">

    {{-- ============================================================
         NAVBAR
         ============================================================ --}}
    <nav class="navbar" :class="{ 'scrolled': scrolled }" role="navigation" aria-label="Main navigation">
        <div class="container">
            <div class="navbar__inner">
                {{-- Logo --}}
                <a href="{{ route('landing') }}" class="navbar__logo" aria-label="Bugar.in home">
                    <div class="navbar__logo-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
                        </svg>
                    </div>
                    Bugar.in
                </a>

                {{-- Desktop Nav --}}
                <div class="navbar__nav" role="menubar">
                    <a href="#komunitas" role="menuitem">Jelajahi Komunitas</a>
                    <a href="#events" role="menuitem">Jadwal & Event</a>
                    <a href="#leaderboard" role="menuitem">Leaderboard</a>
                    <a href="#" role="menuitem">Pusat Bantuan</a>
                </div>

                {{-- Search --}}
                <button class="navbar__search" aria-label="Cari komunitas atau event" id="navbar-search-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Cari komunitas...
                </button>

                {{-- Actions --}}
                <div class="navbar__actions">
                    <a href="{{ route('auth.selection') }}" class="btn btn--outline btn--sm">Masuk</a>
                    <a href="{{ route('auth.selection') }}" class="btn btn--primary btn--sm">Daftar</a>
                </div>

                {{-- Mobile toggle --}}
                <button class="navbar__menu-toggle"
                        @click="mobileOpen = true"
                        aria-label="Open menu"
                        id="navbar-mobile-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>

    {{-- ============================================================
         MOBILE NAV DRAWER (Alpine)
         ============================================================ --}}
    <div class="mobile-nav" :class="{ 'open': mobileOpen }" role="dialog" aria-modal="true" aria-label="Mobile navigation">
        <div class="mobile-nav__overlay" @click="mobileOpen = false"></div>
        <div class="mobile-nav__drawer">
            <button class="mobile-nav__close" @click="mobileOpen = false" aria-label="Close menu">✕</button>

            <a href="{{ route('landing') }}" class="navbar__logo" style="margin-bottom: 1rem;">
                <div class="navbar__logo-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
                    </svg>
                </div>
                Bugar.in
            </a>

            <a href="#komunitas" class="mobile-nav__link" @click="mobileOpen = false">Jelajahi Komunitas</a>
            <a href="#events" class="mobile-nav__link" @click="mobileOpen = false">Jadwal & Event</a>
            <a href="#leaderboard" class="mobile-nav__link" @click="mobileOpen = false">Leaderboard</a>
            <a href="#" class="mobile-nav__link">Pusat Bantuan</a>

            <div style="display:flex; flex-direction:column; gap:0.75rem; margin-top:auto; padding-top:1.5rem;">
                <a href="{{ route('auth.selection') }}" class="btn btn--outline">Masuk</a>
                <a href="{{ route('auth.selection') }}" class="btn btn--primary">Daftar</a>
            </div>
        </div>
    </div>

    {{-- ============================================================
         PAGE CONTENT
         ============================================================ --}}
    <main id="main-content">
        @yield('content')
    </main>

    {{-- ============================================================
         FOOTER
         ============================================================ --}}
    <footer class="footer" role="contentinfo">
        <div class="container">
            <div class="footer__inner">
                <div class="footer__brand">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
                    </svg>
                    Bugar.in
                </div>

                <span class="footer__copy">© 2026 Platform Komunitas Olahraga Indonesia</span>

                <nav class="footer__links" aria-label="Footer navigation">
                    <a href="#">Privacy</a>
                    <a href="#">Ketentuan</a>
                    <a href="#">Fasilitasi Kami</a>
                </nav>
            </div>
        </div>
    </footer>

    {{-- Page-specific scripts --}}
    @stack('scripts')

</body>
</html>
