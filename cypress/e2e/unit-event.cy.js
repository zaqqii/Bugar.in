
describe('Pengujian Form Create Event Penyedia Komunitas', () => {

    const tanggalEvent = '2030-12-20';
    const tanggalPendaftaran = '2030-12-19';

    const gambarValid = {
        contents: Cypress.Buffer.from(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jZ1sAAAAASUVORK5CYII=',
            'base64'
        ),
        fileName: 'cover.png',
        mimeType: 'image/png'
    };

    beforeEach(() => {
        cy.loginAsCommunityOwner();
        cy.visit('/penyedia-komunitas/events/create');

        cy.get('#eventForm').should('be.visible');
        cy.get('#eventForm > div[x-show="step === 1"]')
            .should('be.visible');
    });

    // Mengisi Bagian 1 agar bisa lanjut ke Bagian 2
    function isiBagian1() {
        cy.get('#title').should('be.visible')
            .clear().type('Event Cypress Test');

        // Jenis event default adalah fun_match
        cy.get('input[name="type"]').should('have.value', 'fun_match');

        cy.get('#description').should('be.visible')
            .type('Deskripsi event untuk pengujian Cypress.');

        // Tombol template harus berasal dari Bagian 1 yang sedang terlihat
        cy.get('#eventForm > div[x-show="step === 1"]')
            .should('be.visible')
            .within(() => {
                cy.contains('button', 'Gunakan Template')
                    .should('be.visible')
                    .click();
            });

        cy.get('#rundown')
            .should('contain.value', 'Registrasi Ulang');

        cy.get('#cover_image').selectFile(gambarValid, {
            force: true
        });

        cy.get('img[alt="Cover Preview"]')
            .should('be.visible')
            .and('have.attr', 'src')
            .and('include', 'data:image');

        cy.get('#eventForm > div[x-show="step === 1"]')
            .contains('button', 'Lanjut ke Jadwal & Lokasi')
            .should('be.visible')
            .click();

        cy.get('#eventForm > div[x-show="step === 2"]')
            .should('be.visible');
    }

    // Mengisi Bagian 2 agar bisa lanjut ke Bagian 3
    function isiBagian2() {
        cy.get('#event_date').type(tanggalEvent);
        cy.get('#registration_close_at').type(tanggalPendaftaran);
        cy.get('#start_time').type('07:00');
        cy.get('#end_time').type('09:00');
        cy.get('#timezone').select('WIB');
        cy.get('#location').type('Lapangan Kota');
        cy.get('#maps_url').type('https://maps.google.com/');

        cy.get('#eventForm > div[x-show="step === 2"]')
            .contains('button', 'Lanjut ke Biaya & Kuota')
            .should('be.visible')
            .click();

        cy.get('#eventForm > div[x-show="step === 3"]')
            .should('be.visible');
    }

    // UT-01: Validasi Bagian 1
    it('UT-01 - Memvalidasi informasi dasar event yang wajib diisi', () => {
        cy.get('#eventForm > div[x-show="step === 1"]')
            .contains('button', 'Lanjut ke Jadwal & Lokasi')
            .click();

        cy.contains('Nama event wajib diisi.')
            .should('be.visible');

        cy.contains('Deskripsi event wajib diisi.')
            .should('be.visible');

        cy.contains('Cover banner event wajib diunggah.')
            .should('be.visible');

        // Tetap berada di Bagian 1
        cy.get('#eventForm > div[x-show="step === 1"]')
            .should('be.visible');
    });

    // UT-02: Template dan upload cover Bagian 1
    it('UT-02 - Menggunakan template rundown dan mengunggah cover event', () => {
        cy.get('#title').type('Event Cypress Test');
        cy.get('#description').type('Deskripsi event untuk pengujian.');

        cy.get('#eventForm > div[x-show="step === 1"]')
            .within(() => {
                cy.contains('button', 'Gunakan Template')
                    .should('be.visible')
                    .click();
            });

        cy.get('#rundown')
            .should('contain.value', 'Registrasi Ulang');

        cy.get('#cover_image').selectFile(gambarValid, {
            force: true
        });

        cy.get('img[alt="Cover Preview"]')
            .should('be.visible')
            .and('have.attr', 'src')
            .and('include', 'data:image');

        // Validasi berhasil dan berpindah ke Bagian 2
        cy.get('#eventForm > div[x-show="step === 1"]')
            .contains('button', 'Lanjut ke Jadwal & Lokasi')
            .click();

        cy.get('#eventForm > div[x-show="step === 2"]')
            .should('be.visible');
    });

    // UT-03: Validasi Bagian 2
    it('UT-03 - Memvalidasi jadwal dan lokasi sebelum melanjutkan', () => {
        isiBagian1();

        cy.get('#eventForm > div[x-show="step === 2"]')
            .contains('button', 'Lanjut ke Biaya & Kuota')
            .click();

        cy.contains('Tanggal pelaksanaan event wajib diisi.')
            .should('be.visible');

        cy.contains('Batas akhir pendaftaran wajib diisi.')
            .should('be.visible');

        cy.contains('Waktu mulai event wajib diisi.')
            .should('be.visible');

        cy.contains('Nama tempat / lokasi event wajib diisi.')
            .should('be.visible');

        // Tidak boleh berpindah ke Bagian 3
        cy.get('#eventForm > div[x-show="step === 2"]')
            .should('be.visible');
    });

    // UT-04: Pengisian jadwal dan lokasi
    it('UT-04 - Menyimpan jadwal dan lokasi untuk melanjutkan ke Bagian 3', () => {
        isiBagian1();
        isiBagian2();

        cy.get('#eventForm > div[x-show="step === 3"]')
            .should('be.visible');

        cy.get('#capacity').should('be.visible');
        cy.get('#organizer_contact').should('be.visible');
    });

    // UT-05: Pengujian kuota, biaya, dan persyaratan Bagian 3
    it('UT-05 - Menguji pilihan tiket, kuota, dan template persyaratan', () => {
        isiBagian1();
        isiBagian2();

        // Pilihan gratis
        cy.contains('div', 'Gratis (Free Entry)')
            .should('be.visible')
            .click();

        cy.get('input[name="is_paid"]')
            .should('have.value', '0');

        // Pilihan berbayar
        cy.contains('div', 'Berbayar (Tiket Berbayar)')
            .should('be.visible')
            .click();

        cy.get('input[name="is_paid"]')
            .should('have.value', '1');

        cy.get('#registration_fee_display')
            .should('be.visible')
            .type('50000');

        cy.get('input[name="registration_fee"]')
            .should('have.value', '50000');

        cy.get('#payment_method')
            .should('be.visible')
            .clear()
            .type('Transfer BCA');

        cy.get('#payment_account')
            .should('be.visible')
            .type('1234567890');

        // Kuota terbatas
        cy.get('input[name="capacity_type"][value="limited"]')
            .check();

        cy.get('#capacity')
            .should('be.visible')
            .clear()
            .type('30');

        // Kuota tidak terbatas
        cy.get('input[name="capacity_type"][value="unlimited"]')
            .check();

        cy.get('#capacity').should('not.be.visible');

        // Template persyaratan di Bagian 3
        cy.get('#eventForm > div[x-show="step === 3"]')
            .within(() => {
                cy.contains('button', 'Gunakan Template')
                    .should('be.visible')
                    .click();
            });

        cy.get('#requirements')
            .should('contain.value', 'Terbuka untuk umum');

        cy.get('#organizer_contact')
            .type('081234567890');

        cy.get('#organizer_contact')
            .should('have.value', '081234567890');
    });

    // UT-06: Simpan Draft
    it('UT-06 - Menyimpan event sebagai draft hanya dengan mengisi judul', () => {
        cy.intercept('POST', '/penyedia-komunitas/events', {
            statusCode: 302,
            headers: {
                Location: '/penyedia-komunitas/events'
            }
        }).as('simpanDraft');

        cy.get('#title')
            .should('be.visible')
            .type('Draft Cypress Test');

        cy.contains('button', 'Simpan Draft')
            .first()
            .should('be.visible')
            .click();

        cy.wait('@simpanDraft').then((interception) => {
            expect(interception.request.body).to.include('Draft Cypress Test');
            expect(interception.request.body).to.include('draft');
        });
    });

});
