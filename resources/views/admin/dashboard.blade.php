<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Console Admin — Gerak.in</title>
    @vite(['resources/css/app.css'])
</head>
<body style="min-height:100vh;background:#f4f7fc;display:grid;place-items:center;padding:24px">
    <main style="width:min(100%,680px);background:#fff;border:1px solid #e3eaf5;border-radius:24px;padding:clamp(24px,5vw,48px);box-shadow:0 20px 60px #1d4ed812">
        <p style="margin:0 0 8px;color:#2563eb;font-size:12px;font-weight:800;letter-spacing:.12em;text-transform:uppercase">Gerak.in • Console Ops</p>
        <h1 style="margin:0;color:#172554;font-size:clamp(28px,5vw,40px)">Selamat datang, {{ auth()->user()->name }}</h1>
        <p style="margin:12px 0 28px;color:#64748b;line-height:1.7">Login administrator berhasil. Area ini siap menjadi titik awal fitur pengelolaan platform.</p>
        @if (session('success'))
            <p role="status" style="padding:12px 16px;background:#eff6ff;color:#1d4ed8;border-radius:12px">{{ session('success') }}</p>
        @endif
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" style="border:0;border-radius:12px;padding:12px 18px;background:#2563eb;color:#fff;font:inherit;font-weight:700;cursor:pointer">Keluar dari Console Admin</button>
        </form>
    </main>
</body>
</html>
