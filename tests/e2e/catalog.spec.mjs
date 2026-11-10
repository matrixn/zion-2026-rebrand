import { test, expect } from '@playwright/test';

const catalog = page => page.locator('.zion-catalog-native');
const cards = page => catalog(page).locator('.wc-block-product');
const matching = '/magazin-3d/?categories=prototipuri-demo&min_price=80&max_price=100&filter_stock_status=instock&orderby=price-desc';

test('native filters combine with sorting, reset and browser history', async ({ page }) => {
  await page.goto('/magazin-3d/?orderby=price');
  await expect(cards(page)).toHaveCount(2);
  const category = catalog(page).getByRole('checkbox', { name: /Prototipuri demo/ });
  await category.click();
  await expect(page).toHaveURL(/categories=prototipuri-demo/);
  expect(new URL(page.url()).searchParams.get('orderby')).toBe('price');
  await expect(cards(page)).toHaveCount(1);
  await expect(catalog(page).getByRole('heading', { name: 'Suport modular 3D' })).toBeVisible();

  await page.goto(matching);
  await expect(cards(page)).toHaveCount(1);
  await expect(catalog(page).getByRole('status')).toHaveText('1 produs');
  await expect(catalog(page).getByRole('heading', { name: 'Zion Catalog Tools' })).toHaveCount(0);

  await page.getByLabel('Sortează').selectOption('date');
  await page.getByRole('button', { name: 'Aplică sortarea' }).click();
  const sorted = new URL(page.url());
  expect(sorted.searchParams.get('orderby')).toBe('date');
  expect(sorted.searchParams.get('categories')).toBe('prototipuri-demo');
  expect(sorted.searchParams.get('min_price')).toBe('80');
  await expect(cards(page)).toHaveCount(1);

  await catalog(page).getByRole('link', { name: 'Resetează filtrele' }).click();
  await expect(cards(page)).toHaveCount(2);
  expect(new URL(page.url()).search).toBe('?orderby=date');
  await page.goBack();
  await expect(cards(page)).toHaveCount(1);
  await expect(page).toHaveURL(sorted.href);
  await page.goForward();
  await expect(cards(page)).toHaveCount(2);
});

test('an empty price intersection can be reset', async ({ page }) => {
  await page.goto('/magazin-software/?min_price=9999');
  await expect(cards(page)).toHaveCount(0);
  await expect(catalog(page)).toContainText('Nu există produse pentru selecția curentă.');
  await catalog(page).getByRole('link', { name: 'Resetează filtrele' }).click();
  await expect(cards(page)).toHaveCount(2);
});

for (const width of [390, 768, 1024, 1440]) {
  test(`both catalogs fit at ${width}px`, async ({ page }) => {
    await page.setViewportSize({ width, height: 850 });
    for (const path of ['/magazin-3d/', '/magazin-software/']) {
      await page.goto(path);
      const card = cards(page).first();
      await expect(card).toBeVisible();
      expect(await card.evaluate(element => {
        const trackWidth = parseFloat(getComputedStyle(element.parentElement).gridTemplateColumns);
        return Math.abs(element.getBoundingClientRect().width - trackWidth);
      })).toBeLessThan(1);
      expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
    }
  });
}

test('the mobile filter drawer opens and dismisses by keyboard', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 850 });
  await page.goto('/magazin-3d/');
  const open = catalog(page).getByRole('button', { name: 'Filter products' });
  const dialog = catalog(page).getByRole('dialog', { name: 'Product Filters' });
  await open.focus();
  await page.keyboard.press('Enter');
  await expect(dialog).toBeVisible();
  await expect(dialog.getByRole('checkbox', { name: /Prototipuri demo/ })).toBeVisible();
  await page.keyboard.press('Escape');
  await expect(dialog).toBeHidden();
  expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(390);
});
