import { test, expect } from '@playwright/test';
import { readFile } from 'node:fs/promises';

const theme = JSON.parse(await readFile(new URL('../../themes/zion-theme/theme.json', import.meta.url), 'utf8'));
const tokens = theme.settings.color.palette.map(({ slug, color }) => `--wp--preset--color--${slug}:${color}`).join(';');
const key = 'zion-notice-synthetic-fixture';
async function fixture(page, baseURL) {
  const url = new URL(baseURL);
  expect(['localhost', '127.0.0.1', '[::1]']).toContain(url.hostname);
  expect(url.port).toBe('8917');
  await page.route('**/zion-notification-fixture*', route => route.fulfill({ contentType: 'text/html', body: `<!doctype html><html lang="ro"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>:root{${tokens}}body{margin:0}</style><link rel="stylesheet" href="/wp-content/themes/zion-theme/assets/dist/zion.css"></head><body><aside class="zion-migration-announcement" data-zion-notice-key="${key}" style="display:flex;padding:10px 16px"><p>Anunț sintetic de migrare pentru test.</p><a href="https://example.invalid/contact">Contact de test</a></aside><main><a href="/zion-notification-fixture-next">Următoarea pagină de test</a></main><script src="/wp-content/themes/zion-theme/assets/blocks/announcements.js"></script></body></html>` }));
  await page.goto('/zion-notification-fixture');
}
const toggle = page => page.locator('.zion-migration-announcement__toggle');
const panel = page => page.locator('.zion-migration-announcement__panel');

test('mobile notification remains accessible while sliding open and closed', async ({ page, baseURL }) => {
  const errors = []; page.on('pageerror', error => errors.push(error.message));
  await page.setViewportSize({ width: 390, height: 844 });
  await page.emulateMedia({ reducedMotion: 'no-preference' });
  await fixture(page, baseURL);
  await expect(toggle(page)).toHaveAttribute('aria-expanded', 'true');
  await expect(panel(page)).toHaveCSS('transition-duration', '0.26s');
  expect((await toggle(page).boundingBox()).height).toBeGreaterThanOrEqual(44);
  await toggle(page).click();
  await expect(toggle(page)).toBeVisible();
  await expect(toggle(page)).toHaveAttribute('aria-expanded', 'false');
  await expect(panel(page)).toHaveAttribute('inert', '');
  await expect.poll(async () => (await panel(page).boundingBox()).height).toBe(0);
  await toggle(page).press('Tab');
  await expect(page.getByRole('link', { name: 'Următoarea pagină de test' })).toBeFocused();
  await toggle(page).focus();
  await toggle(page).press('Enter');
  await expect(toggle(page)).toHaveAttribute('aria-expanded', 'true');
  await expect(panel(page)).not.toHaveAttribute('inert', '');
  await expect(page.getByRole('link', { name: 'Contact de test' })).toBeVisible();
  expect(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)).toBe(false);
  expect(errors).toEqual([]);
});

test('both closed and open choices survive navigation, reload and history', async ({ page, baseURL }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await fixture(page, baseURL);
  await toggle(page).click();
  await page.getByRole('link', { name: 'Următoarea pagină de test' }).click();
  await expect(toggle(page)).toHaveAttribute('aria-expanded', 'false');
  await page.reload();
  await expect(toggle(page)).toHaveAttribute('aria-expanded', 'false');
  await page.goBack();
  await expect(toggle(page)).toHaveAttribute('aria-expanded', 'false');
  await toggle(page).click();
  await page.goForward();
  await expect(toggle(page)).toHaveAttribute('aria-expanded', 'true');
  await page.reload();
  await expect(toggle(page)).toHaveAttribute('aria-expanded', 'true');
});

for (const width of [768, 1024, 1440]) {
  test(`desktop notice stays readable at ${width}px without changing the mobile choice`, async ({ page, baseURL }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await fixture(page, baseURL);
    await toggle(page).click();
    await page.setViewportSize({ width, height: 900 });
    await expect(toggle(page)).toBeHidden();
    await expect(panel(page)).toHaveAttribute('aria-hidden', 'false');
    await expect(page.getByRole('link', { name: 'Contact de test' })).toBeVisible();
    expect(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)).toBe(false);
    await page.setViewportSize({ width: 390, height: 844 });
    await expect(toggle(page)).toHaveAttribute('aria-expanded', 'false');
    await expect(panel(page)).toHaveCSS('transition-duration', '0s');
  });
}

test('blocked storage preserves the open and close controls', async ({ page, baseURL }) => {
  await page.addInitScript(() => {
    Storage.prototype.getItem = () => { throw new Error('Synthetic storage restriction'); };
    Storage.prototype.setItem = () => { throw new Error('Synthetic storage restriction'); };
  });
  await page.setViewportSize({ width: 390, height: 844 });
  await fixture(page, baseURL);
  await toggle(page).click(); await expect(toggle(page)).toHaveAttribute('aria-expanded', 'false');
  await toggle(page).click(); await expect(toggle(page)).toHaveAttribute('aria-expanded', 'true');
});

test('without JavaScript the complete original notice remains readable', async ({ browser, baseURL }) => {
  const context = await browser.newContext({ javaScriptEnabled: false, viewport: { width: 390, height: 844 } });
  try {
    const page = await context.newPage(); await fixture(page, baseURL);
    await expect(page.getByText('Anunț sintetic de migrare pentru test.')).toBeVisible();
    await expect(page.getByRole('link', { name: 'Contact de test' })).toBeVisible();
    await expect(toggle(page)).toHaveCount(0);
  } finally { await context.close(); }
});
