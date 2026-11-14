import { randomUUID } from 'node:crypto';
import { test, expect } from '@playwright/test';

const base = '/wp-admin/themes.php?page=zion-theme';
const endpoint = '/wp-admin/admin-post.php';

function contrastRatio(ink, background, opacity = 1) {
  const rgba = value => value.match(/[\d.]+/g).map(Number);
  const bg = rgba(background), fg = rgba(ink), alpha = (fg[3] ?? 1) * opacity;
  const luminance = rgb => rgb.slice(0, 3).map(v => v / 255).map(v => v <= .04045 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4).reduce((sum, v, i) => sum + v * [.2126, .7152, .0722][i], 0);
  const a = luminance(fg.map((v, i) => v * alpha + bg[i] * (1 - alpha))), b = luminance(bg);
  return (Math.max(a, b) + .05) / (Math.min(a, b) + .05);
}

async function expectCouponContrast(input) {
  const colors = await input.evaluate(e => {const style = getComputedStyle(e), hint = getComputedStyle(e, '::placeholder'), label = e.closest('.wc-block-components-text-input').querySelector('label'); return {ink:style.color, bg:style.backgroundColor, placeholder:hint.color, opacity:Number(hint.opacity), label:label ? getComputedStyle(label).color : null};});
  expect(contrastRatio(colors.ink, colors.bg)).toBeGreaterThanOrEqual(4.5);
  expect(contrastRatio(colors.placeholder, colors.bg, colors.opacity)).toBeGreaterThanOrEqual(4.5);
  if(colors.label) expect(contrastRatio(colors.label, colors.bg)).toBeGreaterThanOrEqual(4.5);
}

async function expectSelectTextFits(select) {
  const size = await select.evaluate(e => {
    const s = getComputedStyle(e);
    const rect = e.getBoundingClientRect();
    const label = e.closest('.variations tr')?.querySelector('label')?.getBoundingClientRect();
    return {
      height: e.getBoundingClientRect().height,
      available: e.clientHeight - parseFloat(s.paddingTop) - parseFloat(s.paddingBottom),
      line: s.lineHeight === 'normal' ? parseFloat(s.fontSize) * 1.2 : parseFloat(s.lineHeight),
      labelOffset: label ? Math.abs(label.y + label.height / 2 - rect.y - rect.height / 2) : null,
      labelAbove: label ? label.bottom <= rect.top + 1 : null,
      narrow: innerWidth <= 600,
    };
  });
  expect(size.height).toBeGreaterThanOrEqual(44);
  expect(size.available + .5).toBeGreaterThanOrEqual(size.line);
  if (size.labelOffset !== null) {
    if (size.narrow) expect(size.labelAbove).toBe(true);
    else expect(size.labelOffset).toBeLessThanOrEqual(4);
  }
}

async function openCoupon(page) {
  const input=page.locator('.wc-block-components-totals-coupon input');
  if(!await input.isVisible())await page.getByRole('button',{name:'Adaugă cupoane',exact:true}).click();
  await expect(input).toBeVisible();return input;
}

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

