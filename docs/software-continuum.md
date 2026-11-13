# Software / Zion Continuum 1.1.26

Un singur șablon nativ `software`, editabil în Site Editor, deservește cele șapte destinații locale din meniul Software:

| Pagină | Context vizual |
| --- | --- |
| `/pluginuri-woocommerce/` | Ecosistem modular conectat |
| `/magazin-software/` | Licențe, instrumente și acces |
| `/zion-sms-confirmation-woocommerce/` | Comandă → SMS → răspuns → decizie |
| `/pluginuri-woocommerce/zion-privacy/` | Scanare și semnale de confidențialitate |
| `/zion-multi-currency-for-woocommerce/` | Rute între monede și context comercial |
| `/zion-releasestation-git-deployment-automatizat-pentru-synology-dsm/` | Git → build → verificare → release |
| `/zion-releasestation-automated-git-deployment-for-synology-dsm/` | Același concept ReleaseStation, în engleză |

Linkul separat ZionFood și aplicațiile externe rămân destinații de navigare. Fișele produselor WooCommerce păstrează motorul și presetările existente; nu se duplică paginile comerciale sau datele produselor.

## Editare și arhitectură

Întregul conținut se editează în **Pagini**, cu blocurile native existente. Layoutul comun se editează în **Aspect → Editor → Șabloane → Zion · Software / Continuum**. Modificarea acestui șablon se aplică paginilor care îl folosesc. Headerul și footerul rămân Template Parts native. Pentru alte pagini, autorul poate selecta explicit șablonul `software`; un alt șablon nativ ales explicit are prioritate.

`includes/software.php` definește un registru de contexte de prezentare și rezolvă exact căile WordPress, inclusiv pagina copil Privacy. Pointerul vechi `elementor_theme` este recunoscut numai pentru aceste căi și rezolvat către șablonul nativ, fără rescrierea metadatelor și fără o dependență Elementor. Nu există potrivire după fragmente de URL sau capturarea altor pagini.

Filtrul frontend `the_content`, înainte de randarea blocurilor, organizează H2 în secțiuni și H3 în carduri. Sunt păstrate ordinea și fiecare bloc original: texte, linkuri, liste și cod. Se desface numai wrapperul nativ cunoscut `zion-editorial-panel`; grupurile create ulterior de autor rămân ale sale. O singură funcție produce corpul și ancorele cuprinsului. Rezultatul este memorat numai în cererea curentă, fără opțiuni sau tabele suplimentare. În REST/editor conținutul rămâne în forma salvată.

Pentru planurile migrate care repetă titlul și descrierea într-un link aflat imediat înainte de H3, acel link intră în cardul propriului plan. Ordinea blocurilor originale rămâne identică. Numai la afișare, rezumatul duplicat devine „Vezi produsul”, cu un nume accesibil care include planul; CSS poziționează acțiunea după titlu și descriere. Nu este asociată acțiunea planului următor cu planul curent.

Pagina principală transformă legăturile locale existente în carduri cu descrieri de prezentare. Catalogul păstrează exact shortcode-ul și rendererul Zion Core/WooCommerce: clasificarea `zion_shop`, filtrele, sortarea, paginarea, spinnerul și Add to Cart. Catalogul are un hero animat și lățime completă, fără o a doua bară laterală sau un al doilea motor de filtrare.

Pe pagina Multi-Currency au fost identificate trei CTA-uri cu `href="void(0);"`. La randarea blocului de conținut sunt înlocuite cu **Solicită detalii**, către Contact, cu contextul `software=multi-currency`. Pagina și metadatele originale sunt păstrate. Acest flux oferă un contact funcțional și nu introduce un produs, un preț sau o licență fictivă.

## Animații și accesibilitate

Scenele folosesc același `continuum.js`, același scheduler și culorile din `theme.json`. Hero-ul are un concept distinct pentru fiecare familie de produse. Diagrama laterală reacționează la secțiune și cuprins și este exclusă din bucla continuă de animație. Canvas-urile sunt decorative; informația utilă rămâne în text și legături native.

Există pauză comună, reduced motion inclusiv la schimbarea live a preferinței, oprire în afara viewportului și fallback fără JavaScript/Canvas. Cuprinsul este un element nativ `details`, deschis progresiv pe desktop și compact pe mobil. Legăturile funcționează fără JS. Exemplele de cod au derulare locală și focus pentru tastatură. Versiunea engleză păstrează conținutul în engleză, `lang=en` pe componentă și controalele animației traduse.

Coperta opțională rămâne în paginile individuale, fără a înlocui animația hero. Coperta nepotrivită a hubului și cea a catalogului nu sunt afișate; atașamentele și metadatele lor nu sunt șterse. CSS-ul este limitat la clasele Software și păstrează componentele WooCommerce existente.

## Teste și mediu

```sh
npm run build
composer lint
composer test
npm run test:integration
npm run test:migration
bash scripts/ci-browser.sh start
CI=1 ZION_PLAYWRIGHT_WS_ENDPOINT=ws://127.0.0.1:9347 npx playwright test --workers=2
npm run package
bash scripts/ci-browser.sh stop
```

Cele **10 teste Software** verifică șapte contexte la 390/768/1024/1440 px, **14 referințe vizuale**, animații/pauză/reduced motion, diagrama laterală inactivă între interacțiuni, legăturile native, limbile, cele trei CTA-uri reparate, planurile cu link-rezumat duplicat, ordinea efectivă a prețurilor după sortarea AJAX și resetarea filtrelor mobile, lipsa JavaScript, protecția prin parolă și validitatea în Site Editor. Cele **16 aserțiuni noi de integrare** verifică, între altele, identitatea tuturor blocurilor originale și izolarea aliasului legacy.

