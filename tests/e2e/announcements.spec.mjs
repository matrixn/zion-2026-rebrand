import { test, expect } from '@playwright/test';
import { readFile } from 'node:fs/promises';

const theme = JSON.parse(await readFile(new URL('../../themes/zion-theme/theme.json', import.meta.url), 'utf8'));
const tokens = theme.settings.color.palette.map(({ slug, color }) => `--wp--preset--color--${slug}:${color}`).join(';');
const titles = ['Colaborare sintetică: formă, culoare și descoperire', 'Poveste sintetică despre prototipare și fabricație digitală', 'Piesă 3D sintetică pentru un mecanism cu o denumire foarte lungă, vizibilă integral inclusiv pe telefon', 'Suport modular 3D sintetic'];
const key = 'zion-header-updates-v1';
function assertLocal(baseURL) {
  const url = new URL(baseURL);
  expect(['localhost', '127.0.0.1', '[::1]']).toContain(url.hostname);
  expect(url.port).toBe('8917');
}
async function fixture(page, baseURL, { withHeader = true, account = true, drawer = false, empty = false } = {}) {
  assertLocal(baseURL);
  const notice = `<aside class="zion-updates" aria-label="Noutăți Zion"><div class="zion-updates__panel"><div class="zion-updates__inner"><span class="zion-updates__heading">Noutăți</span><ul class="zion-updates__items">${(empty ? [] : titles).map((title, i) => `<li><a href="/news-${i}"><span class="zion-updates__category">${i < 2 ? 'Din jurnal' : 'Magazin 3D'}</span><span class="zion-updates__title">${title}</span></a></li>`).join('')}</ul><div class="zion-updates__controls" hidden><span class="zion-updates__count" aria-hidden="true"></span><button type="button" class="zion-updates__pause" aria-label="Oprește animația" aria-pressed="false">Ⅱ</button><button type="button" class="zion-updates__next" aria-label="Următoarea noutate">→</button><button type="button" class="zion-updates__close" aria-label="Închide bara de noutăți">×</button></div></div></div></aside>`;
  const cart = drawer ? '<div class="zion-header-cart"><button aria-label="Coș de test">Coș</button></div>' : '<a class="zion-header-cart" href="/test-cart" aria-label="Coș de test">Coș</a>';
  const header = withHeader ? `<header>${notice}<div class="wp-block-group zion-shell zion-header"><a href="/" class="zion-header-logo">Zion</a><nav class="zion-navigation"><button aria-label="Meniu de test">☰</button></nav><div class="zion-header-tools">${account ? '<div class="zion-header-account"><a href="/test-account" aria-label="Cont de test">Cont</a></div>' : ''}${cart}</div></div></header>` : notice;
  await page.route('**/zion-notification-fixture*', route => route.fulfill({ contentType: 'text/html', body: `<!doctype html><html lang="ro"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>:root{${tokens};--wp--preset--spacing--gutter:1rem}body{margin:0}</style><link rel="stylesheet" href="/wp-content/themes/zion-theme/assets/dist/zion.css"></head><body><div class="wp-site-blocks">${header}<main><a href="/zion-notification-fixture-next">Următoarea pagină de test</a></main></div><script src="/wp-content/themes/zion-theme/assets/blocks/announcements.js"></script></body></html>` }));
  await page.goto('/zion-notification-fixture');
}
const toggle = page => page.locator('.zion-updates__toggle');
const panel = page => page.locator('.zion-updates__panel');

async function expectInlineCaret(page) {
  const geometry = await page.locator('.zion-updates .is-current .zion-updates__typed').evaluate(typed => {
    const text = typed.firstChild;
    const range = document.createRange();
    range.setStart(text, text.length - 1);
    range.setEnd(text, text.length);
    const last = range.getBoundingClientRect();
    const caret = typed.querySelector('.zion-updates__caret');
    const anchor = caret.getBoundingClientRect();
    const style = getComputedStyle(caret, '::after');
    return { end: last.right, top: last.top, bottom: last.bottom, x: anchor.x, y: anchor.y,
      width: anchor.width, cursorRight: anchor.right + parseFloat(style.insetInlineStart) + parseFloat(style.width),
      availableRight: typed.parentElement.getBoundingClientRect().right };
  });
  expect(Math.abs(geometry.x - geometry.end)).toBeLessThanOrEqual(1);
  expect(geometry.y).toBeGreaterThanOrEqual(geometry.top);
  expect(geometry.y).toBeLessThanOrEqual(geometry.bottom);
  expect(geometry.width).toBe(0);
  expect(geometry.cursorRight).toBeLessThanOrEqual(geometry.availableRight + 1);
}