async function expectStudioLink(page, name, kind, document) {
  const target = new URL(await page.getByRole('link', { name }).getAttribute('href'));
  expect(target.searchParams.get('page')).toBe('zion-theme');
  expect(target.searchParams.get('section')).toBe('studio');
  expect(target.searchParams.get('kind')).toBe(kind);
  expect(target.searchParams.get('document')).toBe(document);
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
  const chunks = [];
  for await (const chunk of await download.createReadStream()) chunks.push(chunk);
  return Buffer.concat(chunks).toString('utf8');
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
    await expect(page.getByRole('navigation', { name: 'Secțiuni Zion Control Center' }).getByRole('link')).toHaveCount(17);

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
    await field(page, 'catalog_page_size').fill('1');
    await field(page, 'catalog_load_mode').selectOption('manual');
    await save(page);
    await page.goto('/magazin-software/', { waitUntil: 'networkidle' });
    const shopAction = page.locator('.zion-catalog-native .wp-block-woocommerce-product-button').first();
    await expect(shopAction).toBeVisible();
    await expect(page.locator('.zion-catalog-native.zion-catalog-list.zion-catalog-drawer.alignwide')).toBeVisible();
    await page.getByRole('button', { name: 'Filtrează produsele' }).click();
    const filters = page.getByRole('dialog', { name: 'Filtre de produse' });
    await expect(filters).toBeVisible();
    await expect(filters.getByRole('heading', { name: 'Categorii de produse' })).toHaveCount(0);
    await page.keyboard.press('Escape');
    await expect(filters).toBeHidden();
    const next = page.locator('.wp-block-query-pagination-numbers').getByRole('link', { name: /2/ });
    await expect(page.locator('[data-zion-next]')).toHaveCount(0);
    await expect(page.locator('.zion-catalog-native .wc-block-product')).toHaveCount(1);
    const documentStart = await page.evaluate(() => performance.timeOrigin);
    await next.click();
    await expect(page).toHaveURL(/query-74-page=2/);
    await expect(page.locator('.zion-catalog-native .wc-block-product')).toHaveCount(1);
    expect(await page.evaluate(() => performance.timeOrigin)).toBe(documentStart);
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
    await expect(page.getByRole('button', { name: 'Adaugă în coș', exact: true })).toBeVisible();
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
    await expect(page.locator('header a.zion-current-shop .zion-menu-text').first()).toHaveText('Magazin Software');
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
    await expect(page.locator('.zion-article-meta .zion-reading-time')).toHaveText('1 min de citit');
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
    await expect(page.getByRole('button', { name: 'Adaugă în coș', exact: true })).toBeVisible();

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
      attributes: [{ name: 'Material', visible: true, variation: true, options: ['PLA Translucent (95A)', 'PETG'] }],
    });
    const availableVariation = await create({ regular_price: '89', manage_stock: true, stock_quantity: 10, attributes: [{ name: 'Material', option: 'PLA Translucent (95A)' }] }, `/${variable.id}/variations`);
    await create({ regular_price: '99', manage_stock: true, stock_quantity: 0, stock_status: 'outofstock', attributes: [{ name: 'Material', option: 'PETG' }] }, `/${variable.id}/variations`);
    const virtualVariable = await create({
      name: 'Zion virtual variable test', type: 'variable', virtual: true,
      attributes: [{ name: 'Licență', visible: true, variation: true, options: ['Licență personală (1 PC)', 'Licență business (5 PC-uri)'] }],
    });
    await create({ regular_price: '129', virtual: true, attributes: [{ name: 'Licență', option: 'Licență personală (1 PC)' }] }, `/${virtualVariable.id}/variations`);
    await create({ regular_price: '249', virtual: true, attributes: [{ name: 'Licență', option: 'Licență business (5 PC-uri)' }] }, `/${virtualVariable.id}/variations`);

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
    await expect(premium.getByRole('button', { name: 'Adaugă în coș', exact: true })).toBeVisible();

    await page.goto(exhausted.permalink);
    await expect(page.locator('.zion-product-summary__availability')).toContainText(/stoc epuizat/i);
    await expect(page.locator('#zion-purchase, [data-zion-sticky]')).toHaveCount(0);
    await page.goto(grouped.permalink);
    await expect(page.locator('#zion-purchase input.qty')).toHaveCount(1);
    await expect(page.locator('#zion-purchase').getByRole('link', { name: available.name, exact: true })).toBeVisible();
    await expect(page.locator('.zion-buy-now, [data-zion-sticky]')).toHaveCount(0);
    await page.goto(external.permalink);
    await expect(page.locator('#zion-purchase form')).toHaveAttribute('action', 'https://example.test/partner');
    await expect(page.getByRole('button', { name: 'Partener test' })).toBeVisible();
    await expect(page.locator('.zion-buy-now, [data-zion-sticky]')).toHaveCount(0);

    for (const width of [390, 768, 1024, 1440]) {
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
          await expect(form.locator('.woocommerce-variation-availability')).toContainText(/stoc epuizat/i);
        }
        await material.selectOption('');
        await expect(sticky).toHaveText('Alege opțiunile');
        await material.press('ArrowDown');
        await expect(material).toHaveValue('PLA Translucent (95A)');
        await expect(form.locator('input.variation_id')).toHaveValue(String(availableVariation.id));
        await expectSelectTextFits(material);
        const focus = await material.evaluate(e => ({ outline: getComputedStyle(e).outlineStyle, color: getComputedStyle(e).outlineColor, bg: getComputedStyle(e).backgroundColor }));
        expect(focus.outline).toBe('solid');
        expect(contrastRatio(focus.color, focus.bg)).toBeGreaterThanOrEqual(3);
        await expect(sticky).toHaveText('Adaugă în coș');
        await expect(form.locator('.woocommerce-variation-price')).toContainText('89');
        const documentStart = await productPage.evaluate(() => performance.timeOrigin);
        await expect(buy).toBeEnabled();
        await form.locator('input.qty').fill('2');
        expect(await productPage.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
        await sticky.click();
        await expect(productPage.locator('.zion-purchase-notice').getByText(/adăugate în coș/i)).toBeVisible();
        expect(await productPage.evaluate(() => performance.timeOrigin)).toBe(documentStart);
        await productPage.goto('/cart/');
        await expect(productPage.locator('.wc-block-cart-items').getByText(/Zion variable test/).first()).toBeVisible();
        await expect(productPage.locator('.wc-block-cart-items input').first()).toHaveValue('2');
        if (width === 1440) {
          await productPage.goto(variable.permalink);
          await material.selectOption({ label: 'PLA Translucent (95A)' });
          await expect(buy).toBeEnabled();
          await buy.click();
          await expect(productPage).toHaveURL(/\/checkout\/?$/);
        }
        await productPage.goto('/magazin-3d/');
        await expectSelectTextFits(productPage.getByRole('combobox', { name: 'Sortează', exact: true }));
        expect(await productPage.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
        await productPage.goto(virtualVariable.permalink);
        const license = productPage.locator('form.variations_form select');
        await license.selectOption({ label: 'Licență personală (1 PC)' });
        await expectSelectTextFits(license);
        await expect(productPage.locator('.woocommerce-variation-price')).toContainText('129');
        expect(await productPage.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
      } finally {
        await guest.close();
      }
    }

    const noScript = await browser.newContext({ baseURL, javaScriptEnabled: false, viewport: { width: 390, height: 850 } });
    try {
      const productPage = await noScript.newPage();
      await productPage.goto(variable.permalink);
      await expect(productPage.getByText(/Activează JavaScript/)).toBeVisible();
      await expectSelectTextFits(productPage.locator('form.variations_form select'));
      await expect(productPage.locator('[data-zion-sticky]')).toHaveAttribute('href', '#zion-purchase');
      await productPage.goto('/product/suport-modular-3d/');
      await expect(productPage.locator('.zion-product-media .woocommerce-product-gallery')).toHaveCSS('opacity', '1');
      await productPage.getByRole('button', { name: 'Cumpără acum' }).click();
      await expect(productPage).toHaveURL(/\/checkout\/?$/);
    } finally {
      await noScript.close();
    }
    await page.goto('/my-account/edit-address/billing/');
    const accountCountry = page.locator('#billing_country');
    const enhancedCountry = page.locator('.select2-container .select2-selection--single').first();
    if (await enhancedCountry.count()) {
      expect(await enhancedCountry.evaluate(e => e.getBoundingClientRect().height)).toBeGreaterThanOrEqual(44);
      const textSpace = await enhancedCountry.locator('.select2-selection__rendered').evaluate(e => {
        const s = getComputedStyle(e);
        return e.clientHeight - parseFloat(s.paddingTop) - parseFloat(s.paddingBottom) - parseFloat(s.lineHeight);
      });
      expect(textSpace).toBeGreaterThanOrEqual(-.5);
      await expect(accountCountry).toHaveClass(/select2-hidden-accessible/);
    } else {
      await expectSelectTextFits(accountCountry);
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
    expect(new URL(editor).searchParams.get('section')).toBe('studio');
    expect(new URL(editor).searchParams.get('kind')).toBe('parts');
    expect(new URL(editor).searchParams.get('document')).toBe('zion-theme//header');
    await openSection(page, 'pages');
    const sidebarEditor = await page.getByRole('link', { name: /Editează bara laterală/ }).getAttribute('href');
    expect(new URL(sidebarEditor).searchParams.get('section')).toBe('studio');
    expect(new URL(sidebarEditor).searchParams.get('kind')).toBe('parts');
    expect(new URL(sidebarEditor).searchParams.get('document')).toBe('zion-theme//page-sidebar');

    await page.setViewportSize({ width: 1440, height: 850 });
    await page.goto('/');
    await expect(page.locator('body')).toHaveClass(/zion-header-transparent/);
    const header = page.locator('.wp-site-blocks > header');
    await expect(header).toHaveCSS('position', 'sticky');
    await expect(header.getByRole('link', { name: 'Explorează vitrinele' })).toBeVisible();
    const submenu = header.getByRole('button', { name: /Software/ }).first();
    await submenu.focus();
    await page.keyboard.press('Enter');
    await expect(header.locator('.wp-block-navigation__submenu-container').filter({ has: page.getByRole('link', { name: 'Magazin Software', includeHidden: true }) })).toHaveCSS('display', 'grid');
    await expect(header.getByRole('link', { name: 'Magazin Software' })).toBeVisible();
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
    await expect(page.locator('.zion-article-hero .zion-blog-author, .zion-article-hero .zion-blog-date, .zion-article-hero .zion-blog-excerpt, main .zion-blog-comments')).toHaveCount(0);
    await expect(page.locator('.zion-article-meta .zion-reading-time')).toHaveText('1 min de citit');
    // Related cards resolve their own posts; the current article's overrides must not leak.
    const relatedCard = page.locator('.zion-article-related .zion-post-card').filter({ has: page.getByRole('link', { name: 'Articol asociat Zion test', exact: true }) });
    await expect(relatedCard.locator('.zion-blog-author')).toBeVisible();
    await expect(relatedCard.locator('.zion-blog-date')).toBeVisible();
    await expect(page.locator('.zion-article-related').getByRole('link', { name: 'Articol asociat Zion test' })).toHaveAttribute('href', related.link);
    await expect(page.getByRole('link', { name: 'Distribuie prin e-mail' })).toHaveAttribute('href', /^mailto:/);
    await expect(page.getByRole('button', { name: 'Copiază linkul' })).toBeVisible();

    for (const width of [390, 1440]) {
      await page.setViewportSize({ width, height: 850 });
      await page.goto(child.link);
      await expect(page.locator('body')).toHaveClass(/zion-page-width-full/);
      await expect(page.locator('.zion-standard-header')).toHaveClass(/zion-standard-header/);
      await expect(page.locator('.zion-page-title')).toHaveCount(0);
      await expect(page.locator('.zion-header-cart-link')).toHaveAttribute('href', /\/cart\/$/);
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

test('local WooCommerce checkout applies shipping, taxes and a coupon and creates a COD order', async ({ page, browser, baseURL }) => {
  test.setTimeout(90_000);
  const testSite = new URL(baseURL);
  test.skip(testSite.port !== '8917' || !['localhost', '127.0.0.1', '[::1]'].includes(testSite.hostname),
    'Commerce fixtures are restricted to the isolated tests port.');
  await login(page);
  const nonce = await (await page.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text();
  const headers = { 'X-WP-Nonce': nonce };
  const cleanup = [];
  const wc = async (method, path, data) => {
    const response = await page.request.fetch('/wp-json/wc/v3/' + path, { method, headers, ...(data ? { data } : {}) });
    expect(response.ok(), await response.text()).toBe(true);
    return response.json();
  };
  const guest = await browser.newContext({ baseURL });
  const code = 'zion-release-' + randomUUID().slice(0, 8);
  try {
    const taxes = 'settings/general/woocommerce_calc_taxes';
    const originalTaxes = (await wc('GET', taxes)).value;
    cleanup.push(() => wc('PUT', taxes, { value: originalTaxes }));
    await wc('PUT', taxes, { value: 'yes' });
    const cod = await wc('GET', 'payment_gateways/cod');
    cleanup.push(() => wc('PUT', 'payment_gateways/cod', { enabled: cod.enabled }));
    await wc('PUT', 'payment_gateways/cod', { enabled: true });
    const tax = await wc('POST', 'taxes', { country: 'RO', rate: '10.0000', name: 'Zion test tax', shipping: false });
    cleanup.push(() => wc('DELETE', `taxes/${tax.id}?force=true`));
    const zone = await wc('POST', 'shipping/zones', { name: 'Zion test shipping', order: 0 });
    cleanup.push(() => wc('DELETE', `shipping/zones/${zone.id}?force=true`));
    await wc('PUT', `shipping/zones/${zone.id}/locations`, [{ code: 'RO', type: 'country' }]);
    const shipping = await wc('POST', `shipping/zones/${zone.id}/methods`, {
      method_id: 'flat_rate', enabled: true, settings: { cost: '8', title: 'Zion local flat rate', tax_status: 'none' },
    });
    const coupon = await wc('POST', 'coupons', { code, discount_type: 'fixed_cart', amount: '5.00' });
    cleanup.push(() => wc('DELETE', `coupons/${coupon.id}?force=true`));
    const [product] = await wc('GET', 'products?sku=ZION-DEMO-3D-01');

    const base = '/wp-json/wc/store/v1/';
    const session = await guest.request.get(base + 'cart');
    expect(session.ok()).toBe(true);
    const token = session.headers()['cart-token'];
    expect(token).toBeTruthy();
    const store = async (route, data) => {
      const response = await guest.request.post(base + route, { headers: { 'Cart-Token': token }, data });
      expect(response.ok(), await response.text()).toBe(true);
      return response.json();
    };
    const address = {
      first_name: 'Zion', last_name: 'Test', address_1: 'Strada Exemplu 1', city: 'București',
      state: 'B', postcode: '010101', country: 'RO',
    };
    const billing = { ...address, email: `${code}@example.test`, phone: '0700000000' };
    await store('cart/add-item', { id: product.id, quantity: 1 });
    await store('cart/update-customer', { billing_address: billing, shipping_address: address });
    await store('cart/apply-coupon', { code });
    const cart = await store('cart/select-shipping-rate', { package_id: 0, rate_id: `flat_rate:${shipping.instance_id}` });
    expect(Number(cart.totals.total_discount)).toBeGreaterThan(0);
    expect(Number(cart.totals.total_shipping)).toBeGreaterThan(0);
    expect(Number(cart.totals.total_tax)).toBeGreaterThan(0);
    const order = await store('checkout', { billing_address: billing, shipping_address: address, payment_method: 'cod' });
    cleanup.push(() => wc('DELETE', `orders/${order.order_id}?force=true`));
    expect(order.payment_result.payment_status).toBe('success');
    const saved = await wc('GET', `orders/${order.order_id}`);
    expect(saved.payment_method).toBe('cod');
    expect(saved.line_items.map(item => item.product_id)).toContain(product.id);
    expect(saved.coupon_lines.map(item => item.code)).toContain(code);
    expect(saved.shipping_lines.map(item => item.method_id)).toContain('flat_rate');
    expect(Number(saved.total_tax)).toBeGreaterThan(0);
  } finally {
    await guest.close();
    const errors = [];
    for (const restore of cleanup.reverse()) {
      try { await restore(); } catch (error) { errors.push(error); }
    }
    expect(errors).toEqual([]);
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
      const ajax = await member.request.post('/wp-admin/admin-ajax.php', { form: { action: 'zion_theme_control', operation } });
      expect(ajax.status()).toBe(403);
    }
  } finally {
    const deleted = await page.request.delete(`/wp-json/wp/v2/users/${user.id}?force=true&reassign=${admin.id}`, { headers });
    await subscriber.close();
    expect(deleted.ok()).toBe(true);
  }
});

test('visual card choices save with Ajax and remain independent by destination', async ({ page }) => {
  test.setTimeout(180_000);
  await login(page);
  await openSection(page, 'tools');
  const original = await exportSettings(page);
  const presets = ['atelier', 'editorial', 'studio-compact', 'technical', 'focus'];
  const labels = ['Atelier', 'Editorial', 'Compact Studio', 'Technical', 'Premium Focus'];
  try {
    await importSettings(page, JSON.stringify({ version: 2, global: {}, shops: {}, items: {} }));
    let stale;
    for (let index = 0; index < presets.length; index++) {
      await page.goto(`${base}&section=cards&scope=home`);
      await page.getByRole('button', { name: labels[index], exact: true }).click();
      await expect(field(page, 'card_preset')).toHaveValue(presets[index]);
      await expect(page.locator('.zion-design-preview')).toBeVisible();
      await expect.poll(() => page.locator('.zion-design-preview img').evaluate(img => img.complete && img.naturalWidth > 0)).toBe(true);
      const previewWidth = await page.locator('.zion-design-preview').evaluate(element => element.getBoundingClientRect().width);
      const formWidth = await page.locator('[data-zion-section]').evaluate(element => element.getBoundingClientRect().width);
      expect(previewWidth / formWidth).toBeGreaterThan(.25);
      expect(previewWidth / formWidth).toBeLessThan(.65);
      const activeColor = await page.locator('.nav-tab-wrapper').evaluate(element => getComputedStyle(element).backgroundColor);
      await expect(page.locator('[data-zion-design][aria-pressed="true"]')).toHaveCSS('background-color', activeColor);
      if (index === 0) await page.locator('.zion-design-picker').screenshot({ path: 'build/control-card-selector-1.1.9.png' });
      const origin = await page.evaluate(() => performance.timeOrigin);
      if (index === 0) stale = await requestFields(page.locator('[data-zion-section]'));
      const response = page.waitForResponse(reply => reply.url().includes('admin-ajax.php') && reply.request().method() === 'POST');
      await save(page);
      expect((await response).ok()).toBe(true);
      expect(await page.evaluate(() => performance.timeOrigin)).toBe(origin);
      await expect(field(page, 'card_preset')).toHaveValue(presets[index]);

      for (const width of [390, 1440]) {
        await page.setViewportSize({ width, height: 850 });
        await page.goto('/');
        const card = page.locator('.zion-home-products .zion-card-design').first();
        await expect(card).toHaveClass(new RegExp(`zion-product-card--${presets[index]}\\b`));
        await expect(card.locator('.zion-product-card__title a')).toHaveCSS('text-decoration-line', 'none');
        await expect(card.locator('.zion-stock-pill')).toBeVisible();
        expect(await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth)).toBe(0);
        if (width === 1440) await card.screenshot({ path: "build/card-" + presets[index] + "-1.1.8.png" });
        const box = await card.locator('.zion-product-card__image').boundingBox();
        const media = await card.locator('.zion-product-card__media').boundingBox();
        expect(Math.abs(box.width - media.width)).toBeLessThan(1);
      }
    }

    const conflict = await page.request.post('/wp-admin/admin-ajax.php', { form: { ...stale, operation: 'save' } });
    expect(conflict.status()).toBe(409);

    for (const [scope, preset] of [['3d', 'technical'], ['software', 'editorial']]) {
      await page.goto(`${base}&section=cards&scope=${scope}`);
      await field(page, 'card_preset').selectOption(preset);
      await save(page);
    }
    for (const [url, preset] of [['/', 'focus'], ['/magazin-3d/', 'technical'], ['/magazin-software/', 'editorial']]) {
      await page.goto(url);
      await expect(page.locator('main .zion-card-design').first()).toHaveClass(new RegExp(`zion-product-card--${preset}\\b`));
    }
    await page.goto(`${base}&section=cards&scope=home`);
    await page.getByRole('button', { name: 'Resetează secțiunea', exact: true }).click();
    await expect(page.getByText('Secțiunea a revenit la valorile moștenite.', { exact: true })).toBeVisible();
    await expect(page.locator('[data-zion-design="technical"]')).toHaveAttribute('aria-pressed', 'true');
    await page.goto('/');
    await expect(page.locator('.zion-home-products .zion-card-design').first()).toHaveClass(/zion-product-card--technical\b/);
  } finally {
    await importSettings(page, original);
  }
});

test('single product visual designs are independent for 3D Print and Software', async ({ page }) => {
  test.setTimeout(240_000);
  await login(page);
  await openSection(page, 'tools');
  const original = await exportSettings(page);
  const nonce = await (await page.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text();
  const headers = { 'X-WP-Nonce': nonce };
  const originals = [];
  for (const slug of ['suport-modular-3d', 'zion-catalog-tools']) {
    const response = await page.request.get(`/wp-json/wc/v3/products?slug=${slug}`, { headers });
    expect(response.ok()).toBe(true);
    originals.push((await response.json())[0]);
  }
  const designs = ['atelier', 'technical', 'immersive', 'focus', 'editorial'];
  const labels = ['Atelier', 'Technical', 'Immersive', 'Focus cumpărare', 'Editorial Premium'];
  let variable;
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  try {
    await importSettings(page, JSON.stringify({ version: 2, global: {}, shops: {}, items: {} }));
    const created = await page.request.post('/wp-json/wc/v3/products', { headers, data: {
      name: 'Zion design variation fixture', type: 'variable', status: 'publish',
      images: originals.map(product => ({ id: product.images[0].id })),
      attributes: [{ name: 'Material', visible: true, variation: true, options: ['PLA', 'PETG'] }],
    } });
    expect(created.status()).toBe(201);
    variable = await created.json();
    for (const [material, price, stock] of [['PLA', '49', 100], ['PETG', '59', 0]]) {
      const variation = await page.request.post(`/wp-json/wc/v3/products/${variable.id}/variations`, { headers, data: {
        regular_price: price, manage_stock: true, stock_quantity: stock, attributes: [{ name: 'Material', option: material }],
      } });
      expect(variation.status()).toBe(201);
    }
    for (const product of originals) {
      const images = [product.images[0].id, originals.find(other => other.id !== product.id).images[0].id].map(id => ({ id }));
      const response = await page.request.put(`/wp-json/wc/v3/products/${product.id}`, { headers, data: { images } });
      expect(response.ok()).toBe(true);
    }
    for (const [scope, url] of [['3d', '/product/suport-modular-3d/'], ['software', '/product/zion-catalog-tools/']]) {
      for (let index = 0; index < designs.length; index++) {
        await page.setViewportSize({ width: 1440, height: 950 });
        await page.goto(`${base}&section=product&scope=${scope}`);
        await expect(page.getByRole('link', { name: 'Single Product 3D Print', exact: true })).toBeVisible();
        await expect(page.getByRole('link', { name: 'Single Product Software', exact: true })).toBeVisible();
        await page.getByRole('button', { name: labels[index], exact: true }).click();
        await expect(field(page, 'product_design')).toHaveValue(designs[index]);
        await expect.poll(() => page.locator('.zion-design-preview img').evaluate(img => img.complete && img.naturalWidth > 0)).toBe(true);
        if (scope === '3d' && index === 0) {
          await page.locator('.zion-design-picker').screenshot({ path: 'build/control-product-selector-1.1.10.png' });
          await page.screenshot({ path: 'build/control-product-center-1.1.10.png' });
        }
        const timeOrigin = await page.evaluate(() => performance.timeOrigin);
        await save(page);
        expect(await page.evaluate(() => performance.timeOrigin)).toBe(timeOrigin);
        for (const width of [1440, 390, 768, 1024]) {
          await page.setViewportSize({ width, height: 950 });
          await page.goto(url);
          const product = page.locator(`.zion-product-design--${designs[index]}.zion-product-shop--${scope}`);
          await expect(product).toBeVisible();
          await expect(product.locator('h1')).toHaveCount(1);
          await expect(product.locator('form.cart')).toHaveCount(1);
          await expect(product.locator('.single_add_to_cart_button')).toBeVisible();
          await expect(product.locator('.woocommerce-product-gallery__image img').first()).toBeVisible();
          expect(await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth)).toBe(0);
          await expect(product.locator('.flex-control-thumbs img')).toHaveCount(2);
          const media = await product.locator('.zion-product-media').boundingBox();
          const gallery = await product.locator('.wp-block-woocommerce-product-image-gallery').boundingBox();
          expect(Math.abs(media.width - gallery.width)).toBeLessThanOrEqual(2);
          await product.locator('.flex-control-thumbs img').last().press('Enter');
          await expect(product.locator('.flex-control-thumbs img').last()).toHaveClass(/flex-active/);
          // FlexSlider can clear its animating flag before the CSS transform settles.
          await expect.poll(async () => {
            const slide = await product.locator('.woocommerce-product-gallery__image > a > img').last().boundingBox();
            const viewport = await product.locator('.flex-viewport').boundingBox();
            return Math.max(Math.abs(slide.width - viewport.width), Math.abs(slide.x - viewport.x));
          }).toBeLessThan(2);
          if (width === 390 || width === 1440) await product.locator('[data-zion-section="hero"]').screenshot({ path: `build/product-${scope}-${designs[index]}-${width}-1.1.10.png` });
        }
      }
    }
    // Changing Software did not overwrite the 3D choice. Section reset is scoped.
    await page.goto(`${base}&section=product&scope=3d`);
    await expect(field(page, 'product_design')).toHaveValue('editorial');
    await field(page, 'product_design').selectOption('technical');
    await save(page);
    await page.goto('/product/zion-catalog-tools/');
    await expect(page.locator('.zion-product-design--editorial')).toBeVisible();
    await page.goto(`${base}&section=product&scope=software`);
    await page.getByRole('button', { name: 'Resetează secțiunea', exact: true }).click();
    await expect(page.getByText('Secțiunea a revenit la valorile moștenite.', { exact: true })).toBeVisible();
    await page.goto('/product/suport-modular-3d/');
    await expect(page.locator('.zion-product-design--technical')).toBeVisible();
    await page.goto('/product/zion-catalog-tools/');
    await expect(page.locator('.zion-product-design--atelier')).toBeVisible();
    await page.goto('/product/suport-modular-3d/');
    const id = await page.evaluate(() => document.body.className.match(/postid-(\d+)/)[1]);
    await openSection(page, 'tools', id);
    await field(page, 'product_design').selectOption('immersive');
    await save(page);
    await page.goto('/product/suport-modular-3d/');
    await expect(page.locator('.zion-product-design--immersive')).toBeVisible();
    for (const design of designs) {
      await page.goto(`${base}&section=product&scope=global`);
      await field(page, 'product_design').selectOption(design);
      await save(page);
      for (const width of [390, 1440]) {
        await page.setViewportSize({ width, height: 950 });
        await page.goto(variable.permalink);
        const form = page.locator('form.variations_form');
        await expect(page.locator(`.zion-product-design--${design}`)).toBeVisible();
        await expect(form.locator('.single_add_to_cart_button')).toHaveClass(/disabled/);
        await form.locator('select').selectOption({ label: 'PLA' });
        await expect(page.locator('.zion-product-attributes')).toContainText('Material');
        await expect(form.locator('.woocommerce-variation-price')).toContainText('49');
        await expect(form.locator('.single_add_to_cart_button')).not.toHaveClass(/disabled/);
        const origin = await page.evaluate(() => performance.timeOrigin);
        await form.locator('.single_add_to_cart_button').click();
        await expect(page.locator('.zion-purchase-notice')).toContainText(/adăugat.*coș/s);
        expect(await page.evaluate(() => performance.timeOrigin)).toBe(origin);
        expect(await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth)).toBe(0);
      }
    }
    expect(errors).toEqual([]);
  } finally {
    if (variable) await page.request.delete(`/wp-json/wc/v3/products/${variable.id}?force=true`, { headers });
    for (const product of originals) {
      const response = await page.request.put(`/wp-json/wc/v3/products/${product.id}`, { headers, data: { images: product.images.map(image => ({ id: image.id })) } });
      expect(response.ok()).toBe(true);
    }
    await importSettings(page, original);
  }
});

test('cart visual designs preserve native totals, quantities, coupons and checkout', async ({ page, browser, baseURL }) => {
  test.setTimeout(300_000);
  await login(page);
  await openSection(page, 'tools');
  const original = await exportSettings(page);
  const headers = { 'X-WP-Nonce': await (await page.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text() };
  const guest = await browser.newContext({ baseURL, viewport: { width: 1440, height: 1000 } });
  const customer = await guest.newPage();
  const errors = [];
  customer.on('pageerror', error => errors.push(error.message));
  const code = 'zion-cart-' + randomUUID().slice(0, 8);
  let coupon;
  const readCart = async () => {
    const response = await customer.request.get('/wp-json/wc/store/v1/cart');
    expect(response.ok()).toBe(true);
    return response.json();
  };
  try {
    const created = await page.request.post('/wp-json/wc/v3/coupons', { headers, data: { code, discount_type: 'fixed_cart', amount: '5' } });
    expect(created.status()).toBe(201);
    coupon = await created.json();
    for (const slug of ['suport-modular-3d', 'zion-catalog-tools']) {
      const products = await (await customer.request.get(`/wp-json/wc/store/v1/products?slug=${slug}`)).json();
      expect(products).toHaveLength(1);
      await customer.goto(`/?add-to-cart=${products[0].id}`);
    }
    const designs = ['atelier', 'editorial', 'technical', 'contrast', 'focus'];
    const labels = ['Atelier', 'Studio Editorial', 'Technical', 'Contrast Premium', 'Focus Checkout'];
    for (let index = 0; index < designs.length; index++) {
      await page.goto(`${base}&section=cart`);
      await expect(page.getByRole('heading', { name: 'Coș de cumpărături', exact: true })).toBeVisible();
      await expectStudioLink(page, 'Editează șablonul coșului în Zion Studio', 'templates', 'zion-theme//page-cart');
      await page.getByRole('button', { name: labels[index], exact: true }).click();
      await expect(field(page, 'cart_design')).toHaveValue(designs[index]);
      await expect.poll(() => page.locator('.zion-design-preview img').evaluate(img => img.complete && img.naturalWidth > 0)).toBe(true);
      const origin = await page.evaluate(() => performance.timeOrigin);
      await save(page);
      expect(await page.evaluate(() => performance.timeOrigin)).toBe(origin);
      if (index === 0) await page.locator('.zion-design-picker').screenshot({ path: 'build/control-cart-selector-1.1.11.png' });
      const before = await readCart();
      for (const width of [1440, 390, 768, 1024]) {
        await customer.setViewportSize({ width, height: 1000 });
        await customer.goto('/cart/');
        const cart = customer.locator(`.zion-cart--${designs[index]}`);
        await expect(cart).toBeVisible();
        await expect(customer.locator('.zion-cart-page')).toBeVisible();
        await expect(customer.locator('main h1')).toHaveCount(1);
        await expect(cart.locator('.wc-block-components-product-name')).toHaveCount(2);
        await expect(cart.locator('.wc-block-cart__submit-button')).toHaveCount(1);
        await expect(cart.locator('.wc-block-cart__submit-button')).toBeVisible();
        await expect(cart.getByLabel('Cantitatea pentru Suport modular 3D din coș.')).toBeVisible();
        await expectCouponContrast(await openCoupon(customer));
        expect(await customer.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth)).toBe(0);
        expect((await readCart()).totals).toEqual(before.totals);
        for (const row of await cart.locator('.wc-block-cart-items__row').all()) {
          const title = await row.locator('.wc-block-components-product-name').boundingBox();
          const total = await row.locator('.wc-block-cart-item__total').boundingBox();
          expect(title.x + title.width).toBeLessThanOrEqual(total.x + 1);
        }
        await expect(cart.locator('img[src*="admin/previews"]')).toHaveCount(0);
        if (designs[index] === 'editorial') await expect(cart.locator('.wc-block-cart-item__image img').first()).toHaveAttribute('src', before.items[0].images[0].src);
        if (width === 1440 || width === 390) await cart.screenshot({ path: `build/cart-${designs[index]}-${width}-1.1.11.png` });
      }
      const documentStart = await customer.evaluate(() => performance.timeOrigin);
      const quantity = index % 2 === 0 ? 2 : 1;
      let release;
      if (index === 0) {
        const hold = new Promise(resolve => { release = resolve; });
        await customer.route(url => decodeURIComponent(url.href).includes('/wc/store/'), async route => {
          if (route.request().method() === 'POST') await hold;
          await route.continue();
        });
      }
      try {
        await customer.getByLabel('Cantitatea pentru Suport modular 3D din coș.').fill(String(quantity));
        await customer.getByLabel('Cantitatea pentru Suport modular 3D din coș.').press('Tab');
        if (index === 0) await expect(customer.locator('.zion-commerce-progress')).toHaveText('Se încarcă…');
      } finally {
        if (release) { release(); await customer.unrouteAll({ behavior: 'wait' }); }
      }
      await expect.poll(async () => (await readCart()).items.find(item => item.name === 'Suport modular 3D').quantity).toBe(quantity);
      expect(await customer.evaluate(() => performance.timeOrigin)).toBe(documentStart);
      const withoutCoupon = (await readCart()).totals;
      await openCoupon(customer);
      await expectCouponContrast(customer.locator('.wc-block-components-totals-coupon input'));
      await customer.locator('.wc-block-components-totals-coupon input').fill(code);
      await customer.getByRole('button', { name: 'Aplică', exact: true }).click();
      await expect.poll(async () => (await readCart()).coupons.map(item => item.code)).toContain(code);
      expect(Number((await readCart()).totals.total_discount)).toBeGreaterThan(0);
      await customer.locator('.wc-block-components-totals-discount .wc-block-components-chip__remove').click();
      await expect.poll(async () => (await readCart()).coupons).toEqual([]);
      expect((await readCart()).totals).toEqual(withoutCoupon);
    }
    await customer.locator('.wc-block-cart__submit-button').click();
    await expect(customer).toHaveURL(/\/checkout\/?$/);
    await expect(customer.getByRole('form', { name: 'Finalizare comandă', exact: true })).toBeVisible();
    await customer.goBack();
    await expect(customer.locator('.wc-block-cart-item__remove-link')).toHaveCount(2);
    for (const remaining of [1, 0]) {
      await customer.locator('.wc-block-cart-item__remove-link').first().click();
      await expect(customer.locator('.wc-block-cart-item__remove-link')).toHaveCount(remaining);
    }
    await expect(customer.getByRole('heading', { name: /Coșul tău este gol|Your cart is currently empty/i })).toBeVisible();
    await expect(customer.getByRole('heading', { name: 'Coșul tău este gol.', exact: true })).toBeVisible();
    expect(await customer.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth)).toBe(0);
    expect(errors).toEqual([]);
    await openSection(page, 'cart');
    await page.getByRole('button', { name: 'Resetează secțiunea', exact: true }).click();
    await expect(page.getByText('Secțiunea a revenit la valorile moștenite.', { exact: true })).toBeVisible();
    await expect(field(page, 'cart_design')).toHaveValue('');
    await expect(page.locator('[data-zion-design="atelier"]')).toHaveAttribute('aria-pressed', 'true');
  } finally {
    await importSettings(page, original);
    if (coupon) await page.request.delete(`/wp-json/wc/v3/coupons/${coupon.id}?force=true`, { headers });
    await guest.close();
  }
});

