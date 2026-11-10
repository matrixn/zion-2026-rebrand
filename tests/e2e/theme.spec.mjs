import { test, expect } from '@playwright/test';

const routes = [
  { url: '/', heading: /ideile devin realitate/i },
  { url: '/magazin-3d/', heading: 'Magazin 3D' },
  { url: '/magazin-software/', heading: 'Magazin Software' },
  { url: '/jurnal/', heading: 'Jurnal' },
  { url: '/jurnal-prototip-zion/', heading: 'Jurnal de prototipare Zion' },
  { url: '/category/uncategorized/', heading: /uncategorized/i },
  { url: '/?s=prototipare', heading: /prototipare/i },
  { url: '/pagina-zion-inexistenta/', heading: 'Pagina nu a fost găsită', status: 404 },
  { url: '/shop/', heading: /shop/i, block: '.zion-store .wc-block-product' },
  { url: '/product-category/prototipuri-demo/', heading: /prototipuri demo/i, block: '.zion-store .wc-block-product' },
  { url: '/?s=zion&post_type=product', heading: /zion/i, block: '.zion-store .wc-block-product' },
  { url: '/product/zion-catalog-tools/', heading: 'Zion Catalog Tools', block: '.zion-product-summary' },
];

for (const width of [390, 768, 1024, 1440]) {
  test(`theme templates fit at ${width}px`, async ({ page }) => {
    await page.setViewportSize({ width, height: 850 });
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    page.on('console', message => {
      if (message.type() === 'error' && !message.text().startsWith('Failed to load resource:')) {
        errors.push(message.text());
      }
    });

    for (const { url, heading, status = 200, block } of routes) {
      await test.step(url, async () => {
        const response = await page.goto(url);
        expect(response.status()).toBe(status);
        await expect(page.getByRole('heading', { level: 1, name: heading })).toBeVisible();
        await expect(page.locator('header .zion-header')).toBeVisible();
        await expect(page.locator('footer .zion-footer')).toBeVisible();
        if (block) {
          await expect(page.locator(block).first()).toBeVisible();
        }
        expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
        expect(errors).toEqual([]);
      });
    }
  });
}

test('native catalog respects taxonomy and product search', async ({ page }) => {
  await page.goto('/product-category/prototipuri-demo/');
  const collection = page.locator('.zion-store .wp-block-woocommerce-product-collection');
  await expect(collection.getByRole('link', { name: 'Suport modular 3D', exact: true }).last()).toBeVisible();
  await expect(collection.getByRole('link', { name: 'Zion Catalog Tools', exact: true })).toHaveCount(0);

  await page.goto('/?s=zion&post_type=product');
  await expect(collection.getByRole('link', { name: 'Zion Workflow Kit', exact: true }).last()).toBeVisible();
  await expect(collection.getByRole('link', { name: 'Zion Catalog Tools', exact: true }).last()).toBeVisible();
  await expect(collection.getByRole('link', { name: 'Suport modular 3D', exact: true })).toHaveCount(0);

  await page.goto('/?s=zion-necunoscut');
  await expect(page.getByText('Nu am găsit rezultate.', { exact: false })).toBeVisible();
});

test('keyboard can open and dismiss the mobile navigation', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 850 });
  await page.goto('/');
  const nav = page.locator('header nav');
  const menu = nav.locator('.wp-block-navigation__responsive-container-open');
  await menu.focus();
  await expect(menu).toHaveCSS('outline-style', 'solid');
  await page.keyboard.press('Enter');
  await expect(nav.getByRole('dialog')).toBeVisible();
  await page.keyboard.press('Escape');
  await expect(nav.getByRole('dialog')).toBeHidden();
  await expect(menu).toBeFocused();
});

