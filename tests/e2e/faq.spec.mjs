import { test, expect } from '@playwright/test';
import { createHash } from 'node:crypto';

const fingerprint=async canvas=>createHash('sha256').update(await canvas.evaluate(e=>e.toDataURL())).digest('hex');
for(const width of [390,768,1024,1440]) {
  test(`FAQ native layout and disclosures fit at ${width}px @visual`,async({page})=>{
    const errors=[];page.on('pageerror',e=>errors.push(e.message));
    await page.setViewportSize({width,height:950});await page.goto('/intrebari/');
    const root=page.locator('.zion-faq'),items=root.locator('details.wp-block-details');
    await expect(root).toHaveClass(/is-faq-enhanced/);await expect(root).toHaveClass(/is-spatial-static/);
    await expect(page.locator('main h1')).toHaveCount(1);await expect(items).toHaveCount(10);
    await expect(root.locator('canvas')).toHaveCount(2);await expect(page.locator('#zion-faq-js')).toHaveCount(1);
    for(const canvas of await root.locator('canvas').all()) {
      await expect(canvas).toHaveAttribute('data-render-mode','static');await expect(canvas).toHaveAttribute('aria-hidden','true');
      expect(await canvas.evaluate(e=>e.getContext('2d').getImageData(0,0,e.width,e.height).data.some((v,i)=>i%4===3&&v>0))).toBe(true);
    }
    for(const selector of ['.zion-faq-network','.zion-faq-library','.zion-faq-relay']) {
      const b=await root.locator(selector).boundingBox();expect(b.x).toBeGreaterThanOrEqual(0);expect(b.x+b.width).toBeLessThanOrEqual(width+.5);
    }
    if([390,1440].includes(width))await expect(root.locator('.zion-faq-hero')).toHaveScreenshot(`faq-hero-${width}.png`);
    await items.first().locator('summary').click();await expect(items.first()).toHaveAttribute('open','');
    await expect(items.first().locator('p')).toContainText('STL');
    for(const control of await root.locator('summary,input,button:not([hidden])').all())expect((await control.boundingBox()).height).toBeGreaterThanOrEqual(44);
    if([390,1440].includes(width))await expect(items.first()).toHaveScreenshot(`faq-answer-${width}.png`,{animations:'disabled'});
    expect(await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);expect(errors).toEqual([]);
  });
}

test('FAQ keyboard, multi-open, accent-insensitive search and empty state preserve answers',async({page})=>{
  await page.goto('/intrebari/');const root=page.locator('.zion-faq'),items=root.locator('details.wp-block-details');
  await items.first().locator('summary').focus();await page.keyboard.press('Enter');await expect(items.first()).toHaveAttribute('open','');
  await items.nth(1).locator('summary').focus();await page.keyboard.press('Space');await expect(root.locator('details[open]')).toHaveCount(2);
  const search=root.getByRole('searchbox',{name:'Caută în întrebări și răspunsuri'});await search.fill('geometria tolerantele');
  await expect(root.locator('details:not([hidden])')).toHaveCount(1);await expect(root.locator('details:not([hidden]) summary')).toContainText('idee');
  expect(await root.locator('details:not([hidden]) summary').evaluate(e=>getComputedStyle(e,'::before').content)).toBe('"03"');
  await root.getByRole('button',{name:'Deschide toate',exact:true}).click();await expect(root.locator('details:not([hidden])[open]')).toHaveCount(1);
  await root.getByRole('button',{name:'Închide toate',exact:true}).click();await expect(root.locator('details:not([hidden])[open]')).toHaveCount(0);
  await search.fill('nu-exista-raspuns-1125');await expect(root.locator('.zion-faq-empty')).toBeVisible();await expect(root.getByRole('button',{name:'Deschide toate',exact:true})).toBeDisabled();
  await search.press('Escape');await expect(search).toHaveValue('');await expect(root.locator('details:not([hidden])')).toHaveCount(10);
  await root.getByRole('button',{name:'Deschide toate',exact:true}).click();await expect(root.locator('details[open]')).toHaveCount(10);
  await expect(root.getByRole('status')).toHaveText('10 întrebări · 10 răspunsuri deschise');
  await root.getByRole('button',{name:'Închide toate',exact:true}).click();await expect(root.locator('details[open]')).toHaveCount(0);
});

