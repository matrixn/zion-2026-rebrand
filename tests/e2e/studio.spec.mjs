import {test,expect} from '@playwright/test';

test.describe.configure({mode:'serial'});
let admin,session,headers,designOriginal,styleId,fixtures=[];
const base='/wp-admin/themes.php?page=zion-theme';
const native=async(path,data)=>{const response=await admin.request.post('/wp-json/wp/v2/'+path,{headers,data});expect(response.ok(),await response.text()).toBe(true);return response.json();};
const readDesign=async()=>{const r=await admin.request.get('/wp-json/zion-theme/v1/design',{headers});expect(r.ok()).toBe(true);return r.json();};
async function login(page){await page.goto('/wp-login.php');expect((await page.request.post('/wp-login.php',{maxRedirects:0,form:{log:'admin',pwd:'password',testcookie:'1'}})).status()).toBe(302);}
async function restoreDesign(){await native('global-styles/'+styleId,{settings:designOriginal.document.settings||{},styles:designOriginal.document.styles||{}});}

test.beforeAll(async({browser,baseURL})=>{
  const url=new URL(baseURL);if(!['localhost','127.0.0.1','[::1]'].includes(url.hostname)||url.port!=='8917')throw new Error('Studio tests require the isolated wp-env tests instance.');
  session=await browser.newContext({baseURL});admin=await session.newPage();await login(admin);headers={'X-WP-Nonce':await(await admin.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text()};
  designOriginal=await readDesign();const theme=await(await admin.request.get('/wp-json/wp/v2/global-styles/themes/zion-theme',{headers})).json();styleId=theme.id;
  if(!styleId){await admin.goto('/wp-admin/site-editor.php');await admin.waitForFunction(()=>wp.data.select('core').__experimentalGetCurrentGlobalStylesId());styleId=await admin.evaluate(()=>wp.data.select('core').__experimentalGetCurrentGlobalStylesId());}
  const longPage=await native('pages',{title:'Synthetic long page title for responsive design previews and navigation '.repeat(3),status:'publish',content:'<!-- wp:paragraph --><p>Synthetic preview fixture.</p><!-- /wp:paragraph -->'});fixtures.push('pages/'+longPage.id);
});
test.afterAll(async()=>{try{await restoreDesign();for(const path of fixtures.reverse())expect((await admin.request.delete('/wp-json/wp/v2/'+path+'?force=true',{headers})).ok()).toBe(true);}finally{await session.close();}});

test('native color picker previews without writing and saves one Global Styles document',async({page})=>{
  await login(page);await page.goto(base+'&section=design');
  await expect(page.getByRole('button',{name:'Salvează designul',exact:true})).toBeDisabled();
  await page.getByRole('button',{name:'Alege culoarea Lime',exact:true}).click();await expect(page.locator('.components-color-picker')).toBeVisible();await page.keyboard.press('Escape');
  const before=await readDesign();await page.locator('#zion-token-color_accent').fill('#baff33');
  const frame=page.frameLocator('iframe[title="Preview design Zion"]');await expect.poll(()=>frame.locator('html').evaluate(e=>getComputedStyle(e).getPropertyValue('--wp--preset--color--accent').trim())).toBe('#baff33');
  expect((await readDesign()).document).toEqual(before.document);
  await page.getByRole('button',{name:'Salvează designul',exact:true}).click();await expect(page.getByRole('status').filter({hasText:'Design salvat.'})).toBeVisible();
  const after=await readDesign();expect(after.document.settings.color.palette.find(p=>p.slug==='accent').color).toBe('#baff33');
  const stale=await admin.request.post('/wp-json/zion-theme/v1/design',{headers,data:{revision:before.revision,values:{color_accent:'#ffffff'}}});expect(stale.status()).toBe(409);
  await page.goto('/');expect(await page.locator('html').evaluate(e=>getComputedStyle(e).getPropertyValue('--wp--preset--color--accent').trim())).toBe('#baff33');await restoreDesign();
});

test('design controls save native font, dimensions and component radii and validate imports',async({page})=>{
  await login(page);await page.goto(base+'&section=design');await page.getByRole('button',{name:'Tipografie',exact:true}).click();await page.locator('#zion-token-font_body').selectOption('Georgia, serif');
  await page.getByRole('button',{name:'Containere',exact:true}).click();await page.locator('#zion-token-wide_width').fill('1440px');
  await page.getByRole('button',{name:'Componente',exact:true}).click();await page.locator('#zion-token-card_radius').fill('22px');await page.locator('#zion-token-button_radius').fill('15px');
  await page.getByRole('button',{name:'Salvează designul',exact:true}).click();await expect(page.getByRole('status').filter({hasText:'Design salvat.'})).toBeVisible();
  await page.goto('/magazin-software/');await expect(page.locator('.zion-product-card').first()).toHaveCSS('border-radius','22px');await expect(page.locator('.zion-card-actions .wp-element-button').first()).toHaveCSS('border-radius','15px');
  expect(await page.locator('html').evaluate(e=>getComputedStyle(e).getPropertyValue('--wp--style--global--wide-size').trim())).toBe('1440px');
  await page.goto(base+'&section=design');await page.locator('input[type=file]').setInputFiles({name:'bad.json',mimeType:'application/json',buffer:Buffer.from('{"version":1,"values":{"unknown":"red"}}')});await expect(page.getByRole('status').filter({hasText:'Fișier de design invalid.'})).toBeVisible();
  const before=await readDesign(),bad=await admin.request.post('/wp-json/zion-theme/v1/design',{headers,data:{revision:before.revision,values:{color_accent:'url(evil)'}}});expect(bad.status()).toBe(400);expect((await readDesign()).document).toEqual(before.document);
  await page.getByRole('button',{name:'Componente',exact:true}).click();await page.getByRole('button',{name:'Resetează Rotunjire carduri Zion',exact:true}).click();await page.getByRole('button',{name:'Resetează Rotunjire butoane',exact:true}).click();
  const preview=page.frameLocator('iframe[title="Preview design Zion"]');await expect.poll(()=>preview.locator('html').evaluate(e=>getComputedStyle(e).getPropertyValue('--wp--custom--zion--card-radius').trim())).toBe('');await expect.poll(()=>preview.locator('html').evaluate(e=>getComputedStyle(e).getPropertyValue('--zion-button-radius').trim())).toBe(before.registry.button_radius.default);expect((await readDesign()).document).toEqual(before.document);
  await page.getByRole('button',{name:'Salvează designul',exact:true}).click();await expect(page.getByRole('status').filter({hasText:'Design salvat.'})).toBeVisible();const reset=await readDesign();expect(reset.document.settings.custom?.zion?.cardRadius).toBeUndefined();expect(reset.document.styles?.elements?.button?.border?.radius).toBeUndefined();await restoreDesign();
});

test('native page editing and date picker publish a scheduled synthetic page',async({page})=>{
  await login(page);await page.goto(base+'&section=studio&kind=pages');await page.getByRole('button',{name:'Creează pagină',exact:true}).click();await expect(page.getByLabel('Titlu document',{exact:true})).toHaveValue('Pagină Zion');const record={id:Number(await page.getByRole('combobox',{name:'Document',exact:true}).inputValue())};fixtures.push('pages/'+record.id);
  const canvas=page.frameLocator('iframe[name="editor-canvas"]');await canvas.locator('[contenteditable=true]').first().fill('Updated in Zion native canvas.');
  await page.getByLabel('Titlu document',{exact:true}).fill('Synthetic Zion Studio edited');const date=page.locator('.zion-document input[type=datetime-local]');await date.fill('2030-06-15T14:30');await page.getByLabel('Stare document',{exact:true}).selectOption('future');
  await page.getByRole('button',{name:'Salvează documentul',exact:true}).click();await expect(page.getByRole('status').filter({hasText:'Document salvat.'})).toBeVisible();
  const after=await(await admin.request.get('/wp-json/wp/v2/pages/'+record.id+'?context=edit',{headers})).json();expect(after.content.raw).toContain('Updated in Zion native canvas.');expect(after.status).toBe('future');expect(after.date).toBe('2030-06-15T14:30:00');
  const old=await(await admin.request.get('/wp-json/zion-theme/v1/document?kind=pages&id='+record.id,{headers})).json();await native('pages/'+record.id,{title:'Concurrent native edit'});
  const stale=await admin.request.post('/wp-json/zion-theme/v1/document',{headers,data:{kind:'pages',id:String(record.id),revision:old.revision,values:{title:'Stale overwrite'}}});expect(stale.status()).toBe(409);
});

test('home and both storefronts preview and persist isolated native colors fonts and spacing',async({page})=>{
  test.setTimeout(180000);await login(page);
  const profiles=[['home','/','#baff33','Georgia, serif','28px'],['3d','/magazin-3d/','#33ccff','Arial, Helvetica, sans-serif','32px'],['software','/magazin-software/','#cc88ff','Verdana, Geneva, sans-serif','36px']];
  try{
    for(const [scope,path,color,font,space] of profiles){
      const before=await readDesign();await page.goto(base+'&section=design&design_scope='+scope);await expect(page.getByRole('combobox',{name:'Destinație design',exact:true})).toHaveValue(scope);
      await page.locator('#zion-token-color_accent').fill(color);const preview=page.frameLocator('iframe[title="Preview design Zion"]');await expect.poll(()=>preview.locator('body').evaluate(e=>getComputedStyle(e).getPropertyValue('--wp--preset--color--accent').trim())).toBe(color);expect(new URL(await page.locator('iframe[title="Preview design Zion"]').getAttribute('src')).pathname).toBe(path);
      await page.getByRole('button',{name:'Tipografie',exact:true}).click();await page.locator('#zion-token-font_body').selectOption(font);await expect.poll(()=>preview.locator('body').evaluate(e=>getComputedStyle(e).fontFamily)).toContain(font.split(',')[0]);
      await page.getByRole('button',{name:'Spațiere',exact:true}).click();await page.locator('#zion-token-spacing_regular').fill(space);await page.locator('#zion-token-spacing_section').fill('48px');await expect.poll(()=>preview.locator('body').evaluate(e=>getComputedStyle(e).getPropertyValue('--wp--preset--spacing--regular').trim())).toBe(space);
      if(scope==='home')await expect(preview.locator('.zion-homepage > .zion-overview-band').nth(1)).toHaveCSS('padding-top','48px');
      expect((await readDesign()).document).toEqual(before.document);await page.getByRole('button',{name:'Salvează designul',exact:true}).click();await expect(page.getByRole('status').filter({hasText:'Design salvat.'})).toBeVisible();
    }
    for(const [scope,path,color,font,space] of profiles){for(const width of [390,768,1024,1440]){
      await page.setViewportSize({width,height:1000});await page.goto(path);await expect(page.locator('body')).toHaveClass(new RegExp('zion-design-'+scope));expect(await page.locator('body').evaluate(e=>getComputedStyle(e).getPropertyValue('--wp--preset--color--accent').trim())).toBe(color);await expect(page.locator('body')).toHaveCSS('font-family',font);expect(await page.locator('body').evaluate(e=>getComputedStyle(e).getPropertyValue('--wp--preset--spacing--regular').trim())).toBe(space);expect(await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
      if(scope!=='3d'&&width===1440)await expect.poll(()=>page.locator('canvas[data-zion-spatial]').first().evaluate((canvas,color)=>{const pixels=canvas.getContext('2d').getImageData(0,0,canvas.width,canvas.height).data;const rgb=[1,3,5].map(n=>parseInt(color.slice(n,n+2),16));let matching=0;for(let i=0;i<pixels.length;i+=4)if(pixels[i+3]>40&&rgb.every((v,j)=>Math.abs(pixels[i+j]-v)<6))matching++;return matching;},color)).toBeGreaterThan(10);
      if(width===390||width===1440)await page.screenshot({path:`build/design-${scope}-${width}-1.1.28.png`,fullPage:false});
    }}
    for(const path of ['/despre-noi/','/cart/','/product/zion-catalog-tools/']){await page.goto(path);await expect(page.locator('body')).toHaveClass(/zion-design-global/);expect(await page.locator('body').evaluate(e=>getComputedStyle(e).getPropertyValue('--wp--preset--color--accent').trim())).toBe(designOriginal.registry.color_accent.default);}
  }finally{await restoreDesign();}
});

test('context resets imports global inheritance and stale saves protect sibling designs',async({page})=>{
  test.setTimeout(90000);await login(page);
  const saveScope=async(scope,values)=>{const state=await(await admin.request.get('/wp-json/zion-theme/v1/design?scope='+scope,{headers})).json();const response=await admin.request.post('/wp-json/zion-theme/v1/design',{headers,data:{scope,revision:state.revision,values}});expect(response.ok(),await response.text()).toBe(true);return response.json();};
  try{
    await saveScope('global',{color_accent:'#ffdd33'});await saveScope('3d',{color_accent:'#33ddaa'});await saveScope('software',{color_accent:'#aa88ff'});
    await page.goto(base+'&section=design&design_scope=3d');await expect(page.locator('#zion-token-color_accent')).toHaveValue('#33ddaa');await page.getByRole('button',{name:'Resetează Lime',exact:true}).click();await expect(page.locator('#zion-token-color_accent')).toHaveValue('#ffdd33');const preview=page.frameLocator('iframe[title="Preview design Zion"]');await expect.poll(()=>preview.locator('body').evaluate(e=>getComputedStyle(e).getPropertyValue('--wp--preset--color--accent').trim())).toBe('#ffdd33');
    await page.getByRole('button',{name:'Salvează designul',exact:true}).click();await expect(page.getByRole('status').filter({hasText:'Design salvat.'})).toBeVisible();
    const before=await readDesign();await page.locator('input[type=file]').setInputFiles({name:'wrong-scope.json',mimeType:'application/json',buffer:Buffer.from(JSON.stringify({version:2,scope:'software',values:{color_accent:'#ffffff'}}))});await expect(page.getByRole('status').filter({hasText:'Fișier de design invalid.'})).toBeVisible();expect((await readDesign()).document).toEqual(before.document);
    await page.locator('input[type=file]').setInputFiles({name:'3d.json',mimeType:'application/json',buffer:Buffer.from(JSON.stringify({version:2,scope:'3d',values:{color_accent:'#55ccff'}}))});await expect(page.locator('#zion-token-color_accent')).toHaveValue('#55ccff');expect((await readDesign()).document).toEqual(before.document);
    page.once('dialog',dialog=>dialog.dismiss());await page.getByRole('combobox',{name:'Destinație design',exact:true}).selectOption('home');await expect(page.getByRole('combobox',{name:'Destinație design',exact:true})).toHaveValue('3d');
    await page.getByRole('button',{name:'Salvează designul',exact:true}).click();await expect(page.getByRole('status').filter({hasText:'Design salvat.'})).toBeVisible();await page.getByRole('combobox',{name:'Destinație design',exact:true}).selectOption('home');await expect(page.locator('#zion-token-color_accent')).toHaveValue('#ffdd33');
    const stale=await readDesign();await saveScope('software',{spacing_regular:'30px'});const conflict=await admin.request.post('/wp-json/zion-theme/v1/design',{headers,data:{scope:'home',revision:stale.revision,values:{color_accent:'#ffffff'}}});expect(conflict.status()).toBe(409);expect((await readDesign()).contexts.software.color_accent).toBe('#aa88ff');expect((await readDesign()).contexts['3d'].color_accent).toBe('#55ccff');
    const invalid=await admin.request.get('/wp-json/zion-theme/v1/design?scope=product',{headers});expect(invalid.status()).toBe(400);
  }finally{await restoreDesign();}
});

test('native menu links and template parts are edited from Zion without duplicating entities',async({page})=>{
  test.setTimeout(90000);
  const menu=await native('navigation',{title:'Synthetic Zion Studio menu',status:'publish',content:'<!-- wp:navigation-link {"label":"Synthetic link","url":"/contact/"} /-->'});fixtures.push('navigation/'+menu.id);
  const part=await native('template-parts',{slug:'zion-studio-owned-fixture-'+Date.now().toString(36),title:'Synthetic Studio footer',content:'<!-- wp:paragraph --><p>Synthetic footer text.</p><!-- /wp:paragraph -->',area:'footer'});fixtures.push('template-parts/'+part.id);
  await login(page);
  await page.goto(base+'&section=studio&kind=navigation');await page.getByRole('button',{name:'Creează meniu',exact:true}).click();await expect(page.getByLabel('Titlu document',{exact:true})).toHaveValue('Meniu Zion');const createdId=await page.getByRole('combobox',{name:'Document',exact:true}).inputValue();fixtures.push('navigation/'+createdId);
  await page.getByRole('button',{name:'Adaugă link',exact:true}).click();await page.getByLabel('Etichetă link',{exact:true}).fill('Synthetic newly created menu');await page.getByLabel('Adresă link',{exact:true}).fill('/contact/');await page.getByRole('button',{name:'Salvează documentul',exact:true}).click();await expect(page.getByRole('status').filter({hasText:'Document salvat.'})).toBeVisible();const createdMenu=await(await admin.request.get('/wp-json/wp/v2/navigation/'+createdId+'?context=edit',{headers})).json();expect(createdMenu.content.raw).toContain('Synthetic newly created menu');
  for(const [kind,record,text] of [['navigation',menu,'Edited menu link'],['parts',part,'Edited native footer']]){
    await page.goto(base+'&section=studio&kind='+kind+'&document='+encodeURIComponent(record.id));await expect(page.getByLabel('Titlu document',{exact:true})).toHaveValue(record.title.raw);
    if(kind==='navigation')await page.getByLabel('Etichetă link',{exact:true}).first().fill(text);
    else await page.frameLocator('iframe[name="editor-canvas"]').locator('[contenteditable=true]').first().fill(text);
    await page.getByRole('button',{name:'Salvează documentul',exact:true}).click();await expect(page.getByRole('status').filter({hasText:'Document salvat.'})).toBeVisible();
    const endpoint=kind==='parts'?'template-parts':'navigation';const saved=await(await admin.request.get('/wp-json/wp/v2/'+endpoint+'/'+record.id+'?context=edit',{headers})).json();expect(saved.content.raw).toContain(text);
  }
  const linked=await native('template-parts',{slug:'zion-studio-linked-owned-fixture-'+Date.now().toString(36),title:'Synthetic linked header',content:`<!-- wp:navigation {"ref":${menu.id}} /-->`,area:'header'});fixtures.push('template-parts/'+linked.id);
  await page.goto(base+'&section=studio&kind=parts&document='+encodeURIComponent(linked.id));await expect(page.getByLabel('Titlu document',{exact:true})).toHaveValue(linked.title.raw);
  const canvas=page.frameLocator('iframe[name=editor-canvas]');await expect(canvas.getByLabel('Meniu conectat',{exact:true})).toHaveValue(String(menu.id));
  const opened=page.waitForEvent('popup');await canvas.getByRole('link',{name:'Editează linkurile acestui meniu în Zion Studio ↗',exact:true}).click();const menuPage=await opened;
  await menuPage.getByLabel('Etichetă link',{exact:true}).first().fill('Edited nested menu link');await menuPage.getByRole('button',{name:'Adaugă submeniu',exact:true}).last().click();await menuPage.getByLabel('Etichetă link',{exact:true}).last().fill('Synthetic submenu');await menuPage.locator('.zion-menu-tree .zion-menu-tree').getByRole('button',{name:'Adaugă link',exact:true}).click();await menuPage.getByLabel('Etichetă link',{exact:true}).last().fill('Synthetic child link');await menuPage.getByLabel('Adresă link',{exact:true}).last().fill('/contact/');await menuPage.getByRole('button',{name:'Salvează documentul',exact:true}).click();await expect(menuPage.getByRole('status').filter({hasText:'Document salvat.'})).toBeVisible();await menuPage.close();
  const nestedMenu=await(await admin.request.get('/wp-json/wp/v2/navigation/'+menu.id+'?context=edit',{headers})).json();expect(nestedMenu.content.raw).toContain('Edited nested menu link');expect(nestedMenu.content.raw).toContain('Synthetic child link');expect(nestedMenu.content.raw).toContain('wp:navigation-submenu');
  await page.goto(base+'&section=studio&kind=parts&document=zion-theme%2F%2Fheader');await expect(page.locator('.zion-document')).toBeVisible();await expect(page.frameLocator('iframe[name=editor-canvas]').locator('[data-type="core/group"]').first()).toBeVisible();
});

test('native media, switches and visual order persist through the existing controls',async({page})=>{
  test.setTimeout(90000);await login(page);await admin.goto(base+'&section=tools');
  const form=admin.locator('form').filter({has:admin.getByRole('button',{name:'Descarcă JSON',exact:true})});
  const fields=await form.evaluate(e=>Object.fromEntries(new FormData(e)));const exported=await admin.request.post('/wp-admin/admin-post.php',{form:{...fields,operation:'export'}});expect(exported.ok()).toBe(true);const originalConfig=await exported.text();
  const settings=await(await admin.request.get('/wp-json/wp/v2/settings',{headers})).json(),originalIdentity=Object.fromEntries(['title','description','site_logo','site_icon'].map(key=>[key,settings[key]]));
  try{
    await page.goto(base+'&section=header');const toggle=page.getByRole('switch',{name:'Comută Mini-coș',exact:true});const value=await toggle.getAttribute('aria-checked');await toggle.click();await expect(toggle).toHaveAttribute('aria-checked',value==='true'?'false':'true');await page.getByRole('button',{name:'Salvează setările',exact:true}).click();await expect(page.getByText('Setările au fost salvate.',{exact:true})).toBeVisible();
    await page.goto(base+'&section=cards');const row=page.locator('[data-zion-field=card_order]'),list=row.locator('.zion-order-list'),keys=await list.locator('li[draggable=true]').evaluateAll(elements=>elements.map(e=>e.dataset.zionKey));await list.locator('li[draggable=true]').first().locator('button').last().click();await expect(page.locator('#zion-field-card_order')).toHaveValue([keys[1],keys[0],...keys.slice(2)].join(','));await page.getByRole('button',{name:'Salvează setările',exact:true}).click();await expect(page.getByText('Setările au fost salvate.',{exact:true})).toBeVisible();
    await page.goto(base+'&section=design');await page.getByText('Identitatea site-ului · logo, pictogramă și nume',{exact:true}).click();await page.locator('.zion-image-control').first().getByRole('button',{name:/Alege imaginea|Schimbă imaginea/}).click();
    await page.getByRole('tab',{name:'Media Library',exact:true}).click();const media=await(await admin.request.get('/wp-json/wp/v2/media?per_page=100',{headers})).json();const image=media.find(m=>m.media_type==='image'&&m.id!==originalIdentity.site_logo);expect(image).toBeTruthy();await page.locator(`.media-modal .attachment[data-id="${image.id}"]`).click();await page.getByRole('button',{name:'Folosește imaginea',exact:true}).click();await page.getByRole('button',{name:'Salvează identitatea',exact:true}).click();await expect(page.getByRole('status').filter({hasText:'Identitate salvată.'})).toBeVisible();expect((await(await admin.request.get('/wp-json/wp/v2/settings',{headers})).json()).site_logo).toBe(image.id);
  }finally{
    await native('settings',originalIdentity);await admin.goto(base+'&section=tools');const current=await admin.locator('form').filter({has:admin.getByRole('button',{name:'Descarcă JSON',exact:true})}).evaluate(e=>Object.fromEntries(new FormData(e)));const restored=await admin.request.post('/wp-admin/admin-post.php',{form:{...current,operation:'import',settings_json:originalConfig}});expect(restored.ok()).toBe(true);
  }
});

for(const width of [390,1440])test(`Studio admin controls fit at ${width}px and keep native permissions`,async({page,browser,baseURL})=>{
  await login(page);await page.setViewportSize({width,height:1000});await page.goto(base+'&section=design');await expect(page.locator('#zion-token-color_ink')).toBeVisible();expect(await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
  await expect(page.getByText('Identitatea site-ului · logo, pictogramă și nume',{exact:true})).toBeVisible();await expect(page.frameLocator('iframe[title="Preview design Zion"]').locator('h1').first()).toBeVisible();
  expect(await page.frameLocator('iframe[title="Preview design Zion"]').locator('.zion-header').evaluate(element=>element.getBoundingClientRect().height)).toBeLessThan(260);
  if(width===390){const positions=await page.frameLocator('iframe[title="Preview design Zion"]').locator('.zion-header').evaluate(e=>{const cart=e.querySelector('.zion-header-tools').getBoundingClientRect(),menu=e.querySelector('.wp-block-navigation__responsive-container-open').getBoundingClientRect();return {cart:cart.y+cart.height/2,menu:menu.y+menu.height/2,x:menu.x,end:cart.right};});expect(Math.abs(positions.cart-positions.menu)).toBeLessThan(1);expect(positions.x).toBeGreaterThanOrEqual(positions.end);}
  await page.getByRole('button',{name:'Alege culoarea Ink',exact:true}).click();await expect(page.locator('.components-color-picker')).toBeVisible();expect(await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);await page.keyboard.press('Escape');
  await page.screenshot({path:`build/studio-design-${width}-1.1.27.png`,fullPage:false});
  await page.goto(base+'&section=software');const number=page.locator('#zion-field-catalog_columns_desktop');const original=await number.inputValue();const slider=page.getByRole('slider',{name:'Coloane desktop · glisor'});await slider.focus();await slider.press('Home');await slider.press('ArrowRight');await slider.press('ArrowRight');await expect(number).toHaveValue('4');await number.fill(original);
  await page.locator('[data-zion-setting-search]').fill('coloane desktop');await expect(page.locator('[data-zion-field=catalog_columns_desktop]')).toBeVisible();await expect(page.locator('[data-zion-field=catalog_layout]')).toBeHidden();
  const guest=await browser.newContext({baseURL});try{const response=await guest.request.get('/wp-json/zion-theme/v1/design');expect(response.status()).toBe(401);const invalid=await admin.request.post('/wp-json/zion-theme/v1/design',{headers:{'X-WP-Nonce':'invalid'},data:{revision:'bad',values:{}}});expect(invalid.status()).toBe(403);}finally{await guest.close();}
});
