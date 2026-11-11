import { test, expect } from '@playwright/test';
import { createHash } from 'node:crypto';

const fingerprint = async canvas => createHash('sha256').update(await canvas.evaluate(e => e.toDataURL())).digest('hex');

for (const width of [390, 1440]) {
  test(`Continuum transforms through scroll and explicit controls at ${width}px`, async ({ browser, baseURL }) => {
    const context = await browser.newContext({ baseURL, viewport: { width, height: 844 }, reducedMotion: 'no-preference' });
    try {
      const page = await context.newPage(), errors = [];
      page.on('pageerror', error => errors.push(error.message));
      await page.goto('/despre-noi/');
      await expect(page.locator('.zion-about')).toHaveClass(/is-spatial/);
      const hero = page.locator('.zion-about-note');
      await hero.scrollIntoViewIfNeeded();
      const canvas = hero.locator('canvas');
      await expect(canvas).toHaveAttribute('data-render-mode', 'animated');
      const image = () => fingerprint(canvas);
      await expect.poll(async () => canvas.evaluate(e => {
        const data = e.getContext('2d').getImageData(0, 0, e.width, e.height).data;
        return data.some((value, index) => index % 4 === 3 && value > 0);
      })).toBe(true);
      const initial = await image();
      await expect.poll(image).not.toBe(initial);
      if (width === 1440) {
        const transform = await hero.evaluate(e => getComputedStyle(e).transform);
        await hero.hover({ position: { x: 20, y: 100 } });
        await expect.poll(() => hero.evaluate(e => getComputedStyle(e).transform)).not.toBe(transform);
      }
      await hero.getByRole('button', { name: 'Pauză animație', exact: true }).click();
      const paused = await image();
      await page.waitForTimeout(180);
      expect(await image()).toBe(paused);
      await hero.getByRole('button', { name: 'Reia animația', exact: true }).click();
      await expect.poll(image).not.toBe(paused);

      const forge = page.locator('.zion-about-forge');
      const geometry = await forge.evaluate(e => ({ top: e.getBoundingClientRect().top + scrollY, height: e.offsetHeight }));
      await page.evaluate(top => scrollTo(0, top - 115), geometry.top);
      await expect(forge).toHaveAttribute('data-phase', '0');
      await page.evaluate(g => scrollTo(0, g.top + g.height - innerHeight + 8), geometry);
      await expect(forge).toHaveAttribute('data-phase', '3');
      const stage = forge.locator('.zion-forge-stage');
      const stageRect = await stage.boundingBox();
      expect(stageRect.y).toBeGreaterThanOrEqual(0);
      expect(stageRect.y + stageRect.height).toBeLessThanOrEqual(845);
      await stage.getByRole('button', { name: 'Pauză animație', exact: true }).click();
      for (const [index, name] of ['Schiță', 'Straturi', 'Obiect', 'Cod'].entries()) {
        const button = stage.getByRole('button', { name: `0${index + 1} / ${name}`, exact: true });
        await button.focus(); await button.press('Enter');
        await expect(button).toBeFocused();
        await expect(button).toHaveAttribute('aria-pressed', 'true');
        await expect(forge).toHaveAttribute('data-phase', String(index));
        await expect(forge.locator('.zion-forge-phase').nth(index)).toBeVisible();
        expect((await button.boundingBox()).height).toBeGreaterThanOrEqual(44);
      }
      const final = await fingerprint(forge.locator('canvas'));
      await page.waitForTimeout(150);
      expect(await fingerprint(forge.locator('canvas'))).toBe(final);
      await stage.getByRole('button', { name: 'Reia animația', exact: true }).click();
      await page.locator('.zion-about-end').scrollIntoViewIfNeeded();
      await page.waitForTimeout(180);
      const offscreen = await fingerprint(forge.locator('canvas'));
      await page.waitForTimeout(180);
      expect(await fingerprint(forge.locator('canvas'))).toBe(offscreen);
      expect(await page.evaluate(() => document.documentElement.scrollWidth - innerWidth)).toBe(0);
      expect(errors).toEqual([]);
    } finally { await context.close(); }
  });
}

test('Continuum respects live reduced motion and stays conditional', async ({ browser, baseURL }) => {
  const context = await browser.newContext({ baseURL, viewport: { width: 1440, height: 950 }, reducedMotion: 'no-preference' });
  try {
    const page = await context.newPage();
    await page.goto('/magazin-3d/');
    await expect(page.locator('script#zion-continuum-js')).toHaveCount(0);
    await page.goto('/despre-noi/');
    await expect(page.locator('script#zion-continuum-js')).toHaveCount(1);
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await expect(page.locator('.zion-about')).toHaveClass(/is-spatial-static/);
    const canvas = page.locator('.zion-about-note canvas');
    await expect(canvas).toHaveAttribute('data-render-mode', 'static');
    const frozen = await fingerprint(canvas);
    await page.waitForTimeout(180);
    expect(await fingerprint(canvas)).toBe(frozen);
    for (const phase of await page.locator('.zion-forge-phase').all()) await expect(phase).toBeVisible();
    for (const button of await page.locator('.zion-spatial-pause').all()) await expect(button).toBeHidden();
    expect(await page.locator('.zion-about-forge').evaluate(e => e.offsetHeight)).toBeLessThan(1500);
    await page.emulateMedia({ reducedMotion: 'no-preference' });
    await expect.poll(() => fingerprint(canvas)).not.toBe(frozen);
    // The existing theme setting is also honored when the OS permits animation.
    await page.evaluate(() => document.body.classList.add('zion-motion-reduced'));
    await expect(canvas).toHaveAttribute('data-render-mode', 'static');
    await expect(page.locator('.zion-about')).toHaveClass(/is-spatial-static/);
  } finally { await context.close(); }
});
