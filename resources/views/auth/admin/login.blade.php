<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk Console Admin — Gerak.in</title>
    <meta name="description" content="Login khusus administrator platform Gerak.in.">
    @vite(['resources/css/app.css', 'resources/css/auth-admin.css'])
</head>
<body class="admin-login-page">
    <main class="admin-login-shell">
        <section class="admin-login-form-panel" aria-labelledby="login-title">
            <a class="admin-brand" href="{{ route('landing') }}" aria-label="Gerak.in, kembali ke portal publik">
                <span class="admin-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32" fill="none"><path d="M6 21.5 12.2 18l3-8 3.3 4.4 7.5-3.2M9 25l4-4m7-1 4 3m-9-11 4-4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="23.5" cy="7.5" r="2.3" fill="currentColor"/></svg>
                </span>
                <span class="admin-brand-copy"><strong>Gerak.in <small>CONSOLE OPS</small></strong><span>Pusat Tata Kelola &amp; Integritas Platform</span></span>
            </a>

            <div class="admin-login-content">
                <div class="admin-access-note"><span aria-hidden="true">▣</span> Akses khusus staf &amp; tim operasional terverifikasi</div>
                <h1 id="login-title">Masuk Console Admin</h1>
                <p class="admin-login-intro">Autentikasi tingkat lanjut untuk tim moderator, penegakan regulasi, dan manajemen sistem Gerak.in.</p>

                @if (session('success'))
                    <div class="admin-alert admin-alert--success" role="status">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="admin-alert admin-alert--error" role="alert">{{ $errors->first() }}</div>
                @endif

                <form class="admin-login-form" method="POST" action="{{ route('admin.login.post') }}">
                    @csrf
                    <label for="email">Alamat email administrator platform</label>
                    <div class="admin-input-wrap">
                        <span class="admin-input-icon" aria-hidden="true">@</span>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" placeholder="admin@gerak.in" required autofocus>
                    </div>

                    <div class="admin-password-label">
                        <label for="password">Kata sandi / security token</label>
                        <button class="admin-text-button" type="button" data-support-toggle aria-expanded="false" aria-controls="admin-support">Lupa kredensial admin?</button>
                    </div>
                    <div class="admin-input-wrap">
                        <span class="admin-input-icon" aria-hidden="true">♙</span>
                        <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required>
                        <button class="admin-password-toggle" type="button" data-password-toggle aria-label="Tampilkan kata sandi" aria-pressed="false">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.7"/></svg>
                        </button>
                    </div>
                    <p class="admin-support-message" id="admin-support" hidden>Hubungi pengelola sistem Gerak.in untuk verifikasi identitas dan pemulihan akses administrator.</p>

                    <div class="admin-login-options">
                        <label class="admin-remember"><input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}><span>Ingat sesi perangkat kerja aman</span></label>
                    </div>

                    <button class="admin-submit" type="submit">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 5 6v5c0 4.7 3 8 7 10 4-2 7-5.3 7-10V6l-7-3Z" stroke="currentColor" stroke-width="1.7"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Masuk e Portal House <span aria-hidden="true">→</span>
                    </button>
                </form>

                <div class="admin-protocol-title"><span></span> ATAU MASUK DENGAN <span></span></div>
                    </div>
                </div>
                <p class="admin-protocol-caption">Metode autentikasi tambahan dikelola oleh administrator sistem.</p>
            </div>

            <footer class="admin-login-footer">
                <span>• IP whitelist aktif</span><span>• TLS 1.3</span><span>• Audit forensik</span>
                <a href="{{ route('landing') }}">← Kembali ke Portal Publik</a>
            </footer>
        </section>

        <aside class="admin-login-aside" aria-label="Informasi platform">
            <div class="admin-aside-top"><span><i></i> Sentinel Cluster Online • Health 99.98%</span><span>🛡 Lead Platform Integrity &amp; Safety System</span></div>
            <div class="admin-aside-message">
                <span class="admin-trust-tag">★ &nbsp; Platform Governance &amp; Trust</span>
                <h2>“Menjaga ekosistem olahraga nasional yang aman, inklusif, dan bebas diskriminasi.”</h2>
                <p>Monitoring komunitas aktif, audit forensik laporan pelanggaran SARA/etika, serta otomasi moderasi konten berbasis NLP Sentinel v2.4.</p>
                <div class="admin-metrics">
                    <div><strong>99.98%</strong><span>Platform Uptime SLA</span></div>
                    <div><strong>&lt; 15 mnt</strong><span>Respons Laporan</span></div>
                    <div><strong>100%</strong><span>Terenkripsi &amp; Diaudit</span></div>
                </div>
            </div>
            <div class="admin-quote-card">
                <div class="admin-quote-head"><span class="admin-avatar">AP</span><div><strong>Arya Pratama <small>#ADM-004</small></strong><span>Lead Platform Integrity Officer • Divisi Etika Digital</span></div><b>🛡 Tier 3 Clearance</b></div>
                <blockquote>“Setiap tindakan penegakan hukum dan moderasi komunitas terekam permanen dalam log audit terenkripsi demi transparansi tata kelola yang bertanggung jawab.”</blockquote>
                <div class="admin-quote-status"><span>● &nbsp; Otoritas Super Admin Terverifikasi • Multi-Factor Enforced</span><span>Rotasi kunci: 12 jam lalu</span></div>
            </div>
        </aside>
    </main>

    <script>
        document.querySelector('[data-password-toggle]').addEventListener('click', function () {
            const field = document.getElementById('password');
            const showing = field.type === 'password';
            field.type = showing ? 'text' : 'password';
            this.setAttribute('aria-pressed', String(showing));
            this.setAttribute('aria-label', showing ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        });
        document.querySelector('[data-support-toggle]').addEventListener('click', function () {
            const message = document.getElementById('admin-support');
            message.hidden = !message.hidden;
            this.setAttribute('aria-expanded', String(!message.hidden));
        });
    </script>
</body>
</html>
