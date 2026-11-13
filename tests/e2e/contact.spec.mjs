import { test, expect } from '@playwright/test';
import { createHash } from 'node:crypto';

const fingerprint=async canvas=>createHash('sha256').update(await canvas.evaluate(e=>e.toDataURL())).digest('hex');
const fill=async(page,message='Solicitare sintetică Zion pentru un proiect demonstrativ.')=>{
  await page.locator('.zion-contact input[name=your-name]').fill('Test Zion Contact');
  await page.locator('.zion-contact input[name=your-email]').fill('qa@example.test');
  await page.locator('.zion-contact select[name=your-service]').selectOption('Printare 3D');
  await page.locator('.zion-contact textarea[name=your-message]').fill(message);
};

for(const width of [390,768,1024,1440]) {
  test(`Contact native composition and CF7 fit at ${width}px @visual`,async({page})=>{
    const errors=[];page.on('pageerror',e=>errors.push(e.message));
    await page.setViewportSize({width,height:950});
    await page.goto('/contact/?serviciu=printare-3d');
    const root=page.locator('.zion-contact');await expect(root).toHaveClass(/is-spatial-static/);
    await expect(page.locator('main.zion-contact-main h1')).toHaveCount(1);
    await expect(root.locator('canvas')).toHaveCount(3);
    await expect(page.locator('#zion-continuum-js,#contact-form-7-js')).toHaveCount(2);
    await expect(root.locator('.wpcf7 form')).toHaveCount(1);
    await expect(page.locator('.zion-contact select[name=your-service]')).toHaveValue('Printare 3D');
    await expect(root.locator('.zion-contact-progress')).toHaveText('1 / 4 CÂMPURI COMPLETATE');
    for(const selector of ['.zion-contact-signal','.zion-contact-panel']) {
      const frame=root.locator(selector);
      const bounds=await frame.evaluate(e=>({left:e.getBoundingClientRect().left,right:e.getBoundingClientRect().right,viewport:document.documentElement.clientWidth}));
      expect(bounds.left).toBeGreaterThanOrEqual(0);expect(bounds.right).toBeLessThanOrEqual(bounds.viewport+.5);
    }
    for(const canvas of await root.locator('canvas').all()) {
      await expect(canvas).toHaveAttribute('aria-hidden','true');await expect(canvas).toHaveAttribute('data-render-mode','static');
      const shape=await canvas.evaluate(e=>({pixels:e.width/e.height,display:e.clientWidth/e.clientHeight,painted:e.getContext('2d').getImageData(0,0,e.width,e.height).data.some((v,i)=>i%4===3&&v>0)}));
      expect(shape.pixels).toBeCloseTo(shape.display,1);expect(shape.painted).toBe(true);
    }
    for(const input of await root.locator('.wpcf7 input:not([type=hidden]),.wpcf7 select,.wpcf7 textarea').all())expect((await input.boundingBox()).height).toBeGreaterThanOrEqual(44);
    await expect(root.locator('.zion-contact-channel')).toHaveCount(3);
    await expect(root.getByRole('link',{name:'contact@zion3d.ro ↗',exact:true})).toHaveAttribute('href','mailto:contact@zion3d.ro');
    expect(await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
    expect(errors).toEqual([]);
    if([390,1440].includes(width)) {
      await expect(root.locator('.zion-contact-hero')).toHaveScreenshot(`contact-hero-${width}.png`);
      await expect(root.locator('.zion-contact-panel')).toHaveScreenshot(`contact-form-${width}.png`);
    }
  });
}

for(const width of [390,1440]) {
  test(`Contact motion, completion and pause stay accessible at ${width}px`,async({browser,baseURL})=>{
    const context=await browser.newContext({baseURL,viewport:{width,height:850},reducedMotion:'no-preference'});
    try {
      const page=await context.newPage();await page.goto('/contact/');
      const root=page.locator('.zion-contact');
      for(const type of ['signal','console','route']) {
        const canvas=root.locator(`[data-zion-spatial="contact-${type}"]`);
        await canvas.scrollIntoViewIfNeeded();await expect(canvas).toBeInViewport();
        // The console becomes animated when a field supplies a signal.
        if(type==='console') {
          const idle=await fingerprint(canvas);await page.waitForTimeout(140);expect(await fingerprint(canvas)).toBe(idle);
          await page.locator('.zion-contact input[name=your-name]').fill('Test Zion');
        }
        const initial=await fingerprint(canvas);await expect.poll(()=>fingerprint(canvas)).not.toBe(initial);
        const pause=canvas.locator('..').locator('.zion-spatial-pause');
        await pause.focus();await pause.press('Enter');await expect(pause).toHaveAttribute('aria-pressed','true');
        const frozen=await fingerprint(canvas);await page.waitForTimeout(140);expect(await fingerprint(canvas)).toBe(frozen);
        await pause.press('Enter');await canvas.scrollIntoViewIfNeeded();await expect.poll(()=>fingerprint(canvas)).not.toBe(frozen);
      }
      await fill(page);await expect(root.locator('.zion-contact-progress')).toHaveText('4 / 4 CÂMPURI COMPLETATE');
      await page.emulateMedia({reducedMotion:'reduce'});await expect(root).toHaveClass(/is-spatial-static/);
      for(const canvas of await root.locator('canvas').all())await expect(canvas).toHaveAttribute('data-render-mode','static');
      await page.goto('/magazin-3d/');await expect(page.locator('#contact-form-7-js,#contact-form-7-css,#zion-continuum-js')).toHaveCount(0);
    } finally {await context.close();}
  });

  test(`native CF7 validates and submits with a visible spinner at ${width}px`,async({page})=>{
    test.setTimeout(60000);
    await page.setViewportSize({width,height:950});await page.goto('/contact/');
    const form=page.locator('.zion-contact .wpcf7 form'),button=form.locator('input[type=submit]');
    await expect(form.locator('[name=your-name]')).toHaveAccessibleName(/^Numele tău/);
    await expect(form.locator('[name=your-email]')).toHaveAccessibleName(/^Adresa de e-mail/);
    await expect(form.locator('[name=your-service]')).toHaveAccessibleName(/^Serviciul de interes/);
    await button.click();await expect(form).toHaveAttribute('data-status','invalid');
    await expect(form.locator('[aria-invalid=true]')).toHaveCount(4);
    await expect(form.locator('.wpcf7-response-output')).toContainText('Verifică');
    const requestToken=`ZION-CONTACT-QA-${width}-${Date.now()}`;
    await fill(page,`Solicitare sintetică Zion pentru proiectul demonstrativ ${requestToken}.`);
    let release;const gate=new Promise(resolve=>{release=resolve;});
    await page.route('**/contact-form-7/v1/contact-forms/*/feedback',async route=>{await gate;await route.continue();},{times:1});
    await button.click();await expect(form).toHaveAttribute('data-status','submitting');
    await expect(form.locator('.wpcf7-spinner')).toBeVisible();
    await expect(page.locator('.zion-contact')).toHaveAttribute('data-contact-state','submitting');
    release();await expect(form).toHaveAttribute('data-status','sent');
    await expect(form.locator('.wpcf7-response-output')).toContainText('Mesajul a fost trimis');
    const capture=await(await page.request.get('/wp-content/uploads/zion-contact-test-mail.json')).json();
    expect(capture.to).toBe('recipient@example.test');expect(capture.message).toContain(requestToken);expect(capture.message).toContain('Test Zion Contact');expect(capture.message).toContain('qa@example.test');expect(capture.subject).toContain('Printare 3D');
    await fill(page,'ZION-QA-FAIL: solicitare sintetică pentru eroarea de transport.');await button.click();
    await expect(form).toHaveAttribute('data-status','failed');await expect(form.locator('.wpcf7-response-output')).toContainText('nu a putut fi trimis');
    await expect(form.locator('.wpcf7-spinner')).not.toBeVisible();
  });
}

test('CF7 server validation rejects invalid requests and unknown service URL values',async({page})=>{
  await page.goto('/contact/?serviciu=%3Cscript%3E');
  await expect(page.locator('.zion-contact select[name=your-service]')).toHaveValue('');
  const hidden=await page.locator('.zion-contact .wpcf7 input[type=hidden]').evaluateAll(es=>Object.fromEntries(es.map(e=>[e.name,e.value])));
  const response=await page.request.post(`/wp-json/contact-form-7/v1/contact-forms/${hidden._wpcf7}/feedback`,{multipart:{...hidden,'your-name':'','your-email':'not-an-email','your-service':'','your-message':''}});
  const result=await response.json();expect(result.status).toBe('validation_failed');
  expect(result.invalid_fields.map(e=>e.field)).toEqual(expect.arrayContaining(['your-name','your-email','your-service','your-message']));
});

test('Contact remains functional without JS and editable with the native CF7 block',async({browser,page,baseURL})=>{
  test.setTimeout(90000);
  const context=await browser.newContext({baseURL,javaScriptEnabled:false,viewport:{width:390,height:950}});
  try {
    const noJs=await context.newPage();await noJs.goto('/contact/');
    await expect(noJs.locator('.zion-contact-signal-fallback')).toBeVisible();
    for(const canvas of await noJs.locator('.zion-contact canvas').all())await expect(canvas).toBeHidden();
    await fill(noJs);await noJs.locator('.zion-contact input[type=submit]').click();
    await expect(noJs.locator('.zion-contact .wpcf7-response-output')).toContainText('Mesajul a fost trimis');
    expect(await noJs.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
  } finally {await context.close();}
  await page.goto('/wp-login.php');await page.request.post('/wp-login.php',{form:{log:'admin',pwd:'password',testcookie:'1'},maxRedirects:0});
  const pages=await(await page.request.get('/wp-json/wp/v2/pages?slug=contact')).json();
  await page.goto(`/wp-admin/post.php?post=${pages[0].id}&action=edit`);
  await page.waitForFunction(()=>window.wp?.data?.select('core/block-editor')?.getBlocks().length>0);
  const parsed=await page.evaluate(()=>{const walk=blocks=>blocks.flatMap(block=>[block,...walk(block.innerBlocks||[])]);const blocks=walk(wp.data.select('core/block-editor').getBlocks());return {invalid:blocks.filter(b=>b.isValid===false).map(b=>b.name),cf7:blocks.filter(b=>b.name==='contact-form-7/contact-form-selector').length};});
  expect(parsed).toEqual({invalid:[],cf7:1});
});