for(const width of [390,1440]) {
  test(`FAQ signal follows disclosures, pauses and respects reduced motion at ${width}px`,async({page})=>{
    await page.setViewportSize({width,height:950});await page.emulateMedia({reducedMotion:'no-preference'});await page.goto('/intrebari/');
    const root=page.locator('.zion-faq'),hero=root.locator('[data-zion-spatial="faq-network"]');await hero.scrollIntoViewIfNeeded();
    await expect(hero).toHaveAttribute('data-render-mode','animated');let before=await fingerprint(hero);
    await expect.poll(()=>fingerprint(hero)).not.toBe(before);
    await root.locator('.zion-faq-network .zion-spatial-pause').click();before=await fingerprint(hero);await page.waitForTimeout(180);expect(await fingerprint(hero)).toBe(before);
    await expect(root.getByRole('button',{name:'Reia animația',exact:true})).toHaveCount(2);
    // Static preference still reacts to native disclosures; it has no animation loop.
    await page.emulateMedia({reducedMotion:'reduce'});await expect(hero).toHaveAttribute('data-render-mode','static');
    await root.locator('details summary').first().click();await hero.scrollIntoViewIfNeeded();const active=await fingerprint(hero);
    await root.locator('details summary').first().click();await hero.scrollIntoViewIfNeeded();expect(await fingerprint(hero)).not.toBe(active);
    await expect(root.locator('.zion-spatial-pause:visible')).toHaveCount(0);
    await page.emulateMedia({reducedMotion:'no-preference'});await expect(hero).toHaveAttribute('data-render-mode','animated');
  });
}

test('FAQ retains native disclosures without JavaScript or Canvas and stays conditional',async({browser,baseURL})=>{
  const context=await browser.newContext({baseURL,javaScriptEnabled:false,viewport:{width:390,height:950}});const page=await context.newPage();
  try{await page.goto('/intrebari/');await expect(page.locator('.zion-faq-controls')).toHaveCount(0);await expect(page.locator('.zion-faq-art-fallback').first()).toBeVisible();
    await page.locator('.zion-faq-content summary').first().click();await expect(page.locator('.zion-faq-content details').first()).toHaveAttribute('open','');
    await expect(page.locator('.zion-faq-content details p').first()).toBeVisible();expect(await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
  }finally{await context.close();}
  const ctx=await browser.newContext({baseURL});const p=await ctx.newPage();
  try{await p.addInitScript(()=>{HTMLCanvasElement.prototype.getContext=()=>null;});await p.goto('/intrebari/');
    await expect(p.locator('.zion-faq')).toHaveClass(/is-faq-enhanced/);await p.getByRole('searchbox',{name:'Caută în întrebări și răspunsuri'}).fill('STL');await expect(p.locator('.zion-faq-content details:not([hidden])')).toHaveCount(1);
    await expect(p.locator('.zion-faq-art-fallback').first()).toBeVisible();await p.goto('/magazin-3d/');await expect(p.locator('#zion-faq-js,#zion-continuum-js')).toHaveCount(0);
  }finally{await ctx.close();}
});

test('FAQ template and questions remain valid in the native editors',async({page})=>{
  test.setTimeout(90000);
  await page.goto('/wp-login.php');await page.request.post('/wp-login.php',{maxRedirects:0,form:{log:'admin',pwd:'password',testcookie:'1'}});
  await page.goto('/wp-admin/site-editor.php?p=%2Fwp_template%2Fzion-theme%2F%2Ffaq&canvas=edit');
  await page.waitForFunction(()=>window.wp?.data?.select('core/block-editor')?.getBlocks().length>0);
  const blocks=await page.evaluate(()=>{const walk=bs=>bs.flatMap(b=>[b,...walk(b.innerBlocks||[])]);return walk(wp.data.select('core/block-editor').getBlocks());});
  expect(blocks.filter(b=>b.isValid===false).map(b=>b.name)).toEqual([]);
  expect(blocks.some(b=>b.name==='core/post-content')).toBe(true);
  const nonce=await(await page.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text();
  const records=await(await page.request.get('/wp-json/wp/v2/pages?slug=intrebari&context=edit',{headers:{'X-WP-Nonce':nonce}})).json();
  const questionBlocks=await page.evaluate(content=>wp.blocks.parse(content),records[0].content.raw);
  expect(questionBlocks.filter(b=>b.name==='core/details')).toHaveLength(10);expect(questionBlocks.every(b=>b.isValid!==false)).toBe(true);
});
