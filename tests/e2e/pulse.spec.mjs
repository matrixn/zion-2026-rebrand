import { test, expect } from '@playwright/test';

test.beforeAll(async ({ playwright }) => {
  const { execFileSync } = await import('node:child_process');
  execFileSync('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'eval-file', 'wp-content/zion-scripts/seed-pulse.php'], { stdio: 'pipe' });
});

for (const width of [390, 768, 1024, 1440]) {
  test(`Pulse layout, puzzle and modules at ${width}px`, async ({ page }) => {
    await page.setViewportSize({ width, height: 900 });
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    const response = await page.goto('/zion-pulse/');
    expect(response.status()).toBe(200);
    await expect(page.locator('main h1')).toHaveCount(1);
    await expect(page).toHaveTitle('Automatizări ESP32 & Arduino | Sisteme Interactive | Zion Pulse');
    await page.getByRole('link', { name: 'Descoperă ce putem construi ↓', exact: true }).click();
    const anchor = await page.evaluate(() => ({ heading: document.querySelector('#ce-construim h2').getBoundingClientRect().top, header: document.querySelector('.wp-site-blocks > header').getBoundingClientRect().bottom }));
    expect(anchor.heading).toBeGreaterThanOrEqual(anchor.header);
    const demo = page.locator('[data-pulse-demo]');
    await expect(demo).toHaveAttribute('data-enhanced', 'true');
    await demo.getByRole('button', { name: /Verde/ }).click();
    await expect(demo.locator('[data-pulse-progress]')).toHaveText('1 / 5');
    await demo.getByRole('button', { name: /Roșu/ }).click();
    await expect(demo.locator('[data-pulse-progress]')).toHaveText('0 / 5');
    await expect(demo.getByRole('status')).toContainText('greșită');
    for (const color of ['Verde', 'Galben', 'Albastru', 'Roșu', 'Alb']) {
      await demo.getByRole('button', { name: new RegExp(`^${color} `) }).click();
    }
    await expect(demo.locator('[data-pulse-door]')).toHaveText('Deblocată');
    await expect(demo.locator('[aria-pressed="true"]')).toHaveCount(5);
    await demo.getByRole('button', { name: 'RESET', exact: true }).click();
    await expect(demo.locator('[data-pulse-door]')).toHaveText('Blocată');
    await demo.getByRole('button', { name: 'FORCE SOLVE', exact: true }).click();
    await expect(demo.locator('[data-pulse-progress]')).toHaveText('5 / 5');
    await expect(demo.getByRole('status')).toContainText('FORCE SOLVE demonstrativ');
    await page.getByRole('button', { name: 'Senzori NFC/RFID', exact: false }).click();
    await expect(page.locator('[data-pulse-module-description]')).toContainText('cititoare');
    await page.locator('.zion-pulse-faq summary').first().press('Enter');
    await expect(page.locator('.zion-pulse-faq details').first()).toHaveAttribute('open', '');
    await expect(page.locator('.zion-pulse-final a[href^="https://wa.me/"]')).toHaveAttribute('href', /Zion%20Pulse/);
    const service = await page.locator('script[type="application/ld+json"]').allTextContents();
    expect(service.some(value => JSON.parse(value)['@type'] === 'Service')).toBe(true);
    expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
    expect(errors).toEqual([]);
  });
}

test('Pulse keyboard controls, reduced motion and isolation', async ({ page }) => {
  await page.emulateMedia({ reducedMotion: 'reduce' });
  const requests = [];
  await page.goto('/zion-pulse/');
  await page.waitForLoadState('networkidle');
  page.on('request', request => requests.push(request.url()));
  const demo = page.locator('[data-pulse-demo]');
  for (const color of ['Verde', 'Galben', 'Albastru', 'Roșu', 'Alb']) {
    await demo.getByRole('button', { name: new RegExp(`^${color} `) }).focus();
    await page.keyboard.press('Space');
  }
  await expect(demo.locator('[data-pulse-door]')).toHaveText('Deblocată');
  await expect(page.locator('[data-pulse-motion]')).toBeDisabled();
  await expect(page.locator('.zion-pulse-signal')).toHaveCSS('animation-name', 'none');
  expect(requests).toEqual([]);
  await page.goto('/');
  await expect(page.locator('link#zion-pulse-css')).toHaveCount(0);
  await expect(page.locator('script#zion-pulse-js')).toHaveCount(0);
});

test('Pulse remains readable without JavaScript', async ({ browser }) => {
  const context = await browser.newContext({ javaScriptEnabled: false });
  const page = await context.newPage();
  await page.goto('/zion-pulse/');
  await expect(page.locator('main h1')).toContainText('Controlată inteligent.');
  await expect(page.locator('[data-pulse-fallback]')).toBeVisible();
  await expect(page.locator('[data-pulse-reset]')).toBeDisabled();
  await expect(page.locator('.zion-pulse-final a').first()).toHaveAttribute('href', '/contact/');
  await context.close();
});
