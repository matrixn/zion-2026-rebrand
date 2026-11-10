import { test, expect } from '@playwright/test';

for (const width of [390, 768, 1024, 1440]) {
  test(`homepage fits at ${width}px @visual`, async ({ page }) => {
    await page.setViewportSize({ width, height: 850 });
    await page.goto('/');
    await expect(page.getByRole('heading', { level: 1, name: /ideile devin realitate/i })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Zion Catalog Tools', exact: true }).last()).toBeVisible();
    expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);

    if (width === 390 || width === 1440) {
      const images = page.locator('.zion-featured img');
      await expect(images).toHaveCount(4);
      await images.evaluateAll(elements => {
        for (const image of elements) image.loading = 'eager';
      });
      await expect.poll(() => images.evaluateAll(elements =>
        elements.every(image => image.complete && image.naturalWidth > 0),
      )).toBe(true);
      await expect(page).toHaveScreenshot(`homepage-${width}.png`, {
        fullPage: true,
        maxDiffPixelRatio: 0.01,
      });
    }
  });
}

test('mobile navigation opens and reaches the 3D storefront', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 850 });
  await page.goto('/');
  const nav = page.locator('header nav');
  await nav.locator('.wp-block-navigation__responsive-container-open').click();
  await expect(nav.getByRole('dialog')).toBeVisible();
  await nav.getByRole('link', { name: 'Magazin 3D' }).click();
  await expect(page.getByRole('heading', { level: 1, name: 'Magazin 3D' })).toBeVisible();
});

test('3D storefront has a stable product grid @visual', async ({ page }) => {
  await page.setViewportSize({ width: 1024, height: 850 });
  await page.goto('/magazin-3d/');
  await expect(page.getByRole('heading', { name: 'Suport modular 3D', exact: true })).toBeVisible();
  await expect(page).toHaveScreenshot('catalog-3d-1024.png', {
    fullPage: true,
    maxDiffPixelRatio: 0.01,
  });
});

test('both storefronts are isolated and a software product reaches the native cart', async ({ page }) => {
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));

  await page.goto('/magazin-3d/');
  await expect(page.getByRole('heading', { name: 'Suport modular 3D', exact: true })).toBeVisible();
  await expect(page.getByRole('heading', { name: 'Zion Catalog Tools', exact: true })).toHaveCount(0);

  await page.goto('/magazin-software/');
  await expect(page.getByRole('heading', { name: 'Suport modular 3D', exact: true })).toHaveCount(0);
  await page.getByRole('heading', { name: 'Zion Catalog Tools', exact: true }).getByRole('link').click();
  await page.getByRole('button', { name: 'Add to cart', exact: true }).click();
  await page.getByRole('link', { name: /view cart/i }).first().click();
  await expect(page.getByRole('link', { name: 'Zion Catalog Tools' })).toBeVisible();
  await page.getByRole('link', { name: /proceed to checkout/i }).click();
  await expect(page).toHaveURL(/\/checkout\/?$/);
  await expect(page.getByRole('form', { name: 'Checkout', exact: true })).toBeVisible();
  expect(errors).toEqual([]);
});
