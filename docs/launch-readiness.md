# Pregătire lansare cu Duplicator · 1.1.30

Lucrul se face exclusiv pe `https://zion-stage.on-forge.com`. Producția și ZionFood nu se modifică. Utilizatorul a ales explicit: **staging noindex; indexare după mutarea pe live**. Acest raport pregătește mutarea; nu declară proiectul production-ready fără validarea integrărilor și a destinației reale.

## Audit repetabil

```sh
python3 scripts/audit-launch.py https://zion-stage.on-forge.com build/launch-audit.json --seeds build/launch-seeds.json
python3 scripts/render-launch-report.py build/launch-audit.json build/launch-audit.html
python3 -m unittest discover -s tests -p 'test_*.py'
```

Seedul opțional este un array JSON privat cu URL-urile publice din inventarul WordPress; astfel se verifică și paginile nelegate din meniu. Crawlerul verifică linkuri, imagini, OG și sitemapuri, exclusiv prin GET anonim, fără formulare, cumpărări sau URL-uri de acțiuni. Are patru cereri simultane, timeout, limită explicită și validează fiecare redirect. Verifică destinațiile externe fără a extinde crawlul acolo; ZionFood este exclus. Parametrii de filtrare sunt verificați fără expansiune recursivă, pentru a evita combinațiile infinite.

Raportul include statusuri/redirecturi, toate paginile sursă, title, H1, description, canonical, robots, limbă, imagini share, alt lipsă, ancore inexistente și duplicate. HTML-ul este căutabil și funcționează offline. Rapoartele detaliate, planurile și snapshoturile reale rămân în `build/` sau în directorul privat al serverului, în afara repository-ului.

403/429 extern înseamnă acces restricționat pentru crawler, nu automat link mort. Endpointul Cloudflare `/cdn-cgi/l/email-protection` se interpretează împreună cu linkul decodat în browser. Un sitemap sondat, nelinkuit și dezactivat intenționat pe staging nu este un link editorial defect. Noindex-ul intenționat și canonical-urile comune ale variantelor cu parametri nu sunt automat erori.

## SEO și sharing

Tema livrează metadatele în HTML, fără JavaScript. Fără provider SEO activ, descrierile editoriale salvate anterior au prioritate, apoi excerptul nativ și conținutul curățat. Shortcode-urile nu sunt executate pentru descrieri. Conținutul și imaginile paginilor protejate prin parolă nu sunt expuse în metadate.

Homepage-ul folosește noua copertă `themes/zion-theme/assets/images/social/zion-home-share.png`, **1734 × 907 px**, în locul logo-ului. Celelalte pagini și produsele folosesc featured image nativ, apoi o imagine nativă din conținut, apoi coperta Zion. Imaginea reprezentativă se poate schimba din Zion Studio pentru paginile obișnuite; fallbackul nu creează copii în baza de date.

Yoast, Rank Math, AIOSEO, SEOPress și The SEO Framework, dacă sunt activate ulterior, preiau metadatele; tema nu emite un al doilea set. Imaginea și regulile implicite se configurează atunci în acel provider. Datele comerciale structurate rămân ale WooCommerce; nu se inventează prețuri, recenzii sau informații despre firmă. ReleaseStation RO/EN au alternates reciproce numai când ambele sunt publicate; documentul englez are limba corectă.

Canonical-ul singular rămâne WordPress, cu completări pentru arhive și paginarea Product Collection. `/printare-3d/` este alias canonical spre `/servicii/`. Rutele retrase `/shop/`, `/servicii-printare-3d/`, `/termeni-si-condiitii/` au redirecturi exacte către destinații native publicate, numai când sursa este 404; URL-urile necunoscute rămân 404. Sitemapul este cel nativ WordPress, cu paginile utilitare și aliasul Servicii excluse; fără motor paralel. Cart, checkout, cont, căutare, filtre/sortare și arhivele de tag/autor/dată rămân noindex. Produsele, categoriile și conținutul public păstrează URL-uri proprii.

## Corecții editoriale

Backup privat: SQL, Theme/Core, `wp-config.php`, Nginx și snapshot complet al postărilor/metadatelor/taxonomiilor. Planul JSON conține numai modificările explicite aprobate.

```sh
wp eval-file scripts/launch-editorial.php dry-run /private/launch-before.json /private/launch-plan.json
wp eval-file scripts/launch-editorial.php apply /private/launch-before.json /private/launch-plan.json
wp eval-file scripts/launch-editorial.php verify /private/launch-before.json /private/launch-plan.json
```

Scriptul refuză producția/alte hosturi, fișiere publice, modificări concurente, reaplicarea unui jurnal și câmpuri comerciale arbitrare. Permite numai titlu, conținut, excerpt, publish/draft, alegerea unui șablon nativ valid și trei metadate SEO nominalizate. Verifică toate înregistrările din snapshot, păstrează ordinea listelor și ignoră doar ordinea cheilor asociative și timestampurile native ale înregistrărilor planificate. Jurnalul privat înregistrează încercările, operațiile încheiate și codul erorii. La un eșec se inspectează jurnalul și backupul; nu se reaplică orbește și nu se restaurează peste schimbări concurente.

