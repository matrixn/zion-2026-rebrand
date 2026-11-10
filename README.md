# Zion Theme

Temă WordPress bazată pe blocuri, cu identitatea Zion 3D și două vitrine care folosesc același WooCommerce. Obiectivul este înlocuirea XStore și Elementor prin Site Editor, componente native și un panou de configurare dedicat.

## Implementat

- [x] **Zion Theme 1.1.1** cu presetări native în `theme.json` v3, homepage negru-lime, navigație responsivă și header și footer editabile.
- [x] **Zion Control Center** în **Aspect → Zion Theme**, cu setări globale, pe vitrină și pe produs sau pagină, plus resetare și import/export JSON.
- [x] **Șabloane editoriale** pentru pagini, articole, jurnal, arhive, căutare și 404, cu carduri, paginare și stări fără rezultate.
- [x] **Șabloane WooCommerce** pentru catalog, căutare de produse și pagina individuală. Arhivele de categorii, etichete și branduri folosesc layoutul catalogului, iar coșul și checkoutul folosesc șabloanele WooCommerce.
- [x] **Zion Core 1.1.1** cu taxonomia `zion_shop`, clasificare individuală și în masă, câmpuri editoriale și interogări comune pentru colecții.
- [x] **Vitrine configurabile separat** cu grilă sau listă, coloane responsive, Product Collection și Product Filters WooCommerce, sortare, paginare și opțiunea „Încarcă mai multe”.
- [x] **Carduri de produs** cu un renderer comun și cinci preseturi responsive. Imaginea, badge-urile, ordinea, informațiile și acțiunile se configurează global sau pe vitrină.
- [x] **Pagini de produs** cu preseturi Physical 3D, Software, Minimal și Premium, secțiuni reordonabile și suprascrieri per produs. Formularul, variațiile și stocul folosesc WooCommerce.
- [x] **Navigație și șabloane generale** cu logo și CTA în Site Editor, căutare de produse, cont și mini-coș nativ, footer Zion Nexus, jurnal configurabil și sidebar editabil.
- [x] **Homepage și componente Zion** cu 11 pattern-uri, 7 pagini de servicii, 9 blocuri dinamice și shortcode-uri echivalente. Asseturile bibliotecii se încarcă la randare, iar produsele folosesc schema WooCommerce.
- [x] **Catalog demonstrativ** cu două vitrine, patru produse fictive ilustrate și fluxul WooCommerce nativ pentru produs, coș și checkout.
- [x] **Mediu local și de test** cu WordPress, WooCommerce, Docker, wp-env, WP-CLI și date demo încărcate printr-o comandă repetabilă.
- [x] **Calitate și CI** cu build CSS, WPCS, PHPUnit, verificări de integrare, teste Playwright și workflow GitHub Actions.
- [x] **Distribuție** cu arhive ZIP versionate, instalabile separat pentru temă și plugin.

## Roadmap

Ordinea urmează fazele proiectului. Marchează un punct drept finalizat după ce funcționalitatea este verificată.

- [x] **0.2 · Tema nativă.** Șabloane pentru căutare, arhive și WooCommerce, presetări în editor, navigație la tastatură și verificări la 390, 768, 1024 și 1440 px.
- [x] **0.3 · Zion Control Center.** Registru unic, panou nativ WordPress, validare, resetare, import/export JSON și teste pentru precedență, permisiuni și migrarea schemei.
- [x] **0.4 · Administrarea vitrinelor.** Clasificare individuală și în masă, filtrare după categorie și stare, previzualizare, dry-run, jurnal și layout separat pe vitrină.
- [x] **0.5 · Catalog și filtre.** Layout și filtre pe vitrină, sortare și paginare nativă, teste pentru combinații și benchmark local cu 1.000 de produse demo.
- [x] **0.6 · Carduri de produs.** Cinci preseturi, controale de afișare și acțiuni WooCommerce verificate pentru produse simple, virtuale, descărcabile, variabile, grupate și externe.
- [x] **0.7 · Pagina de produs.** Patru preseturi, configurare globală, pe vitrină și pe produs, date editoriale în Zion Core și cumpărare nativă verificată pe mobil și desktop.
- [x] **0.8 · Navigație, footer, blog și pagini.** Header configurabil, căutare și mini-coș WooCommerce, footer Zion Nexus și opțiuni editoriale cu suprascrieri per articol sau pagină.
- [x] **0.9 · Homepage și bibliotecă de componente.** Secțiuni Gutenberg, pagini de servicii, colecții WooCommerce și verificări de performanță, accesibilitate și SEO.
  - [x] Creează paginile pentru printare și proiectare 3D, software personalizat, SaaS, pluginuri și integrări, aplicații Windows/Linux și integrări AI.
  - [x] Creează blocuri dinamice și shortcode-uri pentru grile, recomandări, produse noi, reduceri, produse asociate, servicii, CTA și recenzii.
