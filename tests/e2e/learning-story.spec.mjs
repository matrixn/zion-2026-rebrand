import { test, expect } from '@playwright/test';

let admin, nonce, article, cookies;
test.beforeAll(async ({ browser, baseURL }) => {
  const url = new URL(baseURL);
  if (!['localhost', '127.0.0.1'].includes(url.hostname) || url.port !== '8917') throw new Error('Synthetic draft fixtures require the isolated wp-env tests instance.');
  admin = await browser.newContext({ baseURL });
  const login = await admin.newPage();
  await login.goto('/wp-login.php');
  expect((await admin.request.post('/wp-login.php', { maxRedirects:0, form:{ log:'admin', pwd:'password', testcookie:'1' } })).status()).toBe(302);
  nonce = await (await admin.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text();
  const content = '<!-- wp:group {"className":"zion-learning-story"} --><div class="wp-block-group zion-learning-story"><!-- wp:heading --><h2 class="wp-block-heading">Experiment editorial sintetic</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Un draft sintetic pentru verificarea unei experiențe de lectură. Nu reprezintă o colaborare sau un produs real.</p><!-- /wp:paragraph --><!-- wp:zion/editorial {"part":"learning_lab"} /--><!-- wp:heading --><h2 class="wp-block-heading">Al doilea laborator independent</h2><!-- /wp:heading --><!-- wp:zion/editorial {"part":"learning_lab"} /--></div><!-- /wp:group -->';
  const result = await admin.request.post('/wp-json/wp/v2/posts', { headers:{ 'X-WP-Nonce':nonce }, data:{ title:'DRAFT CURIOSITY LAB SYNTHETIC', status:'draft', content, excerpt:'O demonstrație editorială sintetică, pentru testarea locală.' } });
  expect(result.status(), await result.text()).toBe(201);
  article = await result.json();
  cookies = await admin.cookies();
});
test.afterAll(async () => {
  if (article) expect((await admin.request.delete(`/wp-json/wp/v2/posts/${article.id}?force=true`, { headers:{ 'X-WP-Nonce':nonce } })).ok()).toBe(true);
  await admin?.close();
});
test.beforeEach(async ({ context }) => { await context.addCookies(cookies); });

for (const width of [390, 768, 1024, 1440]) {
  test(`draft learning story works and fits at ${width}px`, async ({ page }) => {
    await page.setViewportSize({ width, height:950 });
    const errors = []; page.on('pageerror', e => errors.push(e.message));
    await page.goto(`/?p=${article.id}&preview=true`);
    await expect(page.locator('main h1')).toHaveCount(1);
    const lab = page.locator('[data-learning-lab]').first();
    await expect(lab).toHaveClass(/is-enhanced/);
    const phase = value => lab.locator(`[data-learning-step="${value}"]`);
    await phase(2).focus(); await phase(2).press('Enter');
    await expect(lab.locator('[data-learning-panel="2"]')).toBeVisible();
    await lab.locator('[data-learning-paint]').press('Space');
    await expect(lab).toHaveClass(/is-painted/);
    await phase(3).click();
    await lab.locator('[data-learning-answer="retry"]').click();
    await expect(lab.getByRole('status')).toContainText('Mai încearcă');
    await lab.locator('[data-learning-answer="correct"]').click();
    await expect(lab.getByRole('status')).toContainText('Exact.');
    await expect(page.locator('[data-learning-lab]').last()).not.toHaveClass(/is-painted|is-discovered/);
    await lab.screenshot({ path:`build/learning-lab-${width}.png` });
    await lab.locator('[data-learning-reset]').click();
    await expect(phase(1)).toHaveAttribute('aria-pressed', 'true');
    await expect(lab).not.toHaveClass(/is-painted|is-discovered/);
    await expect(lab.locator('[data-learning-motion]')).toBeDisabled();
    await expect(lab.locator('.zion-learning-layer')).toHaveCSS('animation-name', 'none');
    expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
    for (const button of await lab.locator('button:visible').all()) expect((await button.boundingBox()).height).toBeGreaterThanOrEqual(44);
    await expect(page.locator('script#zion-learning-lab-js')).toHaveCount(1);
    expect(errors).toEqual([]);
  });
}

test('learning motion pauses, follows preferences and remains conditional', async ({ page }) => {
  await page.emulateMedia({ reducedMotion:'no-preference' });
  await page.goto(`/?p=${article.id}&preview=true`);
  const lab = page.locator('[data-learning-lab]').first();
  await lab.scrollIntoViewIfNeeded();
  await expect(lab.locator('.zion-learning-layer')).toHaveCSS('animation-name', 'zion-learning-scan');
  await lab.locator('[data-learning-motion]').click();
  await expect(lab.locator('.zion-learning-layer')).toHaveCSS('animation-play-state', 'paused');
  await lab.locator('[data-learning-motion]').click();
  await expect(lab.locator('.zion-learning-layer')).toHaveCSS('animation-play-state', 'running');
  await page.emulateMedia({ reducedMotion:'reduce' });
  await expect(lab.locator('.zion-learning-layer')).toHaveCSS('animation-name', 'none');
  await page.goto('/');
  await expect(page.locator('script#zion-learning-lab-js')).toHaveCount(0);
});

test('draft stays private and the article remains readable without JavaScript', async ({ browser, baseURL }) => {
  const anonymous = await browser.newContext({ baseURL });
  try {
    const result = await anonymous.request.get(`/?p=${article.id}&preview=true`);
    expect(await result.text()).not.toContain('DRAFT CURIOSITY LAB SYNTHETIC');
    const context = await browser.newContext({ baseURL, javaScriptEnabled:false, storageState:{ cookies, origins:[] } });
    try {
      const page = await context.newPage(); await page.goto(`/?p=${article.id}&preview=true`);
      await expect(page.locator('main h1')).toHaveCount(1);
      const lab = page.locator('[data-learning-lab]').first();
      await expect(lab.getByText('O idee capătă volum.')).toBeVisible();
      await expect(lab.getByText('Tu adaugi personalitatea.')).toBeVisible();
      await expect(lab.locator('[data-learning-reset]')).toBeDisabled();
    } finally { await context.close(); }
  } finally { await anonymous.close(); }
});