test('Technical card actions stay aligned after the native view-cart link appears', async ({ page, browser, baseURL }) => {
  test.setTimeout(240_000);
  await login(page);
  await openSection(page, 'tools');
  const original = await exportSettings(page);
  try {
    await importSettings(page, JSON.stringify({ version: 2, global: { card_preset: 'technical' }, shops: {}, items: {} }));
    for (const url of ['/', '/magazin-3d/', '/magazin-software/']) {
      for (const width of [390, 768, 1024, 1440]) {
        const guest = await browser.newContext({ baseURL, viewport: { width, height: 950 } });
        try {
          const customer = await guest.newPage();
          await customer.goto(url);
          const card = customer.locator('main .zion-product-card--technical').filter({ has: customer.locator('.wc-block-components-product-button__button') }).first();
          const add = card.locator('.wc-block-components-product-button__button');
          const details = card.locator('.zion-product-card__details a');
          const originalDetails = await details.boundingBox();
          const originalCard = await card.boundingBox();
          await add.click();
          const view = card.locator('.zion-card-cart-hop');
          await expect(view).toBeVisible();
          const [a, b, c, v] = await Promise.all([add.boundingBox(), details.boundingBox(), card.boundingBox(), view.boundingBox()]);
          expect(b.height).toBeCloseTo(originalDetails.height, 0);
          expect(a.height).toBeLessThan(80);
          expect(c.height).toBeCloseTo(originalCard.height,0);
          expect(v.y).toBeLessThan(a.y);
          expect(v.y + v.height).toBeLessThanOrEqual(c.y + c.height + 1);
          if (width>520 && await card.locator('.zion-product-card__body').evaluate(e=>e.clientWidth-parseFloat(getComputedStyle(e).paddingLeft)-parseFloat(getComputedStyle(e).paddingRight))>260) expect(Math.abs(a.y-b.y)).toBeLessThan(1); else expect(b.y).toBeGreaterThanOrEqual(a.y+a.height-1);
          expect(await customer.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth)).toBe(0);
          await customer.reload();await expect(card.locator('.zion-card-cart-hop')).toBeVisible();await expect(card.locator('.wc-block-components-product-button__button')).toContainText('în coș');
          if (url.includes('software') && width === 1440) await card.screenshot({ path: 'build/technical-added-to-cart-1.1.28.png' });
        } finally { await guest.close(); }
      }
    }
  } finally { await importSettings(page, original); }
});

