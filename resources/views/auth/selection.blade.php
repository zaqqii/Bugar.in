<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Pilih Cara Masuk — Bugar.in</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background-color: #f4f8fb;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .auth-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-4) var(--space-8);
            background: transparent;
        }

        .auth-logo {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            font-weight: 800;
            font-size: var(--font-size-xl);
            color: var(--color-gray-900);
        }

        .auth-logo-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--color-primary-500), var(--color-primary-700));
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .auth-back {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            font-size: var(--font-size-sm);
            font-weight: 600;
            color: var(--color-primary-600);
            transition: color var(--transition-fast);
        }

        .auth-back:hover {
            color: var(--color-primary-800);
        }

        .auth-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: var(--space-10) var(--space-4);
        }

        .auth-title {
            font-size: var(--font-size-4xl);
            font-weight: 800;
            color: #1e40af; 
            margin-bottom: var(--space-2);
            text-align: center;
        }

        .auth-subtitle {
            font-size: var(--font-size-base);
            color: var(--color-gray-500);
            text-align: center;
            margin-bottom: var(--space-10);
            max-width: 600px;
        }

        .auth-cards-container {
            display: flex;
            gap: var(--space-6);
            flex-wrap: wrap;
            justify-content: center;
            max-width: 900px;
            width: 100%;
        }

        .auth-card {
            background: var(--color-white);
            border-radius: var(--radius-2xl);
            padding: var(--space-8) var(--space-6);
            flex: 1;
            min-width: 320px;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.03);
            border: 1px solid var(--color-gray-100);
            transition: all var(--transition-base);
        }

        .auth-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.06);
            border-color: var(--color-primary-200);
        }

        .auth-card-icon {
            width: 64px;
            height: 64px;
            border-radius: var(--radius-xl);
            background: #e0f2fe; /* Light blue */
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            margin-bottom: var(--space-6);
        }

        .auth-card-icon--teal {
            background: #ccfbf1;
            color: #0d9488;
        }

        .auth-card-title {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            color: #1e40af;
            margin-bottom: var(--space-3);
        }

        .auth-card-desc {
            font-size: var(--font-size-sm);
            color: var(--color-gray-500);
            margin-bottom: var(--space-8);
            line-height: 1.6;
            flex: 1;
        }

        .auth-btn-blue {
            width: 100%;
            background: #2563eb;
            color: white;
            padding: var(--space-3) var(--space-5);
            border-radius: var(--radius-lg);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            transition: background var(--transition-fast);
        }

        .auth-btn-blue:hover { background: #1d4ed8; }

        .auth-btn-teal {
            width: 100%;
            background: #00bcd0;
            color: white;
            padding: var(--space-3) var(--space-5);
            border-radius: var(--radius-lg);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            transition: background var(--transition-fast);
        }

        .auth-btn-teal:hover { background: #009aad; }

        .auth-links {
            margin-top: var(--space-10);
            display: flex;
            align-items: center;
            gap: var(--space-6);
            font-size: var(--font-size-sm);
            color: var(--color-gray-600);
        }

        .auth-links a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: underline;
        }

        .auth-links a:hover {
            color: #1d4ed8;
        }

        .auth-links-divider {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--color-gray-300);
        }
        
        .admin-portal-link {
            display: flex;
            align-items: center;
            gap: 4px;
            color: #2563eb;
            font-weight: 600;
            text-decoration: none !important;
        }

        .auth-footer {
            padding: var(--space-6) var(--space-8);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid var(--color-gray-200);
            font-size: var(--font-size-sm);
            color: var(--color-gray-500);
        }

        .auth-footer-links {
            display: flex;
            gap: var(--space-6);
        }

        .auth-footer-links a {
            color: #2563eb;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .auth-header { padding: var(--space-4); }
            .auth-footer { 
                flex-direction: column; 
                gap: var(--space-4); 
                text-align: center;
                padding: var(--space-4);
            }
        }
    </style>
</head>
<body>

    <header class="auth-header">
        <div class="auth-logo">
            <div class="auth-logo-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:16px;height:16px;">
                    <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
                </svg>
            </div>
            Bugar.in
        </div>
        <a href="{{ route('landing') }}" class="auth-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Beranda
        </a>
    </header>

    <main class="auth-main">
        <h1 class="auth-title">Masuk ke Bugar.in</h1>
        <p class="auth-subtitle">Pilih cara Anda masuk sesuai dengan peran dan kebutuhan aktivitas olahraga Anda.</p>

        <div class="auth-cards-container">
            {{-- Pengguna Card --}}
            <div class="auth-card">
                <div class="auth-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h2 class="auth-card-title">Pengguna</h2>
                <p class="auth-card-desc">Cari event, daftarkan diri, dan bergabung dengan komunitas olahraga favorit.</p>
                <a href="{{ route('auth.user.register') }}" class="auth-btn-blue">
                    Masuk sebagai Pengguna 
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>

            {{-- Penyedia Komunitas Card --}}
            <div class="auth-card">
                <div class="auth-card-icon auth-card-icon--teal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h2 class="auth-card-title">Penyedia Komunitas</h2>
                <p class="auth-card-desc">Kelola jadwal sesi latihan, publikasikan event, dan atur anggota klub.</p>
                <a href="#" class="auth-btn-teal">
                    Masuk sebagai Penyedia 
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        </div>

        <div class="auth-links">
            <span>Butuh bantuan masuk? <a href="#">Kunjungi Pusat Bantuan</a></span>
            <div class="auth-links-divider"></div>
            <a href="#" class="admin-portal-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.95 11.95 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Portal Admin 
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </main>

    <footer class="auth-footer">
        <div>© 2026 Bugar.in. Seluruh hak cipta dilindungi undang-undang.</div>
        <div class="auth-footer-links">
            <a href="#">Kebijakan Privasi</a>
            <a href="#">Ketentuan Layanan</a>
            <a href="#">Bantuan</a>
        </div>
    </footer>

</body>
</html>
