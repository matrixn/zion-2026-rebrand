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
  const response = await page.request.post('/wp-login.php', {
    maxRedirects: 0,
    form: { log: username, pwd: password, testcookie: '1' },
  });
  expect(response.status()).toBe(302);
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
    await expect(page.getByRole('link', { name: /Înapoi sus/ })).toHaveAttribute('href', '#zion-page-top');

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

test('product presets and native purchases work with real variations, mobile and no JavaScript', async ({ page, browser, baseURL }) => {
  test.setTimeout(180_000);
  await login(page);
  await openSection(page, 'tools');
  const original = await exportSettings(page);
  const nonce = await (await page.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text();
  const headers = { 'X-WP-Nonce': nonce };
  const products = [];
  const endpoint = '/wp-json/wc/v3/products';
  const create = async (data, suffix = '') => {
    const response = await page.request.post(endpoint + suffix, { headers, data: { status: 'publish', ...data } });
    expect(response.status(), await response.text()).toBe(201);
    const product = await response.json();
    if (!suffix) products.push(product);
    return product;
  };

  try {
    const available = await create({ name: 'Zion component test', type: 'simple', regular_price: '39', manage_stock: true, stock_quantity: 20 });
    const exhausted = await create({ name: 'Zion out-of-stock test', type: 'simple', regular_price: '39', stock_status: 'outofstock' });
    const grouped = await create({ name: 'Zion grouped test', type: 'grouped', grouped_products: [available.id] });
    const external = await create({ name: 'Zion affiliate test', type: 'external', regular_price: '22', external_url: 'https://example.test/partner', button_text: 'Partener test' });
    const download = await create({
      name: 'Zion downloadable test', type: 'simple', regular_price: '29', virtual: true, downloadable: true,
      upsell_ids: [available.id], cross_sell_ids: [external.id],
    });
    const variable = await create({
      name: 'Zion variable test', type: 'variable',
      attributes: [{ name: 'Material', visible: true, variation: true, options: ['PLA', 'PETG'] }],
    });
    await create({ regular_price: '89', manage_stock: true, stock_quantity: 10, attributes: [{ name: 'Material', option: 'PLA' }] }, `/${variable.id}/variations`);
    await create({ regular_price: '99', manage_stock: true, stock_quantity: 0, stock_status: 'outofstock', attributes: [{ name: 'Material', option: 'PETG' }] }, `/${variable.id}/variations`);

    await page.goto(`/wp-admin/post.php?post=${download.id}&action=edit`);
    await page.locator('#zion-product-detail-version').fill('7.2.1');
    await page.locator('#zion-product-detail-documentation').fill('https://example.test/manual');
    await page.locator('#zion-product-detail-files').fill('ZIP\nPDF');
    await page.locator('#zion-product-detail-faq').fill('Cum instalez? | Descarcă arhiva.\nLinie fără răspuns');
    await page.locator('#publish').click();
    await expect(page.getByText('Product updated.', { exact: false })).toBeVisible();

    await importSettings(page, JSON.stringify({
      version: 2,
      global: { product_preset: 'minimal', product_show_sticky: 'show', product_show_buy_now: 'show' },
      shops: { '3d': { product_preset: 'physical' }, software: { product_preset: 'software' } },
      items: { product: { [download.id]: {
        product_preset: 'premium', product_section_order: 'version,hero,faq',
        product_summary_order: 'purchase,title,price', product_accent_sections: 'version',
      } } },
    }));

    await page.goto('/product/suport-modular-3d/');
    await expect(page.locator('.zion-product-page--physical')).toBeVisible();
    await page.goto('/product/zion-catalog-tools/');
    await expect(page.locator('.zion-product-page--software')).toBeVisible();
    await expect(page.locator('[data-zion-section="version"]')).toHaveCount(0);
    await page.goto(download.permalink);
    const premium = page.locator('.zion-product-page--premium');
    await expect(premium.locator('[data-zion-section]').first()).toHaveAttribute('data-zion-section', 'version');
    await expect(premium.locator('.zion-product-summary > div').first()).toHaveClass('zion-product-summary__purchase');
    await expect(premium.locator('.zion-product-section--accent')).toContainText('7.2.1');
    await expect(premium.getByRole('link', { name: 'Deschide documentația' })).toHaveAttribute('href', 'https://example.test/manual');
    await expect(premium.locator('[data-zion-section="files"]')).toContainText('ZIP');
    await expect(premium.locator('[data-zion-section="upsells"]')).toContainText(available.name);
    await expect(premium.locator('[data-zion-section="cross_sells"]')).toContainText(external.name);
    await premium.getByText('Cum instalez?').click();
    await expect(premium.getByText('Descarcă arhiva.')).toBeVisible();
    await expect(premium.getByText('Linie fără răspuns')).toHaveCount(0);
    await expect(premium.getByRole('button', { name: 'Add to cart', exact: true })).toBeVisible();

    await page.goto(exhausted.permalink);
    await expect(page.locator('.zion-product-summary__availability')).toContainText(/out of stock/i);
    await expect(page.locator('#zion-purchase, [data-zion-sticky]')).toHaveCount(0);
    await page.goto(grouped.permalink);
    await expect(page.locator('#zion-purchase input.qty')).toHaveCount(1);
    await expect(page.locator('#zion-purchase').getByRole('link', { name: available.name, exact: true })).toBeVisible();
    await expect(page.locator('.zion-buy-now, [data-zion-sticky]')).toHaveCount(0);
    await page.goto(external.permalink);
    await expect(page.locator('#zion-purchase form')).toHaveAttribute('action', 'https://example.test/partner');
    await expect(page.getByRole('button', { name: 'Partener test' })).toBeVisible();
    await expect(page.locator('.zion-buy-now, [data-zion-sticky]')).toHaveCount(0);

    for (const width of [390, 1440]) {
      const guest = await browser.newContext({ baseURL, viewport: { width, height: 850 } });
      try {
        const productPage = await guest.newPage();
        await productPage.goto(variable.permalink);
        const form = productPage.locator('form.variations_form');
        const material = form.locator('select');
        const sticky = productPage.locator('[data-zion-sticky]');
        const buy = form.getByRole('button', { name: 'Cumpără acum' });
        await expect(productPage.locator('.zion-product-page--minimal')).toBeVisible();
        await expect(sticky).toHaveText('Alege opțiunile');
        await expect(buy).toBeDisabled();
        await sticky.click();
        await expect(material).toBeFocused();
        const unavailable = material.locator('option', { hasText: 'PETG' });
        if (await unavailable.count() && !(await unavailable.isDisabled())) {
          await material.selectOption({ label: 'PETG' });
          await expect(sticky).toHaveText('Alege opțiunile');
          await expect(buy).toBeDisabled();
          await expect(form.locator('.woocommerce-variation-availability')).toContainText(/out of stock/i);
        }
        await material.selectOption({ label: 'PLA' });
        await expect(sticky).toHaveText('Adaugă în coș');
        await expect(buy).toBeEnabled();
        await form.locator('input.qty').fill('2');
        expect(await productPage.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
        await sticky.click();
        await expect(productPage.getByText(/added to your cart/i).first()).toBeVisible();
        await productPage.goto('/cart/');
        await expect(productPage.locator('.wc-block-cart-items').getByText(/Zion variable test/).first()).toBeVisible();
        await expect(productPage.locator('.wc-block-cart-items input').first()).toHaveValue('2');
        if (width === 1440) {
          await productPage.goto(variable.permalink);
          await material.selectOption({ label: 'PLA' });
          await expect(buy).toBeEnabled();
          await buy.click();
          await expect(productPage).toHaveURL(/\/checkout\/?$/);
        }
      } finally {
        await guest.close();
      }
    }

    const noScript = await browser.newContext({ baseURL, javaScriptEnabled: false, viewport: { width: 390, height: 850 } });
    try {
      const productPage = await noScript.newPage();
      await productPage.goto(variable.permalink);
      await expect(productPage.getByText(/Activează JavaScript/)).toBeVisible();
      await expect(productPage.locator('[data-zion-sticky]')).toHaveAttribute('href', '#zion-purchase');
      await productPage.goto('/product/suport-modular-3d/');
      await expect(productPage.locator('.zion-product-media .woocommerce-product-gallery')).toHaveCSS('opacity', '1');
      await productPage.getByRole('button', { name: 'Cumpără acum' }).click();
      await expect(productPage).toHaveURL(/\/checkout\/?$/);
    } finally {
      await noScript.close();
    }
  } finally {
    await importSettings(page, original);
    for (const product of products.reverse()) {
      const response = await page.request.delete(`${endpoint}/${product.id}?force=true`, { headers });
      expect(response.ok()).toBe(true);
    }
  }
});

test('site chrome, journal pagination and page overrides use the native editor and templates', async ({ page }) => {
  test.setTimeout(120_000);
  await login(page);
  await openSection(page, 'tools');
  const original = await exportSettings(page);
  const nonce = await (await page.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text();
  const headers = { 'X-WP-Nonce': nonce };
  const fixtures = [];
  const create = async (type, data) => {
    const route = `/wp-json/wp/v2/${type}`;
    const response = await page.request.post(route, { headers, data: { status: 'publish', ...data } });
    expect(response.status(), await response.text()).toBe(201);
    const entry = await response.json();
    fixtures.push(`${route}/${entry.id}?force=true`);
    return entry;
  };
  try {
    const parent = await create('pages', { title: 'Părinte Zion test', content: '<p>Conținut părinte.</p>' });
    const child = await create('pages', { title: 'Pagină Zion test', parent: parent.id, content: '<p>Conținut pagină test.</p>' });
    const related = await create('posts', { title: 'Articol asociat Zion test', content: '<p>Conținut editorial.</p>', categories: [1] });
    const article = await create('posts', {
      title: 'Articol Zion test', content: '<p>Text de probă pentru setările și timpul de citire.</p>',
      categories: [1], excerpt: 'Rezumat test distinct', comment_status: 'open',
    });
    await importSettings(page, JSON.stringify({
      version: 2,
      global: {
        header_sticky: true, header_transparent: true, header_mega: true, header_cta: true,
        footer_cta: false, footer_resources: false, back_to_top: true,
        blog_layout: 'list', blog_per_page: 1, page_breadcrumb: true,
      },
      shops: {},
      items: {
        page: { [child.id]: { page_sidebar: true, page_width: 'full', page_header: 'panel', page_title: false, header_cart: false } },
        post: { [article.id]: { blog_author: false, blog_date: false, blog_excerpt: false, blog_comments: false, reading_time: true } },
      },
    }));

    await openSection(page, 'header');
    const editor = await page.getByRole('link', { name: /Editează Header/ }).getAttribute('href');
    expect(new URL(editor).searchParams.get('p')).toBe('/wp_template_part/zion-theme//header');
    await openSection(page, 'pages');
    const sidebarEditor = await page.getByRole('link', { name: /Editează bara laterală/ }).getAttribute('href');
    expect(new URL(sidebarEditor).searchParams.get('p')).toBe('/wp_template_part/zion-theme//page-sidebar');

    await page.setViewportSize({ width: 1440, height: 850 });
    await page.goto('/');
    await expect(page.locator('body')).toHaveClass(/zion-header-transparent/);
    const header = page.locator('.wp-site-blocks > header');
    await expect(header).toHaveCSS('position', 'sticky');
    await expect(header.getByRole('link', { name: 'Explorează vitrinele' })).toBeVisible();
    const submenu = header.getByRole('button', { name: /Explorează/ }).first();
    await submenu.focus();
    await page.keyboard.press('Enter');
    await expect(header.locator('.wp-block-navigation__submenu-container')).toHaveCSS('display', 'grid');
    await expect(header.getByRole('link', { name: 'Toate produsele' })).toBeVisible();
    await page.keyboard.press('Escape');
    await page.evaluate(() => window.scrollTo(0, 480));
    expect(await header.evaluate(element => element.getBoundingClientRect().top)).toBeGreaterThanOrEqual(0);
    await expect(page.locator('footer .zion-footer-cta, footer .zion-footer-resources')).toHaveCount(0);
    await expect(page.locator('footer .zion-footer-copyright')).toContainText('Zion');
    const backTop = page.getByRole('link', { name: /Înapoi sus/ });
    await expect(backTop).toHaveAttribute('href', '#zion-page-top');
    await backTop.click();
    await expect.poll(() => page.evaluate(() => window.scrollY)).toBeLessThanOrEqual(1);

    await page.goto('/jurnal/');
    await expect(page.locator('body')).toHaveClass(/zion-blog-list/);
    await expect(page.locator('.zion-post-card')).toHaveCount(1);
    await expect(page.locator('.wp-block-query-pagination-next')).toBeVisible();
    await page.goto(article.link);
    await expect(page.locator('main .zion-blog-author, main .zion-blog-date, main .zion-blog-excerpt, main .zion-blog-comments')).toHaveCount(0);
    await expect(page.locator('main .zion-reading-time')).toHaveText('1 min de citit');
    await expect(page.locator('.zion-article-related').getByRole('link', { name: 'Articol asociat Zion test' })).toHaveAttribute('href', related.link);
    await expect(page.getByRole('link', { name: 'Distribuie prin e-mail' })).toHaveAttribute('href', /^mailto:/);
    await expect(page.getByRole('button', { name: 'Copiază linkul' })).toBeVisible();

    for (const width of [390, 1440]) {
      await page.setViewportSize({ width, height: 850 });
      await page.goto(child.link);
      await expect(page.locator('body')).toHaveClass(/zion-page-width-full/);
      await expect(page.locator('.zion-standard-header')).toHaveClass(/zion-standard-header/);
      await expect(page.locator('.zion-page-title, .zion-header-cart')).toHaveCount(0);
      await expect(page.locator('aside .zion-page-sidebar')).toBeVisible();
      await expect(page.getByRole('navigation', { name: 'Fir de navigare' }).getByRole('link', { name: 'Părinte Zion test' })).toHaveAttribute('href', parent.link);
      expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
    }
    await page.goto('/cart/');
    await expect(page.locator('.zion-page-sidebar, .zion-page-breadcrumb')).toHaveCount(0);
  } finally {
    await importSettings(page, original);
    for (const route of fixtures.reverse()) {
      expect((await page.request.delete(route, { headers })).ok()).toBe(true);
    }
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
