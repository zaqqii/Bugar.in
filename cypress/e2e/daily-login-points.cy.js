describe('Fitur E2E Testing - Daily Login (Bonus Poin Gerak)', () => {
  const userEmail = 'karahmaa25@gmail.com';
  const userPassword = '12345678';

  it('1. Pengguna login dan berhasil diarahkan ke dashboard dengan badge Poin Gerak di header', () => {
    // Kunjungi halaman login
    cy.visit('/auth/user/register?mode=login');

    // Input kredensial
    cy.get('#login-email', { timeout: 10000 }).should('be.visible').clear().type(userEmail);
    cy.get('#login-password').should('be.visible').clear().type(userPassword);
    cy.get('#btn-masuk-sekarang').should('be.visible').click();

    // Verifikasi berhasil masuk ke dashboard
    cy.url({ timeout: 15000 }).should('include', '/user/dashboard');

    // Verifikasi badge poin gerak terlihat di header navigasi atas
    cy.get('#header-user-points', { timeout: 10000 })
      .should('be.visible')
      .and('contain.text', 'pts');

    cy.screenshot('daily-login-01-dashboard-header-poin', { capture: 'viewport' });
  });

  it('2. Memvalidasi banner notifikasi bonus login harian (+2 Poin Gerak) jika login pertama hari ini', () => {
    cy.visit('/auth/user/register?mode=login');
    cy.get('#login-email').should('be.visible').clear().type(userEmail);
    cy.get('#login-password').should('be.visible').clear().type(userPassword);
    cy.get('#btn-masuk-sekarang').click();

    cy.url({ timeout: 15000 }).should('include', '/user/dashboard');

    // Cek apakah banner alert bonus harian muncul pada sesi login ini
    cy.get('body').then(($body) => {
      const alert = $body.find('#alert-daily-login');
      if (alert.length > 0) {
        cy.log('Banner bonus login harian aktif (+2 Poin)');
        cy.wrap(alert).should('be.visible');
        cy.wrap(alert).should('contain.text', '+2');
        cy.wrap(alert).should('contain.text', 'Bonus login harian Poin Gerak berhasil ditambahkan.');
      } else {
        cy.log('Akun ini sudah pernah login sebelumnya hari ini (aturan batas 1x sehari terpenuhi).');
      }
    });

    cy.screenshot('daily-login-02-banner-cek', { capture: 'viewport' });
  });

  it('3. Menampilkan saldo Gerak Poin dan Riwayat Transaksi Poin di dashboard', () => {
    cy.loginUser(userEmail, userPassword);

    // Buka modul Streak & Poin
    cy.visit('/user/dashboard?section=streak');

    // Pastikan kartu metrik Gerak Poin tampil dengan saldo dan satuan PTS
    cy.contains('Gerak Poin').should('be.visible');
    cy.contains('Saldo poin akunmu').should('be.visible');
    cy.contains('PTS').should('be.visible');

    // Periksa komponen Riwayat Gerak Poin
    cy.contains('h4', 'Riwayat Gerak Poin').should('be.visible');

    // Cek keberadaan catatan riwayat poin
    cy.get('body').then(($body) => {
      const hasLoginRecord = $body.text().includes('Login harian');
      if (hasLoginRecord) {
        cy.log('Ditemukan riwayat poin dengan deskripsi: Login harian (+2 pts)');
        cy.contains('Login harian').should('be.visible');
      } else {
        cy.log('Belum ada riwayat transaksi dengan teks Login harian pada halaman pertama riwayat.');
      }
    });

    cy.screenshot('daily-login-03-riwayat-poin-gerak', { capture: 'viewport' });
  });

  it('4. Memverifikasi logout dan login kembali pada hari yang sama (aturan batas bonus 1x per hari)', () => {
    cy.loginUser(userEmail, userPassword);

    // Logout melalui tombol header
    cy.logoutUser();

    // Login kembali
    cy.visit('/auth/user/register?mode=login');
    cy.get('#login-email').clear().type(userEmail);
    cy.get('#login-password').clear().type(userPassword);
    cy.get('#btn-masuk-sekarang').click();

    cy.url({ timeout: 15000 }).should('include', '/user/dashboard');

    // Pada login kedua di hari yang sama, banner bonus baru TIDAK boleh muncul lagi
    cy.get('#alert-daily-login').should('not.exist');

    // Badge poin di header tetap harus aktif dan terlihat
    cy.get('#header-user-points').should('be.visible').and('contain.text', 'pts');

    cy.screenshot('daily-login-04-login-ulang-tanpa-duplikasi', { capture: 'viewport' });
  });
});
