describe('Registrasi Penyedia Komunitas Bugar.in', () => {
  it('daftar komunitas baru berhasil', () => {
    cy.visit('/auth/penyedia-komunitas/register')

    cy.get('form').first().within(() => {
      cy.get('input[type="text"]').first().type('Futbol FC') // nama komunitas
      cy.get('select').select('Futsal / Football')
      cy.get('input[placeholder="Eks: Jakarta Selatan, DKI"]').type('Malang, Jawa Timur')
      cy.get('input[placeholder="Eks: Budi Santoso"]').type('Budi Santoso')
      cy.get('input[type="email"]').type('futbolfc@gmail.com')
      cy.get('input[placeholder="+62 812-3456-7890"]').type('081234567890')
      cy.get('input[type="password"]').type('hai12345')
      cy.get('input[type="checkbox"]').check({ force: true })
    })

    cy.screenshot('register-penyedia-terisi', { capture: 'viewport' })
    cy.contains('button', 'Daftarkan Komunitas Sekarang').click()

    cy.url({ timeout: 10000 }).should('not.include', '/auth/penyedia-komunitas/register')
    cy.screenshot('register-penyedia-berhasil', { capture: 'viewport' })
  })
})