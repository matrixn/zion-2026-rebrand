import { test, expect } from '@playwright/test';

test('public documents have server-rendered sharing and one native canonical', async ({ page, request }) => {
  for (const path of ['/', '/servicii/', '/magazin-3d/', '/magazin-software/', '/jurnal/']) {
    const response = await page.goto(path);
    expect(response.ok(), path).toBe(true);
    await expect(page.locator('head meta[name="description"]')).toHaveCount(1);
    await expect(page.locator('head link[rel="canonical"]')).toHaveCount(1);
    await expect(page.locator('head meta[property="og:image"]')).toHaveCount(1);
    await expect(page.locator('head meta[name="twitter:card"]')).toHaveAttribute('content', 'summary_large_image');
    const image = await page.locator('head meta[property="og:image"]').getAttribute('content');
    const asset = await request.get(image);
    expect(asset.ok(), image).toBe(true);
    expect(asset.headers()['content-type']).toMatch(/^image\//);
    if (path === '/') expect(image).toContain('/assets/images/social/zion-home-share.png');
    await expect(page.locator('main h1')).toHaveCount(1);
  }
});

test('catalog variants canonicalize safely and cart remains noindex', async ({ page }) => {
  await page.goto('/magazin-3d/?orderby=price&filter_material=petg');
  expect(await page.locator('head link[rel="canonical"]').getAttribute('href')).not.toMatch(/orderby|filter_material/);
  await expect(page.locator('head meta[name="robots"]')).toHaveAttribute('content', /noindex/);
  await page.goto('/cos/');
  await expect(page.locator('head meta[name="robots"]')).toHaveAttribute('content', /noindex/);
  const shop = await page.goto('/shop/');
  expect(shop.ok()).toBe(true);
  await expect(page.locator('head link[rel="canonical"]')).toHaveCount(1);
});
