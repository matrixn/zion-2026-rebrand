import { test, expect } from '@playwright/test';

for (const width of [390, 768, 1024, 1440]) {
  test(`Pulse homepage card and native navigation at ${width}px`, async ({ page }) => {
    await page.setViewportSize({ width, height: 950 });
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.goto('/');
    await expect(page.locator('.zion-home-service')).toHaveCount(8);
    await expect(page.locator('.zion-home-services h2')).toHaveText('Opt puncte de plecare.');
    const card = page.locator('.zion-home-service-pulse');
    await expect(card).toHaveCount(1);
    await expect(card.locator('svg.zion-pulse-circuit')).toBeVisible();
    const copyBox = await card.locator('.zion-home-pulse-copy').boundingBox();
    const circuitBox = await card.locator('.zion-pulse-preview').boundingBox();
    if (width >= 700) {
      expect(circuitBox.x).toBeGreaterThanOrEqual(copyBox.x + copyBox.width);
      expect(Math.abs(circuitBox.y - copyBox.y)).toBeLessThan(100);
    } else {
      expect(circuitBox.y).toBeGreaterThanOrEqual(copyBox.y + copyBox.height);
    }
    await expect(card.getByRole('link', { name: 'Zion Pulse', exact: true })).toHaveAttribute('href', /\/zion-pulse\/$/);
    await expect(card.locator('[data-pulse-motion]')).toBeDisabled();
    await expect(card.locator('.zion-pulse-signal')).toHaveCSS('animation-name', 'none');
    await expect(page.locator('link#zion-pulse-css')).toHaveCount(1);
    await expect(page.locator('script#zion-pulse-js')).toHaveCount(1);
    expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
    await card.getByRole('link', { name: 'Zion Pulse', exact: true }).click();
    await expect(page).toHaveURL(/\/zion-pulse\/$/);
    const header = page.locator('.zion-header');
    const menu = header.locator('.wp-block-navigation__responsive-container-open');
    if (await menu.isVisible()) await menu.click();
    const services = header.getByRole('button', { name: /Servicii/ });
    await services.focus();
    if (await services.getAttribute('aria-expanded') !== 'true') await services.press('Enter');
    const pulse = header.getByRole('link', { name: 'Zion Pulse · Automatizări', exact: true });
    await expect(pulse).toHaveAttribute('aria-current', 'page');
    await expect(pulse.locator('.zion-menu-description')).toHaveText('Electronică, senzori și automatizări.');
    await expect(pulse.locator('.zion-menu-icon--pulse')).toHaveCount(1);
    expect(errors).toEqual([]);
  });
}

test('Pulse preview pauses independently and follows the homepage master pause', async ({ page }) => {
  await page.emulateMedia({ reducedMotion: 'no-preference' });
  await page.goto('/');
  const card = page.locator('.zion-home-service-pulse');
  await card.scrollIntoViewIfNeeded();
  const scene = card.locator('[data-pulse-motion-scene]');
  await expect(scene).toHaveClass(/is-visible/);
  const pause = card.locator('[data-pulse-motion]');
  await pause.focus();
  await pause.press('Enter');
  await expect(pause).toHaveAttribute('aria-pressed', 'true');
  await expect(card.locator('.zion-pulse-signal')).toHaveCSS('animation-play-state', 'paused');
  await expect(page).toHaveURL(/\/$/);
  await pause.press('Enter');
  await expect(card.locator('.zion-pulse-signal')).toHaveCSS('animation-play-state', 'running');
  const master = page.locator('.zion-home-motion--services .zion-spatial-pause');
  await master.click();
  await expect(pause).toBeDisabled();
  await expect(scene).toHaveClass(/is-paused/);
  await master.click();
  await expect(pause).toBeEnabled();
  await card.scrollIntoViewIfNeeded();
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await expect(card.locator('.zion-pulse-signal')).toHaveCSS('animation-name', 'none');
});
