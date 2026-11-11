import { test, expect } from '@playwright/test';

for (const width of [390, 768, 1024, 1440]) {
  test(`About Zion uses the editorial layout at ${width}px`, async ({ page }) => {
    const errors = []; page.on('pageerror', error => errors.push(error.message));
    await page.setViewportSize({ width, height: 950 });
    await page.goto('/despre-noi/');
    const about = page.locator('main.zion-about-main .zion-about');
    await expect(about).toBeVisible();
    await expect(page.locator('main h1')).toHaveCount(1);
    await expect(about.locator('.zion-about-note')).toBeVisible();
    await expect(about.locator('h2')).toHaveText([
      'Mai întâi, curiozitatea.', 'Privește cum prinde formă.', 'Două lumi. Același fel de a gândi.',
      'Lucrurile bune se construiesc cu grijă.', 'Nu ai nevoie de un brief perfect.', 'Spune-ne ce ai în minte.'
    ]);
    expect(await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth)).toBe(0);
    await expect(about.locator('.zion-about-world')).toHaveCount(2);
    await expect(about.locator('.zion-about-value')).toHaveCount(3);
    await expect(about.locator('.zion-forge-phase')).toHaveCount(4);
    for (const phase of await about.locator('.zion-forge-phase').all()) await expect(phase).toBeVisible();
    await expect(about.locator('canvas[data-render-mode="static"]')).toHaveCount(2);
    for (const canvas of await about.locator('canvas').all()) {
      const aspect = await canvas.evaluate(e => ({ pixels: e.width / e.height, display: e.getBoundingClientRect().width / e.getBoundingClientRect().height }));
      expect(aspect.pixels).toBeCloseTo(aspect.display, 1);
    }
    await expect(about.locator('.zion-about-hero')).toHaveCSS('color', 'rgb(243, 246, 239)');
    await expect(about.locator('.zion-about-story')).toHaveCSS('color', 'rgb(8, 13, 9)');
    expect(await about.locator('.zion-about-hero').evaluate(e => getComputedStyle(e, '::after').animationName)).toBe('none');
    const story = about.getByRole('link', { name: 'Cunoaște-ne ↓', exact: true });
    await story.focus(); await expect(story).toBeFocused(); await story.press('Enter');
    await expect(page).toHaveURL(/#povestea-zion$/);
    await expect(about.locator('#povestea-zion')).toBeInViewport();
    expect(errors).toEqual([]);
    if ([390, 1440].includes(width)) {
      await page.goto('/despre-noi/');
      await expect(page).toHaveScreenshot(`about-${width}.png`, { fullPage: true, animations: 'disabled', maxDiffPixelRatio: .01 });
    }
  });
}

test('About remains readable without JavaScript and valid in the native editor', async ({ browser, page, baseURL }) => {
  test.skip(!['localhost', '127.0.0.1'].includes(new URL(baseURL).hostname));
  const context = await browser.newContext({ baseURL, javaScriptEnabled: false, viewport: { width:390, height:950 } });
  try {
    const noJs = await context.newPage(); await noJs.goto('/despre-noi/');
    await expect(noJs.locator('main h1')).toBeVisible();
    for (const phase of await noJs.locator('.zion-forge-phase').all()) await expect(phase).toBeVisible();
    await expect(noJs.locator('.zion-about-end a').first()).toHaveAttribute('href', /^(mailto:|https?:)/);
    expect(await noJs.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth)).toBe(0);
  } finally { await context.close(); }
  await page.goto('/wp-login.php');
  await page.request.post('/wp-login.php', { form:{log:'admin',pwd:'password',testcookie:'1'},maxRedirects:0 });
  const pages = await (await page.request.get('/wp-json/wp/v2/pages?slug=despre-noi')).json();
  await page.goto(`/wp-admin/post.php?post=${pages[0].id}&action=edit`);
  await page.waitForFunction(() => window.wp?.data?.select('core/block-editor')?.getBlocks().length > 0);
  const invalid = await page.evaluate(() => {
    const walk = blocks => blocks.flatMap(block => [ ...(block.isValid === false ? [block.name] : []), ...walk(block.innerBlocks ?? []) ]);
    return walk(wp.data.select('core/block-editor').getBlocks());
  });
  expect(invalid).toEqual([]);
});
