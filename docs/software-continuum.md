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

Cele **10 teste Software** verifică șapte contexte la 390/768/1024/1440 px, **14 referințe vizuale**, animații/pauză/reduced motion, diagrama laterală inactivă între interacțiuni, legăturile native, limbile, cele trei CTA-uri reparate, sortarea AJAX și resetarea filtrelor mobile, lipsa JavaScript, protecția prin parolă și validitatea în Site Editor. Cele **14 aserțiuni noi de integrare** verifică, între altele, identitatea tuturor blocurilor originale și izolarea aliasului legacy.

Proiectul Playwright `software` rulează **după storefront, înainte de editorial și control**. Creează numai fixture-uri sintetice proprii în instanța de teste `localhost:8917`, refuză suprascrierea căilor existente și elimină exact paginile create. Astfel, pagina Software și linkurile sale nu schimbă footerul în comparațiile vizuale ale celorlalte pagini. Nu se importă datele reale de staging în teste sau în Git. Pentru iterații: `npx playwright test --project=software --no-deps --workers=1`.

## Staging și rollback

Înaintea instalării sunt salvate privat SQL-ul, componentele anterioare și **43 de înregistrări editoriale cu metadate, termeni, comentarii, setările Zion, citirea și permalinkurile**. Cele șapte pagini există deja; instalarea componentelor și decorarea la afișare nu necesită rescrierea lor. Comparatorul editorial existent verifică snapshotul strict:

```sh
wp eval-file scripts/journal-audit.php /cale/privata/software-continuum-before-1126.json
```

Cache-ul PHP se invalidează punctual printr-un helper temporar autentificat, restricționat la staging și eliminat după utilizare. Pentru rollback, componentele anterioare se extrag privat, se împachetează și se reinstalează prin WordPress cu `--force`, eliminând și fișierele noi. Snapshotul se verifică din nou; SQL-ul nu se restaurează peste editări independente. Rezultatele finale și CI sunt consemnate în README. Producția nu face parte din această livrare.
