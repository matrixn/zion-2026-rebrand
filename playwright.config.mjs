import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  snapshotPathTemplate: '{testDir}/snapshots/{arg}{ext}',
  forbidOnly: Boolean(process.env.CI),
  use: {
    baseURL: process.env.ZION_TEST_URL || 'http://localhost:8917',
    browserName: 'chromium',
    colorScheme: 'dark',
    reducedMotion: 'reduce',
    trace: 'retain-on-failure',
  },
});
