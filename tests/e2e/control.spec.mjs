import { randomUUID } from 'node:crypto';
import { readFile } from 'node:fs/promises';
import { test, expect } from '@playwright/test';

const base = '/wp-admin/themes.php?page=zion-theme';
const endpoint = '/wp-admin/admin-post.php';

test.beforeEach(({ baseURL }) => {
  test.skip(!['localhost', '127.0.0.1', '[::1]'].includes(new URL(baseURL).hostname), 'Settings tests mutate the isolated local wp-env database.');
});

async function login(page, username = 'admin', password = 'password') {
  await page.goto('/wp-login.php');
  await page.locator('#user_login').fill(username);
  await page.locator('#user_pass').fill(password);
  await page.locator('#wp-submit').click();
  await page.waitForURL(url => !url.pathname.endsWith('/wp-login.php'), { timeout: 10_000 });
}

async function openSection(page, section, item = 0) {
  await page.goto(`${base}&section=${section}${item ? `&item=${item}` : ''}`);
  await expect(page.getByRole('heading', { name: 'Zion Control Center', exact: true })).toBeVisible();
}

function field(page, key) {
  return page.locator(`#zion-field-${key}`);
}

async function save(page) {
  await page.getByRole('button', { name: 'Salvează setările' }).click();
  await expect(page.getByText('Setările au fost salvate.', { exact: true })).toBeVisible();
}

async function exportSettings(page) {
  const pending = page.waitForEvent('download');
  await page.getByRole('button', { name: 'Descarcă JSON' }).click();
  const download = await pending;
  expect(download.suggestedFilename()).toBe('zion-theme-settings.json');
  return readFile(await download.path(), 'utf8');
}

async function importSettings(page, json) {
  await openSection(page, 'tools');
  await page.getByLabel('Configurație JSON').fill(json);
  await page.getByRole('button', { name: 'Validează și importă' }).click();
  await expect(page.getByText('Configurația a fost importată.', { exact: true })).toBeVisible();
}

async function requestFields(form) {
  return form.evaluate(element => Object.fromEntries(new FormData(element)));
}

