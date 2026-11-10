import { test, expect } from '@playwright/test';

for (const width of [390, 768, 1024, 1440]) {
  test(`homepage fits at ${width}px @visual`, async ({ page }) => {
    await page.setViewportSize({ width, height: 850 });
    await page.goto('/');
    await expect(page.getByRole('heading', { level: 1, name: /ideile devin realitate/i })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Zion Catalog Tools', exact: true }).last()).toBeVisible();
    expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);

    if (width === 390 || width === 1440) {
      await expect(page.locator('.zion-featured img')).toHaveCount(4);
      const images = page.locator('.zion-library-collection img');
      await images.evaluateAll(async elements => {
        for (const image of elements) image.loading = 'eager';
        await Promise.all(elements.map(image => image.decode()));
      });
      for (const image of await images.all()) await image.scrollIntoViewIfNeeded();
      await page.evaluate(() => window.scrollTo(0, 0));
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
  const documentStart = await page.evaluate(() => performance.timeOrigin);
  let complete;
  const hold = new Promise(resolve => { complete = resolve; });
  await page.route('**/product/zion-catalog-tools/', async route => {
    if (route.request().method() === 'POST') await hold;
    await route.continue();
  });
  await page.getByRole('button', { name: 'Adaugă în coș', exact: true }).click();
  try {
    await expect(page.locator('.zion-commerce-progress')).toHaveText('Se încarcă…');
  } finally {
    complete();
  }
  await expect(page.locator('.zion-purchase-notice')).toContainText('a fost adăugat în coș');
  expect(await page.evaluate(() => performance.timeOrigin)).toBe(documentStart);
  await page.getByRole('link', { name: /vezi coșul/i }).first().click();
  await expect(page.getByRole('link', { name: 'Zion Catalog Tools' })).toBeVisible();
  await expect(page.getByLabel('Cantitatea pentru Zion Catalog Tools din coș.')).toBeVisible();
  await page.getByRole('link', { name: /continuă la finalizarea comenzii/i }).click();
  await expect(page).toHaveURL(/\/checkout\/?$/);
  const checkout = page.getByRole('form', { name: 'Finalizare comandă', exact: true });
  await expect(checkout).toBeVisible();
  await expect(checkout.getByRole('textbox', { name: 'Adresă de e-mail' })).toBeVisible();
  await expect(checkout.getByRole('button', { name: 'Plasează comanda' })).toBeVisible();
  await expect(page.getByText(/1 × Zion Catalog Tools — total:/)).toHaveCount(1);
  expect(errors).toEqual([]);
});

test('the shared card adds a simple product through the native WooCommerce button', async ({ page }) => {
  await page.goto('/magazin-software/');
  const card = page.locator('.zion-product-card').filter({ has: page.getByRole('heading', { name: 'Zion Catalog Tools', exact: true }) });
  await card.getByRole('button', { name: /Adaugă în coș/ }).click();
  await expect(card.getByRole('link', { name: /vezi coșul/i })).toBeVisible();
  await card.getByRole('link', { name: /vezi coșul/i }).click();
  await expect(page.getByRole('link', { name: 'Zion Catalog Tools', exact: true }).last()).toBeVisible();
});