- [x] **1.0 · Pregătirea migrării.** Audit JSON, plan validat, dry-run, backup verificat prin SHA-256, jurnal și rollback pe date demo.
  - [x] Checkout COD cu livrare, taxe și cupon. Verificare HPOS, permisiuni de descărcare și accesibilitatea mini-coșului și a filtrelor.
  - [x] Raport automat de diferențe, crawler URL/SEO, plan de rollback, checklist și arhivele 1.0.0.
  - [ ] Rulează inventarul pe o clonă reală zion3d.ro, reconstruiește paginile dependente și validează URL-urile pe staging.
  - [ ] Validează gateway-urile în sandbox, descărcarea fișierelor reale, abonamentele unde sunt folosite, cache-ul și Core Web Vitals în teren.
  - [ ] Exersează restaurarea completă pe staging și aprobă checklistul de lansare.

## Magazin 1.1.1

- [x] Încărcare automată la derulare în cele două vitrine. În **Aspect → Zion Theme → WooCommerce** poți alege derularea automată sau doar paginarea numerică, global, per vitrină sau per pagină. Extinderea Ajax afișează maximum 96 de produse în lista curentă. Butoanele de paginare rămân disponibile cât timp există alte pagini. Înapoi și Înainte păstrează selecția și lotul de produse din istoricul browserului.
- [x] Sortarea pornește la selectare, păstrează filtrele și confirmă actualizarea printr-un mesaj scurt. În vitrine, formularul și linkurile funcționează și cu JavaScript dezactivat.
- [x] Pagina produsului adaugă în coș prin Ajax, inclusiv pentru variații. WooCommerce gestionează selecția, prețul, stocul și răspunsurile formularului. Acțiunea „Cumpără acum” continuă către checkout.
- [x] Etichetele uzuale WooCommerce au o traducere implicită în română, inclusiv pentru mini-coș, coș și formularul de comandă. Pentru catalogul complet al mesajelor și al țărilor, setează **Limba site-ului → Română** și instalează pachetele WordPress și WooCommerce. Textele extensiilor și ale metodelor de plată configurate se traduc în setările lor.

- [x] Indicator de încărcare pentru sortare, derulare, navigația AJAX a catalogului și solicitările WooCommerce din coș și produs. Indicatorul folosește directivele native WordPress și respectă preferința pentru mișcare redusă.
- [x] Carduri cu descrieri scurte, categorie și disponibilitate în presetul Classic. Galeria nativă are o imagine mare și o bandă de miniaturi sub ea.
- [x] Navigația implicită urmează structura Zion 3D, cu submeniuri pentru servicii și software. Linkurile interne folosesc paginile publicate pe instalarea curentă. Meniul rămâne editabil în Site Editor.
- [x] Comandă de reconstrucție pentru paginile de pe staging: după un export al bazei de date, rulează `wp eval-file scripts/staging-editorial.php` pentru previzualizare și adaugă `apply` doar pe clonă. Comanda păstrează URL-urile și folosește blocuri native; nu transferă CSS, JavaScript, formulare sau widgeturi Elementor.
- [x] Pe staging au fost reconstruite 20 de pagini, inclusiv cele șapte servicii, paginile de prezentare software, FAQ și cele două vitrine. Pagina Contact oferă e-mail și telefon. Verificarea checkoutului se oprește înainte de trimiterea unei comenzi.
- [x] Testele din browser verifică spinnerul pentru filtre și adăugarea în coș după încărcarea scripturilor WooCommerce, plus istoricul și derularea cu un catalog de 1.000 de produse demo.

## Migrare XStore și Elementor

[Procedura de migrare și checklistul de lansare](docs/migration.md) acoperă auditul, reconstrucția, aprobarea planului, backupul, acceptanța WooCommerce și revenirea. [Raportul 1.0.0](docs/release-1.0.md) consemnează validarea locală și verificările rămase pe staging. Producția rămâne în afara acestei faze.

```sh
npm run demo:test
npm run test:migration
npm run test:lighthouse
```

Repetiția locală scrie auditul, dry-run-ul și rezumatul în log. Pentru rapoarte separate, capturează logul și rulează `node scripts/collect-migration-reports.mjs <log>`. Rapoartele Lighthouse se află în `build/lighthouse/`.

## Instalare din ZIP

Din rădăcina repo-ului rulează comenzile de mai jos. Împachetarea folosește Node, npm și PHP CLI cu extensia `zip`.

```sh
npm ci
npm run package
```

Comanda compilează CSS-ul și creează în `build/` două arhive independente. Versiunea fiecărei arhive vine din antetul componentei, iar o nouă rulare reface arhivele pentru versiunile respective.

