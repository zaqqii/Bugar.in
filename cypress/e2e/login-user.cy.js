describe('Login Pengguna Bugar.in', () => {
  it('login berhasil dengan akun yang sudah ada', () => {
    cy.visit('/auth/user/register?mode=login')

    cy.get('#login-email').type('karahmaa25@gmail.com')
    cy.get('#login-password').type('12345678')
    cy.get('#btn-masuk-sekarang').click()

    cy.get('body').then(($b) => {
      const pesan = $b.find('.auth-error-alert').text().trim()
      cy.log('PESAN ERROR: ' + (pesan || '(tidak ada)'))
    })
    cy.screenshot('login-user-hasil', { capture: 'viewport' })

    cy.url({ timeout: 10000 }).should('include', '/user/dashboard')
  })
})