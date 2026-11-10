# Migrarea Zion 3D

Rulează fluxul întâi pe o clonă locală, apoi pe o clonă staging autorizată. Păstrează o clonă de referință cu XStore și Elementor pentru comparație și o clonă separată pentru candidat. Configurează acces privat, indexare dezactivată, email capturat local și ieșire de rețea restricționată înainte de primul bootstrap WordPress. Elimină cheile live și dezactivează webhooks, joburi și integrări externe. Staging folosește HTTPS și `WP_ENVIRONMENT_TYPE=staging`. Local folosește `local`, loopback sau un domeniu `.test`. Instrumentul refuză domeniile de producție, Multisite și URL-urile care diferă de configurația clonei.

## A. Inventar și referință

Pe hostul clonei, în rădăcina WordPress, folosește o copie a repo-ului la commitul de livrare. Exemplul presupune un shell POSIX, un director privat existent în afara rădăcinii web și un administrator de staging.

```sh
umask 077
repo=/srv/repos/zion
out=/srv/private/zion-migration
site=https://staging.example.test/
migration="$repo/scripts/migration.php"
wp eval-file "$migration" audit "$site" > "$out/before.json"
node "$repo/scripts/crawl-migration.mjs" "$out/before.json" "$site" > "$out/urls-before.json"
```

Revizuiește paginile și template-urile, markerii Elementor, widgeturile candidate XStore, shortcode-urile, tipurile și taxonomiile produselor, extensiile comerciale, URL-urile și SEO. Detecția dependențelor este euristică. Verifică vizual header, footer, meniuri, popups, template-uri globale, conținut injectat de extensii și configurația gateway-urilor. Compară sitemapurile, redirecționările și canonicalurile cu referința. Crawlerul citește HTML din aceeași origine, fără redirect automat, și raportează separat răspunsurile inaccesibile. Adaugă URL-urile din sitemap și redirecționările care lipsesc din inventarul editorial în lista de QA.

## B. Backup, manifest și reconstrucție

Înainte de orice modificare pe candidat, capturează baza de date și copia fișierelor, uploadurilor și configurației. Notează tema și extensiile active, permalinks, configurarea cache-ului și versiunile. Verifică restaurarea unei copii a backupului pe o clonă de sacrificiu. Pune staging în fereastră de mentenanță pe durata scrierilor.

```sh
wp db export "$out/pre-zion.sql"
sha256sum "$out/pre-zion.sql"
wp plugin install "$repo/build/zion-core-1.0.0.zip" --force
wp plugin activate zion-core
wp theme install "$repo/build/zion-theme-1.0.0.zip" --force
wp theme activate zion-theme
```

Pregătește loturi și verifică în ordinea de mai jos.

1. Homepage. Confirmă ID-ul existent și template-ul Zion de front page. Folosește `homepage` numai după aprobarea înlocuirii complete a corpului. Verifică CTA-urile și meniul.
2. Servicii. Reconstruiește printarea și proiectarea 3D, software personalizat, SaaS, pluginuri și integrări, aplicații Windows/Linux și integrări AI. Folosește `service` pentru slugurile recunoscute de Zion, sau `native` cu blocuri Gutenberg revizuite pentru alte sluguri. Adaugă conținutul specific și media autorizată.
3. Pagini standard. Folosește `native` cu blocuri de bază. Migrează formularele, media, linkurile și accesibilitatea separat. Paginile native de sistem WooCommerce sunt protejate de script.
4. Blog. Păstrează articolele, autorii, categoriile și permalinks. Configurează `page_for_posts` cu ID-ul existent. Reconstruiește manual articolele legacy inventariate.
5. Vitrine. Folosește paginile existente la `/magazin-3d/` și `/magazin-software/`, sau creează-le cu aceste sluguri în staging și înregistrează ID-urile în manifest. Păstrează pagina Shop nativă. Configurează vitrinele în Zion Control Center.
6. Clasificare. Confirmă termenii 3D și Software cu persoana responsabilă de catalog. Folosește ambele unde este justificat. Verifică produsele neclasificate, vizibilitatea și fiecare combinație de filtre.

Manifestul este un obiect JSON cu `version: 1`, `site` identic cu URL-ul aprobat, `pages`, `shops` și `reading`. Fiecare pagină conține `id`, `content_sha256` din audit și `kind` din `homepage`, `service`, `native`, `3d`, `software`. Pentru `native` include `blocks` cu markup de blocuri, de exemplu `<!-- wp:paragraph --><p>Conținut aprobat.</p><!-- /wp:paragraph -->`. Pentru produse, folosește `{"id":42,"from":["3d"],"to":["3d","software"]}`. Cheile permise în `reading` sunt `show_on_front`, `page_on_front`, `page_for_posts`, fiecare cu `from` și `to`. Folosește ID-uri numerice, iar pentru `show_on_front` folosește `page` sau `posts`.

