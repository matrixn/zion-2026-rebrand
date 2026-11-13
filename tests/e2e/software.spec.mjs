import { test, expect } from '@playwright/test';
import { createHash } from 'node:crypto';

test.describe.configure({mode:'serial'});
const fingerprint=async canvas=>createHash('sha256').update(await canvas.evaluate(e=>e.toDataURL())).digest('hex');
const contexts=[
  ['pluginuri-woocommerce','hub','Pluginuri WooCommerce'],['magazin-software','catalog','Magazin Software'],
  ['zion-sms-confirmation-woocommerce','sms','Zion SMS Confirmation for WooCommerce'],
  ['pluginuri-woocommerce/zion-privacy','privacy','Zion Privacy'],
  ['zion-multi-currency-for-woocommerce','currency','Zion Multi-Currency for WooCommerce'],
  ['zion-releasestation-git-deployment-automatizat-pentru-synology-dsm','release','Zion ReleaseStation · Română'],
  ['zion-releasestation-automated-git-deployment-for-synology-dsm','release','Zion ReleaseStation · English'],
];
const paragraph=text=>`<!-- wp:paragraph --><p>${text}</p><!-- /wp:paragraph -->`;
const heading=(text,level=2)=>`<!-- wp:heading${level===2?'':' '+JSON.stringify({level})} --><h${level} class="wp-block-heading">${text}</h${level}><!-- /wp:heading -->`;
let admin,api,headers,created=[],records=[],protectedPage;

