Cypress.Commands.add('loginAsCommunityOwner', () => {

    cy.session('community-owner', () => {

        // Buka halaman login
        cy.visit('/auth/penyedia-komunitas/login');

        // Pastikan form login tersedia
        cy.get('#form-login').should('be.visible');
        cy.get('#slide-login').should('be.visible');

        // Pantau proses login
        cy.intercept('POST', '**/auth/penyedia-komunitas/login')
            .as('loginRequest');

        // Isi email
        cy.get('#login-email')
            .should('be.visible')
            .clear()
            .type('futbolfc@gmail.com');

        // Isi password
        cy.get('#login-password')
            .should('be.visible')
            .clear()
            .type('hai12345', { log: false });

        // Klik tombol masuk
        cy.get('#btn-masuk-host')
            .should('be.enabled')
            .click();

        // Periksa respons backend
        cy.wait('@loginRequest').then(({ response }) => {

            expect(response, 'Respons dari server').to.exist;

            const status = response.statusCode;
            const destination = response.headers.location || '';

            cy.log('Status login: ' + status);
            cy.log('Tujuan redirect: ' + destination);

            expect(status).to.be.oneOf([302, 303]);

            // Jika kembali ke halaman login, berarti login ditolak
            if (destination.includes('/auth/penyedia-komunitas/login')) {
                throw new Error(
                    'Login ditolak oleh server. Periksa email, password, dan role akun.'
                );
            }
        });

        // Tes akses dashboard secara langsung
        cy.visit('/penyedia-komunitas/dashboard');

        cy.location('pathname')
            .should('eq', '/penyedia-komunitas/dashboard');

    }, {
        validate() {
            // Memastikan sesi masih berlaku
            cy.visit('/penyedia-komunitas/dashboard');

            cy.location('pathname')
                .should('eq', '/penyedia-komunitas/dashboard');
        }
    });

});