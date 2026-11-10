# Zion Theme

Temă WordPress bazată pe blocuri, cu identitatea Zion 3D și două vitrine care folosesc același WooCommerce. Obiectivul este înlocuirea XStore și Elementor prin Site Editor, componente native și un panou de configurare dedicat.

## Implementat

- [x] **Zion Theme 0.3** cu presetări native în `theme.json` v3, homepage negru-lime, navigație responsivă și header și footer editabile.
- [x] **Zion Control Center** în **Aspect → Zion Theme**, cu setări globale, pe vitrină și pe produs sau pagină, plus resetare și import/export JSON.
- [x] **Șabloane editoriale** pentru pagini, articole, jurnal, arhive, căutare și 404, cu carduri, paginare și stări fără rezultate.
- [x] **Șabloane WooCommerce** pentru catalog, căutare de produse și pagina individuală. Arhivele de categorii, etichete și branduri folosesc layoutul catalogului, iar coșul și checkoutul folosesc șabloanele WooCommerce.
- [x] **Zion Core 0.1** cu taxonomia `zion_shop` și shortcode-ul `[zion_catalog]`. Un produs poate apărea în oricare dintre cele două vitrine sau în ambele.
- [x] **Catalog demonstrativ** cu două vitrine, patru produse fictive ilustrate și fluxul WooCommerce nativ pentru produs, coș și checkout.
- [x] **Mediu local și de test** cu WordPress, WooCommerce, Docker, wp-env, WP-CLI și date demo încărcate printr-o comandă repetabilă.
- [x] **Calitate și CI** cu build CSS, WPCS, PHPUnit, verificări de integrare, teste Playwright și workflow GitHub Actions.
- [x] **Distribuție** cu arhive ZIP versionate, instalabile separat pentru temă și plugin.

## Roadmap

Ordinea urmează fazele proiectului. Marchează un punct drept finalizat după ce funcționalitatea este verificată.

- [x] **0.2 · Tema nativă.** Șabloane pentru căutare, arhive și WooCommerce, presetări în editor, navigație la tastatură și verificări la 390, 768, 1024 și 1440 px.
- [x] **0.3 · Zion Control Center.** Registru unic, panou nativ WordPress, validare, resetare, import/export JSON și teste pentru precedență, permisiuni și migrarea schemei.
- [ ] **0.4 · Administrarea vitrinelor.** Completează clasificarea produselor cu identificarea celor neclasificate și operații în masă cu previzualizare și jurnal.
- [ ] **0.5 · Catalog și filtre.** Adaugă layout configurabil, filtre native WooCommerce și teste pentru combinațiile de filtre, sortare și paginare. Măsoară performanța cu cel puțin 1.000 de produse demo.
- [ ] **0.6 · Carduri de produs.** Adaugă preseturi și controale pentru imagini, badge-uri, informații și acțiuni, inclusiv pentru produsele variabile, grupate și externe.
- [ ] **0.7 · Pagina de produs.** Adaugă preseturi 3D și Software, componente reordonabile și configurare per produs, cu verificări pentru variante și disponibilitate.
- [ ] **0.8 · Navigație, footer, blog și pagini.** Completează căutarea, contul și mini-coșul, navigația extinsă, footerul Zion Nexus și opțiunile editoriale.
- [ ] **0.9 · Homepage și bibliotecă de componente.** Completează secțiunile pentru cele șapte servicii, ecosistemul Zion, recenzii și articole recente.
  - [ ] Creează paginile pentru printare și proiectare 3D, software personalizat, SaaS, pluginuri și integrări, aplicații Windows/Linux și integrări AI.
  - [ ] Creează blocuri dinamice și shortcode-uri pentru grile, recomandări, produse noi, reduceri, produse asociate, servicii, CTA și recenzii.
- [ ] **1.0 · Migrare și lansare.**
  - [ ] Inventariază paginile, widgeturile Elementor/XStore, shortcode-urile, extensiile WooCommerce, URL-urile și metadatele SEO.
  - [ ] Reconstruiește paginile în blocuri, clasifică produsele și verifică păstrarea URL-urilor pe staging.
  - [ ] Validează tipurile de produs, descărcările, contul, checkoutul, plățile, livrarea, taxele, cupoanele, comenzile, HPOS și abonamentele unde sunt folosite.
  - [ ] Măsoară Core Web Vitals, verifică accesibilitatea și compatibilitatea cu cache-ul WooCommerce.
  - [ ] Pregătește backupul, raportul de diferențe, planul de rollback și verificările post-deploy.

## Instalare din ZIP

Din rădăcina repo-ului rulează comenzile de mai jos. Împachetarea folosește Node, npm și PHP CLI cu extensia `zip`.

```sh
npm ci
npm run package
```

Comanda compilează CSS-ul și creează în `build/` două arhive independente. Versiunea fiecărei arhive vine din antetul componentei, iar o nouă rulare reface arhivele pentru versiunile respective.

| Arhivă | Instalare în WordPress |
| --- | --- |
| `zion-theme-0.3.0.zip` | **Aspect → Teme → Adaugă o temă → Încarcă tema**, apoi activează Zion Theme. |
| `zion-core-0.1.0.zip` | **Module → Adaugă un modul → Încarcă modulul**, apoi activează Zion Core. Activează WooCommerce pentru vitrine. |

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
