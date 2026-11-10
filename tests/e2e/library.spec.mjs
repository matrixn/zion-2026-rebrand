import { test, expect } from '@playwright/test';

const products = (page, mode) => page.locator(`.zion-library-collection--${mode}`);
const names = collection => collection.getByRole('heading', { level: 3 }).allTextContents();

test('all nine component demos render with the native product scope and pagination', async ({ page }) => {
  await page.goto('/componente-zion-demo/');
  const grid = products(page, 'products').first();
  const initial = await names(grid);
  expect(initial).toHaveLength(1);
  expect(await names(products(page, 'featured'))).toHaveLength(4);
  expect(await names(products(page, 'shop'))).toEqual(['Prototip carcasă 3D', 'Suport modular 3D']);
  expect(await names(products(page, 'products').last())).toEqual(await names(products(page, 'shop')));
  expect(await names(products(page, 'new'))).toHaveLength(3);
  expect(await names(products(page, 'sale'))).toEqual(['Zion Workflow Kit']);
  const related = await names(products(page, 'related'));
  expect(related.length).toBeGreaterThan(0);
  expect(related).not.toContain('Zion Catalog Tools');
  await expect(page.locator('.zion-services-grid article')).toHaveCount(7);
  await expect(page.locator('.zion-library-cta').getByRole('heading')).toHaveText('CTA demonstrativ');
  await expect(page.locator('.zion-library-testimonials')).toContainText('Recenzie demo Zion');

  const next = grid.locator('.wp-block-query-pagination-next');
  await expect(next).toBeVisible();
  await next.click();
  await expect(page).toHaveURL(/query-101-page=2/);
  expect(await names(grid)).toHaveLength(1);
  expect(await names(grid)).not.toEqual(initial);
  expect(await names(products(page, 'products').last())).toEqual(await names(products(page, 'shop')));
});

for (const width of [390, 1440]) {
  test(`eleven homepage sections and service links fit at ${width}px`, async ({ page }) => {
    await page.setViewportSize({ width, height: 850 });
    await page.goto('/');
    await expect(page.locator('main h1')).toHaveCount(1);
    await expect(page.locator('.zion-hero .zion-blueprint')).toHaveCSS('animation-name', 'none');
    expect(await page.locator('main h2').allTextContents()).toEqual([
      'Ce construim.', 'De la atelier la aplicație.', 'Produse recomandate', 'Magazin 3D', 'Magazin Software',
      'Ecosistemul Zion', 'De la schiță la soluție.', 'Recenzii din magazin', 'Articole recente', 'Găsește următoarea piesă.'
    ]);
    await expect(page.locator('.zion-services-grid article h3 a')).toHaveCount(7);
    expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
    const services = await page.locator('.zion-services-grid article h3 a').evaluateAll(links =>
      links.map(link => ({ url: link.href, title: link.textContent }))
    );
    for (const service of services) {
      await page.goto(service.url);
      await expect(page.locator('main.zion-service-main h1')).toBeVisible();
      await expect(page.locator('.zion-overview-lead .zion-overview-label')).toContainText(service.title);
      await expect(page.locator('.zion-detail-screen')).toBeVisible();
      await expect(page.locator('#beneficii .zion-service-card')).toHaveCount(4);
      await expect(page.locator('.zion-detail-end a[href*="/contact/?serviciu="]')).toBeVisible();
      expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
    }
  });
}

test('frontend assets follow rendered components and WooCommerce emits native Product schema once', async ({ page }) => {
  await page.goto('/jurnal/');
  await expect(page.locator('#zion-collections-css, #zion-library-content-css, #zion-card-js, #zion-library-editor-js')).toHaveCount(0);
  await page.goto('/servicii/');
  await expect(page.locator('.zion-overview-card a')).toHaveCount(7);
  await expect(page.locator('#zion-library-content-css')).toHaveCount(0);
  await expect(page.locator('#zion-collections-css, #zion-card-js, #zion-library-editor-js')).toHaveCount(0);
  await expect(page.locator('head meta[name="description"]')).toHaveCount(1);
  await page.goto('/');
  await expect(page.locator('#zion-collections-css, #zion-library-content-css')).toHaveCount(2);
  await expect(page.locator('#zion-card-js, #zion-library-editor-js')).toHaveCount(0);
  await expect(page.locator('.zion-featured .zion-product-card__image img').first()).toHaveAttribute('loading', 'lazy');
  await page.goto('/product/zion-catalog-tools/');
  await expect(page.locator('head link[rel="canonical"]')).toHaveCount(1);
  await expect(page.locator('head meta[name="description"]')).toHaveCount(1);
  const schemas = await page.locator('script[type="application/ld+json"]').evaluateAll(scripts => {
    const all = scripts.flatMap(script => {
      const data = JSON.parse(script.textContent);
      return data['@graph'] ?? [data];
    });
    return all.filter(item => item['@type'] === 'Product');
  });
  expect(schemas).toHaveLength(1);
  expect(schemas[0].name).toBe('Zion Catalog Tools');
  expect(schemas[0].offers).toBeTruthy();
});

test('Nexus footer and service circuit adapt to viewport and reduced motion', async ({ page }) => {
  const backdrop = (selector) => page.locator(selector).evaluate(band => {
    const grid = getComputedStyle(band, '::before');
    const signal = getComputedStyle(band, '::after');
    return { grid: grid.transform, signal: signal.backgroundPosition, animation: signal.animationName };
  });

  for (const width of [1440, 390]) {
    await page.setViewportSize({ width, height: 850 });
    await page.emulateMedia({ reducedMotion: 'no-preference' });
    await page.goto('/servicii/');
    const footer = page.locator('footer .zion-nexus');
    await expect(footer.locator('.zion-nexus-column')).toHaveCount(4);
    await expect(footer.getByRole('link', { name: 'PORNEȘTE UN PROIECT' })).toHaveAttribute('href', /\/contact\/$/);
    await expect(footer.getByRole('link', { name: 'ANPC' })).toHaveAttribute('href', 'https://anpc.ro/');
    expect(await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth)).toBe(0);

    const hero = '.zion-services-overview > .zion-overview-hero';
    const content = '.zion-services-overview > .zion-overview-band:nth-child(2)';
    const initial = await backdrop(hero);
    expect(initial.animation).toContain('zion-circuit-signals');
    await expect.poll(async () => (await backdrop(hero)).signal).not.toBe(initial.signal);
    await page.locator(content).scrollIntoViewIfNeeded();
    await expect.poll(async () => (await backdrop(hero)).grid).not.toBe(initial.grid);
    expect((await backdrop(content)).animation).toContain('zion-circuit-signals');
    await page.emulateMedia({ reducedMotion: 'reduce' });
    expect((await backdrop(hero)).animation).toBe('none');
    expect((await backdrop(content)).animation).toBe('none');
  }
});
