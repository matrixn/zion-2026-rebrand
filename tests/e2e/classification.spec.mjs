import { randomUUID } from 'node:crypto';
import { test, expect } from '@playwright/test';

const screen = '/wp-admin/edit.php?post_type=product&page=zion-shops';

test('the bulk classifier filters, previews, applies once and records both outcomes', async ({ page, baseURL }) => {
  test.setTimeout(90_000);
  test.skip(!['localhost', '127.0.0.1', '[::1]'].includes(new URL(baseURL).hostname), 'Classification tests mutate only local wp-env.');
  await page.goto('/wp-login.php');
  await page.locator('#user_login').fill('admin');
  await page.locator('#user_pass').fill('password');
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  const nonce = await (await page.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text();
  const headers = { 'X-WP-Nonce': nonce };
  await page.goto(screen);
  const categoryOption = page.getByLabel('Categorie', { exact: true }).locator('option').filter({ hasText: 'Prototipuri demo' });
  const category = Number(await categoryOption.getAttribute('value'));
  expect(category).toBeGreaterThan(0);
  const products = [];

  try {
    for (const status of ['publish', 'draft']) {
      const response = await page.request.post('/wp-json/wc/v3/products', {
        headers,
        data: {
          name: `Zion bulk ${status} ${randomUUID().slice(0, 8)}`,
          type: 'simple',
          status,
          regular_price: '18.00',
          categories: [{ id: category }],
        },
      });
      expect(response.status()).toBe(201);
      products.push(await response.json());
    }
    const [published, draft] = products;
    await page.goto('/wp-admin/edit.php?post_type=product&zion_shop_state=unclassified');
    await expect(page.getByRole('row').filter({ hasText: published.name })).toContainText('Neclasificat');
    await expect(page.getByRole('row').filter({ hasText: draft.name })).toContainText('Neclasificat');
    await expect(page.getByRole('row').filter({ hasText: 'Suport modular 3D' })).toHaveCount(0);

    await page.goto(`/wp-admin/post.php?post=${published.id}&action=edit`);
    const field = page.locator('#zion_shopdiv');
    const shop3d = field.getByRole('checkbox', { name: 'Magazin 3D', exact: true }).first();
    await expect(shop3d).toBeVisible();
    await expect(field.getByRole('checkbox', { name: 'Magazin Software', exact: true }).first()).toBeVisible();
    await shop3d.check();
    await page.locator('#publish').click();
    await expect(page.getByText('Product updated.', { exact: false })).toBeVisible();
    await expect(shop3d).toBeChecked();
    await shop3d.uncheck();
    await page.locator('#publish').click();
    await expect(page.getByText('Product updated.', { exact: false })).toBeVisible();
    await expect(shop3d).not.toBeChecked();

    await page.goto(screen);
    await expect(page.getByRole('heading', { name: 'Clasificare vitrine Zion' })).toBeVisible();
    await page.getByLabel('Categorie', { exact: true }).selectOption(String(category));
    await page.getByLabel('Clasificare vitrină').selectOption('unclassified');
    await page.getByRole('button', { name: 'Filtrează produse' }).click();
    await expect(page.getByRole('checkbox', { name: published.name })).toBeVisible();
    await expect(page.getByRole('checkbox', { name: draft.name })).toBeVisible();
    await expect(page.getByRole('checkbox', { name: 'Zion Catalog Tools' })).toHaveCount(0);

    const journal = page.getByRole('region', { name: 'Jurnalul clasificărilor' });
    const baseline = await journal.locator('details').count();
    async function selectBatch() {
      await page.getByRole('checkbox', { name: published.name }).check();
      await page.getByRole('checkbox', { name: draft.name }).check();
      await page.getByLabel('Operație').selectOption('replace');
      const targets = page.getByRole('group', { name: 'Vitrine țintă' });
      await targets.getByRole('checkbox', { name: 'Magazin 3D' }).check();
      await targets.getByRole('checkbox', { name: 'Magazin Software' }).check();
    }

    await selectBatch();
    await page.getByRole('button', { name: 'Dry-run' }).click();
    const dry = page.getByRole('region', { name: 'Dry-run finalizat' });
    await expect(dry.getByRole('row').filter({ hasText: published.name })).toContainText('Magazin 3D + Magazin Software');
    await expect(page.getByRole('button', { name: 'Aplică modificările confirmate' })).toHaveCount(0);
    await expect(journal.locator('details')).toHaveCount(baseline);
    await expect(page.getByRole('checkbox', { name: published.name })).toBeVisible();

    await selectBatch();
    await page.getByRole('button', { name: 'Previzualizează' }).click();
    const preview = page.getByRole('region', { name: 'Previzualizare clasificare' });
    await expect(preview.getByRole('row').filter({ hasText: draft.name })).toContainText('Neclasificat');
    await expect(journal.locator('details')).toHaveCount(baseline);
    const form = preview.locator('form');
    const plan = await form.evaluate(element => Object.fromEntries(new FormData(element)));
    await preview.getByRole('button', { name: 'Aplică modificările confirmate' }).click();
    const applied = page.getByRole('region', { name: 'Rezultatul aplicării' });
    for (const product of products) {
      await expect(applied.getByRole('row').filter({ hasText: product.name })).toContainText('Aplicat');
    }
    await journal.locator('summary').first().click();
    await expect(journal.getByRole('row').filter({ hasText: published.name })).toContainText('Magazin 3D + Magazin Software');

    const replay = await page.request.post(screen, { form: { ...plan, zion_shop_action: 'apply' } });
    expect(await replay.text()).toContain('Previzualizarea a expirat sau a fost folosită');

    await page.goto('/wp-admin/edit.php?post_type=product&zion_shop_state=both');
    await expect(page.getByRole('row').filter({ hasText: published.name })).toContainText('Magazin 3D + Magazin Software');

    for (const path of ['/magazin-3d/', '/magazin-software/']) {
      await page.goto(path);
      await expect(page.getByRole('heading', { name: published.name, exact: true })).toBeVisible();
      await expect(page.getByRole('heading', { name: draft.name, exact: true })).toHaveCount(0);
    }
    await page.goto(`/magazin-3d/?zion_category=${category}`);
    await expect(page).toHaveURL(/categories=prototipuri-demo/);
    await expect(page.getByRole('heading', { name: published.name, exact: true })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Prototip carcasă 3D', exact: true })).toHaveCount(0);
  } finally {
    for (const product of products) {
      const deleted = await page.request.delete(`/wp-json/wc/v3/products/${product.id}?force=true`, { headers });
      expect(deleted.ok()).toBe(true);
    }
  }
});
