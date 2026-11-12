import { test, expect } from '@playwright/test';
import { createHash } from 'node:crypto';

test.describe.configure({mode:'serial'});
const fingerprint=async canvas=>createHash('sha256').update(await canvas.evaluate(e=>e.toDataURL())).digest('hex');
const tools='/wp-admin/themes.php?page=zion-theme&section=tools';
let admin,api,headers,original,baseline,settings,category,article,protectedArticle,fixtures=[];
const originalTitle='De la idee la obiect: un jurnal de materiale și experimente Zion';

async function importSettings(config) {
  await admin.goto(tools);
  await admin.getByLabel('Configurație JSON').fill(typeof config==='string'?config:JSON.stringify(config));
  await admin.getByRole('button',{name:'Validează și importă'}).click();
  await expect(admin.getByText('Configurația a fost importată.',{exact:true})).toBeVisible();
}

test.beforeAll(async({browser,baseURL})=>{
  const url=new URL(baseURL);
  if(!['localhost','127.0.0.1','[::1]'].includes(url.hostname)||url.port!=='8917')throw new Error('Editorial fixtures may only use the isolated wp-env tests instance.');
  api=await browser.newContext({baseURL});admin=await api.newPage();
  await admin.goto('/wp-login.php');
  expect((await admin.request.post('/wp-login.php',{maxRedirects:0,form:{log:'admin',pwd:'password',testcookie:'1'}})).status()).toBe(302);
  headers={'X-WP-Nonce':await(await admin.request.get('/wp-admin/admin-ajax.php?action=rest-nonce')).text()};
  await admin.goto(tools);
  const pending=admin.waitForEvent('download');await admin.getByRole('button',{name:'Descarcă JSON'}).click();
  const download=await pending,chunks=[];for await(const chunk of await download.createReadStream())chunks.push(chunk);
  original=Buffer.concat(chunks).toString('utf8');baseline=JSON.parse(original);
  baseline.global={...baseline.global,blog_layout:'grid',blog_image:true,blog_categories:true,blog_date:true,blog_author:true,blog_excerpt:true,blog_related:true,blog_share:true,blog_comments:true,reading_time:true};
  delete baseline.global.blog_per_page;
  await importSettings(baseline);
  settings=await(await admin.request.get('/wp-json/wp/v2/settings',{headers})).json();
  expect((await admin.request.post('/wp-json/wp/v2/settings',{headers,data:{posts_per_page:3}})).ok()).toBe(true);
  const create=async(type,data)=>{
    const response=await admin.request.post(`/wp-json/wp/v2/${type}`,{headers,data});
    expect(response.status(),await response.text()).toBe(201);
    const entry=await response.json();fixtures.push(`/wp-json/wp/v2/${type}/${entry.id}?force=true`);return entry;
  };
  category=await create('categories',{name:'Atelier Zion · demonstrație',slug:'zion-journal-atelier-demo'});
  const media=await(await admin.request.get('/wp-json/wp/v2/media?per_page=100',{headers})).json();
  const image=media.find(item=>item.source_url.includes('zion-demo-3d-01'));expect(image).toBeTruthy();
  const content=[
    '<!-- wp:paragraph --><p>Un experiment editorial sintetic despre alegeri, materiale și prototipuri. Datele sunt demonstrative.</p><!-- /wp:paragraph -->',
    '<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">Contextul experimentului</h1><!-- /wp:heading -->',
    ...Array.from({length:5},(_,i)=>`<!-- wp:heading --><h2 class="wp-block-heading">Etapa ${i+1}: observăm și construim</h2><!-- /wp:heading --><!-- wp:paragraph --><p>${'Un prototip bun pornește de la o întrebare clară, apoi trece prin observație, testare și ajustări. '.repeat(12)}</p><!-- /wp:paragraph -->`),
    '<!-- wp:code --><pre class="wp-block-code"><code>&lt;h1&gt;Exemplu de cod păstrat&lt;/h1&gt;</code></pre><!-- /wp:code -->',
    `<figure class="wp-block-image"><img src="${image.source_url}" alt="Piesă 3D demonstrativă pentru jurnalul Zion"/><figcaption>Imagine sintetică pentru testarea lecturii.</figcaption></figure>`,
    '<p><svg viewBox="0 0 20 20"><path d="M2 10 8 16 18 3"/></svg></p>',
    '<!-- wp:paragraph --><p>FINALUL EXPERIMENTULUI EDITORIAL ZION.</p><!-- /wp:paragraph -->',
  ].join('');
  for(let i=0;i<5;i++) {
    const post=await create('posts',{status:'publish',slug:`zion-journal-fixture-${i}`,title:i===0?originalTitle:`Perspective Zion / experimentul ${i}`,date:`2020-04-0${5-i}T10:00:00`,content:i===0?content:'<!-- wp:paragraph --><p>Notă editorială sintetică, din aceeași colecție de experimente.</p><!-- /wp:paragraph -->',excerpt:i===0?'Dintr-o întrebare simplă se poate naște un obiect util. Un exemplu de articol demonstrativ, cu imagini, cod și etape de lucru.':'O perspectivă demonstrativă din atelierul Zion.',categories:[category.id],featured_media:i===0?image.id:0,comment_status:'open'});
    if(i===0)article=post;
  }
  protectedArticle=await create('posts',{status:'publish',password:'zion-demo-only',slug:'zion-journal-protected',title:'Notă protejată Zion',date:'2020-03-01T10:00:00',content:'SECRET EDITORIAL CARE NU TREBUIE AFIȘAT',categories:[category.id],featured_media:image.id});
  await create('posts',{status:'draft',title:'ZION DRAFT EDITORIAL',content:'PRIVATE DRAFT CONTENT',categories:[category.id]});
  await create('posts',{status:'private',title:'ZION PRIVATE EDITORIAL',content:'PRIVATE POST CONTENT',categories:[category.id]});
});