Proiectul Playwright `software` rulează **după storefront, înainte de editorial și control**. Creează numai fixture-uri sintetice proprii în instanța de teste `localhost:8917`, refuză suprascrierea căilor existente și elimină exact paginile create. Astfel, pagina Software și linkurile sale nu schimbă footerul în comparațiile vizuale ale celorlalte pagini. Nu se importă datele reale de staging în teste sau în Git. Pentru iterații: `npx playwright test --project=software --no-deps --workers=1`.

## Staging și rollback

Înaintea instalării sunt salvate privat SQL-ul, componentele anterioare și **43 de înregistrări editoriale cu metadate, termeni, comentarii, setările Zion, citirea și permalinkurile**. Cele șapte pagini există deja; instalarea componentelor și decorarea la afișare nu necesită rescrierea lor. Comparatorul editorial existent verifică snapshotul strict:

```sh
wp eval-file scripts/journal-audit.php /cale/privata/software-continuum-before-1126.json
```

Cache-ul PHP se invalidează punctual printr-un helper temporar autentificat, restricționat la staging și eliminat după utilizare. Pentru rollback, componentele anterioare se extrag privat, se împachetează și se reinstalează prin WordPress cu `--force`, eliminând și fișierele noi. Snapshotul se verifică din nou; SQL-ul nu se restaurează peste editări independente. Rezultatele finale și CI sunt consemnate în README. Producția nu face parte din această livrare.

## QA pe clona reală

Cele șapte pagini au fost verificate la **390/768/1024/1440 px**: un H1, zero overflow, fără erori PHP vizibile. Hubul are șase carduri; paginile SMS/Privacy/Multi-Currency/ReleaseStation au **9/10/7/13 secțiuni**, respectiv **25/17/17/13 carduri**. ReleaseStation RO/EN păstrează coperta și exemplul de cod; cuprinsul folosește ancore native și actualizează diagrama. Pe mobil este inițial închis și funcționează cu Enter. Pauza și reduced motion au fost verificate pe staging; testele locale verifică inclusiv schimbarea efectivă a cadrelor.

Clona avea tabela nativă WooCommerce `wc_product_meta_lookup` goală. După backup privat, `wc_update_product_lookup_tables()` din WP-CLI a refăcut **82 de rânduri derivate**. Comparatorul înainte/după confirmă identitatea tuturor înregistrărilor `product`/`product_variation` și a metadatelor lor: nu au fost modificate prețurile sau stocurile sursă. S-au invalidat numai cache-urile derivate. Sortarea reală a celor opt produse Software este acum crescătoare/descrescătoare, inclusiv după navigarea AJAX și Back. Backupul SQL și copia privată a indexului anterior sunt păstrate în `.zion-migration` de pe staging.

Homepage/About/Servicii/Contact/blog/FAQ/Magazin 3D păstrează **6/2/10/3/1/2/0 scene**, fără clase Software sau overflow la 390/1440 px. Auditul strict confirmă cele **43 de înregistrări editoriale**, metadatele, termenii, comentariile și setările neschimbate. Șablonul Software provine din temă și nu are copie salvată în Site Editor. Producția nu a fost modificată.

Tema finală instalată provine din `3add04a`; Theme/Core sunt **1.1.26**. Cele **28/28 verificări** ale paginilor au fost repetate după ultima instalare. Cele patru planuri SMS au acțiuni distincte și corect asociate la fiecare rezoluție. Cele **opt fișe WooCommerce Software** au trecut **16/16 verificări** la 390/1440 px, cu un H1, formular nativ de cumpărare și zero overflow; nu s-au plasat comenzi. Starea fără rezultate, resetarea și istoricul sortării au fost verificate după refacerea indexului.

SHA-256 final CSS: `2505d485bb3ed207679b255617a190312fd70e1a44f9e91c8471e74ffe5c0b9e`; renderer Continuum: `d632df796b7e60509a5b010f3d5e473918ef587b09d2b0ddc3941ae60c8c68df`. Hashurile instalate corespund sursei locale. Ambele helperuri de cache s-au eliminat după folosire, iar absența a fost verificată din CLI. Arhivele instalabile conțin **128 fișiere Theme / 9 Core**, fiecare intrare identică sursei și cu antete **1.1.26**.

Dovezi locale excluse din Git: `build/software-staging-qa-1.1.26.json`, `build/software-product-links-qa-1.1.26.json`, `build/software-package-verification-1126.json` și capturile `software-staging-desktop-1.1.26.png`, `software-staging-mobile-1.1.26.png`, `software-sms-plans-mobile-1.1.26.png`, `software-hub-cards-desktop-1.1.26.png`.

## Rezultatul final CI

[Rularea 43354915141](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43354915141), pentru `3add04a7b369358531b0eeb9c39fbc73d9a47c93`, este verde: **119/119 teste Playwright în 12,1 minute**, **46 teste PHPUnit / 394 aserțiuni**, build reproductibil, WPCS, integrare și migrare/rollback trecute. Verificarea locală finală a trecut **10/10 Software în 1,7 minute**, scenariul Control Center în **51,2 s** și cele **4/4 verificări de încărcare condițională**. Snapshoturile nu au fost actualizate în aceste rulări finale. Browserul Docker folosit pentru teste a fost oprit după verificări; instanța wp-env rămâne disponibilă.

ZIP-uri finale: `build/zion-theme-1.1.26.zip` și `build/zion-core-1.1.26.zip`. Raportul de verificare conține hashurile arhivelor și confirmă toate intrările față de sursa locală. SQL-ul și datele reale rămân exclusiv în backupurile private de pe staging.