```sh
wp eval-file "$migration" dry-run "$site" "$out/manifest.json" > "$out/dry-run.json"
wp --user=staging-admin eval-file "$migration" apply "$site" "$out/manifest.json" \
  "$approved_plan_sha256" "$out/pre-zion.sql" "$verified_backup_sha256" "$out/rollback.json"
wp eval-file "$migration" audit "$site" > "$out/after.json"
node "$repo/scripts/compare-migration.mjs" "$out/before.json" "$out/after.json" > "$out/diff.json"
node "$repo/scripts/crawl-migration.mjs" "$out/after.json" "$site" > "$out/urls-after.json"
```

Aprobă hashul planului din dry-run și hashul backupului restaurat în repetiție. Folosește jurnal unic pentru fiecare lot. Jurnalul conține corpul paginilor înainte și după migrare. Tratează backupurile, inventarele și jurnalele ca materiale private. Fluxul păstrează ID-urile, slugurile, SEO și datele Elementor pentru revenire. Verifică manual template-urile, shortcode-urile indirecte și conținutul încă dependent. Orice schimbare inevitabilă de URL cere o redirecționare 301 documentată, testată fără lanțuri.

## C. Acceptanță WooCommerce

- [ ] Catalog 3D și Software, Shop nativ, produse ascunse, sortare, filtre, paginare și separarea vitrinelor.
- [ ] Produse simple, variabile și digitale. Verifică selecția, stocul, prețul și descărcarea fișierului real cu și fără drepturi.
- [ ] Coș, checkout invitat și autentificat, comenzi și istoric în My Account, cu tastatură și pe mobil și desktop.
- [ ] Gateway-uri folosite, în sandbox. Acoperă succes, refuz, revenire, webhook de test, anulare și refund.
- [ ] Livrare după zonă și adresă, taxe și rotunjire, cupoane valide și invalide și totaluri comparate cu referința.
- [ ] HPOS și compatibilitatea extensiilor active. Verifică comenzile în WooCommerce și starea sincronizării înaintea oricărei schimbări HPOS.
- [ ] Dacă există abonamente, verifică un checkout de test, reînnoire automată, reînnoire eșuată, modificarea metodei de plată și anulare. Folosește sandbox și cron izolat.

Vezi documentația pentru [HPOS](https://developer.woocommerce.com/docs/features/orders/high-performance-order-storage/) și [Store API](https://developer.woocommerce.com/docs/apis/store-api/).

## D. Performanță și accesibilitate

Rulează Lighthouse mobil și desktop pe homepage, ambele vitrine, produs, coș și checkout. Testează cache rece și cald. Compară LCP, CLS și TBT de laborator cu referința. Pentru date reale verifică LCP, INP și CLS la percentila 75, cu ținte de 2,5 s, 200 ms și 0,1. Inspectează imaginile responsive, lazy loading sub primul ecran, asseturile condiționale și CSS/JS de livrare. Verifică navigarea la tastatură, focusul, dialogurile, contrastul și mesajele de checkout. Confirmă că cache-ul și CDN-ul exclud coșul, checkoutul, contul, sesiunile WooCommerce și Store API din cache public, și că rezultatele filtrelor țin cont de query string. [Documentația Lighthouse](https://developer.chrome.com/docs/lighthouse/overview/)

## E. Lansare și revenire

- [ ] Salvează raportul de inventar, manifestele aprobate, diff-ul, crawl-urile, rezultatele QA și hashurile ZIP. Marchează diferențele acceptate și atribuie un responsabil fiecărei excepții.
- [ ] Confirmă că toate paginile dependente au fost reconstruite și verificate înainte de dezactivarea Elementor/XStore pe candidat. Repetă crawl-ul și QA după dezactivare.
- [ ] Exersează instalarea acelorași ZIP-uri și restaurarea backupului pe staging. Stabilește decizia go/no-go, fereastra de lansare și cine aprobă. Deploy-ul pe producție se aprobă separat.
- [ ] După deploy verifică rutele principale, canonical, robots și sitemap, logurile PHP/JS, cache-ul, produsele, coșul, contul și fluxul de comandă autorizat.

Pentru revenire pe clonă, oprește scrierile, rulează jurnalele în ordine inversă, reactivează tema și extensiile inițiale și golește cache-urile relevante. Compară auditul și crawl-ul cu referința.

```sh
wp --user=staging-admin eval-file "$migration" rollback "$site" "$out/rollback.json"
```

Dacă apar schimbări externe care împiedică rollbackul de lot sau au fost modificate fișiere și setări în afara jurnalului, folosește backupul verificat pe staging. Pentru o lansare viitoare pe producție, stabilește separat cum păstrezi comenzile și celelalte scrieri ulterioare backupului înainte de o restaurare completă.
