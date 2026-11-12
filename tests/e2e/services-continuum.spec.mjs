import { test, expect } from '@playwright/test';
import { createHash } from 'node:crypto';

const keys = ['printare-3d', 'proiectare-3d', 'software-personalizat', 'platforme-saas', 'pluginuri-si-integrari', 'aplicatii-windows-linux', 'integrari-ai'];
const fingerprint = async canvas => createHash('sha256').update(await canvas.evaluate(e => e.toDataURL())).digest('hex');
const destinations = async page => {
  await page.goto('/servicii/');
  const urls = await page.locator('.zion-overview-card a').evaluateAll(links => links.map(a => a.href));
  expect(urls).toHaveLength(7);
  return [{key:'directory',url:'/servicii/'},...urls.map((url,i)=>({key:keys[i],url}))];
};

for (const width of [390, 768, 1024, 1440]) {
  test(`service Continuum preserves eight native compositions at ${width}px @visual`, async ({ page }) => {
    test.setTimeout(180000);
    const errors=[]; page.on('pageerror',e=>errors.push(e.message));
    await page.setViewportSize({width,height:850});
    for (const {key,url} of await destinations(page)) {
      await page.goto(url);
      const root=page.locator('.zion-service-continuum');
      await expect(root).toHaveAttribute('data-zion-service',key);
      await expect(root).toHaveClass(/is-spatial-static/);
      await expect(page.locator('main h1')).toHaveCount(1);
      await expect(root.locator('canvas')).toHaveCount(key==='directory' ? 10 : 3);
      await expect(page.locator('#zion-continuum-js')).toHaveCount(1);
      const frame=page.locator('.is-continuum-service-art');
      const bounds=await frame.evaluate(e=>({left:e.getBoundingClientRect().left,right:e.getBoundingClientRect().right,viewport:document.documentElement.clientWidth}));
      expect(bounds.left).toBeGreaterThanOrEqual(0); expect(bounds.right).toBeLessThanOrEqual(bounds.viewport+.5);
      for (const canvas of await root.locator('canvas').all()) {
        await expect(canvas).toBeVisible();
        await expect(canvas).toHaveAttribute('aria-hidden','true');
        await expect(canvas).toHaveAttribute('data-render-mode','static');
        const shape=await canvas.evaluate(e=>({pixels:e.width/e.height,display:e.clientWidth/e.clientHeight,painted:e.getContext('2d').getImageData(0,0,e.width,e.height).data.some((v,i)=>i%4===3&&v>0)}));
        expect(shape.pixels).toBeCloseTo(shape.display,1); expect(shape.painted).toBe(true);
      }
      for(const button of await root.locator('.zion-spatial-pause').all()) await expect(button).toBeHidden();
      if(key==='directory') await expect(root.locator('.zion-overview-card a')).toHaveCount(7);
      else {
        await expect(root.locator('#beneficii .zion-service-card')).toHaveCount(4);
        await expect(root.locator('.zion-detail-end a[href*="/contact/?serviciu="]')).toBeVisible();
        await expect(root.locator('.zion-detail-caption')).toBeVisible();
      }
      expect(await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
      if(width===390 || width===1440) await expect(frame).toHaveScreenshot(`services-continuum-${key}-${width}.png`);
    }
    expect(errors).toEqual([]);
  });
}

for(const width of [390,1440]) {
  test(`service scenes animate, pause, and sleep offscreen at ${width}px`,async({browser,baseURL})=>{
    test.setTimeout(180000);
    const context=await browser.newContext({baseURL,viewport:{width,height:850},reducedMotion:'no-preference'});
    try {
      const page=await context.newPage(),errors=[];page.on('pageerror',e=>errors.push(e.message));
      const drawings=[];
      for(const {key,url} of await destinations(page)) {
        await page.goto(url);
        const hero=page.locator('[data-zion-spatial="service-hero"]');
        await hero.scrollIntoViewIfNeeded(); await expect(hero).toBeInViewport();
        const initial=await fingerprint(hero);await expect.poll(()=>fingerprint(hero)).not.toBe(initial);
        drawings.push(await fingerprint(hero));
        const pause=page.locator('.zion-service-motion--hero .zion-spatial-pause');
        await pause.focus();await pause.press('Enter');
        await expect(pause).toBeFocused();await expect(pause).toHaveAttribute('aria-pressed','true');
        expect((await pause.boundingBox()).height).toBeGreaterThanOrEqual(44);
        const frozen=await fingerprint(hero);await page.waitForTimeout(140);expect(await fingerprint(hero)).toBe(frozen);
        for(const button of await page.locator('.zion-service-continuum .zion-spatial-pause').all()) await expect(button).toHaveAttribute('aria-pressed','true');
        await pause.press('Enter');await hero.scrollIntoViewIfNeeded();await expect(hero).toBeInViewport();
        await expect.poll(()=>fingerprint(hero)).not.toBe(frozen);
        for(const type of ['process','handoff']) {
          const canvas=page.locator(`[data-zion-spatial="service-${type}"]`);
          await canvas.scrollIntoViewIfNeeded();const before=await fingerprint(canvas);
          await expect.poll(()=>fingerprint(canvas)).not.toBe(before);
        }
        await page.waitForTimeout(140);const sleeping=await fingerprint(hero);
        await page.waitForTimeout(140);expect(await fingerprint(hero)).toBe(sleeping);
        if(key==='directory') {
          const card=page.locator('.zion-overview-card').first(),preview=card.locator('canvas');
          await card.scrollIntoViewIfNeeded();await page.waitForTimeout(140);
          const still=await fingerprint(preview);await page.waitForTimeout(140);expect(await fingerprint(preview)).toBe(still);
          await card.locator('a').focus();await expect(card).toHaveClass(/is-continuum-engaged/);
          await expect.poll(()=>fingerprint(preview)).not.toBe(still);
          await card.hover();await page.mouse.move(0,0);
          await expect(card.locator('a')).toBeFocused();await expect(card).toHaveClass(/is-continuum-engaged/);
          await page.locator('.zion-overview-services h2').evaluate(e=>{e.tabIndex=-1;e.focus();});
          await expect(card).not.toHaveClass(/is-continuum-engaged/);
          await page.waitForTimeout(100);const disengaged=await fingerprint(preview);
          await page.waitForTimeout(140);expect(await fingerprint(preview)).toBe(disengaged);
        }
        const process=page.locator('.zion-overview-process');
        const geometry=await process.evaluate(e=>({top:e.getBoundingClientRect().top+scrollY,height:e.offsetHeight}));
        await page.evaluate(g=>scrollTo(0,g.top-innerHeight*.9),geometry);
        await expect(process).toHaveAttribute('data-continuum-step',/\d/);
        const step=await process.getAttribute('data-continuum-step');
        await page.evaluate(g=>scrollTo(0,g.top+g.height-innerHeight*.1),geometry);
        await expect.poll(()=>process.getAttribute('data-continuum-step')).not.toBe(step);
      }
      expect(new Set(drawings).size).toBe(8);expect(errors).toEqual([]);
    } finally {await context.close();}
  });
}

test('service Continuum follows live reduced motion and remains conditional',async({page})=>{
  await page.goto('/servicii/');
  const canvas=page.locator('[data-zion-spatial="service-hero"]');
  const initial=await fingerprint(canvas);await page.waitForTimeout(140);expect(await fingerprint(canvas)).toBe(initial);
  await page.emulateMedia({reducedMotion:'no-preference'});await canvas.scrollIntoViewIfNeeded();
  await expect(canvas).toHaveAttribute('data-render-mode','animated');await expect.poll(()=>fingerprint(canvas)).not.toBe(initial);
  await page.emulateMedia({reducedMotion:'reduce'});await expect(page.locator('.zion-service-continuum')).toHaveClass(/is-spatial-static/);
  await page.emulateMedia({reducedMotion:'no-preference'});await page.evaluate(()=>document.body.classList.add('zion-motion-reduced'));
  await expect(canvas).toHaveAttribute('data-render-mode','static');
  await page.goto('/magazin-software/');await expect(page.locator('#zion-continuum-js')).toHaveCount(0);
  await page.goto('/');await expect(page.locator('.zion-homepage canvas')).toHaveCount(6);
  await page.goto('/despre-noi/');await expect(page.locator('.zion-about canvas')).toHaveCount(2);
});

test('all eight service pages retain their editorial fallback without JavaScript',async({browser,baseURL})=>{
  test.setTimeout(120000);
  const context=await browser.newContext({baseURL,javaScriptEnabled:false,viewport:{width:390,height:850}});
  try {
    const page=await context.newPage();
    for(const {key,url} of await destinations(page)) {
      await page.goto(url);await expect(page.locator('main h1')).toBeVisible();
      await expect(page.locator(key==='directory' ? '.zion-overview-orbit' : '.zion-detail-symbol')).toBeVisible();
      for(const panel of await page.locator('.zion-service-motion').all()) await expect(panel).toBeHidden();
      for(const link of await page.locator('.zion-overview-end a').all()) await expect(link).toBeVisible();
      expect(await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
    }
  } finally {await context.close();}
});