for (const width of [390, 1440]) test(`native WordPress header renders the real bounded news feed at ${width}px`, async ({ page, baseURL }, testInfo) => {
  assertLocal(baseURL);
  await page.setViewportSize({ width, height: 900 });
  await page.goto('/');
  const header = page.locator('header.wp-block-template-part');
  const notice = header.locator('.zion-updates');
  await expect(notice).toHaveCount(1);
  await expect(notice.locator('li')).toHaveCount(4);
  await expect(notice.locator('.zion-updates__category')).toHaveText(['Din jurnal', 'Din jurnal', 'Magazin 3D', 'Magazin 3D']);
  await expect(header.locator('.zion-migration-announcement')).toHaveCount(0);
  expect(await notice.evaluate(node => node.nextElementSibling.classList.contains('zion-header'))).toBe(true);
  for (const href of await notice.locator('a').evaluateAll(links => links.map(link => link.href))) expect(new URL(href).origin).toBe(new URL(baseURL).origin);
  await header.screenshot({ path: testInfo.outputPath(`native-news-${width}.png`) });
  await expect(toggle(page)).toHaveAttribute('aria-expanded', 'true');
  expect(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)).toBe(false);
});

for (const width of [390, 768, 1024, 1440]) test(`news sits above menu, has four links and dismisses with either control at ${width}px`, async ({ page, baseURL }, testInfo) => {
  const errors = []; page.on('pageerror', error => errors.push(error.message));
  await page.setViewportSize({ width, height: 900 });
  await fixture(page, baseURL);
  const notice = page.locator('.zion-updates');
  expect(await notice.evaluate(node => node.nextElementSibling.classList.contains('zion-header'))).toBe(true);
  await expect(toggle(page)).toHaveAttribute('aria-expanded', 'true');
  const bell = await toggle(page).boundingBox(), account = await page.locator('.zion-header-account').boundingBox(), cart = await page.locator('.zion-header-cart').boundingBox();
  expect(bell.x).toBeGreaterThanOrEqual(account.x + account.width);
  expect(cart.x).toBeGreaterThanOrEqual(bell.x + bell.width);
  expect(bell.height).toBeGreaterThanOrEqual(44);
  expect((await notice.boundingBox()).y + (await notice.boundingBox()).height).toBeLessThanOrEqual((await page.locator('.zion-header').boundingBox()).y);
  await expect(notice.getByRole('link')).toHaveCount(1);
  await expect(notice.getByRole('link')).toHaveAccessibleName(`Din jurnal: ${titles[0]}`);
  const top = await page.locator('main').evaluate(node => node.getBoundingClientRect().top);
  for (let i = 1; i <= 4; i++) {
    await notice.getByRole('button', { name: 'Următoarea noutate' }).click();
    await expect(notice).toHaveAttribute('data-current', String(i % 4 + 1));
    expect(await page.locator('main').evaluate(node => node.getBoundingClientRect().top)).toBe(top);
    expect(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)).toBe(false);
    await expectInlineCaret(page);
  }
  await page.screenshot({ path: testInfo.outputPath(`news-${width}.png`) });
  await notice.getByRole('button', { name: 'Închide bara de noutăți' }).click();
  await expect(toggle(page)).toBeFocused();
  await expect(toggle(page)).toHaveAttribute('aria-expanded', 'false');
  await expect(panel(page)).toHaveAttribute('inert', '');
  await expect.poll(async () => (await panel(page).boundingBox()).height).toBe(0);
  await toggle(page).press('Enter');
  await expect(toggle(page)).toHaveAttribute('aria-expanded', 'true');
  await toggle(page).click();
  await expect(toggle(page)).toHaveAttribute('aria-expanded', 'false');
  expect(errors).toEqual([]);
});

for (const width of [390, 1440]) test(`both news choices survive navigation reload and browser history at ${width}px`, async ({ page, baseURL }) => {
  await page.setViewportSize({ width, height: 844 });
  await fixture(page, baseURL);
  await toggle(page).click();
  await page.getByRole('link', { name: 'Următoarea pagină de test' }).click();
  await expect(toggle(page)).toHaveAttribute('aria-expanded', 'false');
  await page.reload(); await expect(toggle(page)).toHaveAttribute('aria-expanded', 'false');
  await page.goBack(); await expect(toggle(page)).toHaveAttribute('aria-expanded', 'false');
  await toggle(page).click(); await page.goForward();
  await expect(toggle(page)).toHaveAttribute('aria-expanded', 'true');
  await page.reload(); await expect(toggle(page)).toHaveAttribute('aria-expanded', 'true');
  expect(await page.evaluate(key => localStorage.getItem(key), key)).toBe('open');
});