test('Control Center persists validated settings and applies inheritance on public pages', async ({ page }) => {
  test.setTimeout(120_000);
  await login(page);
  await openSection(page, 'tools');
  const original = await exportSettings(page);
  const empty = JSON.stringify({ version: 2, global: {}, shops: {}, items: {} });

  try {
    await importSettings(page, empty);
    await openSection(page, 'general');
    await expect(page.getByRole('navigation', { name: 'Secțiuni Zion Control Center' }).getByRole('link')).toHaveCount(13);

    await openSection(page, 'cards');
    await expect(field(page, 'catalog_density')).toHaveValue('');
    await field(page, 'catalog_density').selectOption('compact');
    await field(page, 'catalog_actions').selectOption('0');
    const stale = await requestFields(page.locator('[data-zion-section]'));
    await save(page);

    await page.goto('/shop/');
    await expect(page.locator('body')).toHaveClass(/zion-catalog-compact/);
    await expect(page.locator('.zion-store .zion-product-card--compact').first()).toHaveCSS('padding-top', '12px');
    const action = page.locator('.zion-store .wc-block-product .wp-block-woocommerce-product-button').first();
    await expect(action).toHaveCount(0);

    await openSection(page, 'software');
    await field(page, 'catalog_actions').selectOption('1');
    await field(page, 'product_meta').selectOption('0');
    await field(page, 'catalog_layout').selectOption('list');
    await field(page, 'catalog_category_filter').selectOption('0');
    await field(page, 'catalog_filter_layout').selectOption('drawer');
    await field(page, 'catalog_width').selectOption('wide');
    await save(page);
    await page.goto('/magazin-software/');
    const shopAction = page.locator('.zion-catalog-native .wp-block-woocommerce-product-button').first();
    await expect(shopAction).toBeVisible();
    await expect(page.locator('.zion-catalog-native.zion-catalog-list.zion-catalog-drawer.alignwide')).toBeVisible();
    await page.getByRole('button', { name: 'Filter products' }).click();
    const filters = page.getByRole('dialog', { name: 'Product Filters' });
    await expect(filters).toBeVisible();
    await expect(filters.getByRole('heading', { name: 'Product categories' })).toHaveCount(0);
    await page.keyboard.press('Escape');
    await expect(filters).toBeHidden();
    await page.goto('/magazin-3d/');
    await expect(page.locator('.zion-catalog-native.zion-catalog-grid.zion-catalog-sidebar')).toBeVisible();
    await expect(page.locator('.zion-catalog-native').getByRole('checkbox', { name: /Prototipuri demo/ })).toBeVisible();

    const pages = await (await page.request.get('/wp-json/wp/v2/pages?slug=magazin-software')).json();
    await openSection(page, 'tools', pages[0].id);
    await field(page, 'catalog_actions').selectOption('0');
    await field(page, 'page_home_link').selectOption('1');
    await save(page);
    await page.goto('/magazin-software/');
    await expect(page.locator('.zion-page-home a')).toHaveText('← Acasă');
    await expect(shopAction).toBeHidden();

    const products = await (await page.request.get('/wp-json/wc/store/v1/products?slug=zion-catalog-tools')).json();
    await page.goto('/product/zion-catalog-tools/');
    await expect(page.locator('.wp-block-woocommerce-product-meta')).toHaveCount(0);
    await expect(page.getByRole('button', { name: 'Add to cart', exact: true })).toBeVisible();
    await openSection(page, 'tools', products[0].id);
    await field(page, 'product_meta').selectOption('1');
    await field(page, 'product_related').selectOption('0');
    await save(page);
    await page.goto('/product/zion-catalog-tools/');
    await expect(page.locator('.wp-block-woocommerce-product-meta')).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Related products' })).toHaveCount(0);

    await openSection(page, 'header');
    await field(page, 'highlight_shop').selectOption('1');
    await save(page);
    await openSection(page, 'footer');
    await field(page, 'back_to_top').selectOption('1');
    await save(page);
    await page.goto('/product/zion-catalog-tools/');
    await expect(page.locator('header a.zion-current-shop').first()).toHaveText('Magazin Software');
    await expect(page.getByRole('link', { name: /Înapoi sus/ })).toHaveAttribute('href', '#zion-top');

    await openSection(page, 'blog');
    await expect(field(page, 'reading_speed')).toBeHidden();
    await field(page, 'reading_time').selectOption('1');
    await expect(field(page, 'reading_speed')).toBeVisible();
    await field(page, 'reading_speed').fill('300');
    await save(page);
    await field(page, 'reading_time').selectOption('');
    await expect(field(page, 'reading_speed')).toBeHidden();
    await page.goto('/jurnal-prototip-zion/');
    await expect(page.locator('.zion-reading-time')).toHaveText('1 min de citit');
    await openSection(page, 'blog');
    await field(page, 'reading_time').selectOption('0');
    await save(page);
    await expect(page.getByText('Viteza configurată este păstrată', { exact: false })).toBeVisible();

    await openSection(page, 'design');
    await field(page, 'motion').selectOption('reduced');
    await save(page);
    await openSection(page, 'performance');
    await field(page, 'disable_emoji_assets').selectOption('1');
    await save(page);
    await page.goto('/');
    await expect(page.locator('body')).toHaveClass(/zion-motion-reduced/);
    await expect(page.locator('#wp-emoji-settings')).toHaveCount(0);

    await openSection(page, 'woo');
    await field(page, 'catalog_counts').selectOption('0');
    await save(page);
    await page.goto('/magazin-3d/');
    await expect(page.locator('.woocommerce-result-count')).toHaveCount(0);
    await page.goto('/shop/');
    await expect(page.locator('.woocommerce-result-count')).toBeAttached();
    await expect(page.locator('.woocommerce-result-count')).toBeHidden();

    await openSection(page, 'tools');
    const exported = await exportSettings(page);
    const data = JSON.parse(exported);
    expect(data.version).toBe(2);
    expect(data.global.catalog_counts).toBe(false);
    expect(data.shops.software.catalog_actions).toBe(true);
    expect(data.items.page[pages[0].id].catalog_actions).toBe(false);
    expect(data.items.product[products[0].id].product_meta).toBe(true);

    const importForm = page.locator('form').filter({ has: page.getByRole('button', { name: 'Validează și importă' }) });
    const current = await requestFields(importForm);
    const invalid = await page.request.post(endpoint, { form: { ...current, operation: 'import', settings_json: '{"version":999}' } });
    expect(invalid.status()).toBe(400);
    expect(JSON.parse(await exportSettings(page))).toEqual(data);

    const reset = await page.request.post(endpoint, { form: { ...current, operation: 'reset_all' } });
    expect(reset.status()).toBe(400);
    const old = await page.request.post(endpoint, { form: { ...stale, operation: 'save' } });
    expect(old.status()).toBe(409);
    const noNonce = await page.request.post(endpoint, { form: { ...current, _wpnonce: '', operation: 'reset_all', confirm_reset: '1' } });
    expect(noNonce.status()).toBe(403);

    await openSection(page, 'cards');
    await page.getByRole('button', { name: 'Resetează secțiunea' }).click();
    await expect(field(page, 'catalog_density')).toHaveValue('');
    await openSection(page, 'tools');
    const afterSection = JSON.parse(await exportSettings(page));
    expect(afterSection.global.catalog_density).toBeUndefined();
    expect(afterSection.shops.software.catalog_actions).toBe(true);

    await page.getByRole('checkbox', { name: /Confirm că vreau/ }).check();
    await page.getByRole('button', { name: 'Resetează toate setările' }).click();
    await expect(page.getByText('Toate setările au revenit la valorile implicite.', { exact: true })).toBeVisible();
    const afterReset = JSON.parse(await exportSettings(page));
    expect(Object.keys(afterReset.global)).toHaveLength(0);
    expect(Object.keys(afterReset.shops)).toHaveLength(0);
    expect(Object.keys(afterReset.items)).toHaveLength(0);

    await importSettings(page, exported);
    expect(JSON.parse(await exportSettings(page))).toEqual(data);
    await importSettings(page, '{"version":1,"settings":{"reading_time":true,"reading_speed":280}}');
    expect(JSON.parse(await exportSettings(page)).global).toEqual({ reading_speed: 280, reading_time: true });
  } finally {
    await importSettings(page, original);
  }
});