test('Site Editor opens the theme templates and template parts as native blocks', async ({ page }) => {
  test.setTimeout(120_000);
  await page.goto('/wp-login.php');
  const login = await page.request.post('/wp-login.php', {
    maxRedirects: 0,
    form: { log: 'admin', pwd: 'password', testcookie: '1' },
  });
  expect(login.status()).toBe(302);

  const editorViews = [
    ['wp_template', 'front-page', 'core/heading'],
    ['wp_template', 'page', 'core/post-content'],
    ['wp_template', 'single', 'core/post-content'],
    ['wp_template', 'home', 'core/query'],
    ['wp_template', 'archive', 'core/query'],
    ['wp_template', 'search', 'core/search'],
    ['wp_template', '404', 'core/search'],
    ['wp_template', 'archive-product', 'woocommerce/product-collection'],
    ['wp_template', 'product-search-results', 'woocommerce/product-collection'],
    ['wp_template', 'single-product', 'zion/product-page'],
    ['wp_template_part', 'header', 'core/navigation'],
    ['wp_template_part', 'footer', 'core/search'],
    ['wp_template_part', 'page-sidebar', 'core/search'],
  ];
  for (const [type, slug, block] of editorViews) {
    await test.step(slug, async () => {
      await page.goto(`/wp-admin/site-editor.php?p=/${type}/${encodeURIComponent(`zion-theme//${slug}`)}&canvas=edit`);
      const canvas = page.frameLocator('iframe[name="editor-canvas"]');
      const scope = block === 'core/search' && type === 'wp_template' ? 'main ' : '';
      await expect(canvas.locator(`${scope}[data-type="${block}"]`).first()).toBeVisible({ timeout: 15_000 });
      if (slug === 'front-page') {
        await expect(canvas.locator('[data-type="zion/services-grid"]').first()).toBeVisible();
        await expect(canvas.locator('[data-type="zion/featured-products"]').first()).toBeVisible();
      }
      await expect(canvas.getByText('This block contains unexpected or invalid content.')).toHaveCount(0);
    });
  }
});

for (const width of [390, 1440]) {
  test(`product presentation at ${width}px @visual`, async ({ page }) => {
    await page.setViewportSize({ width, height: 850 });
    await page.goto('/product/zion-catalog-tools/');
    await expect(page.locator('.zion-product-summary').getByRole('heading', { name: 'Zion Catalog Tools' })).toBeVisible();
    const images = page.locator('.zion-product-media .woocommerce-product-gallery__image img');
    await images.evaluateAll(elements => {
      for (const image of elements) image.loading = 'eager';
    });
    await expect.poll(() => images.evaluateAll(elements =>
      elements.length > 0 && elements.every(image => image.complete && image.naturalWidth > 0),
    )).toBe(true);
    await page.addStyleTag({ content: '[data-zion-section="related"] { display: none }' });
    await expect(page).toHaveScreenshot(`product-${width}.png`, { fullPage: true, maxDiffPixelRatio: 0.01 });
  });

  test(`physical product presentation at ${width}px @visual`, async ({ page }) => {
    await page.setViewportSize({ width, height: 850 });
    await page.goto('/product/suport-modular-3d/');
    await expect(page.locator('.zion-product-page--physical').getByRole('heading', { name: 'Suport modular 3D', exact: true })).toBeVisible();
    const image = page.locator('.zion-product-media .woocommerce-product-gallery__image img').first();
    await expect(image).toBeVisible();
    await expect.poll(() => image.evaluate(element => element.complete && element.naturalWidth > 0)).toBe(true);
    await expect(page.locator('#zion-purchase').getByRole('button', { name: 'Adaugă în coș', exact: true })).toBeVisible();
    expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
    await page.addStyleTag({ content: '[data-zion-section="related"] { display: none }' });
    await expect(page).toHaveScreenshot(`product-physical-${width}.png`, { fullPage: true, maxDiffPixelRatio: 0.01 });
    await page.locator('.zion-product-media .woocommerce-product-gallery__trigger').click();
    await expect(page.locator('.pswp--open').first()).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(page.locator('.pswp--open')).toHaveCount(0);
  });
}