test('typewriter advances upward, pauses, and honors changes to reduced motion', async ({ page, baseURL }) => {
  await page.setViewportSize({ width: 1280, height: 900 });
  await page.mouse.move(0, 890);
  await page.emulateMedia({ reducedMotion: 'no-preference' });
  await page.clock.install();
  await fixture(page, baseURL);
  const notice = page.locator('.zion-updates');
  const typed = notice.locator('.is-current .zion-updates__typed');
  await page.clock.runFor(400);
  expect((await typed.textContent()).length).toBeGreaterThan(0);
  expect((await typed.textContent()).length).toBeLessThan(titles[0].length);
  await expect(notice.getByRole('link')).toHaveAccessibleName(`Din jurnal: ${titles[0]}`);
  await page.clock.runFor(titles[0].length * 22 + 3600 + 100 - 400);
  await expect(notice).toHaveClass(/is-leaving/);
  await page.clock.runFor(400);
  await expect(notice).toHaveAttribute('data-current', '2');
  await notice.getByRole('button', { name: 'Oprește animația' }).click();
  await page.mouse.move(0, 890);
  await page.clock.runFor(20000);
  await expect(notice).toHaveAttribute('data-current', '2');
  await expect(typed).toHaveText(titles[1]);
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await expect(notice.locator('.zion-updates__pause')).toBeDisabled();
  await page.clock.runFor(20000);
  await expect(notice).toHaveAttribute('data-current', '2');
  await notice.getByRole('button', { name: 'Următoarea noutate' }).press('Enter');
  await expect(notice).toHaveAttribute('data-current', '3');
  await expect(typed).toHaveText(titles[2]);
});

test('terminal caret blinks after typing and stays static with pause or reduced motion', async ({ page, baseURL }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.mouse.move(0, 800);
  await page.emulateMedia({ reducedMotion: 'no-preference' });
  await page.clock.install();
  await fixture(page, baseURL);
  const caret = page.locator('.zion-updates .is-current .zion-updates__caret');
  const animation = () => caret.evaluate(node => getComputedStyle(node, '::after').animationName);
  await page.clock.runFor(400);
  expect(await animation()).toBe('none');
  await expectInlineCaret(page);
  await page.clock.runFor(titles[0].length * 22 + 100 - 400);
  expect(await animation()).toBe('zion-news-caret');
  await expectInlineCaret(page);
  for (const [time, opacity] of [[100, '1'], [600, '0']]) {
    const actual = await caret.evaluate((node, time) => {
      const blink = node.getAnimations({ subtree: true }).find(animation => animation.animationName === 'zion-news-caret');
      blink.pause(); blink.currentTime = time;
      return getComputedStyle(node, '::after').opacity;
    }, time);
    expect(actual).toBe(opacity);
  }
  await page.getByRole('button', { name: 'Oprește animația', exact: true }).click();
  expect(await animation()).toBe('none');
  await page.emulateMedia({ reducedMotion: 'reduce' });
  expect(await animation()).toBe('none');
  expect(await caret.evaluate(node => getComputedStyle(node, '::after').opacity)).toBe('1');
  await expectInlineCaret(page);
});

test('blocked storage, missing account and a native cart drawer remain usable', async ({ page, baseURL }) => {
  await page.addInitScript(() => { Storage.prototype.getItem = () => { throw new Error('blocked'); }; Storage.prototype.setItem = () => { throw new Error('blocked'); }; });
  await page.setViewportSize({ width: 390, height: 844 });
  await fixture(page, baseURL, { account: false, drawer: true });
  await toggle(page).click(); await expect(toggle(page)).toHaveAttribute('aria-expanded', 'false');
  await toggle(page).click(); await expect(toggle(page)).toHaveAttribute('aria-expanded', 'true');
  await expect(page.getByRole('button', { name: 'Coș de test' })).toBeVisible();
});

test('standalone fallback and empty feed fail safely', async ({ page, baseURL }) => {
  await fixture(page, baseURL, { withHeader: false });
  await toggle(page).click(); await expect(toggle(page)).toHaveAttribute('aria-expanded', 'false');
  await fixture(page, baseURL, { withHeader: false, empty: true });
  await expect(toggle(page)).toHaveCount(0);
});

test('without JavaScript all four full titles remain native accessible links', async ({ browser, baseURL }) => {
  const context = await browser.newContext({ javaScriptEnabled: false, viewport: { width: 390, height: 844 } });
  const page = await context.newPage();
  await fixture(page, baseURL);
  await expect(page.locator('.zion-updates a')).toHaveCount(4);
  for (const title of titles) await expect(page.getByRole('link', { name: new RegExp(title) })).toBeVisible();
  await expect(page.locator('.zion-updates__controls')).toBeHidden();
  expect(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)).toBe(false);
  await context.close();
});
