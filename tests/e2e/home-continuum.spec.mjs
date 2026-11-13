import { test, expect } from '@playwright/test';
import { createHash } from 'node:crypto';

const fingerprint = async canvas => createHash('sha256').update(await canvas.evaluate(e => e.toDataURL())).digest('hex');
const types = ['hero', 'physical', 'digital', 'services', 'process', 'portal'];

for (const width of [390, 768, 1024, 1440]) {
  test(`homepage Continuum preserves readable commerce and native layout at ${width}px`, async ({ page }) => {
    const errors = []; page.on('pageerror', e => errors.push(e.message));
    await page.setViewportSize({ width, height: 850 });
    await page.goto('/');
    await expect(page.locator('.zion-homepage')).toHaveClass(/is-spatial-static/);
    await expect(page.locator('main h1')).toHaveCount(1);
    const heroBounds = await page.locator('.zion-home-lab').evaluate(e => ({ left:e.getBoundingClientRect().left, right:e.getBoundingClientRect().right, viewport:document.documentElement.clientWidth }));
    expect(heroBounds.left).toBeGreaterThanOrEqual(0);
    expect(heroBounds.right).toBeLessThanOrEqual(heroBounds.viewport);
    await expect(page.locator('script#zion-continuum-js')).toHaveCount(1);
    await expect(page.locator('.zion-home-products .zion-library-collection')).toHaveCount(1);
    await expect(page.locator('.zion-home-products canvas')).toHaveCount(0);
    await expect(page.locator('.zion-home-service h3 a')).toHaveCount(7);
    for (const type of types) {
      const canvas = page.locator(`[data-zion-spatial="home-${type}"]`);
      await expect(canvas).toHaveAttribute('data-render-mode', 'static');
      await expect(canvas).toBeVisible();
      const shape = await canvas.evaluate(e => {
        const r = e.getBoundingClientRect();
        return { pixels: e.width / e.height, display: r.width / r.height,
          painted: e.getContext('2d').getImageData(0,0,e.width,e.height).data.some((v,i) => i%4===3 && v>0) };
      });
      expect(shape.pixels).toBeCloseTo(shape.display, 1);
      expect(shape.painted).toBe(true);
    }
    for (const button of await page.locator('.zion-homepage .zion-spatial-pause').all()) await expect(button).toBeHidden();
    expect(await page.evaluate(() => document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
    expect(errors).toEqual([]);
  });
}

for (const width of [390, 1440]) {
  test(`homepage Continuum runs only visible scenes and pauses all sections at ${width}px`, async ({ browser, baseURL }) => {
    const context = await browser.newContext({ baseURL, viewport:{width,height:850}, reducedMotion:'no-preference' });
    try {
      const page = await context.newPage(), errors = []; page.on('pageerror', e => errors.push(e.message));
      await page.goto('/');
      const hero = page.locator('[data-zion-spatial="home-hero"]');
      await hero.scrollIntoViewIfNeeded();
      const first = await fingerprint(hero);
      await expect.poll(() => fingerprint(hero)).not.toBe(first);
      const pause = page.locator('.zion-home-motion--hero .zion-spatial-pause');
      await pause.focus(); await pause.press('Enter');
      await expect(pause).toBeFocused();
      await expect(pause).toHaveAttribute('aria-pressed','true');
      expect((await pause.boundingBox()).height).toBeGreaterThanOrEqual(44);
      const frozen = await fingerprint(hero); await page.waitForTimeout(160);
      expect(await fingerprint(hero)).toBe(frozen);
      for (const button of await page.locator('.zion-homepage .zion-spatial-pause').all()) await expect(button).toHaveAttribute('aria-pressed','true');
      expect(await page.locator('.zion-home-hero').evaluate(e=>getComputedStyle(e,'::after').animationPlayState)).toBe('paused');
      await pause.press('Enter');
      await expect.poll(() => fingerprint(hero)).not.toBe(frozen);
      const drawings = [];
      for (const type of types.slice(1)) {
        const canvas = page.locator(`[data-zion-spatial="home-${type}"]`);
        await canvas.scrollIntoViewIfNeeded();
        const before = await fingerprint(canvas); await expect.poll(() => fingerprint(canvas)).not.toBe(before);
        drawings.push(await fingerprint(canvas));
      }
      expect(new Set(drawings).size).toBe(5);
      // At the FAQ all other scenes are outside the viewport and stop drawing.
      await page.waitForTimeout(180); const offscreen = await fingerprint(hero);
      await page.waitForTimeout(180); expect(await fingerprint(hero)).toBe(offscreen);
      const process = page.locator('.zion-home-process');
      const geometry = await process.evaluate(e=>({top:e.getBoundingClientRect().top+scrollY,height:e.offsetHeight}));
      await page.evaluate(g=>scrollTo(0,g.top-innerHeight*.9),geometry);
      const step = await process.getAttribute('data-continuum-step');
      await page.evaluate(g=>scrollTo(0,g.top+g.height-innerHeight*.1),geometry);
      await expect.poll(()=>process.getAttribute('data-continuum-step')).not.toBe(step);
      expect(await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
      expect(errors).toEqual([]);
    } finally { await context.close(); }
  });
}

test('homepage Continuum follows live motion preferences and remains conditional', async ({ page }) => {
  await page.goto('/');
  const canvas=page.locator('[data-zion-spatial="home-hero"]');
  const staticFrame=await fingerprint(canvas);
  await page.waitForTimeout(120); expect(await fingerprint(canvas)).toBe(staticFrame);
  await page.emulateMedia({reducedMotion:'no-preference'});
  await canvas.scrollIntoViewIfNeeded();
  await expect(canvas).toHaveAttribute('data-render-mode','animated');
  await expect.poll(()=>fingerprint(canvas)).not.toBe(staticFrame);
  await page.emulateMedia({reducedMotion:'reduce'});
  await expect(page.locator('.zion-homepage')).toHaveClass(/is-spatial-static/);
  await page.emulateMedia({reducedMotion:'no-preference'});
  await page.evaluate(()=>document.body.classList.add('zion-motion-reduced'));
  await expect(canvas).toHaveAttribute('data-render-mode','static');
  await page.goto('/magazin-3d/');
  await expect(page.locator('script#zion-continuum-js')).toHaveCount(0);
});

test('homepage stays complete without JavaScript', async ({ browser, baseURL }) => {
  const context=await browser.newContext({baseURL,javaScriptEnabled:false,viewport:{width:390,height:850}});
  try {
    const noJs=await context.newPage(); await noJs.goto('/');
    await expect(noJs.locator('.zion-home-lab svg')).toBeVisible();
    await expect(noJs.locator('.zion-home-hero h1')).toBeVisible();
    await expect(noJs.locator('.zion-home-service h3 a')).toHaveCount(7);
    await expect(noJs.locator('.zion-home-products .zion-product-card').first()).toBeVisible();
    for (const panel of await noJs.locator('.zion-home-motion').all()) await expect(panel).toBeHidden();
    expect(await noJs.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
  } finally { await context.close(); }
});

test('active Continuum settles when a motion change notification is delayed', async ({ browser, baseURL }) => {
  const context=await browser.newContext({baseURL,reducedMotion:'no-preference'});
  try {
    const page=await context.newPage();
    await page.addInitScript(()=>{
      const nativeMatchMedia=window.matchMedia.bind(window);
      window.matchMedia=query=>{
        const list=nativeMatchMedia(query);
        if(query==='(prefers-reduced-motion: reduce)') {
          const listen=list.addEventListener.bind(list);
          // Keep the real browser preference; suppress only its notification.
          list.addEventListener=(type,...args)=>{if(type!=='change')listen(type,...args);};
        }
        return list;
      };
    });
    await page.goto('/');
    const canvas=page.locator('[data-zion-spatial="home-hero"]');
    await canvas.scrollIntoViewIfNeeded();
    const frame=await fingerprint(canvas);
    await expect.poll(()=>fingerprint(canvas)).not.toBe(frame);
    await page.emulateMedia({reducedMotion:'reduce'});
    expect(await page.evaluate(()=>matchMedia('(prefers-reduced-motion: reduce)').matches)).toBe(true);
    await expect(page.locator('.zion-homepage')).toHaveClass(/is-spatial-static/);
    for(const scene of await page.locator('[data-zion-spatial]').all())await expect(scene).toHaveAttribute('data-render-mode','static');
    const still=await fingerprint(canvas);
    await page.waitForTimeout(120);expect(await fingerprint(canvas)).toBe(still);
  } finally {await context.close();}
});
