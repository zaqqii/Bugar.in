import { defineConfig } from "cypress";

export default defineConfig({
  screenshotOnRunFailure: false,
  video: false,
  pageLoadTimeout: 120000,
  defaultCommandTimeout: 20000,
  requestTimeout: 20000,
  responseTimeout: 20000,
  e2e: {
    baseUrl: "http://127.0.0.1:8000",
    setupNodeEvents(on, config) {},
  },
});