test('checkout design selector preserves native form, validation and totals at four widths', async ({ page, browser, baseURL }) => {
  test.setTimeout(240_000);
  await login(page);
  await openSection(page, 'tools');
  const original = await exportSettings(page);
  const guest = await browser.newContext({ baseURL });
  const customer = await guest.newPage();
  const errors = [];
  customer.on('pageerror', error => errors.push(error.message));
  try {
    for (const slug of ['suport-modular-3d', 'zion-catalog-tools']) {
      const products = await (await customer.request.get(`/wp-json/wc/store/v1/products?slug=${slug}`)).json();
      await customer.goto(`/?add-to-cart=${products[0].id}`);
    }
    const readCart = async () => (await customer.request.get('/wp-json/wc/store/v1/cart')).json();
    const totals = (await readCart()).totals;
    const designs = ['atelier', 'editorial', 'technical', 'contrast', 'focus'];
    const labels = ['Atelier', 'Studio Editorial', 'Technical', 'Contrast Premium', 'Focus Checkout'];
    for (let index = 0; index < designs.length; index++) {
      await openSection(page, 'checkout');
      await expectStudioLink(page, 'Editează șablonul checkoutului în Zion Studio', 'templates', 'zion-theme//page-checkout');
      await page.getByRole('button', { name: labels[index], exact: true }).click();
      await expect(field(page, 'checkout_design')).toHaveValue(designs[index]);
      await expect.poll(() => page.locator('.zion-design-preview img').evaluate(img => img.complete && img.naturalWidth > 0)).toBe(true);
      const start = await page.evaluate(() => performance.timeOrigin);
      await save(page);
      expect(await page.evaluate(() => performance.timeOrigin)).toBe(start);
      if (index === 0) await page.locator('.zion-design-picker').screenshot({ path: 'build/control-checkout-selector-1.1.12.png' });
      for (const width of [1440, 390, 768, 1024]) {
        await customer.setViewportSize({ width, height: 1000 });
        await customer.goto('/checkout/');
        const checkout = customer.locator(`.zion-checkout--${designs[index]}`);
        await expect(checkout).toBeVisible();
        await expect(customer.locator('.zion-checkout-page')).toBeVisible();
        await expect(checkout.getByRole('form', { name: 'Finalizare comandă', exact: true })).toHaveCount(1);
        await expect(checkout.locator('.wc-block-checkout__sidebar .wc-block-components-order-summary-item')).toHaveCount(2);
        const badge = checkout.locator('.wc-block-components-order-summary-item__quantity').first();
        const badgeColors = await badge.evaluate(e=>({ink:getComputedStyle(e.querySelector('span')).color,bg:getComputedStyle(e).backgroundColor}));
        expect(contrastRatio(badgeColors.ink,badgeColors.bg)).toBeGreaterThanOrEqual(4.5);
        await expect(checkout.locator('.wc-block-components-checkout-place-order-button')).toHaveCount(1);
        await expect(customer.locator('#email')).toBeVisible();
        for (const select of await checkout.locator('select:visible').all()) await expectSelectTextFits(select);
        // Physical carts may default to using the shipping address for billing.
        // Select separate billing explicitly before testing the billing fields.
        const sameAddress = customer.getByRole('checkbox', { name: 'Folosește aceeași adresă pentru facturare', exact: true });
        await expect(customer.locator('#billing-first_name').or(sameAddress).first()).toBeVisible();
        if (await sameAddress.count()) await sameAddress.uncheck();
        await expect(customer.locator('#billing-first_name')).toBeVisible();
        await expectCouponContrast(await openCoupon(customer));
        expect(await customer.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth)).toBe(0);
        expect((await readCart()).totals).toEqual(totals);
        await expect(checkout.locator('img[src*="admin/previews"]')).toHaveCount(0);
        const input = await customer.locator('#email').evaluate(el => ({ bg: getComputedStyle(el).backgroundColor, ink: getComputedStyle(el).color }));
        expect(input.bg).not.toBe(input.ink);
        if (width === 1440 || width === 390) await checkout.screenshot({ path: `build/checkout-${designs[index]}-${width}-1.1.12.png` });
      }
      const formStart = await customer.evaluate(() => performance.timeOrigin);
      await openCoupon(customer);
      const couponInput=customer.locator('.wc-block-components-totals-coupon input');
      await expectCouponContrast(couponInput);
      await couponInput.fill('contrast-demo');
      await expectCouponContrast(couponInput);
      await couponInput.clear();
      await customer.locator('#email').fill('invalid');
      await customer.locator('#email').press('Tab');
      await expect(customer.locator('#email')).toHaveAttribute('aria-invalid', 'true');
      await customer.locator('#email').fill('checkout@example.test');
      await customer.locator('#email').press('Tab');
      await expect(customer.locator('#email')).toHaveAttribute('aria-invalid', 'false');
      await customer.locator('#billing-first_name').fill('Zion');
      await customer.locator('#billing-first_name').press('Tab');
      await customer.setViewportSize({ width: 1440, height: 1000 });
      await expect(customer.locator('#billing-first_name')).toHaveValue('Zion');
      expect(await customer.evaluate(() => performance.timeOrigin)).toBe(formStart);
    }
    expect(errors).toEqual([]);
    await openSection(page, 'checkout');
    await page.getByRole('button', { name: 'Resetează secțiunea', exact: true }).click();
    await expect(page.getByText('Secțiunea a revenit la valorile moștenite.', { exact: true })).toBeVisible();
    await expect(field(page, 'checkout_design')).toHaveValue('');
  } finally {
    await importSettings(page, original);
    await guest.close();
  }
});

