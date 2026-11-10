
describe('Integration Test - Event Penyedia Komunitas', () => {

    beforeEach(() => {
        cy.loginAsCommunityOwner();
    });

    // UT-01: Menyimpan draft event
    it('UT-01 - Menyimpan draft dan menampilkannya pada daftar event', () => {
        const judulDraft = `Draft Bugar Fun Run 5K`;

        cy.intercept('POST', '**/penyedia-komunitas/events').as('saveDraft');

        cy.visit('/penyedia-komunitas/events/create');

        // Isi judul event
        cy.get('#title')
            .should('be.visible')
            .type(judulDraft);

        // Simpan draft
        cy.contains('button', 'Simpan Draft')
            .first()
            .click();

        // Periksa respons server
        cy.wait('@saveDraft').then(({ response }) => {
            cy.log(`Save draft response: ${response?.statusCode}`);

            expect(response, 'Respons server').to.exist;
            expect([200, 302, 303]).to.include(response.statusCode);
        });

        // Pastikan kembali ke halaman daftar event
        cy.location('pathname')
            .should('eq', '/penyedia-komunitas/events');

        // Buka tab draft
        cy.visit('/penyedia-komunitas/events?status=draft');

        // Pastikan event muncul dengan status Draft
        cy.contains('tbody tr', judulDraft)
            .should('be.visible')
            .within(() => {
                cy.contains('Draft').should('be.visible');
            });
    });


    // UT-02: Mempublikasikan event gratis
    it('UT-02 - Mempublikasikan event dan menampilkannya pada daftar event', () => {
        const judulEvent = `Bugar Fun Run 5K - Malang`;

        cy.intercept('POST', '**/penyedia-komunitas/events').as('publishEvent');

        cy.visit('/penyedia-komunitas/events/create');

        // STAGE 1: Informasi Event
        cy.get('#title')
            .should('be.visible')
            .type(judulEvent);

        cy.get('#description')
            .type('Kegiatan lari santai sejauh 5 kilometer yang terbuka bagi anggota komunitas dan masyarakat umum. Event ini bertujuan untuk meningkatkan kebugaran jasmani serta membangun kebiasaan hidup aktif dan sehat.');

        // Gunakan template rundown
        cy.get('#eventForm > div[x-show="step === 1"]')
            .within(() => {
                cy.contains('button', 'Gunakan Template').click();
            });

        // Upload gambar dari folder OneDrive
        cy.get('#cover_image')
            .selectFile(
                'C:/Users/L E N O V O/OneDrive/Pictures/2040762329711984.jpe',
                { force: true }
            );

        // Periksa preview gambar
        cy.get('img[alt="Cover Preview"]')
            .should('be.visible');

        // Lanjut ke stage 2
        cy.get('#eventForm > div[x-show="step === 1"]')
            .contains('button', 'Lanjut ke Jadwal & Lokasi')
            .click();


        // STAGE 2: Jadwal dan Lokasi
        cy.get('#eventForm > div[x-show="step === 2"]')
            .should('be.visible');

        cy.get('#event_date').type('2030-12-20');
        cy.get('#registration_close_at').type('2030-12-19');
        cy.get('#start_time').type('07:00');
        cy.get('#end_time').type('09:00');
        cy.get('#timezone').select('WIB');
        cy.get('#location').type('Lapangan Kota');
        cy.get('#maps_url').type('https://maps.google.com/');

        // Lanjut ke stage 3
        cy.get('#eventForm > div[x-show="step === 2"]')
            .contains('button', 'Lanjut ke Biaya & Kuota')
            .click();


        // STAGE 3: Biaya dan Kuota
        cy.get('#eventForm > div[x-show="step === 3"]')
            .should('be.visible');

        // Pilih event gratis
        cy.contains('div', 'Gratis (Free Entry)')
            .should('be.visible')
            .click();

        // Pilih kuota terbatas
        cy.get('input[name="capacity_type"][value="limited"]')
            .check({ force: true });

        cy.get('#capacity')
            .clear()
            .type('30');

        // Gunakan template persyaratan
        cy.get('#eventForm > div[x-show="step === 3"]')
            .within(() => {
                cy.contains('button', 'Gunakan Template').click();
            });

        // Isi kontak panitia
        cy.get('#organizer_contact')
            .type('081234567890');

        // Publikasikan event
        cy.contains('button', 'Publikasikan Event Sekarang')
            .click();

        // Periksa respons server
        cy.wait('@publishEvent').then(({ response }) => {
            cy.log(`Publish response: ${response?.statusCode}`);

            expect(response, 'Respons server').to.exist;
            expect([200, 302, 303]).to.include(response.statusCode);
        });

        // Pastikan kembali ke halaman daftar event
        cy.location('pathname')
            .should('eq', '/penyedia-komunitas/events');

        // Buka tab event yang dipublikasikan
        cy.visit('/penyedia-komunitas/events?status=published');

        // Pastikan event muncul dengan status Pendaftaran Dibuka
        cy.contains('tbody tr', judulEvent)
            .should('be.visible')
            .within(() => {
                cy.contains('Pendaftaran Dibuka')
                    .should('be.visible');
            });
    });

});
