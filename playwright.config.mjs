import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  snapshotPathTemplate: '{testDir}/snapshots/{arg}{ext}',
  forbidOnly: Boolean(process.env.CI),
  projects: [
    { name: 'storefront', testIgnore: ['**/control.spec.mjs', '**/classification.spec.mjs'] },
    { name: 'control', testMatch: '**/control.spec.mjs', dependencies: ['storefront'] },
    { name: 'classification', testMatch: '**/classification.spec.mjs', dependencies: ['control'] },
  ],
  use: {
    baseURL: process.env.ZION_TEST_URL || 'http://localhost:8917',
    browserName: 'chromium',
    colorScheme: 'dark',
    reducedMotion: 'reduce',
    trace: 'retain-on-failure',
  },
});
