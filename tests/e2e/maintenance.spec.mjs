import { test, expect } from '@playwright/test';
import { createHash } from 'node:crypto';
import { readFile } from 'node:fs/promises';
import { maintenanceDocument } from '../../scripts/build-maintenance.mjs';

const document = await maintenanceDocument();
const logo = await readFile(new URL('../../themes/zion-theme/assets/brand/zion-logo.webp', import.meta.url));
const fingerprint = async canvas => createHash('sha256').update(await canvas.evaluate(e => e.toDataURL())).digest('hex');
const install = async page => {
  // Synthetic document on the tests origin; never switches a real site's maintenance setting.
  await page.route('https://zion3d.ro/wp-content/uploads/2025/11/ZionMediaLogo-e1762644499622.webp', route => route.fulfill({contentType:'image/webp',body:logo}));
  await page.route('**/?zion-maintenance-fixture=1', route => route.fulfill({contentType:'text/html',body:document}));
  await page.goto('/?zion-maintenance-fixture=1');
};

for(const width of [390,768,1024,1440]) {
  test(`maintenance Continuum is readable and fits ${width}px`, async ({page}) => {
    const errors=[];page.on('pageerror',e=>errors.push(e.message));
    await page.setViewportSize({width,height:900});await install(page);
    await expect(page.getByRole('heading',{level:1})).toHaveCount(1);
    await expect(page.locator('main')).toHaveClass(/is-ready/);
    await expect(page.locator('canvas')).toHaveAttribute('data-render-mode','static');
    await expect(page.getByRole('button')).toBeHidden();
    for(const a of await page.locator('.zion-uc__actions a').all()) {
      await expect(a).toBeVisible();expect((await a.boundingBox()).height).toBeGreaterThanOrEqual(44);
    }
    expect(await page.locator('canvas').evaluate(e=>e.getContext('2d').getImageData(0,0,e.width,e.height).data.some((v,i)=>i%4===3&&v>0))).toBe(true);
    expect(await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
    await page.keyboard.press('Tab');await expect(page.getByText('Sari la conținut')).toBeFocused();
    await page.keyboard.press('Enter');expect(await page.evaluate(()=>location.hash)).toBe('#zion-uc-content');
    await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content','noindex,nofollow,noarchive');
    expect(errors).toEqual([]);
  });
}

test('maintenance motion pauses at keyboard and responds to live reduced-motion changes',async({page})=>{
  await page.emulateMedia({reducedMotion:'no-preference'});await page.setViewportSize({width:1440,height:1000});await install(page);
  const canvas=page.locator('canvas'),button=page.getByRole('button');
  const first=await fingerprint(canvas);await expect.poll(()=>fingerprint(canvas)).not.toBe(first);
  await button.focus();await button.press('Enter');await expect(button).toHaveAttribute('aria-pressed','true');
  const paused=await fingerprint(canvas);await page.waitForTimeout(180);expect(await fingerprint(canvas)).toBe(paused);
  await button.press('Enter');await expect.poll(()=>fingerprint(canvas)).not.toBe(paused);
  await page.emulateMedia({reducedMotion:'reduce'});await expect(button).toBeHidden();
  const reduced=await fingerprint(canvas);await page.waitForTimeout(180);expect(await fingerprint(canvas)).toBe(reduced);
  await page.emulateMedia({reducedMotion:'no-preference'});await expect(button).toBeVisible();
  await expect.poll(()=>fingerprint(canvas)).not.toBe(reduced);
});

test('maintenance remains useful without JavaScript and does not initiate contact requests',async({browser,baseURL})=>{
  const context=await browser.newContext({baseURL,javaScriptEnabled:false,viewport:{width:390,height:844}});
  try {
    const page=await context.newPage();const outgoing=[];page.on('request',r=>outgoing.push(r.url()));await install(page);
    await expect(page.getByRole('heading',{name:/Realitatea începe/})).toBeVisible();
    await expect(page.locator('.zion-uc__fallback')).toBeVisible();
    await expect(page.getByRole('link',{name:/Povestește-ne/})).toHaveAttribute('href','https://wa.me/40770675597');
    await expect(page.locator('.zion-uc__phone')).toHaveAttribute('href','tel:+40770675597');
    expect(outgoing.filter(url=>/wa\.me|tel:/.test(url))).toEqual([]);
    expect(await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
  }finally{await context.close();}
});
