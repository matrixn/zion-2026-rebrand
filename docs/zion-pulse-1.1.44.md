# Zion Pulse · 1.1.44

Serviciu nou în română, destinat automatizărilor electronice, sistemelor embedded și experiențelor interactive. Publicat în **17 noiembrie 2026**, la **https://zion3d.ro/zion-pulse/**, pagina WordPress **900122**. Pagina descrie capabilități și exemple, fără portofoliu, recenzii sau instalații comerciale inventate.

## Extensie și editare

Se extinde **Zion Core existent**, fără un plugin nou. `includes/pulse.php` oferă integrarea WordPress și componentele `[zion_pulse type="hero|demo|modules"]`. Prezentarea este în fișiere noi ale temei: șablon HTML nativ, pattern Gutenberg, componente PHP, CSS condițional și JavaScript fără framework. Nu sunt rescrise funcțiile, șabloanele sau stilurile existente ale temei.

Textul, titlurile, cele șase carduri, tehnologiile, procesul și FAQ sunt blocuri native editabile în editorul paginii. Animația SVG și demonstrația sunt componente reutilizabile; nu depind de Elementor, servicii externe sau dispozitive reale. Compoziția de bază se regenerează cu `python scripts/build-pulse-content.py` și este distribuită în `assets/pulse/page.html`.

Identitatea folosește presetările din `theme.json`. Singurele culori semantice suplimentare sunt cele cinci intrări cerute pentru puzzle; nu formează o paletă paralelă a site-ului. Fonturile sunt cele ale site-ului, fără descărcări suplimentare.

Fișierele Pulse folosesc versiunea componentei plus hashul SHA-256 al conținutului, pentru a evita livrarea asseturilor vechi din CDN. Ancorele țin cont de înălțimea headerului, inclusiv notificarea extensibilă, printr-o măsurare `ResizeObserver` limitată la pagina Pulse.

## Interacțiuni

- Cinci butoane etichetate: Verde → Galben → Albastru → Roșu → Alb. O alegere greșită resetează progresul.
- Confirmare LED și textuală, progres și stare într-un panou Game Master. Rezolvarea deblochează cutia virtuală; RESET și FORCE SOLVE funcționează local.
- Module selectabile cu explicații și stări `aria-pressed`. FAQ nativ cu `details` și `summary`.
- Animație de circuit SVG cu pauză, respectarea `prefers-reduced-motion`, oprire în afara ecranului și la ascunderea documentului. Animația de succes rulează o singură dată.
- Fără cereri de rețea pentru comenzi, fără stocare persistentă și fără integrare cu hardware. Fără JavaScript, conținutul și CTA-urile rămân disponibile; controalele demonstrației sunt dezactivate și explicate.

## Integrare și SEO

Publicarea paginii activează automat un **link nativ WordPress** în submeniul **Servicii**, păstrând copiii existenți, și o invitație dedicată la finalul secțiunii de expertiză din directorul **Servicii** (inclusiv aliasul `/printare-3d/`). Pagina și meniurile salvate nu sunt rescrise pentru integrare. Revenirea paginii la ciornă ascunde cele două trimiteri.

Metadatele refolosesc câmpurile deja înțelese de Zion Theme. O casetă meta nativă **Zion Pulse · SEO** oferă titlu, descriere și expresie principală; salvarea verifică nonce-ul și capabilitatea de editare. Expresia este editorială, fără un tag inutil `meta keywords`. Structura are un singur H1 și heading-uri H2–H3.