test('all card presets fit desktop and mobile and store overrides control the shared renderer', async ({ page }) => {
  test.setTimeout(150_000);
  await login(page);
  await openSection(page, 'tools');
  const original = await exportSettings(page);
  const card = page.locator('.zion-catalog-native .zion-product-card').first();

  try {
    for (const preset of ['minimal', 'classic', 'premium', 'compact', 'horizontal']) {
      await importSettings(page, JSON.stringify({ version: 2, global: { card_preset: preset }, shops: {}, items: {} }));
      for (const width of [390, 1440]) {
        await page.setViewportSize({ width, height: 850 });
        await page.goto('/magazin-3d/');
        await expect(card).toHaveClass(new RegExp(`zion-product-card--${preset}\\b`));
        await expect(card.locator('.zion-product-card__image img')).toBeVisible();
        await expect(card.getByRole('heading')).toBeVisible();
        await expect(card.locator('.zion-product-card__cart')).toBeVisible();
        const tracks = await card.evaluate(element => getComputedStyle(element).gridTemplateColumns.split(' ').length);
        expect(tracks).toBe(preset === 'horizontal' ? 2 : 1);
        expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
      }
    }

    await openSection(page, '3d');
    await field(page, 'card_preset').selectOption('premium');
    await field(page, 'card_ratio').selectOption('3/4');
    await field(page, 'card_fit').selectOption('contain');
    await field(page, 'card_color').selectOption('light');
    await field(page, 'card_buttons').selectOption('large');
    await field(page, 'card_title_lines').selectOption('1');
    await field(page, 'card_badge_place').selectOption('bottom-end');
    await field(page, 'card_order').fill('price, title, quick_view');
    await field(page, 'card_show_quick_view').selectOption('show');
    await field(page, 'catalog_actions').selectOption('0');
    await field(page, 'card_show_wishlist').selectOption('show');
    await field(page, 'card_show_compare').selectOption('show');
    await save(page);

    await page.goto('/magazin-3d/');
    await expect(card).toHaveClass(/zion-product-card--premium.*zion-card-color-light.*zion-card-badges-bottom-end/);
    await expect(card.locator('.zion-product-card__image')).toHaveCSS('aspect-ratio', '3 / 4');
    await expect(card.locator('.zion-product-card__image img')).toHaveCSS('object-fit', 'contain');
    await expect(card.getByRole('heading')).toHaveCSS('-webkit-line-clamp', '1');
    expect(await card.locator('.zion-product-card__body > div').evaluateAll(elements => elements.slice(0, 3).map(element => element.className)))
      .toEqual(['zion-product-card__price', 'zion-product-card__title', 'zion-product-card__quick_view']);
    await expect(card.locator('.zion-product-card__cart, .zion-product-card__wishlist, .zion-product-card__compare')).toHaveCount(0);
    const quickView = card.getByRole('link', { name: 'Quick View' });
    await quickView.focus();
    await page.keyboard.press('Enter');
    const dialog = card.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await expect(dialog.getByRole('button', { name: 'Închide' })).toBeFocused();
    await page.keyboard.press('Escape');
    await expect(dialog).toBeHidden();
    await expect(quickView).toBeFocused();
    await card.getByRole('heading').getByRole('link').click();
    await expect(page.getByRole('button', { name: 'Add to cart', exact: true })).toBeVisible();

    await page.goto('/magazin-software/');
    await expect(card).toHaveClass(/zion-product-card--horizontal/);
    await expect(card.locator('.zion-product-card__cart')).toBeVisible();
    await expect(card.getByRole('link', { name: 'Quick View' })).toHaveCount(0);
    await page.goto('/shop/');
    await expect(page.locator('.zion-store .zion-product-card--horizontal').first()).toBeVisible();
  } finally {
    await importSettings(page, original);
  }
});