test.afterAll(async()=>{
  if(!admin)return;
  try {
    for(const url of fixtures.reverse())expect((await admin.request.delete(url,{headers})).ok()).toBe(true);
    if(settings)expect((await admin.request.post('/wp-json/wp/v2/settings',{headers,data:{posts_per_page:settings.posts_per_page}})).ok()).toBe(true);
    if(original)await importSettings(original);
  } finally {await api.close();}
});

for(const width of [390,768,1024,1440]) {
  test(`journal and article native layouts fit at ${width}px @visual`,async({page})=>{
    test.setTimeout(60000);
    const errors=[];page.on('pageerror',e=>errors.push(e.message));await page.setViewportSize({width,height:950});
    await page.goto('/jurnal/');
    await expect(page.locator('.zion-journal')).toHaveClass(/is-spatial-static/);
    await expect(page.locator('main h1')).toHaveCount(1);await expect(page.locator('.zion-post-card')).toHaveCount(3);
    await expect(page.getByRole('navigation',{name:'Categorii din jurnal'}).getByRole('link',{name:'Toate articolele'})).toHaveAttribute('aria-current','page');
    await expect(page.getByRole('searchbox',{name:'Caută în jurnal'})).toBeVisible();
    expect(await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
    if([390,1440].includes(width))await expect(page.locator('.zion-editorial-hero')).toHaveScreenshot(`journal-hero-${width}.png`);
    await page.goto(category.link);await expect(page.locator('main h1')).toHaveText(category.name);
    await expect(page.getByRole('navigation',{name:'Categorii din jurnal'}).getByRole('link',{name:category.name})).toHaveAttribute('aria-current','page');
    if([390,1440].includes(width))await expect(page.locator('.zion-post-card').first()).toHaveScreenshot(`journal-card-${width}.png`);
    await page.goto(article.link);
    await expect(page.locator('.zion-article')).toHaveClass(/is-spatial-static/);
    await expect(page.locator('main h1')).toHaveCount(1);await expect(page.locator('main h1')).toHaveText(originalTitle);
    await expect(page.locator('.zion-article-copy h2').first()).toHaveText('Contextul experimentului');
    await expect(page.locator('.zion-article-copy code')).toHaveText('<h1>Exemplu de cod păstrat</h1>');
    await expect(page.locator('.zion-article-cover img')).toBeVisible();
    await expect(page.locator('.zion-article-related .zion-post-card')).toHaveCount(3);
    await expect(page.locator('.zion-article-copy img')).toHaveJSProperty('complete',true);
    expect(await page.locator('.zion-article-copy svg').evaluate(e=>e.getBoundingClientRect().width)).toBeLessThan(40);
    await expect(page.getByRole('button',{name:'Trimite comentariul',exact:true})).toBeVisible();
    await expect(page.getByRole('textbox',{name:'Comentariu',exact:false})).toHaveAttribute('name','comment');
    expect(await page.locator('.zion-blog-comments form').getAttribute('action')).toContain('/wp-comments-post.php');
    expect(await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
    if([390,1440].includes(width)) {
      await expect(page.locator('.zion-article-hero')).toHaveScreenshot(`journal-article-hero-${width}.png`);
      await expect(page.locator('.zion-article-copy h2').first()).toHaveScreenshot(`journal-article-heading-${width}.png`);
    }
    expect(errors).toEqual([]);
  });
}

for(const width of [390,1440]) {
  test(`journal motion and scroll-driven reading stay quiet at ${width}px`,async({browser,baseURL})=>{
    const context=await browser.newContext({baseURL,viewport:{width,height:850},reducedMotion:'no-preference'});
    try {
      const page=await context.newPage();await page.goto(category.link);
      const art=page.locator('[data-zion-spatial="journal-pages"]');await art.scrollIntoViewIfNeeded();
      const initial=await fingerprint(art);await expect.poll(()=>fingerprint(art)).not.toBe(initial);
      const pause=page.locator('.zion-editorial-motion .zion-spatial-pause');await pause.focus();await pause.press('Enter');
      await expect(pause).toHaveAttribute('aria-pressed','true');const still=await fingerprint(art);await page.waitForTimeout(140);expect(await fingerprint(art)).toBe(still);
      await pause.press('Enter');await art.scrollIntoViewIfNeeded();await expect.poll(()=>fingerprint(art)).not.toBe(still);
      await page.goto(article.link);
      const flow=page.locator('[data-zion-spatial="article-flow"]');await expect(flow).toHaveAttribute('data-render-mode','scroll');
      await flow.scrollIntoViewIfNeeded();const resting=await fingerprint(flow);await page.waitForTimeout(160);expect(await fingerprint(flow)).toBe(resting);
      const copy=page.locator('.zion-article-copy'),geometry=await copy.evaluate(e=>({top:e.getBoundingClientRect().top+scrollY,height:e.offsetHeight}));
      const before=await page.locator('.zion-reading-progress progress').getAttribute('value');
      await page.evaluate(g=>scrollTo(0,g.top+g.height*.55),geometry);
      await expect.poll(()=>page.locator('.zion-reading-progress progress').getAttribute('value')).not.toBe(before);
      await page.locator('.zion-article-copy p').last().scrollIntoViewIfNeeded();await expect(page.locator('.zion-reading-progress progress')).toHaveAttribute('value','100');
      await page.emulateMedia({reducedMotion:'reduce'});await expect(page.locator('.zion-editorial')).toHaveClass(/is-spatial-static/);
      for(const canvas of await page.locator('[data-zion-spatial]').all())await expect(canvas).toHaveAttribute('data-render-mode','static');
      await page.goto('/contact/');await expect(page.locator('[data-zion-spatial="journal-pages"]')).toHaveCount(0);
      await page.goto('/magazin-3d/');await expect(page.locator('#zion-continuum-js')).toHaveCount(0);
    } finally {await context.close();}
  });
}

test('native category, pagination, history and post-only search preserve scope',async({page})=>{
  await page.goto(category.link);const first=await page.locator('.zion-post-card .wp-block-post-title').allTextContents();
  await page.locator('.wp-block-query-pagination-next').click();expect(new URL(page.url()).pathname).toContain('/page/2/');
  const second=await page.locator('.zion-post-card .wp-block-post-title').allTextContents();expect(second.some(title=>first.includes(title))).toBe(false);
  await page.goBack();await expect(page.locator('.zion-post-card .wp-block-post-title').first()).toHaveText(first[0]);
  await page.goForward();await expect(page.locator('.zion-post-card .wp-block-post-title').first()).toHaveText(second[0]);
  await page.getByRole('searchbox',{name:'Caută în jurnal'}).fill('experimente Zion');await page.locator('.zion-journal-search').getByRole('button',{name:'Caută',exact:true}).click();
  expect(new URL(page.url()).searchParams.get('post_type')).toBe('post');
  await expect(page.locator('main.zion-journal')).toBeVisible();await expect(page.getByRole('heading',{name:originalTitle})).toBeVisible();
  await page.goto('/?s=zion-imposibil-inexistent&post_type=post');await expect(page.locator('.wp-block-query-no-results')).toContainText('Nu am găsit rezultate');
  await page.goto(protectedArticle.link);await expect(page.locator('.post-password-form')).toBeVisible();await expect(page.locator('.zion-article-cover,.zion-reading-progress,.zion-article-related,.zion-article-share')).toHaveCount(0);
  await expect(page.locator('main')).not.toContainText('SECRET EDITORIAL');
  await page.goto('/?s=ZION%20PRIVATE%20EDITORIAL&post_type=post');await expect(page.locator('.zion-post-card')).toHaveCount(0);
  await page.goto('/?s=ZION%20DRAFT%20EDITORIAL&post_type=post');await expect(page.locator('.zion-post-card')).toHaveCount(0);
});

test('existing Control Center options affect both native cards and the article',async({page})=>{
  const config=structuredClone(baseline);config.global={...config.global,blog_layout:'list',blog_image:false,blog_categories:false,blog_author:false,blog_date:false,blog_excerpt:false,reading_time:false,blog_comments:false,blog_related:false,blog_share:false};
  try {
    await importSettings(config);await page.goto(category.link);await expect(page.locator('body')).toHaveClass(/zion-blog-list/);
    await expect(page.locator('main .zion-blog-image,main .zion-editorial-placeholder,main .zion-blog-categories,main .zion-blog-author,main .zion-blog-date,main .zion-blog-excerpt,main .zion-reading-time')).toHaveCount(0);
    const row=await page.locator('.zion-post-card-copy').first().evaluate(e=>({copy:e.getBoundingClientRect().width,card:e.parentElement.getBoundingClientRect().width}));
    expect(row.copy).toBeGreaterThan(row.card*.95);
    await page.goto(article.link);await expect(page.locator('main .zion-blog-comments,main .zion-article-related,main .zion-article-share,main .zion-article-cover')).toHaveCount(0);
    await expect(page.locator('.zion-article-copy')).toContainText('FINALUL EXPERIMENTULUI');
  } finally {await importSettings(baseline);}
});

test('journal and article remain native without JavaScript and valid in Site Editor',async({browser,baseURL,page})=>{
  test.setTimeout(90000);const context=await browser.newContext({baseURL,javaScriptEnabled:false,viewport:{width:390,height:950}});
  try {
    const noJs=await context.newPage();await noJs.goto(category.link);await expect(noJs.locator('.zion-editorial-art-fallback')).toBeVisible();await expect(noJs.locator('.zion-post-card')).toHaveCount(3);
    await noJs.locator('.wp-block-query-pagination-next').click();expect(noJs.url()).toContain('/page/2/');
    await noJs.goto(article.link);await expect(noJs.locator('.zion-article-copy')).toContainText('FINALUL EXPERIMENTULUI');await expect(noJs.locator('.zion-reading-progress')).toBeHidden();
    await expect(noJs.getByRole('button',{name:'Trimite comentariul'})).toBeVisible();
    expect(await noJs.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth)).toBe(0);
  } finally {await context.close();}
  await page.goto('/wp-login.php');await page.request.post('/wp-login.php',{maxRedirects:0,form:{log:'admin',pwd:'password',testcookie:'1'}});
  await page.goto('/wp-admin/site-editor.php?p=%2Fwp_template%2Fzion-theme%2F%2Fsingle&canvas=edit');
  await page.waitForFunction(()=>window.wp?.data?.select('core/block-editor')?.getBlocks().length>0);
  const blocks=await page.evaluate(()=>{const walk=bs=>bs.flatMap(b=>[b,...walk(b.innerBlocks||[])]);return walk(wp.data.select('core/block-editor').getBlocks()).filter(b=>b.isValid===false).map(b=>b.name);});
  expect(blocks).toEqual([]);
  const saved=await(await admin.request.get(`/wp-json/wp/v2/posts/${article.id}?context=edit`,{headers})).json();
  expect(saved.content.raw).toContain('<h1 class="wp-block-heading">Contextul experimentului</h1>');
});