Se folosește schema [Service](https://schema.org/Service), cu furnizorul și serviciul descris vizibil. FAQ rămâne conținut semantic vizibil; nu se promite un rezultat extins FAQ. Google a retras această funcționalitate în mai 2026 și documentația în iunie, conform [actualizărilor oficiale Search Central](https://developers.google.com/search/updates).

## Validare și livrare

- [x] Build, WPCS, **61 teste PHPUnit / 436 aserțiuni** și integrare WordPress, cu **8 verificări Pulse** pentru compoziție, componente, asseturi, versiune de cache, meniuri și permisiuni.
- [x] **9 teste Playwright**: puzzle, eroare/reset, force solve, module, FAQ, tastatură, mișcare redusă, ancore, lipsa JavaScript și regresii de navigație, căutare, cont și mini-coș.
- [x] Afișare la **390/768/1024/1440 px**, fără overflow; un singur H1, metadate și încărcare condițională verificate. Butoanele colorate au aproximativ **58 × 110 px** pe telefonul de 390 px.
- [x] Backup privat, ciornă creată prin editorul WordPress, metadate salvate prin caseta nativă și previzualizare verificată înaintea publicării. Blocurile au fost deschise și în editorul vizual.
- [x] Publicare și verificări live: rezolvare normală, secvență greșită, reset și force solve la toate lățimile; tastatură, pauză, mișcare redusă, cele șapte FAQ și module verificate. Nu au fost capturate erori JS.
- [x] Navigație desktop și mobil, cu toți cei șapte copii anteriori și noul link Pulse. `aria-current="page"` pe Pulse și `current-menu-ancestor` pe Servicii, fără rescrierea meniului salvat.
- [x] Directorul `/servicii/` și aliasul `/printare-3d/` afișează invitația Pulse. CTA-ul principal ajunge la Contact; WhatsApp are URL-ul și mesajul precompletat cerute, fără trimitere de mesaje.
- [x] GET anonim pe Pulse, Servicii, aliasul Printare 3D, Contact și homepage: **200**. Canonical Pulse corect, un singur H1 și fără `noindex`. Titlul și descrierea sunt cele cerute.
- [x] README actualizat și ZIP-uri Theme/Core **1.1.44** generate, cu CRC verificat. Codul și documentația sunt livrate pe `main` prin commit și push.

## Performanță

Două rulări Lighthouse 13.6.0, pe instanța locală cu date sintetice: performanță **89–92 mobil / 98–100 desktop**, accesibilitate **100**, SEO **100**. Ultima măsurătoare: mobil **LCP 2.399 ms, TBT 171 ms, CLS 0**; desktop **LCP 496 ms, TBT 0 ms, CLS 0**. Acestea sunt măsurători de laborator, nu Core Web Vitals în teren și nici un scor Lighthouse măsurat pe producție.

Comandă: `ZION_LIGHTHOUSE_PULSE=1 npm run test:lighthouse` în shell-ul Linux al mediului wp-env. Raportul este `build/lighthouse/summary-pulse.json`. Testele browser rulează prin `npx playwright test tests/e2e/pulse.spec.mjs tests/e2e/site-shell.spec.mjs --project=storefront --workers=1`; fixture-ul Pulse este pregătit exclusiv în mediul local/development de `scripts/seed-pulse.php`.

## Instalare și rollback

Zion Core și Zion Theme **1.1.44** au fost actualizate din ZIP-uri prin interfața nativă WordPress. Nu a fost instalat un plugin suplimentar. Verificarea finală confirmă **162 de fișiere** identice cu arhivele; inventar SHA-256 `890edd7dd0ec5f0da2fe49ad75c0c6a29751620a4b9cd63ea097199a9aa076ea`. Conținutul celorlalte pagini publicate, Headerul, navigațiile salvate și setările protejate au hash identic înainte și după publicare. Au fost invalidate exclusiv **2 fișiere** din cache-ul Nginx dedicat site-ului la verificarea finală.

Backup privat: `/home/forge/.zion-deploy/pulse-1.1.44-20261117T194349Z/`, cu componentele **1.1.43**, starea inițială și rapoartele de verificare pentru ciornă și publicare. Pentru revenire, trece întâi pagina Pulse în ciornă (cele două trimiteri generate dispar), apoi restaurează exclusiv componentele anterioare prin fluxul nativ WordPress și invalidează cache-ul site-ului. Păstrează pagina și reviziile sale pentru reluarea lucrului; nu restaura comenzile sau baza de date întreagă pentru această extensie.

Arhive: `build/zion-theme-1.1.44.zip` și `build/zion-core-1.1.44.zip`. Capturi și măsurători locale, excluse din Git: `build/pulse-1.1.44/live-desktop.jpg`, `live-mobile.jpg`, `preview-qa.json`, `anchor-qa.json`, `faq-qa.json` și `live-qa.json`.

Nu se efectuează plăți, comenzi, mesaje WhatsApp sau comenzi către instalații în timpul testării. Evaluarea electrică și intervențiile autorizate rămân necesare în proiectele fizice, conform explicației FAQ.
