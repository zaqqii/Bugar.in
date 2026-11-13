// ***********************************************
// Cypress Custom Commands for Bugar.in
// ***********************************************

/**
 * Command untuk melakukan login sebagai User reguler Bugar.in
 * Menggunakan credential akun uji default atau parameter yang dioper
 */
Cypress.Commands.add('loginUser', (email, password) => {
  const userEmail = email || 'karahmaa25@gmail.com';
  const userPassword = password || '12345678';

  cy.visit('/auth/user/register?mode=login');

  // Input email dan kata sandi pada form login
  cy.get('#login-email', { timeout: 10000 }).should('be.visible').clear().type(userEmail);
  cy.get('#login-password').should('be.visible').clear().type(userPassword);
  cy.get('#btn-masuk-sekarang').should('be.visible').click();

  // Pastikan berhasil masuk ke dashboard pengguna
  cy.url({ timeout: 15000 }).should('include', '/user/dashboard');
});

/**
 * Command untuk logout dari akun pengguna melalui tombol di header
 */
Cypress.Commands.add('logoutUser', () => {
  cy.get('#btn-user-logout', { timeout: 8000 }).should('be.visible').click();
  cy.url({ timeout: 10000 }).should('not.include', '/user/dashboard');
});

/**
 * Command untuk navigasi ke section tertentu di dashboard user via sidebar
 */
Cypress.Commands.add('openSidebarSection', (sectionName) => {
  const selector = `#nav-${sectionName}`;
  cy.get(selector, { timeout: 8000 }).should('exist').click();
  cy.get(selector).should('have.class', 'nav-item--active');
});
