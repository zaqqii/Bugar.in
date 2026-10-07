<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard — Bugar.in</title>
    <meta name="description" content="Dashboard pengguna Bugar.in - kelola aktivitas olahraga dan komunitas kamu.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .dashboard-body {
            background: #f0f4f8;
            min-height: 100vh;
            font-family: var(--font-family);
        }

        /* ---- TOPBAR ---- */
        .dashboard-topbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 2rem;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .dashboard-brand {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            text-decoration: none;
        }

        .dashboard-brand-icon {
            width: 34px;
            height: 34px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .dashboard-brand-icon svg {
            width: 17px;
            height: 17px;
        }

        .dashboard-brand-name {
            font-weight: 800;
            font-size: 1.1rem;
            color: #0f172a;
        }

        .dashboard-topbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .dashboard-user-info {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .dashboard-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #60a5fa, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        .dashboard-user-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1e293b;
        }

        .dashboard-user-role {
            font-size: 0.7rem;
            color: #94a3b8;
        }

        .dashboard-logout-btn {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.45rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            transition: all 200ms ease;
        }

        .dashboard-logout-btn:hover {
            border-color: #fca5a5;
            color: #ef4444;
            background: #fef2f2;
        }

        /* ---- MAIN AREA ---- */
        .dashboard-main {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem;
        }

        /* Success banner */
        .dashboard-success {
            background: linear-gradient(90deg, #f0fdf4, #dcfce7);
            border: 1px solid #86efac;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            color: #15803d;
            font-size: 0.875rem;
            font-weight: 600;
        }

        /* ---- WELCOME CARD ---- */
        .dashboard-welcome {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #3b82f6 100%);
            border-radius: 20px;
            padding: 2.5rem;
            color: white;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .dashboard-welcome::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }

        .dashboard-welcome::after {
            content: '';
            position: absolute;
            bottom: -60px;
            right: 80px;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
        }

        .dashboard-welcome-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: rgba(255,255,255,0.65);
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .dashboard-welcome-title {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            position: relative;
        }

        .dashboard-welcome-sub {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.75);
            position: relative;
        }

        .dashboard-welcome-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 99px;
            padding: 0.3rem 0.75rem;
            font-size: 0.72rem;
            font-weight: 600;
            margin-top: 1.25rem;
            position: relative;
        }

        /* ---- STATS ---- */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .dashboard-stat-card {
            background: white;
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            border: 1px solid #f1f5f9;
            box-shadow: 0 1px 6px rgba(0,0,0,0.04);
        }

        .dashboard-stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #94a3b8;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .dashboard-stat-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .dashboard-stat-sub {
            font-size: 0.72rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }

        /* ---- COMING SOON AREA ---- */
        .dashboard-coming-soon {
            background: white;
            border-radius: 20px;
            border: 2px dashed #e2e8f0;
            padding: 4rem 2rem;
            text-align: center;
        }

        .dashboard-coming-soon-icon {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: #2563eb;
        }

        .dashboard-coming-soon-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .dashboard-coming-soon-desc {
            font-size: 0.875rem;
            color: #94a3b8;
            max-width: 400px;
            margin: 0 auto 1.5rem;
            line-height: 1.65;
        }

        .dashboard-coming-soon-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
        }

        .dashboard-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.35rem 0.875rem;
            border-radius: 99px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="dashboard-body">

    {{-- ====== TOPBAR ====== --}}
    <header class="dashboard-topbar">
        <a href="{{ route('landing') }}" class="dashboard-brand">
            <div class="dashboard-brand-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M13.49 5.48c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm-3.6 13.9l1-4.4 2.1 2v6h2v-7.5l-2.1-2 .6-3c1.3 1.5 3.3 2.5 5.5 2.5v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1l-5.2 2.2v4.7h2v-3.4l1.8-.7-1.6 8.1-4.9-1-.4 2 7 1.4z"/>
                </svg>
            </div>
            <span class="dashboard-brand-name">Bugar.in</span>
        </a>

        <div class="dashboard-topbar-right">
            <div class="dashboard-user-info">
                <div class="dashboard-avatar">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <div class="dashboard-user-name">{{ $user->name }}</div>
                    <div class="dashboard-user-role">Pengguna</div>
                </div>
            </div>

            <form method="POST" action="{{ route('user.logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="dashboard-logout-btn" id="btn-logout">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </header>

    {{-- ====== MAIN CONTENT ====== --}}
    <main class="dashboard-main">

        {{-- Flash success --}}
        @if(session('success'))
            <div class="dashboard-success" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Welcome Card --}}
        <div class="dashboard-welcome">
            <div class="dashboard-welcome-label">Dashboard Pengguna</div>
            <h1 class="dashboard-welcome-title">Halo, {{ $user->name }}! 👋</h1>
            <p class="dashboard-welcome-sub">Selamat datang di Bugar.in. Mulai temukan komunitas dan event olahraga di sekitarmu.</p>
            <div class="dashboard-welcome-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.95 11.95 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Akun Terverifikasi
            </div>
        </div>

        {{-- Stats --}}
        <div class="dashboard-stats">
            <div class="dashboard-stat-card">
                <div class="dashboard-stat-label">Komunitas Diikuti</div>
                <div class="dashboard-stat-value">0</div>
                <div class="dashboard-stat-sub">Belum bergabung</div>
            </div>
            <div class="dashboard-stat-card">
                <div class="dashboard-stat-label">Event Diikuti</div>
                <div class="dashboard-stat-value">0</div>
                <div class="dashboard-stat-sub">Belum ada event</div>
            </div>
            <div class="dashboard-stat-card">
                <div class="dashboard-stat-label">Total Poin</div>
                <div class="dashboard-stat-value">{{ $user->total_points ?? 0 }}</div>
                <div class="dashboard-stat-sub">Kumpulkan lebih banyak!</div>
            </div>
        </div>

        {{-- Coming Soon --}}
        <div class="dashboard-coming-soon">
            <div class="dashboard-coming-soon-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                </svg>
            </div>
            <h2 class="dashboard-coming-soon-title">Fitur Segera Hadir</h2>
            <p class="dashboard-coming-soon-desc">
                Dashboard lengkap dengan komunitas, event, leaderboard, dan streak aktivitas olaharaga kamu sedang dalam pengembangan.
            </p>
            <div class="dashboard-coming-soon-tags">
                <span class="dashboard-tag">🏃 Jelajahi Komunitas</span>
                <span class="dashboard-tag">📅 Jadwal Event</span>
                <span class="dashboard-tag">🏆 Leaderboard</span>
                <span class="dashboard-tag">🔥 Streak Harian</span>
                <span class="dashboard-tag">📊 Statistik Aktivitas</span>
            </div>
        </div>

    </main>

</body>
</html>