test('anonymous carts stay isolated after refresh and native country changes persist', async ({page,browser,baseURL})=>{
  test.setTimeout(90000);
  test.skip(new URL(baseURL).port!=='8917','Guest fixtures require the isolated tests instance.');
  await login(page);
  const headers={'X-WP-Nonce':await(await page.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text()};
  const route='/wp-json/wc/v3/settings/general/woocommerce_default_customer_address';
  const original=(await(await page.request.get(route,{headers})).json()).value;
  const first=await browser.newContext({baseURL}),second=await browser.newContext({baseURL});
  try {
    expect((await page.request.put(route,{headers,data:{value:'geolocation'}})).ok()).toBe(true);
    const visitor=await first.newPage(),other=await second.newPage();
    const products=await(await first.request.get('/wp-json/wc/store/v1/products?slug=suport-modular-3d')).json();
    const product=products[0];await visitor.goto('/magazin-3d/');
    const card=visitor.locator(`.zion-product-card[data-zion-product="${product.id}"]`);
    await card.getByRole('button',{name:/Adaugă în coș/}).click();
    await expect(card.locator('.zion-card-cart-hop')).toBeVisible();await card.locator('.zion-card-cart-hop').click();
    await expect(visitor.locator('.wc-block-cart-items')).toContainText(product.name);
    await visitor.reload();await expect(visitor.locator('.wc-block-cart-items')).toContainText(product.name);
    await other.goto('/cart/');expect((await(await second.request.get('/wp-json/wc/store/v1/cart')).json()).items).toEqual([]);
    await visitor.goto('/checkout/');const country=visitor.locator('#billing-country');
    const same=visitor.getByRole('checkbox',{name:'Folosește aceeași adresă pentru facturare',exact:true});if(await same.count())await same.uncheck();
    await expect(country).toHaveValue('RO');expect(await country.locator('option:not([disabled])').first().getAttribute('value')).toBe('RO');
    await visitor.locator('#email').fill('guest@example.test');await visitor.locator('#billing-first_name').fill('Zion');await visitor.locator('#billing-last_name').fill('Demo');await country.selectOption('DE');await visitor.locator('#billing-address_1').fill('Teststrasse 1');await visitor.locator('#billing-city').fill('Berlin');await visitor.locator('#billing-postcode').fill('10115');await visitor.locator('#billing-postcode').press('Tab');await expect.poll(async()=>(await(await first.request.get('/wp-json/wc/store/v1/cart')).json()).billing_address.country).toBe('DE');
    await visitor.reload();if(await same.count())await same.uncheck();await expect(country).toHaveValue('DE');
    await visitor.goto('/cart/');await expect(visitor.locator('.wc-block-cart-items')).toContainText(product.name);
  } finally {
    expect((await page.request.put(route,{headers,data:{value:original}})).ok()).toBe(true);
    await first.close();await second.close();
  }
});

test('all design sections use compact previews and keyboard accessible lightboxes', async ({ page }) => {
  await login(page);
  for (const section of ['cards', 'product', 'cart', 'checkout', 'account']) {
    await page.setViewportSize({ width: 1440, height: 950 });
    await openSection(page, section);
    const opener = page.locator('.zion-design-preview a');
    const dialog = page.getByRole('dialog', { name: 'Previzualizare design' });
    await expect.poll(() => page.locator('.zion-design-preview img').evaluate(img => img.complete && img.naturalWidth > 0)).toBe(true);
    const small = await page.locator('.zion-design-preview').boundingBox();
    expect(small.width).toBeLessThan(500);
    await opener.click();
    await expect(dialog).toBeVisible();
    await expect(dialog.locator('img')).toHaveAttribute('src', await page.locator('.zion-design-preview img').getAttribute('src'));
    expect((await dialog.boundingBox()).width).toBeGreaterThan(small.width);
    await expect(dialog.getByRole('button')).toBeFocused();
    await page.keyboard.press('Escape');
    await expect(dialog).not.toBeVisible();
    await expect(opener).toBeFocused();
    await page.setViewportSize({ width: 390, height: 850 });
    await opener.click();
    await expect(dialog).toBeVisible();
    expect((await dialog.boundingBox()).x + (await dialog.boundingBox()).width).toBeLessThanOrEqual(390);
    expect((await page.locator('.zion-design-picker').boundingBox()).width).toBeLessThan(390);
    await dialog.getByRole('button', { name: 'Închide previzualizarea' }).click();
    await expect(opener).toBeFocused();
    if (section === 'checkout') await page.screenshot({ path: 'build/admin-compact-mobile-1.1.13.png', fullPage: true });
  }
});

test('active category chips share one heading and preserve native removal and history', async ({ page, browser, baseURL }) => {
  await login(page);
  const headers = { 'X-WP-Nonce': await (await page.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text() };
  const original = (await (await page.request.get('/wp-json/wc/v3/products?slug=suport-modular-3d', { headers })).json())[0];
  const slug = 'zion-chip-' + randomUUID().slice(0, 8);
  const created = await page.request.post('/wp-json/wc/v3/products/categories', { headers, data: { name: 'Categorie secundară demo', slug } });
  expect(created.status()).toBe(201);
  const term = await created.json();
  const guest = await browser.newContext({ baseURL });
  const customer = await guest.newPage();
  try {
    expect((await page.request.put(`/wp-json/wc/v3/products/${original.id}`, { headers, data: { categories: [...original.categories.map(c => ({ id: c.id })), { id: term.id }] } })).ok()).toBe(true);
    await customer.goto(`/magazin-3d/?categories=prototipuri-demo,${slug}&min_price=1`, { waitUntil: 'networkidle' });
    await expect(customer.locator('.zion-category-group')).toHaveCount(1);
    await expect(customer.locator('.zion-category-group')).toHaveCSS('display', 'block');
    await expect(customer.locator('.zion-category-chip')).toHaveCount(2);
    expect(await customer.locator('.zion-category-chip .wc-block-product-filter-removable-chips__label').evaluateAll(nodes => nodes.map(n => n.dataset.zionShortLabel))).toEqual(['Categorie secundară demo', 'Prototipuri demo']);
    await customer.locator('.zion-category-chip').filter({ hasText: 'Categorie secundară demo' }).getByRole('button').click();
    await expect(customer).toHaveURL(/categories=prototipuri-demo/);
    await expect(customer.locator('.zion-category-chip')).toHaveCount(1);
    await expect(customer.locator('.zion-category-group')).toHaveCount(1);
    await customer.goto(`/magazin-3d/?categories=prototipuri-demo,${slug}&min_price=1`, { waitUntil: 'networkidle' });
    await customer.goto('/magazin-software/');
    await customer.goBack();
    await expect(customer.locator('.zion-category-chip')).toHaveCount(2);
    await expect(customer.locator('.zion-category-group')).toHaveCount(1);
    await customer.getByRole('link', { name: 'Resetează filtrele' }).click();
    await expect(customer.locator('.zion-category-group')).toHaveCount(0);
  } finally {
    await page.request.put(`/wp-json/wc/v3/products/${original.id}`, { headers, data: { categories: original.categories.map(c => ({ id: c.id })) } });
    await page.request.delete(`/wp-json/wc/v3/products/categories/${term.id}?force=true`, { headers });
    await guest.close();
  }
});

test('account designs preserve native endpoints, authentication and customer order isolation', async ({ page, browser, baseURL }) => {
  test.setTimeout(300_000);
  await login(page); await openSection(page, 'tools');
  const original = await exportSettings(page);
  const headers = { 'X-WP-Nonce': await (await page.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text() };
  const admin = await (await page.request.get('/wp-json/wp/v2/users/me', { headers })).json();
  const created = [];
  const orders = [];
  const password = randomUUID();
  const memberContext = await browser.newContext({ baseURL });
  const guestContext = await browser.newContext({ baseURL });
  const member = await memberContext.newPage(); const guest = await guestContext.newPage();
  const errors = []; member.on('pageerror', e => errors.push(e.message)); guest.on('pageerror', e => errors.push(e.message));
  try {
    for (let i = 0; i < 2; i++) {
      const username = 'zion-account-' + randomUUID().slice(0, 8);
      const response = await page.request.post('/wp-json/wp/v2/users', { headers, data: { username, password, email: username+'@example.test', roles: ['customer'] } });
      expect(response.status()).toBe(201); created.push(await response.json());
      const order = await page.request.post('/wp-json/wc/v3/orders', { headers, data: { customer_id: created[i].id, status: 'on-hold' } });
      expect(order.status()).toBe(201); orders.push(await order.json());
    }
    await login(member, created[0].slug, password);
    const apiPages = await (await page.request.get('/wp-json/wp/v2/pages?slug=my-account', { headers })).json();
    expect(apiPages.length).toBe(1);
    const account = new URL(apiPages[0].link).pathname;
    const designs = ['atelier', 'editorial', 'technical', 'contrast', 'focus'];
    for (const design of designs) {
      await openSection(page, 'account');
      await field(page, 'account_design').selectOption(design);
      await expect.poll(() => page.locator('.zion-design-preview img').evaluate(img => img.complete && img.naturalWidth > 0)).toBe(true);
      await save(page);
      for (const width of [1440, 390, 768, 1024]) {
        await member.setViewportSize({ width, height: 1000 }); await guest.setViewportSize({ width, height: 1000 });
        await member.goto(account); await guest.goto(account);
        const root = member.locator(`.zion-account--${design}`);
        await expect(root).toBeVisible();
        await expect(root.locator('.woocommerce-MyAccount-navigation')).toHaveCount(1);
        await expect(root.locator('.zion-account-orders')).toContainText('#'+orders[0].number);
        await expect(root.locator('.zion-account-orders')).not.toContainText('#'+orders[1].number);
        await expect(guest.locator(`.zion-account--${design} form.login`)).toBeVisible();
        await expect(guest.locator('.zion-account-orders')).toHaveCount(0);
        expect(await member.evaluate(() => document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
        expect(await guest.evaluate(() => document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
        if (width === 1440 || width === 390) await root.screenshot({ path: `build/account-${design}-${width}-1.1.14.png` });
      }
      for (const width of [390, 1440]) {
        await member.setViewportSize({ width, height: 1000 });
        for (const endpoint of ['orders', 'downloads', 'edit-address', 'edit-account']) {
          await member.goto(account+endpoint+'/');
          await expect(member.locator(`.zion-account--${design} .woocommerce-MyAccount-content`)).toBeVisible();
          expect(await member.evaluate(() => document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
          if (endpoint === 'edit-account') await expect(member.locator('form.edit-account')).toHaveCount(1);
        }
        await member.goto(account+'view-order/'+orders[0].id+'/');
        await expect(member.locator('.woocommerce-MyAccount-content')).toContainText(orders[0].number);
        await member.goto(account+'view-order/'+orders[1].id+'/');
        await expect(member.locator('.woocommerce-MyAccount-content')).toContainText('Comandă invalidă.');
        await expect(member.locator('.woocommerce-order-details')).toHaveCount(0);
      }
    }
    await guest.goto(account+'lost-password/');
    await expect(guest.locator('.lost_reset_password')).toBeVisible();
    await member.goto(account+'edit-account/');
    await member.locator('#account_first_name').fill('Zion');
    await member.locator('#account_last_name').fill('Test');
    await member.locator('#account_display_name').fill('Zion Account Test');
    await member.locator('button[name=save_account_details]').click();
    await expect(member.locator('.woocommerce-message, .wc-block-components-notice-banner.is-success')).toBeVisible();
    await expect(member.locator('#account_display_name')).toHaveValue('Zion Account Test');
    await member.goto(account);
    await expect(member.locator('.woocommerce-MyAccount-content')).toContainText('Zion Account Test');
    await member.locator('.woocommerce-MyAccount-navigation-link--customer-logout a').click();
    await expect(member.locator('form.login')).toBeVisible();
    expect(errors).toEqual([]);
    await openSection(page, 'account');
    await page.getByRole('button', {name:'Resetează secțiunea',exact:true}).click();
    await expect(field(page,'account_design')).toHaveValue('');
  } finally {
    await importSettings(page, original);
    for (const order of orders) await page.request.delete(`/wp-json/wc/v3/orders/${order.id}?force=true`, { headers });
    for (const user of created) await page.request.delete(`/wp-json/wp/v2/users/${user.id}?force=true&reassign=${admin.id}`, { headers });
    await memberContext.close(); await guestContext.close();
  }
});

test('header cart stays visible and switches between cart page and native drawer', async ({ page, browser, baseURL }) => {
  test.setTimeout(120_000);
  await login(page); await openSection(page, 'tools');
  const original = await exportSettings(page);
  const context = await browser.newContext({ baseURL }); const guest = await context.newPage();
  try {
    for (const enabled of ['0', '1']) {
      await openSection(page, 'header');
      await field(page, 'header_cart').selectOption(enabled); await save(page);
      for (const width of [390, 768, 1024, 1440]) {
        await guest.setViewportSize({ width, height: 950 });
        await guest.goto('/magazin-3d/');
        const header = guest.locator('.zion-header');
        await expect(header.locator('.zion-header-cart')).toHaveCount(1);
        if (enabled === '0') {
          const link = header.locator('.zion-header-cart-link');
          await expect(link).toHaveAttribute('href', /\/cart\/$/);
          await expect(link.locator('svg')).toHaveCount(1);
          await expect(header.locator('.wc-block-mini-cart__button')).toHaveCount(0);
          await link.focus(); await guest.keyboard.press('Enter');
          await expect(guest).toHaveURL(/\/cart\/$/);
          await expect(guest.getByRole('dialog')).toHaveCount(0);
        } else {
          const url = guest.url();
          await header.locator('.wc-block-mini-cart__button').focus(); await guest.keyboard.press('Enter');
          const drawer = guest.locator('.wc-block-mini-cart__drawer[role="dialog"]');
          await expect(drawer).toBeVisible();
          // WooCommerce mounts its focus trap after the drawer becomes visible.
          await expect.poll(() => drawer.evaluate(element => element.contains(document.activeElement))).toBe(true);
          expect(guest.url()).toBe(url);
          await guest.keyboard.press('Escape');
          await expect(guest.locator('.wc-block-mini-cart__drawer[role="dialog"]')).toBeHidden();
        }
        expect(await guest.evaluate(() => document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
      }
    }
  } finally { await importSettings(page, original); await context.close(); }
});

test('cart link badge follows native quantities and Ajax while the mini-cart is disabled', async ({ page, browser, baseURL }) => {
  test.setTimeout(180_000);
  await login(page); await openSection(page, 'tools');
  const original = await exportSettings(page);
  const context = await browser.newContext({ baseURL }); const guest = await context.newPage();
  const otherContext = await browser.newContext({ baseURL }); const other = await otherContext.newPage();
  const errors = []; guest.on('pageerror', error => errors.push(error.message));
  try {
    await openSection(page, 'header');
    await field(page, 'header_cart').selectOption('0');
    await expect(field(page, 'header_cart_badge')).toBeVisible();
    await field(page, 'header_cart_badge').selectOption('1'); await save(page);
    for (const width of [390, 768, 1024, 1440]) {
      await guest.setViewportSize({ width, height: 950 }); await guest.goto('/magazin-software/');
      await expect(guest.locator('.zion-header-cart-count')).toHaveText('0');
      await expect(guest.locator('.zion-header-cart-count')).toBeVisible();
      expect(await guest.evaluate(() => document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
    }
    for (const width of [390, 1440]) {
      await guest.setViewportSize({ width, height: 950 }); await guest.goto('/magazin-software/');
      const card = guest.locator('.zion-product-card').filter({ has: guest.getByRole('heading', { name: 'Zion Catalog Tools', exact: true }) });
      const time = await guest.evaluate(() => performance.timeOrigin);
      await card.getByRole('button', { name: /Adaugă în coș/i }).click();
      await expect(guest.locator('.zion-header-cart-count')).toHaveText('1');
      expect(await guest.evaluate(() => performance.timeOrigin)).toBe(time);
      await guest.goto('/product/zion-catalog-tools/');
      await guest.locator('form.cart input.qty').fill('2');
      const productTime = await guest.evaluate(() => performance.timeOrigin);
      await guest.locator('form.cart .single_add_to_cart_button').click();
      await expect(guest.locator('.zion-header-cart-count')).toHaveText('3');
      await expect(guest.locator('.zion-header-cart-link')).toHaveAccessibleName('Coș, 3 produse');
      expect(await guest.evaluate(() => performance.timeOrigin)).toBe(productTime);
      await other.goto('/'); await expect(other.locator('.zion-header-cart-count')).toHaveText('0');
      await guest.locator('.zion-header-cart-link').press('Enter'); await expect(guest).toHaveURL(/\/cart\/$/);
      await expect(guest.locator('.zion-header-cart-count')).toHaveText('3');
      await guest.getByRole('button', {name:/Mărește cantitatea pentru/}).click();
      await expect(guest.locator('.zion-header-cart-count')).toHaveText('4');
      await guest.getByRole('button', {name:/Elimină .* din coș/}).click();
      await expect(guest.locator('.zion-header-cart-count')).toHaveText('0');
      await expect(guest.locator('.wc-block-mini-cart__drawer')).toHaveCount(0);
    }
    await openSection(page, 'header'); await field(page, 'header_cart_badge').selectOption('0'); await save(page);
    await guest.goto('/');
    await expect(guest.locator('.zion-header-cart-link')).toBeVisible();
    await expect(guest.locator('.zion-header-cart-count')).toHaveCount(0);
    await expect(guest.locator('script[src*="header-cart-count.js"]')).toHaveCount(0);
    expect(errors).toEqual([]);
  } finally { await importSettings(page, original); await context.close(); await otherContext.close(); }
});

test('five header designs preserve an edited native menu, active ancestors, pointer paths and mobile navigation', async ({ page, browser, baseURL }) => {
  test.setTimeout(300_000);
  await login(page); await openSection(page, 'tools');
  const original = await exportSettings(page);
  const nonce = await (await page.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text();
  const headers = { 'X-WP-Nonce': nonce };
  const headerRoute = '/wp-json/wp/v2/template-parts/zion-theme//header';
  const originalHeader = await (await page.request.get(headerRoute+'?context=edit', {headers})).json();
  const context = await browser.newContext({baseURL,reducedMotion:'reduce'}); const guest = await context.newPage();
  const fixtureRoutes = []; let headerEdited = false;
  const errors = []; guest.on('pageerror', error=>errors.push(error.message));
  const create = async (resource, data) => {
    const response = await page.request.post('/wp-json/wp/v2/'+resource,{headers,data:{status:'publish',...data}});
    expect(response.status(), await response.text()).toBe(201);
    const result = await response.json(); fixtureRoutes.unshift('/wp-json/wp/v2/'+resource+'/'+result.id+'?force=true'); return result;
  };
  const item = (label,url,children='') => children ? `<!-- wp:navigation-submenu ${JSON.stringify({label,url,kind:'custom'})} -->${children}<!-- /wp:navigation-submenu -->` : `<!-- wp:navigation-link ${JSON.stringify({label,url,kind:'custom'})} /-->`;
  try {
    const group = await create('pages',{title:'WordPress & WooCommerce test',slug:'zion-menu-group-'+randomUUID().slice(0,8)});
    const leaf = await create('pages',{title:'Zion Privacy test',parent:group.id,slug:'zion-privacy-test',content:'<!-- wp:paragraph --><p>Pagină demonstrativă de navigație.</p><!-- /wp:paragraph -->'});
    const content = item('Acasă',baseURL+'/')+item('Magazin 3D',baseURL+'/magazin-3d/')+
      item('Servicii',baseURL+'/servicii/',item('Printare 3D',baseURL+'/printare-3d/')+item('Proiectare și modelare 3D',baseURL+'/proiectare-3d/')+item('Software personalizat pentru afaceri',baseURL+'/software-personalizat/')+item('Platforme și produse SaaS',baseURL+'/platforme-saas/')+item('Pluginuri și integrări software',baseURL+'/pluginuri-si-integrari/')+item('Aplicații Windows și Linux',baseURL+'/aplicatii-windows-linux/')+item('Integrări AI și automatizări inteligente',baseURL+'/integrari-ai/'))+
      item('Software',baseURL+'/magazin-software/',item('WordPress & WooCommerce',group.link,item('Zion Privacy',leaf.link))+item('Magazin Software',baseURL+'/magazin-software/'))+
      item('Zion Food','https://food.zion3d.ro/')+item('Resurse',baseURL+'/jurnal/',item('Blog',baseURL+'/jurnal/')+Array.from({length:24},(_,i)=>item('Destinația '+(i+1),group.link+'?destination='+i)).join(''))+item('Despre noi',group.link)+item('Contact',group.link);
    const menu = await create('navigation',{title:'Zion edited menu fixture',content});
    const canonicalMenu = (await (await page.request.get('/wp-json/wp/v2/navigation/'+menu.id+'?context=edit',{headers})).json()).content.raw;
    const customHeader = `<!-- wp:group {"className":"zion-shell zion-header"} --><div class="wp-block-group zion-shell zion-header"><!-- wp:site-logo {"width":56,"className":"zion-header-logo"} /--><!-- wp:site-title {"level":0,"className":"zion-wordmark"} /--><!-- wp:navigation {"ref":${menu.id},"ariaLabel":"Meniu principal","overlayMenu":"mobile","className":"zion-navigation","layout":{"type":"flex","justifyContent":"right"}} /--><!-- wp:group {"className":"zion-header-tools"} --><div class="wp-block-group zion-header-tools"><!-- wp:woocommerce/customer-account {"displayStyle":"icon_only","className":"zion-header-account"} /--><!-- wp:woocommerce/mini-cart {"hasHiddenPrice":true,"className":"zion-header-cart"} /--></div><!-- /wp:group --></div><!-- /wp:group -->`;
    const updated = await page.request.post(headerRoute,{headers,data:{content:customHeader}});
    expect(updated.ok(),await updated.text()).toBe(true); headerEdited=true;
    await importSettings(page, JSON.stringify({version:2,global:{header_cart:false,header_cart_badge:true},shops:{},items:{}}));
    const designs=['atelier','nexus','capsule','technical','signature'];
    const labels=['Atelier','Nexus Mega','Capsule','Technical Contrast','Signature'];
    for(let index=0;index<designs.length;index++) {
      await page.setViewportSize({width:1440,height:950}); await openSection(page,'header');
      const picker=page.locator('[data-zion-design-picker][data-control="zion-field-header_design"]');
      await expect(picker.locator('[data-zion-design]')).toHaveCount(5);
      await picker.getByRole('button',{name:labels[index],exact:true}).click();
      await expect(field(page,'header_design')).toHaveValue(designs[index]);
      await expect.poll(()=>picker.locator('figure img').evaluate(e=>e.complete&&e.naturalWidth>0)).toBe(true);
      if(index===0) {
        await picker.locator('figure a').click(); await expect(picker.locator('dialog')).toBeVisible();
        await page.keyboard.press('Escape'); await expect(picker.locator('figure a')).toBeFocused();
        await page.screenshot({path:'build/menu-selector-1.1.17.png'});
      }
      const adminTime=await page.evaluate(()=>performance.timeOrigin); await save(page);
      expect(await page.evaluate(()=>performance.timeOrigin)).toBe(adminTime);
      for(const width of [390,768,1024,1440]) {
        await guest.setViewportSize({width,height:950}); await guest.goto(leaf.link+'?menu-check='+designs[index]);
        await expect(guest.locator('body')).toHaveClass(new RegExp('zion-menu-'+designs[index]));
        const nav=guest.locator('header nav.zion-navigation'); const active=nav.locator(`a[href="${leaf.link}"]`);
        await expect(active).toHaveAttribute('aria-current','page');
        await expect(active.locator('.zion-menu-icon--shield')).toHaveCount(1);
        await expect(nav.locator('.zion-nav-ancestor')).toHaveCount(3);
        await expect(guest.locator('.zion-header-logo img')).toBeVisible();
        const geometry=await guest.locator('.wp-site-blocks > header').evaluate(e=>({left:e.getBoundingClientRect().left,right:e.getBoundingClientRect().right,width:document.documentElement.clientWidth,line:getComputedStyle(e,'::after').height}));
        expect(Math.abs(geometry.left)).toBeLessThanOrEqual(1); expect(Math.abs(geometry.right-geometry.width)).toBeLessThanOrEqual(1); expect(geometry.line).toBe('1px');
        expect(await guest.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
        if(width<1101) {
          const opener=nav.locator('.wp-block-navigation__responsive-container-open');const cart=await guest.locator('.zion-header-cart-link').boundingBox();const burger=await opener.boundingBox();expect(burger.x).toBeGreaterThanOrEqual(cart.x+cart.width-1);expect(Math.abs(burger.y+burger.height/2-cart.y-cart.height/2)).toBeLessThan(2);
          expect(await guest.locator('.zion-header').evaluate(header=>!!(header.querySelector('.zion-header-tools').compareDocumentPosition(header.querySelector('nav.zion-navigation'))&Node.DOCUMENT_POSITION_FOLLOWING))).toBe(true);
          await opener.focus(); await guest.keyboard.press('Enter');
          await expect(nav.getByRole('dialog')).toBeVisible(); await expect(active).toBeVisible();
          const parent=nav.getByRole('button',{name:/WordPress/}); await expect(parent).toHaveAttribute('aria-expanded','true');
          const services=nav.getByRole('button',{name:/Servicii/}); await expect(services).toHaveAttribute('aria-expanded','false');
          if(designs[index]==='technical') await expect(parent.locator('..').locator(':scope > ul')).toHaveCSS('background-color','rgb(241, 244, 235)');
          await parent.focus(); await guest.keyboard.press('Enter'); await expect(parent).toHaveAttribute('aria-expanded','false'); await expect(active).toBeHidden();
          await guest.keyboard.press('Enter'); await expect(active).toBeVisible();
          const longMenu=nav.getByRole('button',{name:'Resurse submenu',exact:true});await longMenu.click();const lastLink=nav.getByRole('link',{name:'Destinația 24',exact:true});await lastLink.focus();await expect(lastLink).toBeInViewport();const overlay=nav.locator('.wp-block-navigation__responsive-container');expect(await overlay.evaluate(e=>e.scrollTop)).toBeGreaterThan(100);const panel=await overlay.locator('.wp-block-navigation__responsive-close').boundingBox();const tail=await lastLink.boundingBox();expect(panel.y+panel.height).toBeGreaterThanOrEqual(tail.y+tail.height);await expect(overlay.locator('.wp-block-navigation__responsive-close')).toHaveCSS('background-color','rgb(20, 31, 22)');if(width===390)await guest.screenshot({path:`build/menu-scroll-${designs[index]}-1.1.28.png`});await longMenu.click();await active.focus();
          await nav.locator('.wp-block-navigation__responsive-container-close').focus(); await guest.keyboard.press('Shift+Tab');
          expect(await nav.evaluate(e=>e.contains(document.activeElement))).toBe(true);
          if(width===390) await guest.screenshot({path:`build/menu-${designs[index]}-${width}-1.1.17.png`});
          await guest.keyboard.press('Escape'); await expect(nav.getByRole('dialog')).toBeHidden(); await expect(opener).toBeFocused();
        } else {
          const software=nav.getByRole('button',{name:/^Software/}).first(); await software.focus(); await guest.keyboard.press('Enter');
          await expect(software).toHaveAttribute('aria-expanded','true');
          const parent=nav.getByRole('button',{name:/WordPress/}); await expect(parent).toBeVisible();
          // Core binds aria-expanded before its reactive initMenu callback records
          // the focus boundary. Let that paint finish, then use real Tab navigation.
          await guest.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
          await guest.keyboard.press('Tab');
          await expect(nav.getByRole('link',{name:'WordPress & WooCommerce',exact:true})).toBeFocused();
          await guest.keyboard.press('Tab'); await expect(parent).toBeFocused();
          await guest.keyboard.press('Enter'); await expect(parent).toHaveAttribute('aria-expanded','true'); await expect(active).toBeVisible();
          if(['nexus','signature'].includes(designs[index])) {
            const panel = software.locator('..').locator(':scope > .wp-block-navigation__submenu-container');
            const panelBox=await panel.boundingBox(); const headerBox=await guest.locator('.zion-header').boundingBox();
            expect(Math.abs(panelBox.x-headerBox.x)).toBeLessThanOrEqual(1);
            expect(Math.abs(panelBox.width-headerBox.width)).toBeLessThanOrEqual(1);
            await expect(active.locator('xpath=ancestor::ul[1]')).toHaveCSS('position','static');
          }
          const bounds=await active.boundingBox(); expect(bounds.x).toBeGreaterThanOrEqual(0); expect(bounds.x+bounds.width).toBeLessThanOrEqual(width);
          await guest.screenshot({path:`build/menu-${designs[index]}-${width}-1.1.17.png`});
        }
      }
      if (['nexus', 'signature'].includes(designs[index])) {
        for (const width of [1440, 1920]) {
          await guest.setViewportSize({ width, height: 950 });
          // A fresh page prevents focus-within from masking a broken hover path.
          await guest.goto(leaf.link + '?pointer-menu=' + designs[index]);
          const nav = guest.locator('header nav.zion-navigation');
          for (const label of ['Servicii', 'Software', 'Resurse']) {
            const toggle = nav.getByRole('button', { name: label + ' submenu', exact: true });
            const root = toggle.locator('..');
            const trigger = root.locator(':scope > .wp-block-navigation-item__content');
            const panel = root.locator(':scope > .wp-block-navigation__submenu-container');
            for (const diagonal of [false, true]) {
              await trigger.hover(); await expect(panel).toBeVisible();
              const start = await trigger.boundingBox(), bounds = await panel.boundingBox();
              const first = panel.locator('a.wp-block-navigation-item__content').first();
              const destination = await first.boundingBox();
              const startX = start.x + start.width / 2, startY = start.y + start.height - 1;
              const endY = bounds.y + 4;
              const endX = diagonal ? startX + (destination.x + destination.width / 2 - startX) * (endY - startY) / (destination.y + destination.height / 2 - startY) : startX;
              await guest.mouse.move(startX, startY);
              for (let step = 1; step <= 12; step++) {
                await guest.mouse.move(startX + (endX - startX) * step / 12, startY + (endY - startY) * step / 12);
                await expect(panel, `${designs[index]} ${label} ${width}px ${diagonal ? 'diagonal' : 'vertical'} step ${step}`).toBeVisible();
              }
              await first.hover(); await expect(panel).toBeVisible();
              await panel.locator('a.wp-block-navigation-item__content').last().hover(); await expect(panel).toBeVisible();
              const headerControls = await guest.locator('.zion-header').evaluate(header =>
                [...header.querySelectorAll('.zion-header-logo a, .zion-header-cart-link, .wp-block-navigation__container > li > a')].every(link => {
                  const box = link.getBoundingClientRect();
                  return link.contains(document.elementFromPoint(box.x + box.width / 2, box.bottom - 1));
                }));
              expect(headerControls, 'The hover bridge must leave header links, logo and cart clickable').toBe(true);
              await guest.mouse.move(width - 10, 900);
              await expect(panel).toBeHidden();
            }
          }
          // Reach and activate a third-level native link using only the mouse.
          await nav.getByRole('link', { name: 'Software', exact: true }).hover();
          await nav.getByRole('link', { name: 'WordPress & WooCommerce', exact: true }).hover();
          await nav.getByRole('link', { name: 'Zion Privacy', exact: true }).click();
          await expect(guest).toHaveURL(leaf.link);
        }
      }
      const savedMenu=await (await page.request.get('/wp-json/wp/v2/navigation/'+menu.id+'?context=edit',{headers})).json();
      expect(savedMenu.content.raw).toBe(canonicalMenu);
    }
    for (const design of ['nexus', 'signature']) {
      await openSection(page, 'header');
      await field(page, 'header_design').selectOption(design);
      await field(page, 'header_cart').selectOption('1'); await save(page);
      await guest.setViewportSize({width:1440,height:950}); await guest.goto(leaf.link);
      await guest.locator('nav.zion-navigation').getByRole('link', {name:'Servicii',exact:true}).hover();
      await guest.locator('.zion-header .wc-block-mini-cart__button').focus();
      await guest.keyboard.press('Enter');
      const drawer = guest.locator('.wc-block-mini-cart__drawer[role="dialog"]');
      await expect(drawer).toBeVisible();
      await expect.poll(() => drawer.evaluate(element => element.contains(document.activeElement))).toBe(true);
      await expect.poll(() => drawer.evaluate(element => {
        const box = element.getBoundingClientRect(), header = document.querySelector('.zion-header').getBoundingClientRect();
        return element.contains(document.elementFromPoint(box.left + 24, header.bottom + 24));
      })).toBe(true);
      await guest.keyboard.press('Escape'); await expect(drawer).toBeHidden();
    }
    await page.setViewportSize({width:390,height:950}); await page.goto(leaf.link);
    await page.locator('header nav.zion-navigation .wp-block-navigation__responsive-container-open').click();
    const adminBar=await page.locator('#wpadminbar').boundingBox();
    const closeButton=page.locator('header nav.zion-navigation .wp-block-navigation__responsive-container-close');
    const closeBox=await closeButton.boundingBox(); expect(closeBox.y).toBeGreaterThanOrEqual(adminBar.y+adminBar.height);
    await closeButton.press('Escape');
    const noJsContext=await browser.newContext({baseURL,javaScriptEnabled:false,viewport:{width:390,height:950}}); const noJs=await noJsContext.newPage();
    try { await noJs.goto(leaf.link); await expect(noJs.locator(`header nav a[href="${leaf.link}"]`)).toBeVisible(); expect(await noJs.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0); } finally {await noJsContext.close();}
    expect(errors).toEqual([]);
    await openSection(page,'header'); await page.getByRole('button',{name:'Resetează secțiunea',exact:true}).click(); await expect(field(page,'header_design')).toHaveValue('');
  } finally {
    if(headerEdited) {
      if(originalHeader.source==='custom') await page.request.post(headerRoute,{headers,data:{content:originalHeader.content.raw}});
      else await page.request.delete(headerRoute+'?force=true',{headers});
    }
    for(const route of fixtureRoutes) await page.request.delete(route,{headers});
    await importSettings(page,original); await context.close();
  }
});