test('a subscriber cannot open, import, export or reset theme settings', async ({ page, browser }) => {
  test.setTimeout(60_000);
  await login(page);
  const nonce = await (await page.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text();
  const headers = { 'X-WP-Nonce': nonce };
  const admin = await (await page.request.get('/wp-json/wp/v2/users/me', { headers })).json();
  const username = `zion-test-${randomUUID()}`;
  const password = randomUUID();
  const created = await page.request.post('/wp-json/wp/v2/users', {
    headers,
    data: { username, password, email: `${username}@example.test`, roles: ['subscriber'] },
  });
  expect(created.status()).toBe(201);
  const user = await created.json();
  const subscriber = await browser.newContext();
  try {
    const member = await subscriber.newPage();
    await login(member, username, password);
    const restricted = await member.request.get(base, { maxRedirects: 0 });
    expect([302, 403]).toContain(restricted.status());
    for (const operation of ['save', 'import', 'export', 'reset_all']) {
      const denied = await member.request.post(endpoint, { form: { action: 'zion_theme_control', operation } });
      expect(denied.status()).toBe(403);
      expect(await denied.text()).toContain('Nu ai permisiunea');
    }
  } finally {
    const deleted = await page.request.delete(`/wp-json/wp/v2/users/${user.id}?force=true&reassign=${admin.id}`, { headers });
    await subscriber.close();
    expect(deleted.ok()).toBe(true);
  }
});
