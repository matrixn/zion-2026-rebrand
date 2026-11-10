import { test, expect } from '@playwright/test';

test('the native desktop submenu is keyboard accessible', async ({ page }) => {
  await page.setViewportSize({ width: 1440, height: 850 });
  await page.goto('/');
  const navigation = page.locator('.zion-header .zion-navigation');
  const submenu = navigation.getByRole('button', { name: /Software/ }).first();
  await expect(navigation.locator('.wp-block-navigation__responsive-container-open')).toBeHidden();
  await submenu.focus();
  await page.keyboard.press('Enter');
  const shop = navigation.getByRole('link', { name: 'Magazin Software' });
  await expect(shop).toBeVisible();
  await page.keyboard.press('Tab');
  await expect(shop).toBeFocused();
  await page.keyboard.press('Escape');
  await expect(shop).toBeHidden();
});

for (const width of [390, 1440]) {
  test(`native product search, account and mini-cart at ${width}px`, async ({ page }) => {
    await page.setViewportSize({ width, height: 850 });
    const requests = [];
    page.on('request', request => requests.push(new URL(request.url()).pathname));
    await page.goto('/magazin-software/', { waitUntil: 'domcontentloaded' });
    const timings = await page.evaluate(() => {
      const navigation = performance.getEntriesByType('navigation')[0];
      return { ttfb: navigation.responseStart, dcl: navigation.domContentLoadedEventStart };
    });
    expect(timings.ttfb).toBeLessThan(5000);
    expect(timings.dcl).toBeLessThan(8000);
    const header = page.locator('.zion-header');
    await expect(header.locator('.wc-block-mini-cart__button')).toHaveCount(1);
    await expect(header.getByRole('link', { name: 'Autentificare' })).toHaveAttribute('href', /\/my-account\/$/);
    expect(requests.filter(path => /\/wc\/store\/v1\/products\/?$/.test(path))).toHaveLength(0);
    const trigger = header.locator('.zion-header-search summary');
    await trigger.focus();
    await page.keyboard.press('Enter');
    const search = header.getByRole('searchbox', { name: 'Caută produse' });
    await expect(search).toBeVisible();
    await search.fill('Zion');
    await search.press('Enter');
    await expect(page).toHaveURL(/post_type=product/);
    await page.waitForLoadState('networkidle');
    const card = page.locator('.zion-product-card').filter({ has: page.getByRole('heading', { name: 'Zion Catalog Tools', exact: true }) });
    await expect(card).toBeVisible();
    await card.getByRole('button', { name: /Adaugă în coș/i }).click();
    const cart = header.getByRole('button', { name: /Număr de produse în coș: 1/ });
    await expect(cart).toBeVisible();
    await expect(header.locator('.wc-block-mini-cart__badge')).toHaveText('1');
    await cart.focus();
    await page.keyboard.press('Enter');
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await expect(dialog.getByRole('link', { name: 'Zion Catalog Tools', exact: true })).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(dialog).toBeHidden();
    await expect(page.locator('.wc-block-mini-cart__drawer')).toHaveCSS('visibility', 'hidden');
    expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
  });
}