Preflightul validează și șabloanele moștenite, înainte de prima scriere. WordPress poate valida `page_template` după actualizarea rândului SQL: o editare de titlu poate întoarce eroare pentru un pointer Elementor invalid, deși titlul s-a schimbat deja. Recuperarea păstrează backupul și jurnalul originale, verifică exact câmpurile afectate și creează un checkpoint separat înainte de reluarea planului amendat. Nu sunt acceptate diferențe arbitrare sau schimbări de URL/autor/date comerciale. Testele sintetice verifică refuzul fără scriere și înlocuirea explicită cu un șablon nativ.

La articolele din plan, singura excepție operațională este markerul core `_encloseme=['1']`, pe care WordPress îl creează/consumă pentru scanarea linkurilor media. El nu este eliminat din WordPress și nu modifică rezultatul comparației datelor autorului. Alte valori ale markerului, articolele neplanificate și toate metadatele comerciale rămân comparate strict. `verify` poate finaliza jurnalul unei aplicări în care toate înregistrările sunt deja încheiate, numai după validarea aceluiași hash de plan și a tuturor datelor; nu reaplică modificările.

## WordPress.org și server

Directorul oficial era blocat de `WP_HTTP_BLOCK_EXTERNAL=true`, fără allowlist. Stagingul păstrează blocarea generală, cu excepții pentru `api.wordpress.org`, `downloads.wordpress.org`, `s.w.org`, `wordpress.org`. Nu se autorizează automat API-uri comerciale de producție. Pachetele românești WordPress/WooCommerce/CF7 folosesc sistemul nativ de traduceri.

Regula Nginx pentru robots trebuie să ajungă la WordPress:

```nginx
location = /robots.txt {
    try_files $uri /index.php?$query_string;
    access_log off;
    log_not_found off;
}
```

Nu se introduce un robots.txt static cu domeniul staging. Configurația webserverului nu este transportată automat de Duplicator; ruta și statusul se verifică din nou la destinație. Sitemapul WordPress rămâne dezactivat cât timp `blog_public=0`.

## Checklist pentru mutare și rollback

- [ ] Backup separat al producției, fișiere + SQL, arhivă verificată și restaurare exersată pe mediu izolat.
- [ ] Fereastră de mutare și reconcilierea comenzilor/clienților/modificărilor apărute pe live după clonare. **Nu suprascrie comenzile recente cu baza veche de staging.**
- [ ] Pachet Duplicator după QA; exclude backupuri private, cache, loguri și rapoarte reale. Verifică scanarea/arhiva și păstrează pachetul în spațiu privat. Elimină installerul și arhiva publică după instalare.
- [ ] Installer Duplicator cu înlocuire corectă a domeniului și datelor serializate; verifică home/siteurl, HTTPS și redirectul variantei www/non-www.
- [ ] `WP_ENVIRONMENT_TYPE=production` pe destinație; inspectează debug, noindex și blocarea HTTP externă transferate. Autorizează exact integrările reale necesare, după teste sandbox.
- [ ] Configurează și testează transportul SMTP/e-mail. Utilizatorul a amânat explicit această configurare; validarea formularului nu confirmă livrarea.
- [ ] Sandbox real pentru gateway-uri, produse simple/variabile/digitale, descărcări, cart, checkout, taxe, livrare, cupoane, cont, comenzi HPOS și abonamente dacă există. Testele SEO nu validează plățile.
- [ ] Confirmarea datelor firmei și a condițiilor comerciale/legale de către proprietar; corecturile tehnice nu certifică adecvarea juridică.
- [ ] După validarea mutării: permite indexarea în **Setări → Citire**, verifică antetele serverului/proxy-ului și HTML-ul anonim. Stagingul rămâne noindex.
- [ ] robots.txt 200 text/plain, `/wp-sitemap.xml` 200 XML, canonical/hreflang/JSON-LD și imagini OG cu domeniul live; zero referințe staging/localhost.
- [ ] Permalinks/cache regenerate, excluderi WooCommerce pentru cart/checkout/cont, teste anonime/private, mobil/desktop, loguri PHP/JS și performanță fără cache de administrator.
- [ ] Sitemap în Search Console și verificarea URL-urilor importante; refresh al cache-ului social pentru linkurile distribuite anterior.
- [ ] Verificări post-deploy și responsabil pentru rollback. Restaurarea trebuie să protejeze comenzile apărute după lansare; nu se restaurează orbește un SQL mai vechi.

## Imagine generată

Instrument: image_gen integrat; asset nou. Promptul folosit:

> Use case: ads-marketing. Create the final social share cover image for the homepage of Zion 3D Print, a Romanian digital fabrication and software studio. A sophisticated premium cinematic wide 1200 by 630 composition (1.91:1 aspect ratio), near-black and anthracite background, restrained electric lime #D9FB4F highlights, precise CAD construction lines. Main subject: a beautifully detailed sculptural mechanical component transitioning from glowing lime wireframe geometry into a matte black layered 3D printed physical object, with subtle digital code particles and circuit paths extending toward the opposite side, representing design, additive manufacturing, software and AI as one continuous workflow. Dramatic but controlled studio lighting, confident negative space, exceptionally refined modern industrial art direction, crisp tangible textures, strong clarity even at small thumbnail size. Include the exact clear large text 'ZION 3D' and smaller Romanian text 'De la idee la realitate.' in clean contemporary white typography, safe centered margins. This is an evocative editorial cover, not a logo, no existing site logo or emblem, no webpage UI, no watermark, no extra tiny unreadable text. Deliver a finished beautiful raster image suitable for homepage Open Graph sharing.
