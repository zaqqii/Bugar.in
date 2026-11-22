// ***********************************************************
// This support/e2e.js file is processed and loaded automatically
// before your test files.
// ***********************************************************

// Import custom commands
import './commands'

// Tangani uncaught exception agar script non-fatal (seperti ResizeObserver atau Alpine animation)
// tidak menyebabkan test Cypress gagal secara keliru.
Cypress.on('uncaught:exception', (err, runnable) => {
  if (
    err.message.includes('ResizeObserver') ||
    err.message.includes('Script error') ||
    err.message.includes('transition')
  ) {
    return false;
  }
  return false;
});
