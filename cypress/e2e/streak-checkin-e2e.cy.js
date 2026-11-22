/**
 * E2E Test: Alur Lengkap QR Check-in & Streak Bugar.in
 *
 * Alur Single-Spec:
 *  1. Penyedia login (rifatrampage@gmail.com / rempeg23) → buat jadwal hari ini
 *     dengan start_time 15 menit ke depan (streak enabled).
 *  2. User baru daftar manual → onboarding → join jadwal via POST form.
 *  3. User cek card Sesi Hari Ini di dashboard → buka modal → Tampilkan QR.
 *  4. Penyedia login → buka /penyedia-komunitas/jadwal/{id}/checkin → Check-in Manual peserta.
 *  5. User login → verifikasi Streak Aktif (+1 Hari), Poin Gerak (+30 PTS), dan riwayat +30 pts.
 */

describe('E2E Alur QR Check-in & Validasi Streak Bugar.in', () => {

  // Tangani semua uncaught exception browser agar test tidak terputus
  Cypress.on('uncaught:exception', () => false);

  const ts = Date.now().toString().slice(-6);
  const user = {
    nama    : `Peserta E2E ${ts}`,
    username: `user_${ts}`,
    email   : `cypress_${ts}@mailinator.com`,
    wa      : `081200${ts}`,
    password: 'Password123!',
  };

  const provider = {
    email: 'rifatrampage@gmail.com',
    pass : 'rempeg23',
  };

  function loginProvider() {
    cy.clearAllCookies();
    cy.clearAllLocalStorage();
    cy.visit('/auth/penyedia-komunitas/login');
    cy.get('body').then($b => {
      if ($b.find('#btn-header-masuk').length) cy.get('#btn-header-masuk').click();
    });
    cy.get('#login-email').clear().type(provider.email);
    cy.get('#login-password').clear().type(provider.pass);
    cy.get('#btn-masuk-host').click();
    cy.url({ timeout: 20000 }).should('include', '/penyedia-komunitas');
  }

  function loginUser() {
    cy.clearAllCookies();
    cy.clearAllLocalStorage();
    cy.visit('/auth/user/login');
    cy.get('body').then($b => {
      if ($b.find('#btn-header-masuk').length) cy.get('#btn-header-masuk').click();
    });
    cy.get('#login-email').clear().type(user.email);
    cy.get('#login-password').clear().type(user.password);
    cy.get('#btn-masuk-sekarang').click();
    cy.url({ timeout: 20000 }).should('include', '/user/dashboard');
  }

  it('Full E2E Flow: Provider Buat Jadwal → User Registrasi & Join → Provider Check-in Manual → User Streak & Poin Terverifikasi', () => {

    // ─────────────────────────────────────────────────────────────────
    // LANGKAH 1: Penyedia buat jadwal aktif hari ini (streak enabled)
    // ─────────────────────────────────────────────────────────────────
    loginProvider();
    cy.visit('/penyedia-komunitas/jadwal/create');

    const now = new Date();
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const dd = String(now.getDate()).padStart(2, '0');
    const todayStr = `${yyyy}-${mm}-${dd}`;

    // Waktu mulai 15 menit ke depan (sesi belum mulai sehingga user bisa join,
    // sekaligus sudah masuk window check-in 30 menit sebelum sesi).
    const startTotalMin = now.getHours() * 60 + now.getMinutes() + 15;
    const startH = String(Math.floor(startTotalMin / 60) % 24).padStart(2, '0');
    const startM = String(startTotalMin % 60).padStart(2, '0');
    const startTime = `${startH}:${startM}`;
    const endTime   = '23:59';

    const jadwalTitle = `Latihan E2E ${ts}`;

    cy.get('#title').clear().type(jadwalTitle);
    cy.get('#level').select('all_levels');
    cy.get('#description').clear().type('Sesi pengujian otomatis E2E Cypress check-in & streak.');
    cy.get('#date').clear().type(todayStr);
    cy.get('#start_time').clear().type(startTime);
    cy.get('#end_time').clear().type(endTime);
    cy.get('#location').clear().type('GOR Rampage Basketball Court');

    // Centang checkbox streak_enabled & is_active
    cy.get('input[type="checkbox"][name="streak_enabled"]').check({ force: true });
    cy.get('input[type="checkbox"][name="is_active"]').check({ force: true });

    cy.contains('button', 'Simpan Jadwal Rutin').click();

    // Pastikan jadwal berhasil dibuat
    cy.url({ timeout: 20000 }).should('include', '/penyedia-komunitas/jadwal');
    cy.contains('berhasil', { timeout: 10000 }).should('be.visible');

    // Dapatkan schedule ID dari h3 judul kartu (bukan dari flash alert)
    cy.get('h3')
      .contains(jadwalTitle, { timeout: 15000 })
      .parents('[class*="rounded-2xl"]')
      .find('a[href*="/checkin"]')
      .first()
      .then($a => {
        const href = $a.attr('href');
        const m = href.match(/\/jadwal\/(\d+)\/checkin/);
        expect(m, 'URL checkin harus berisi schedule ID').to.not.be.null;
        cy.wrap(m[1]).as('sid');
      });

    // ─────────────────────────────────────────────────────────────────
    // LANGKAH 2: User baru mendaftar manual & onboarding
    // ─────────────────────────────────────────────────────────────────
    cy.get('@sid').then(sid => {
      cy.log(`📌 Jadwal berhasil dibuat dengan ID: ${sid}`);

      cy.clearAllCookies();
      cy.clearAllLocalStorage();
      cy.visit('/auth/user/register');
      cy.get('body').then($b => {
        if ($b.find('#btn-header-daftar').length) cy.get('#btn-header-daftar').click();
      });

      cy.get('#reg-nama').clear().type(user.nama);
      cy.get('#reg-username').clear().type(user.username);
      cy.get('#reg-email').clear().type(user.email);
      cy.get('#reg-wa').clear().type(user.wa);
      cy.get('#reg-password').clear().type(user.password);
      cy.get('#reg-password-confirm').clear().type(user.password);
      cy.get('#reg-terms').check({ force: true });
      cy.get('#btn-daftar-sekarang').click();

      // Onboarding minat olahraga → lewati
      cy.url({ timeout: 20000 }).should('include', '/user/onboarding');
      cy.contains('a', 'Lewati', { timeout: 10000 }).first().click();

      // Masuk ke dashboard user
      cy.url({ timeout: 20000 }).should('include', '/user/dashboard');
      cy.log('✅ User baru berhasil mendaftar & masuk ke dashboard');

      // ─────────────────────────────────────────────────────────────────
      // LANGKAH 3: User bergabung (Join) ke sesi jadwal & periksa QR
      // ─────────────────────────────────────────────────────────────────
      // Submit POST join jadwal via form di browser (dengan session & CSRF sah)
      cy.window().then(win => {
        const form = win.document.createElement('form');
        form.method = 'POST';
        form.action = `/user/schedules/${sid}/join`;
        const csrfMeta = win.document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.content : '';
        const input = win.document.createElement('input');
        input.type = 'hidden';
        input.name = '_token';
        input.value = csrfToken;
        form.appendChild(input);
        win.document.body.appendChild(form);
        form.submit();
      });

      // Konfirmasi redirect kembali ke dashboard setelah join
      cy.url({ timeout: 20000 }).should('include', '/user/dashboard');
      cy.log('✅ User berhasil join sesi jadwal');

      // Verifikasi sesi hari ini tampil di dashboard
      cy.contains('Lihat Detail Sesi', { timeout: 20000 }).should('be.visible').click();

      // Modal detail sesi terbuka → klik "Tampilkan QR Check-in"
      cy.contains('Tampilkan QR Check-in', { timeout: 10000 }).should('be.visible').click();

      // Verifikasi canvas container QR Code muncul
      cy.get('#qr-canvas-container', { timeout: 15000 }).should('exist');
      cy.log('✅ QR Code Check-in berhasil dirender di modal');

      // ─────────────────────────────────────────────────────────────────
      // LANGKAH 4: Penyedia Komunitas melakukan Check-in Manual peserta
      // ─────────────────────────────────────────────────────────────────
      loginProvider();
      cy.visit(`/penyedia-komunitas/jadwal/${sid}/checkin`);

      // Tangani window.confirm dari Alpine.js secara otomatis
      cy.on('window:confirm', msg => {
        cy.log('Confirm dialog: ' + msg);
        return true;
      });

      // Tunggu nama peserta muncul di tabel
      cy.contains(user.nama, { timeout: 25000 }).should('be.visible');
      cy.log('✅ Nama peserta ditemukan di tabel daftar kehadiran');

      // Pastikan status awal "Belum Hadir"
      cy.contains(user.nama)
        .closest('tr')
        .contains('Belum Hadir')
        .should('be.visible');

      // Klik tombol Check-in Manual pada baris peserta
      cy.contains(user.nama)
        .closest('tr')
        .find('button')
        .contains('Check-in Manual')
        .click();

      // Verifikasi status pada baris peserta berubah menjadi "Hadir"
      cy.contains(user.nama, { timeout: 20000 })
        .closest('tr')
        .contains('Hadir')
        .should('be.visible');

      cy.log('✅ Check-in Manual berhasil diproses — status peserta kini: Hadir');

      // ─────────────────────────────────────────────────────────────────
      // LANGKAH 5: Verifikasi di sisi User: Streak (+1) & Poin (+30 PTS)
      // ─────────────────────────────────────────────────────────────────
      loginUser();

      // Verifikasi di header dashboard stats
      cy.contains('Streak', { timeout: 15000 }).should('be.visible');
      cy.contains('1 Hari').should('be.visible');
      cy.log('✅ Verifikasi Header: Streak tercatat 1 Hari');

      cy.contains('Poin Gerak', { timeout: 10000 }).should('be.visible');
      cy.contains('30 PTS').should('be.visible');
      cy.log('✅ Verifikasi Header: Poin Gerak tercatat 30 PTS');

      // Buka seksi streak untuk verifikasi riwayat transaksi poin
      cy.visit('/user/dashboard?section=streak');

      // Verifikasi entri poin +30 pts ada di Riwayat Gerak Poin
      cy.contains('+30 pts', { timeout: 15000 }).should('be.visible');
      cy.log('✅ Verifikasi Riwayat: +30 pts berhasil masuk ke catatan transaksi!');

      cy.log('🎉 SELURUH ALUR E2E (REGISTRASI, JOIN, CHECK-IN, STREAK +1, POIN +30) LULUS 100%!');
    });
  });
});
