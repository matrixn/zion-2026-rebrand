import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  snapshotPathTemplate: '{testDir}/snapshots/{arg}{ext}',
  forbidOnly: Boolean(process.env.CI),
  projects: [
    { name: 'storefront', testIgnore: ['**/software.spec.mjs', '**/journal.spec.mjs', '**/control.spec.mjs', '**/classification.spec.mjs', '**/catalog-scale.spec.mjs'] },
    { name: 'software', testMatch: '**/software.spec.mjs', dependencies: ['storefront'] },
    { name: 'editorial', testMatch: '**/journal.spec.mjs', dependencies: ['software'] },
    { name: 'control', testMatch: '**/control.spec.mjs', dependencies: ['editorial'] },
    { name: 'classification', testMatch: '**/classification.spec.mjs', dependencies: ['control'] },
    ...(process.env.ZION_CATALOG_SCALE === '1' ? [{ name: 'catalog-scale', testMatch: '**/catalog-scale.spec.mjs' }] : []),
  ],
  use: {
    baseURL: process.env.ZION_TEST_URL || 'http://localhost:8917',
    browserName: 'chromium',
    ...(process.env.ZION_PLAYWRIGHT_WS_ENDPOINT ? { connectOptions: { wsEndpoint: process.env.ZION_PLAYWRIGHT_WS_ENDPOINT } } : {}),
    colorScheme: 'dark',
    reducedMotion: 'reduce',
    trace: 'retain-on-failure',
  },
});