test.beforeAll(async({browser,baseURL})=>{
  const url=new URL(baseURL);if(!['localhost','127.0.0.1','[::1]'].includes(url.hostname)||url.port!=='8917')throw new Error('Software fixtures require the isolated wp-env tests instance.');
  api=await browser.newContext({baseURL});admin=await api.newPage();await admin.goto('/wp-login.php');
  expect((await admin.request.post('/wp-login.php',{maxRedirects:0,form:{log:'admin',pwd:'password',testcookie:'1'}})).status()).toBe(302);
  headers={'X-WP-Nonce':await(await admin.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text()};
  const create=async(data)=>{const r=await admin.request.post('/wp-json/wp/v2/pages',{headers,data:{status:'publish',...data}});expect(r.status(),await r.text()).toBe(201);const p=await r.json();created.push(p.id);return(await admin.request.get(`/wp-json/wp/v2/pages/${p.id}?context=edit`,{headers})).json();};
  for(const [path] of contexts){if(path==='magazin-software')continue;const slug=path.split('/').pop();const existing=await(await admin.request.get(`/wp-json/wp/v2/pages?slug=${slug}`,{headers})).json();expect(existing,'Software test paths must be absent before creating owned synthetic pages.').toHaveLength(0);}
  const hubLinks=contexts.slice(1).map(([path,,title])=>paragraph(`<a href="${baseURL}/${path}/">${title}</a>`)).join('');
  const hub=await create({slug:'pluginuri-woocommerce',title:contexts[0][2],content:paragraph('Ecosistem software demonstrativ Zion, fără date comerciale reale.')+hubLinks});records.push(hub);
  const media=await(await admin.request.get('/wp-json/wp/v2/media?per_page=100',{headers})).json();const image=media.find(m=>m.source_url.includes('zion-demo-3d-01'));
  for(const [path,key,title] of contexts.slice(2)) {
    const english=path.includes('automated'),intro=english?'A synthetic software page for repeatable layout testing.':'O pagină software sintetică pentru verificări reproductibile.';
    let content=paragraph(intro)+heading(english?'Context and capabilities':'Context și funcționalități');
    for(let i=1;i<=4;i++)content+=heading(`${english?'Capability':'Funcționalitatea'} ${i}`,3)+paragraph(english?'A native, editable explanation of the demonstration workflow.':'O explicație nativă, editabilă, a fluxului demonstrativ.');
    content+=heading(english?'A visible workflow':'Un flux vizibil');
    for(let i=1;i<=4;i++)content+=heading(`${english?'Step':'Etapa'} ${i}`,3)+paragraph(english?'A synthetic step, without external actions or customer data.':'Un pas sintetic, fără acțiuni externe sau date de client.');
    content+=heading(english?'Implementation notes':'Note de implementare')+paragraph('Acest exemplu păstrează codul și blocurile native.');
    content+='<!-- wp:code --><pre class="wp-block-code"><code>schema: zion.demo/v1\n'+('native_example_'.repeat(20))+'\n&lt;h1&gt;Cod păstrat&lt;/h1&gt;</code></pre><!-- /wp:code -->';
    content+=heading(english?'Access and next steps':'Acces și pașii următori')+paragraph(`<a href="${key==='currency'?'void(0);':baseURL+'/contact/'}">${key==='currency'?'Vezi produsul':english?'Request access':'Discută cu noi'}</a>`);
    if(key==='currency')content+=paragraph('<a href="void(0);">Cumpără pluginul →</a>')+paragraph('<a href="void(0);">Vezi produsul →</a>');
    if(key==='release')content+=paragraph(`<a href="${baseURL}/${contexts[5][0]}/">Română</a> · <a href="${baseURL}/${contexts[6][0]}/">English</a>`);
    if(key==='sms')for(const plan of ['Alpha','Beta'])content+=paragraph(`<a href="/product/synthetic-${plan.toLowerCase()}/">Choose Plan ${plan}. Synthetic plan description. View product.</a>`)+heading(`Plan ${plan}`,3)+paragraph('Synthetic plan description.');
    const page=await create({slug:path.split('/').pop(),parent:key==='privacy'?hub.id:0,title,content,featured_media:key==='release'?image.id:0});records.push(page);
  }
  protectedPage=await create({slug:'zion-software-protected-fixture',title:'Protected Software',template:'software',password:'local-demo-only',content:paragraph('SOFTWARE SECRET FIXTURE')});
});

test.afterAll(async()=>{if(!admin)return;try{for(const id of created.reverse())expect((await admin.request.delete(`/wp-json/wp/v2/pages/${id}?force=true`,{headers})).ok()).toBe(true);}finally{await api.close();}});

for(const width of [390,768,1024,1440]) {
  test(`seven Software contexts fit at ${width}px @visual`,async({page})=>{
    test.setTimeout(90000);const errors=[];page.on('pageerror',e=>errors.push(e.message));await page.setViewportSize({width,height:950});
    for(const [path,key,title] of contexts) {
      await page.goto(`/${path}/`);const root=page.locator('.zion-software');await expect(root).toHaveAttribute('data-zion-software',key);await expect(root).toHaveClass(/is-spatial-static/);
      await expect(root.getByRole('heading',{level:1})).toHaveText(title);await expect(root.locator('h1')).toHaveCount(1);await expect(root.locator('canvas')).toHaveCount(key==='catalog'?1:2);
      await expect(root.locator('h1')).toHaveCSS('font-weight','680');
      if(!['hub','catalog'].includes(key)){await expect(root.locator('.zion-software-section')).toHaveCount(5);await expect(root.locator('.zion-software-card')).toHaveCount(key==='sms'?10:8);}
      if(key==='sms')for(const plan of ['Alpha','Beta']){const card=root.locator('.zion-software-plan').filter({has:page.getByRole('heading',{name:`Plan ${plan}`,exact:true})});await expect(card).toHaveCount(1);const action=card.getByRole('link',{name:`Vezi produsul: Plan ${plan}`,exact:true});await expect(action).toHaveText('Vezi produsul ↗');await expect(action).toHaveAttribute('href',`/product/synthetic-${plan.toLowerCase()}/`);expect((await action.boundingBox()).height).toBeGreaterThanOrEqual(48);}
      if(key==='hub')await expect(root.locator('.zion-software-link-card')).toHaveCount(6);
      for(const canvas of await root.locator('canvas').all()){await expect(canvas).toHaveAttribute('data-render-mode','static');expect(await canvas.evaluate(e=>e.getContext('2d').getImageData(0,0,e.width,e.height).data.some((v,i)=>i%4===3&&v>0))).toBe(true);}
      for(const frame of await root.locator('.zion-software-hero-art,.zion-software-copy').all()){const b=await frame.boundingBox();expect(b.x).toBeGreaterThanOrEqual(0);expect(b.x+b.width).toBeLessThanOrEqual(width+.5);}
      expect(await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
      if([390,1440].includes(width))await expect(root.locator('.zion-software-hero')).toHaveScreenshot(`software-${path.includes('automated')?'release-en':key}-${width}.png`);
    }expect(errors).toEqual([]);
  });
}

for(const width of [390,1440]) {
  test(`Software concepts animate and the navigation map stays quiet at ${width}px`,async({page})=>{
    test.setTimeout(90000);await page.setViewportSize({width,height:950});await page.emulateMedia({reducedMotion:'no-preference'});
    for(const [path,key] of contexts.filter(c=>c[1]!=='catalog'&&!c[0].includes('automated'))) {
      await page.goto(`/${path}/`);const root=page.locator('.zion-software'),hero=root.locator('[data-zion-spatial="software-hero"]');await hero.scrollIntoViewIfNeeded();
      const before=await fingerprint(hero);await expect.poll(()=>fingerprint(hero)).not.toBe(before);await root.locator('.zion-spatial-pause').click();const paused=await fingerprint(hero);await page.waitForTimeout(160);expect(await fingerprint(hero)).toBe(paused);
      await root.locator('.zion-spatial-pause').click();const map=root.locator('[data-zion-spatial="software-map"]');await map.scrollIntoViewIfNeeded();await expect(map).toHaveAttribute('data-render-mode','scroll');
      const still=await fingerprint(map);await page.waitForTimeout(180);expect(await fingerprint(map)).toBe(still);
      await page.emulateMedia({reducedMotion:'reduce'});for(const canvas of await root.locator('canvas').all())await expect(canvas).toHaveAttribute('data-render-mode','static');await page.emulateMedia({reducedMotion:'no-preference'});
    }
  });
}

test('Software hub, native section links, languages and repaired inquiry actions work',async({page})=>{
  await page.goto('/pluginuri-woocommerce/');for(const [path] of contexts.slice(1))await expect(page.locator('.zion-software-link-card a').filter({hasText:contexts.find(c=>c[0]===path)[2]})).toHaveAttribute('href',new RegExp(path+'/$'));
  await page.goto(`/${contexts[2][0]}/`);const nav=page.getByRole('navigation',{name:'În această pagină'});if(!(await nav.locator('details').getAttribute('open')!==null))await nav.locator('summary').click();const section=nav.getByRole('link',{name:'Un flux vizibil',exact:true});await section.click();await expect(page).toHaveURL(/#zion-software-section-3$/);await expect(page.locator('#zion-software-section-3')).toBeInViewport();
  await page.goto(`/${contexts[4][0]}/`);const actions=page.locator('.zion-software-content a.zion-software-inquiry');await expect(actions).toHaveCount(3);for(const a of await actions.all()){await expect(a).toHaveAttribute('href',/\/contact\/\?software=multi-currency$/);await expect(a).toContainText('Solicită detalii');}
  await page.goto(`/${contexts[6][0]}/`);await expect(page.locator('.zion-software')).toHaveAttribute('lang','en');await page.emulateMedia({reducedMotion:'no-preference'});await expect(page.getByRole('button',{name:'Pause animation',exact:true})).toBeVisible();await expect(page.locator('.zion-software-content a').filter({hasText:'Română'})).toHaveAttribute('href',new RegExp(contexts[5][0]+'/$'));
});

test('Software catalog keeps native AJAX sorting, visibility and empty-state recovery',async({page})=>{
  await page.setViewportSize({width:390,height:950});await page.goto('/magazin-software/?orderby=price');const catalog=page.locator('.zion-catalog-native');await expect(catalog.locator('.wc-block-product')).toHaveCount(2);
  const prices=async()=>catalog.locator('.wc-block-product').evaluateAll(cards=>cards.map(card=>Number([...card.querySelectorAll('.woocommerce-Price-amount')].at(-1).textContent.replace(/[^\d.,]/g,'').replace(',','.'))));const ascending=await prices();expect(ascending).toEqual([...ascending].sort((a,b)=>a-b));
  await expect(catalog).not.toContainText('Suport modular 3D');const started=await page.evaluate(()=>performance.timeOrigin);await page.getByLabel('Sortează',{exact:true}).selectOption('price-desc');await expect(page.getByRole('status').filter({hasText:'Sortarea a fost actualizată.'})).toBeVisible();expect(await page.evaluate(()=>performance.timeOrigin)).toBe(started);await expect(page.locator('.zion-software-hero canvas')).toHaveCount(1);
  expect(await prices()).toEqual([...ascending].sort((a,b)=>b-a));
  await page.goto('/magazin-software/?min_price=9999');await expect(catalog).toContainText('Nu există produse pentru selecția curentă.');await expect(catalog.locator('.wc-block-product')).toHaveCount(0);await catalog.getByRole('button',{name:'Filtrează produsele',exact:true}).click();await catalog.getByRole('link',{name:'Resetează filtrele',exact:true}).click();await expect(catalog.locator('.wc-block-product')).toHaveCount(2);expect(await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
});

test('Software remains readable without JavaScript and respects native password protection',async({browser,baseURL,page})=>{
  const ctx=await browser.newContext({baseURL,javaScriptEnabled:false,viewport:{width:390,height:950}});try{const p=await ctx.newPage();await p.goto(`/${contexts[5][0]}/`);await expect(p.locator('.zion-software-art-fallback').first()).toBeVisible();await p.locator('.zion-software-toc summary').click();await p.locator('.zion-software-toc a').filter({hasText:'Note de implementare'}).click();await expect(p.locator('pre[tabindex="0"]')).toBeVisible();expect(await p.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);}finally{await ctx.close();}
  await page.goto(protectedPage.link);await expect(page.locator('.post-password-form')).toBeVisible();await expect(page.locator('.zion-software-content')).not.toContainText('SOFTWARE SECRET FIXTURE');await expect(page.locator('.zion-software-toc')).toHaveCount(0);
});

test('Software canonical template and all saved blocks stay native and unchanged',async({page})=>{
  test.setTimeout(90000);await page.goto('/wp-login.php');await page.request.post('/wp-login.php',{maxRedirects:0,form:{log:'admin',pwd:'password',testcookie:'1'}});await page.goto('/wp-admin/site-editor.php?p=%2Fwp_template%2Fzion-theme%2F%2Fsoftware&canvas=edit');await page.waitForFunction(()=>window.wp?.data?.select('core/block-editor')?.getBlocks().length>0);
  expect(await page.evaluate(()=>{const walk=bs=>bs.flatMap(b=>[b,...walk(b.innerBlocks||[])]);return walk(wp.data.select('core/block-editor').getBlocks()).filter(b=>b.isValid===false).map(b=>b.name);})).toEqual([]);
  for(const record of records){const actual=await(await admin.request.get(`/wp-json/wp/v2/pages/${record.id}?context=edit`,{headers})).json();expect(actual.content.raw).toBe(record.content.raw);expect(actual.template).toBe(record.template);expect(actual.link).toBe(record.link);}
});

test('Software long section navigation keeps native scrolling with Zion scrollbar colors',async({page})=>{
  const fixture=records.find(record=>record.slug==='zion-sms-confirmation-woocommerce'),original=fixture.content.raw;
  const changed=await admin.request.post(`/wp-json/wp/v2/pages/${fixture.id}`,{headers,data:{content:Array.from({length:20},(_,i)=>heading('Secțiunea demonstrativă '+(i+1))+paragraph('Conținut sintetic pentru navigarea cu tastatura.')).join('')}});expect(changed.ok()).toBe(true);
  try {
    for(const width of [390,1440]){
      await page.setViewportSize({width,height:950});await page.goto(fixture.link);
      const nav=page.locator('.zion-software-toc'),list=nav.locator('ol');if(await nav.locator('details').getAttribute('open')===null)await nav.locator('summary').click();
      expect(await list.evaluate(e=>getComputedStyle(e).scrollbarColor)).not.toBe('auto');expect(await list.evaluate(e=>e.scrollHeight>e.clientHeight)).toBe(true);
      await nav.getByRole('link').last().focus();await expect(nav.getByRole('link').last()).toBeInViewport();expect(await list.evaluate(e=>e.scrollTop)).toBeGreaterThan(0);
      expect(await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
      await page.screenshot({path:`build/software-scrollbar-${width}-1.1.29.png`,fullPage:false});
    }
    await page.emulateMedia({forcedColors:'active'});await expect(page.locator('.zion-software-toc ol')).toHaveCSS('scrollbar-color','auto');
  } finally {expect((await admin.request.post(`/wp-json/wp/v2/pages/${fixture.id}`,{headers,data:{content:original}})).ok()).toBe(true);}
});
