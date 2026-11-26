describe('Pengujian Fitur Data Pengguna di Console Admin', () => {
  beforeEach(() => {
    // Login sebagai Admin sebelum setiap test case dijalankan
    cy.loginAdmin();
  });

  it('1. Berhasil mengakses menu Data Pengguna melalui Sidebar Admin', () => {
    // Klik menu Data Pengguna pada sidebar (pastikan memilih yang visible di viewport)
    cy.get('aside a[href*="/admin/users"]').filter(':visible').first().click({ force: true });

    // Verifikasi URL dan elemen utama halaman Data Pengguna
    cy.url().should('include', '/admin/users');
    cy.contains('h1', 'Pusat Data & Akun Pengguna').should('be.visible');
    cy.contains('Database Terhubung').should('be.visible');
  });

  it('2. Memverifikasi 4 Card Metrik Ringkasan KPI dan Kanvas Grafik', () => {
    cy.visit('/admin/users');

    // Pastikan 4 Kartu Metrik KPI tampil
    cy.contains('Total Akun Pengguna').should('be.visible');
    cy.contains('Pengguna Reguler').should('be.visible');
    cy.contains('Tergabung Komunitas').should('be.visible');
    cy.contains('Total Poin Gerak').should('be.visible');

    // Pastikan Canvas Chart visualisasi ter-render di DOM
    cy.get('#userRegistrationChart').should('exist');
    cy.get('#userRoleChart').should('exist');
  });

  it('3. Memvalidasi struktur kolom tabel (tanpa kolom Poin Gerak & Komunitas)', () => {
    cy.visit('/admin/users');

    // Verifikasi header tabel pengguna
    cy.get('table thead').within(() => {
      cy.contains('th', 'Pengguna').should('be.visible');
      cy.contains('th', 'Kontak').should('be.visible');
      cy.contains('th', 'Status Akun').should('be.visible');
      cy.contains('th', 'Bergabung').should('be.visible');
      cy.contains('th', 'Aksi').should('be.visible');

      // Memastikan kolom yang telah dihapus tidak muncul di header tabel
      cy.contains('th', 'Poin Gerak').should('not.exist');
      cy.contains('th', 'Komunitas yang Diikuti').should('not.exist');
    });

    // Verifikasi baris tabel memiliki tombol Detail jika ada data pengguna
    cy.get('table tbody').then(($tbody) => {
      if ($tbody.find('tr').length > 0) {
        cy.get('table tbody tr').first().within(() => {
          cy.get('button[title="Lihat Rincian Detail"]').should('exist');
        });
      }
    });
  });

  it('4. Menguji pencarian real-time via AJAX (Debounce input search)', () => {
    cy.visit('/admin/users');

    // Lakukan pencarian nama/email admin
    cy.get('input[x-model="search"]')
      .should('be.visible')
      .clear()
      .type('admin@bugar.in');

    // Tunggu debounce 350ms dan request AJAX selesai
    cy.wait(800);

    // Hasil pencarian harus memuat baris dengan email admin
    cy.get('table tbody').should('contain', 'admin@bugar.in');

    // Verifikasi URL mencantumkan query search tanpa reload halaman penuh (toleransi format encoding @ / %40)
    cy.url().should('match', /search=admin(@|%40)bugar\.in/);
  });

  it('5. Menguji filter per peran (Role) dan fungsionalitas Reset Filter', () => {
    cy.visit('/admin/users');

    // 1. Filter Role: User Reguler
    cy.get('select[x-model="role"]').select('user');
    cy.wait(800);
    cy.url().should('include', 'role=user');
    cy.get('table tbody tr').first().should('contain', 'User');

    // Tombol Reset Filter harus muncul saat ada filter aktif
    cy.contains('button', 'Reset Filter')
      .should('be.visible')
      .click({ force: true });
    cy.wait(800);

    // Input filter role harus kembali kosong (Semua Peran)
    cy.get('select[x-model="role"]').should('have.value', '');
  });

  it('6. Menguji Modal Pop-up Rincian Detail khusus role User Reguler', () => {
    cy.visit('/admin/users');

    // Filter role 'user' untuk memastikan baris adalah User reguler
    cy.get('select[x-model="role"]').select('user');
    cy.wait(800);

    // Klik tombol Detail pada baris pertama
    cy.get('table tbody tr').first().within(() => {
      cy.get('button[title="Lihat Rincian Detail"]').click({ force: true });
    });

    // Verifikasi modal pop-up terbuka dengan profil khusus User
    cy.get('#modal-profile-title', { timeout: 10000 })
      .should('be.visible')
      .and('contain', 'Profil Pengguna Bugar.in');

    // Verifikasi metrik khas User reguler
    cy.contains('Poin Gerak').should('be.visible');
    cy.contains('Target Mingguan').should('be.visible');
    cy.contains('Level Kebugaran').should('be.visible');
    cy.contains('Status Sparring').should('be.visible');
    cy.contains('Komunitas yang Diikuti').should('be.visible');

    // Tutup modal
    cy.get('div[role="dialog"]').contains('button', 'Tutup').click({ force: true });
    cy.get('#modal-profile-title').should('not.be.visible');
  });

  it('7. Menguji Modal Pop-up Rincian Detail khusus role Penyedia Komunitas', () => {
    cy.visit('/admin/users');

    // Filter role 'community_owner'
    cy.get('select[x-model="role"]').select('community_owner');
    cy.wait(800);

    // Pastikan baris data penyedia tersedia
    cy.get('table tbody').then(($tbody) => {
      if ($tbody.find('button[title="Lihat Rincian Detail"]').length > 0) {
        cy.get('table tbody tr').first().within(() => {
          cy.get('button[title="Lihat Rincian Detail"]').click({ force: true });
        });

        // Verifikasi judul modal khusus Penyelenggara Komunitas
        cy.get('#modal-profile-title', { timeout: 10000 })
          .should('be.visible')
          .and('contain', 'Profil Penyelenggara Komunitas');

        // Verifikasi elemen khusus penyedia
        cy.contains('Komunitas yang Dikelola').should('be.visible');
        cy.contains('Status Penyelenggara').should('be.visible');

        // Pastikan metrik user biasa TIDAK muncul di modal penyedia
        cy.contains('Level Kebugaran').should('not.exist');

        // Tutup modal
        cy.get('div[role="dialog"]').contains('button', 'Tutup').click({ force: true });
        cy.get('#modal-profile-title').should('not.be.visible');
      }
    });
  });

  it('8. Menguji Modal Pop-up Rincian Detail khusus role Administrator', () => {
    cy.visit('/admin/users');

    // Filter role 'admin'
    cy.get('select[x-model="role"]').select('admin');
    cy.wait(800);

    // Buka detail akun Admin
    cy.get('table tbody tr').first().within(() => {
      cy.get('button[title="Lihat Rincian Detail"]').click({ force: true });
    });

    // Verifikasi judul modal khusus Administrator
    cy.get('#modal-profile-title', { timeout: 10000 })
      .should('be.visible')
      .and('contain', 'Profil Administrator Console');

    // Verifikasi informasi kredensial & hak akses admin
    cy.contains('Akses Tata Kelola Platform & Integritas Operasional').should('be.visible');
    cy.contains('Email Terverifikasi').should('be.visible');
    cy.contains('Console Ops Superadmin').should('be.visible');

    // Pastikan section komunitas dan poin gerak TIDAK ada untuk admin
    cy.contains('Target Mingguan').should('not.exist');
    cy.contains('Komunitas yang Dikelola').should('not.exist');

    // Tutup modal
    cy.get('div[role="dialog"]').contains('button', 'Tutup').click({ force: true });
    cy.get('#modal-profile-title').should('not.be.visible');
  });

  it('9. Menguji pagination responsif via AJAX', () => {
    cy.visit('/admin/users');

    // Periksa apakah pagination bar memiliki tautan halaman selanjutnya (halaman 2)
    cy.get('.pagination-container').then(($pagination) => {
      // Prioritaskan link page=2 yang visible (tampil di viewport desktop/mobile aktif)
      const visibleLinks = $pagination.find('a[href*="page=2"]:visible');

      if (visibleLinks.length > 0) {
        cy.wrap(visibleLinks.first()).click();
        cy.wait(800);

        // Verifikasi URL mencantumkan page=2 tanpa full page reload
        cy.url().should('include', 'page=2');
        cy.get('table tbody tr').should('have.length.at.least', 1);
      } else {
        // Fallback jika berada di kontainer responsive yang tidak aktif di viewport saat ini
        const anyLinks = $pagination.find('a[href*="page=2"]');
        if (anyLinks.length > 0) {
          cy.wrap(anyLinks.first()).click({ force: true });
          cy.wait(800);

          cy.url().should('include', 'page=2');
          cy.get('table tbody tr').should('have.length.at.least', 1);
        }
      }
    });
  });
});
