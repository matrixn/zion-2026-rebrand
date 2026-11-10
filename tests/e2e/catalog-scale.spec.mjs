import { test, expect } from '@playwright/test';

const threeD = '/magazin-3d/';
const software = '/magazin-software/';
const params = 'categories=zion-scale-3d&brands=zion-scale-brand-3d&min_price=45&max_price=150&filter_stock_status=instock&filter_zion-scale-material=pla&on_sale=1&orderby=price';

const ids = page => page.locator('.zion-catalog-native .wc-block-product').evaluateAll(cards => cards.map(card => card.dataset.wpKey));

test('native combined filters paginate the 1,000-product fixture without crossing stores', async ({ page }) => {
  await page.goto(`${threeD}?${params}`);
  const first = await ids(page);
  expect(first).toHaveLength(12);
  await expect(page.locator('.zion-catalog-native')).not.toContainText(/Zion Scale Software \d/);

  const second = page.locator('.wp-block-query-pagination .page-numbers').getByText('2', { exact: true });
  await second.click();
  await expect(page).toHaveURL(/query-73-page=2/);
  expect(new URL(page.url()).searchParams.get('filter_zion-scale-material')).toBe('pla');
  expect(new URL(page.url()).searchParams.get('on_sale')).toBe('1');
  expect(new URL(page.url()).searchParams.get('orderby')).toBe('price');
  const next = await ids(page);
  expect(next).toHaveLength(12);
  expect(next.every(id => !first.includes(id))).toBe(true);
  await page.goBack();
  expect(await ids(page)).toEqual(first);
  await page.goForward();
  expect(await ids(page)).toEqual(next);
  await page.reload();
  expect(await ids(page)).toEqual(next);

  await page.goto(`${software}?categories=zion-scale-shared&filter_zion-scale-platform=wordpress`);
  await expect(page.locator('.zion-catalog-native .wc-block-product')).toHaveCount(12);
  await expect(page.locator('.zion-catalog-native')).not.toContainText(/Zion Scale 3D \d/);

  await page.goto(`${threeD}?categories=zion-scale-software`);
  await expect(page.locator('.zion-catalog-native')).toContainText('Nu există produse pentru selecția curentă.');
});

test('variable products participate in native filters and keep their variation form', async ({ page }) => {
  await page.goto(`${threeD}?categories=zion-scale-3d&filter_zion-scale-material=pla&on_sale=1&orderby=menu_order`);
  const variable = page.locator('.zion-catalog-native').getByRole('heading', { name: 'Zion Scale 3D 001', exact: true });
  await expect(variable).toBeVisible();
  await variable.getByRole('link').click();
  await expect(page.locator('form.variations_form select[name="attribute_pa_zion-scale-material"]')).toBeVisible();
});

test('scrolling to the next batch loads products without a new document', async ({ page }) => {
  await page.goto(`${threeD}?categories=zion-scale-shared&orderby=date`);
  const first = await ids(page);
  expect(first).toHaveLength(12);
  const documentStart = await page.evaluate(() => performance.timeOrigin);
  await page.getByRole('link', { name: 'Încarcă mai multe' }).scrollIntoViewIfNeeded();
  await expect(page).toHaveURL(/zion_more=(24|36|48|60|72|84|96)/);
  expect(await page.evaluate(() => performance.timeOrigin)).toBe(documentStart);
  expect(new URL(page.url()).searchParams.get('categories')).toBe('zion-scale-shared');
  expect(new URL(page.url()).searchParams.get('orderby')).toBe('date');
  const next = await ids(page);
  expect(next.length).toBeGreaterThanOrEqual(24);
  expect(next.slice(0, 12)).toEqual(first);
  expect(new Set(next).size).toBe(next.length);
  await page.getByLabel('Sortează').selectOption('price');
  await expect(page.getByRole('status').filter({ hasText: 'Sortarea a fost actualizată.' })).toBeVisible();
  expect(new URL(page.url()).searchParams.has('zion_more')).toBe(false);
  await expect(page.locator('.zion-catalog-native .wc-block-product')).toHaveCount(12);
});