| Arhivă | Instalare în WordPress |
| --- | --- |
| `zion-theme-1.1.1.zip` | **Aspect → Teme → Adaugă o temă → Încarcă tema**, apoi activează Zion Theme. |
| `zion-core-1.1.1.zip` | **Module → Adaugă un modul → Încarcă modulul**, apoi activează Zion Core. Activează WooCommerce pentru vitrine, pagini de produs și colecții. |

Arhivele includ fișierele necesare rulării. Datele demonstrative, testele, sursele CSS și dependențele de dezvoltare se află în repo. Directorul `build/` este ignorat de Git.

## Pornire locală

Ai nevoie de Docker cu Compose, Node.js 24.18+ și npm 11.16+ pentru dependențele wp-env. Pentru verificările PHP instalează PHP 8.3+ și Composer 2. Pe Windows, rulează comenzile din WSL cu Docker și Node instalate în WSL, iar pentru testele vizuale folosește Ubuntu 22.04.

```sh
npm ci
composer install
npm run build
npm run env:start
npm run demo
```

Deschide [site-ul local](http://localhost:8916/) sau [Site Editor](http://localhost:8916/wp-admin/site-editor.php) cu contul local implicit wp-env (`admin` / `password`). Catalogul demonstrativ se află la [Magazin 3D](http://localhost:8916/magazin-3d/) și [Magazin Software](http://localhost:8916/magazin-software/). Mediul de teste are o bază separată la portul 8917. `npm run env:stop` oprește cele două instanțe.

Seed-ul include și [Jurnalul](http://localhost:8916/jurnal/), [catalogul WooCommerce](http://localhost:8916/shop/) și un [produs demonstrativ](http://localhost:8916/product/zion-catalog-tools/). Pentru un site fără seed, alege pagina de articole din **Setări → Citire** ca să apară linkul Jurnal în navigația implicită. Footerul afișează linkul de confidențialitate după ce alegi și publici pagina respectivă în WordPress.

## Zion Control Center

Deschide **Aspect → Zion Theme** cu un cont care are capabilitatea `edit_theme_options`. Pentru o pagină sau un produs, folosește acțiunea **Setări Zion** din lista de conținut sau introdu ID-ul în **Instrumente**. Valoarea explicită de pe element are prioritate, apoi vitrina, valoarea globală și valoarea implicită. Alege **Moștenește** pentru a elimina valoarea explicită.

Panoul controlează prezentarea și comportamentele Zion, precum densitatea și acțiunile cardurilor, metadatele și produsele asociate, timpul de citire sau reducerea mișcării. Site Editor gestionează culorile, fonturile, meniurile și structura șabloanelor. WooCommerce gestionează datele comerciale.

În **Instrumente**, descarcă exportul JSON înainte de modificări ample. Importul validează configurația integral înainte de a o înlocui. Override-urile folosesc ID-uri locale, deci importă-le în aceeași instalare. Poți reseta o secțiune din propriul formular sau confirma resetarea tuturor setărilor din **Instrumente**. Vezi [arhitectura](docs/architecture.md) pentru schema și regulile de moștenire.

## Clasificarea produselor

În editorul unui produs, bifează **Magazin 3D**, **Magazin Software** sau ambele în câmpul **Vitrine Zion**. Pentru inventar, folosește coloana și filtrul de clasificare din lista WooCommerce. Pentru operații în masă, deschide **Produse → Vitrine Zion**. Acolo poți crea și paginile lipsă, păstrând pagina Shop configurată în WooCommerce.

[Instrucțiuni pentru produse noi, clasificare în masă și configurarea vitrinelor](docs/storefronts.md)

## Cardurile produselor

Alege **Aspect → Zion Theme → Carduri produse** pentru valorile globale. **Magazin 3D** și **Magazin Software** pot moșteni sau suprascrie fiecare control. Preseturile sunt **Minimal**, **Classic**, **Premium**, **Compact** și **Horizontal**. Pentru ordinea informațiilor și acțiunilor, introdu cheile separate prin virgulă. Elementele omise rămân disponibile în ordinea lor implicită, după cele enumerate. Imaginea are propriile controale de încadrare, iar badge-urile pot fi mutate între colțuri.

Blocul **Card produs Zion** folosește aceeași prezentare în vitrine, arhivele catalogului, căutarea WooCommerce, homepage și produse asociate. Șabloanele deja personalizate în Site Editor își păstrează blocurile salvate și pot folosi cardul Zion în interiorul unui **Product Template**. Quick View afișează un rezumat și deschide pagina produsului prin „Vezi detalii”. Configurarea variațiilor și cumpărarea folosesc WooCommerce.

Wishlist și Compare afișează controalele modulelor YITH compatibile când shortcode-ul lor este înregistrat. Alege modul de afișare prin shortcode în setările extensiei pentru ca Zion să controleze poziția și vizibilitatea pe card. Pentru alți furnizori, tema expune filtrele `zion_theme_card_wishlist_html` și `zion_theme_card_compare_html`, care primesc HTML-ul furnizorului implicit și produsul WooCommerce. [Wishlist](https://github.com/yithemes/yith-woocommerce-wishlist/blob/master/includes/class-yith-wcwl-shortcode.php) · [Compare](https://github.com/wp-plugins/yith-woocommerce-compare/blob/master/includes/class.yith-woocompare-frontend.php)

## Paginile de produs

Configurează presetul, stilul, ordinea și vizibilitatea din **Aspect → Zion Theme → Pagini individuale produse**. Setările din **Magazin 3D** sau **Magazin Software** pot suprascrie nivelul global. Pentru un produs anume, deschide **Setări Zion** din lista de produse sau selectează produsul în **Instrumente**.

Completează conținutul editorial în **Produse → Editează → Informații produs Zion**. Folosește atributele WooCommerce pentru specificații și datele native pentru preț, variații, stoc, descărcări, upsells și cross-sells. [Ghidul paginilor de produs](docs/product-pages.md)

## Navigație, footer și șabloane editoriale

În **Aspect → Zion Theme**, secțiunile **Header și navigație**, **Footer**, **Blog și articole** și **Pagini standard** conțin setările de afișare și linkuri directe spre șabloanele și părțile editabile. [Ghidul de configurare](docs/site-navigation.md)

## Homepage și biblioteca Zion

Secțiunile homepage-ului și pattern-urile paginilor de servicii se găsesc în categoria **Zion · Componente** din Gutenberg. În **Aspect → Zion Theme → Instrumente** poți publica paginile de servicii lipsă. Datele locale pentru `npm run demo:test` includ toate blocurile în **Biblioteca Zion · demonstrații**. [Blocuri, shortcode-uri și configurare](docs/component-library.md)

## Test de catalog cu 1.000 de produse

Comenzile folosesc instanța de teste wp-env de la portul 8917. Seed-ul standard rămâne potrivit pentru testele vizuale.

```sh
npm run demo:test
npm run demo:scale
npm run test:catalog-scale
ZION_CATALOG_SCALE=1 npm run test:e2e -- --project=catalog-scale
npm run demo:scale:clean
```

Benchmarkul verifică numărul de rezultate, limita de carduri în răspuns, izolarea vitrinelor și repetabilitatea cererilor. [Metodă și rezultate](docs/catalog-performance.md)

## Verificări

```sh
npm run build
composer lint
composer test
npm run demo:test
npm run test:integration
npx playwright install --with-deps chromium
npm run test:e2e
```

Folosește `npm run test:visual:update` pentru actualizarea intenționată a capturilor, apoi verifică diferențele înainte de commit. GitHub Actions rulează verificările pe push și pull request.

Matricea Playwright verifică 12 rute publice la 390, 768, 1024 și 1440 px. Suita mai verifică șabloanele în Site Editor, meniul mobil la tastatură, filtrarea nativă în catalog și traseul produs → coș → checkout.

Testele Control Center rulează după cele ale vitrinei, în baza de date izolată wp-env. Verifică salvarea, moștenirea, efectele publice, importul/exportul, resetarea și accesul unui abonat. Pentru a rula doar aceste teste, folosește `npm run test:e2e -- --project=control --no-deps`.

Suita de clasificare verifică tipurile WooCommerce, relațiile taxonomiei și fluxul de administrare. Testul de browser rulează după cele ale Control Center și lasă o intrare de audit în baza locală de teste.

## Flux de livrare

1. Actualizează README-ul cu schimbarea și starea checklistului.
2. Rulează verificările relevante și `npm run package`. La o lansare, actualizează mai întâi versiunea în antetul fiecărei componente lansate.
3. Creează commitul și publică-l pe `main`.
4. Comunică linkul commitului și căile către cele două arhive versionate din `build/`.

Vezi [arhitectura și convențiile](docs/architecture.md) și [compatibilitatea și migrarea](docs/compatibility.md).

## Structura proiectului

| Director | Conținut |
| --- | --- |
| `themes/zion-theme/` | Tema, șabloanele, patterns și CSS-ul. |
| `plugins/zion-core/` | Clasificarea produselor și shortcode-ul de catalog. |
| `config/`, `scripts/` | Date demonstrative, seed WP-CLI, build și împachetare. |
| `tests/`, `.github/workflows/` | Testele și automatizarea CI. |
| `docs/`, `AGENTS.md` | Documentația tehnică și regulile de lucru. |
