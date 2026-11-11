# Zion Theme

Temă WordPress bazată pe blocuri, cu identitatea Zion 3D și două vitrine care folosesc același WooCommerce. Obiectivul este înlocuirea XStore și Elementor prin Site Editor, componente native și un panou de configurare dedicat.

## Implementat

- [x] **Zion Theme 1.1.17** cu presetări native în `theme.json` v3, homepage negru-lime, navigație responsivă și header și footer editabile.
- [x] **Zion Control Center** în **Aspect → Zion Theme**, cu setări globale, pe vitrină și pe produs sau pagină, plus resetare și import/export JSON.
- [x] **Șabloane editoriale** pentru pagini, articole, jurnal, arhive, căutare și 404, cu carduri, paginare și stări fără rezultate.
- [x] **Șabloane WooCommerce** pentru catalog, căutare de produse și pagina individuală. Arhivele de categorii, etichete și branduri folosesc layoutul catalogului, iar coșul și checkoutul folosesc șabloanele WooCommerce.
- [x] **Zion Core 1.1.17** cu taxonomia `zion_shop`, clasificare individuală și în masă, câmpuri editoriale și interogări comune pentru colecții.
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

## Cinci meniuri cu selector vizual 1.1.17

- [x] **Aspect → Zion Theme → Header și navigație → Design meniu și header**: Atelier, Nexus Mega, Capsule, Technical Contrast și Signature. Cele cinci imagini aprobate, preview compact, lightbox, salvare Ajax, reset de secțiune și import/export.
- [x] Un singur `header_design` global în registrul și opțiunea `zion_theme_settings`; implicit Atelier. Același bloc Navigație și aceleași linkuri editabile în Site Editor, inclusiv meniuri salvate ca `wp_navigation`. Schimbarea stilului nu rescrie meniul salvat.
- [x] Iconițe SVG proprii pentru servicii, magazine, software, WordPress, Synology, confidențialitate, SMS, resurse și contact. Descrieri scurte pentru destinațiile cunoscute în mega meniuri; linkurile personalizate primesc un simbol neutru, fără descrieri inventate.
- [x] Pagina activă și toate nivelurile părinte sunt evidențiate după URL. Parametrii de filtrare/sortare nu pierd selecția; linkurile externe și grupurile fără URL nu devin accidental active. Meniul mobil se deschide pe traseul paginii curente, cu acordioane accesibile, focus vizibil, navigare cu tastatura și Escape.
- [x] Linia lime aparține containerului exterior al Header-ului și traversează pagina, independent de lățimea conținutului sau de Capsule. Culori din `theme.json`, suprafețe contrastante și variantă Technical cu submeniuri deschise la culoare. Sticky header, căutarea, contul, CTA și coșul păstrează controalele existente.
- [x] Logo-ul Zion este inclus ca asset de brand și folosește blocul Site Logo. Seed-ul local îl importă numai dacă lipsește logo-ul; logo-urile configurate se păstrează. Structura implicită grupează Software și Resurse; meniurile personalizate nu sunt înlocuite.
- [x] Meniul mobil rezervă spațiu pentru bara WordPress a utilizatorilor autentificați, astfel încât butonul de închidere să rămână vizibil și accesibil.
- [x] Test dedicat pentru cinci variante × 390/768/1024/1440 px, cu meniu WordPress editat și trei niveluri, verificarea traseului activ, a contrastului Technical, a liniei pe întreaga lățime, preview/lightbox, acordioane, tastatură și fallback mobil fără JavaScript. PHPUnit: 46 teste / 394 aserțiuni.
- [x] Build, WPCS, PHPUnit, integrarea catalogului și migrare/rollback trecute. Cele 54 de cazuri Playwright acoperite local: 52 trecute în suita completă, apoi testul Control Center ajustat pentru descrierile noi și testul dependent de clasificare trecute separat. Referințe vizuale actualizate pentru noul header și logo; ZIP-urile Theme/Core 1.1.17 generate.
- [x] Revalidare după QA staging: build, WPCS, 46 teste PHPUnit / 394 aserțiuni, integrare și migrare/rollback trecute; testul celor cinci meniuri include acum bara de administrator. Testul mini-coșului așteaptă intrarea reală a focusului în drawer înainte de Escape; trei repetări secvențiale au trecut, fără pauze fixe sau relaxarea verificărilor.
- [ ] Instalare și verificare pe staging, confirmarea rulării GitHub Actions și raportul final de livrare.

Ghid: [designurile meniului](docs/menu-designs.md).

## Număr produse pe pictograma Coș 1.1.16

- [x] **Header și navigație → Badge cantitate coș** este disponibil și când mini-coșul este dezactivat. Pictograma păstrează linkul către pagina Coș; cu mini-coșul activ rămâne drawer-ul nativ.
- [x] Numărul reprezintă cantitatea totală WooCommerce, inclusiv mai multe bucăți din același produs. Badge lime, focus vizibil și etichetă accesibilă în română; zero pentru coșul gol.
- [x] Contorul citește store-ul nativ `wc/store/cart`, reacționează la modificările native și evenimentele Ajax și se resincronizează la restaurarea paginii din browser. Fără calcul propriu, endpoint nou sau număr personalizat în HTML-ul cache-uit. Până la răspunsul WooCommerce, linkul funcționează fără număr.
- [x] Script încărcat numai când linkul Coș cu badge este randat; dezactivarea badge-ului păstrează pictograma și elimină scriptul suplimentar. Datele și regulile comerciale rămân în WooCommerce.
- [x] Build, WPCS, PHPUnit (45 teste / 378 aserțiuni), integrarea catalogului și migrarea/rollback-ul trecute. Suita locală completă: **53/53 teste Playwright**, inclusiv contorul la 390/768/1024/1440 px, adăugare din catalog și pagina produsului, cantități multiple, eliminare, coș gol, izolare între sesiuni și dezactivarea badge-ului. ZIP-urile Theme/Core 1.1.16 generate.
- [x] Theme/Core 1.1.16 instalate pe staging după backup privat. Badge **3** verificat la 390/768/1024/1440 px, fără overflow, cu mini-coș dezactivat și link către `/cos/`. Homepage-ul și ambele magazine afișează aceeași cantitate. Adăugare din catalog și eliminare în coș: **3 → 4 → 3**, la 390 și 1440 px, fără reîncărcarea paginii la adăugare. Modificarea cantității în coș actualizează și badge-ul. Coșul inițial a fost restaurat (cantități 1 și 2, total 255,00 lei); opțiunile au rămas Mini-coș „Nu”, Badge „Da”. Fără erori de consolă, comenzi sau plăți trimise; producția nu a fost modificată.
- [x] **GitHub Actions verde** pentru commitul `6f2f38d`: [rularea 43060590513](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43060590513), cu **53/53 teste Playwright** și toate verificările build/PHP/integrare/migrare trecute. README actualizat, ambele ZIP-uri versionate generate; livrare pe `main`.

Ghid: [header și navigație](docs/site-navigation.md).

## Acces permanent la coș și CI 1.1.15

- [x] **Header și navigație → Mini-coș** controlează acțiunea accesului Coș: activat deschide drawer-ul nativ WooCommerce; dezactivat păstrează pictograma și trimite la pagina Coș configurată în WooCommerce, inclusiv cu slug personalizat. Headerul rămâne editabil în Site Editor.
- [x] Link accesibil la tastatură, SVG nativ și focus vizibil. În modul link nu se randează mini-coșul și nu sunt cerute asseturile drawer-ului de către acest control. Badge-ul cantității rămâne opțiunea mini-coșului nativ.
- [x] GitHub Actions: Chromium rulează în imaginea oficială Playwright cu dependențele deja instalate, la aceeași versiune cu lockfile-ul. Elimină pasul APT care eșua la rezolvarea DNS pentru `azure.archive.ubuntu.com`; buildul și toate testele rămân obligatorii.
- [x] Rulările depășite de un commit nou sunt anulate; commiturile exclusiv de documentație nu pornesc din nou suita. Rulare manuală disponibilă. Exporturile din testele de administrare folosesc un flux compatibil cu browserul remote.
- [x] Referințele vizuale au fost regenerate pentru fonturile imaginii oficiale și inspectate, fără relaxarea pragurilor de comparație.
- [x] Date fixe pentru articolele și recenzia demo în seed-ul exclusiv local; capturile nu mai depind de data instalării WordPress. Sincronizare cu randarea imaginilor în testul homepage.
- [x] Build, WPCS, PHPUnit (45 teste / 378 aserțiuni), integrarea catalogului și migrarea/rollback-ul trecute. Regresiile pentru header și clasificare au trecut după restrângerea selectorului la drawer-ul coșului; nouă verificări vizuale revalidate separat în imaginea oficială.
- [x] Theme și Core 1.1.15 instalate pe staging după backup privat. Mini-coș dezactivat: pictogramă și link către `/cos/`, navigare cu Enter. Activat: drawer nativ, URL păstrat, Escape funcțional. Ambele moduri verificate la 390/768/1024/1440 px, fără overflow; accesul verificat și pe homepage și magazinul Software. Fără erori în consola tabului verificat. Selecția inițială „Nu” a fost restaurată; coșul existent, comenzile și producția nu au fost modificate. Ambele ZIP-uri 1.1.15 generate.
- [x] **GitHub Actions verde**, verificat pe commitul `571dc67`: [rularea 43054985763](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43054985763). Build/CSS generat, WPCS, PHPUnit, integrare și migrare trecute; **52/52 teste Playwright trecute în 6,8 minute**, pe instanță WordPress proaspătă și browserul oficial Playwright 1.65.0. Containerul a fost oprit corect la final.

Ghiduri: [header și navigație](docs/site-navigation.md), [CI și browserul reproductibil](docs/ci.md).

## Contul meu cu selector vizual 1.1.14

- [x] **Aspect → Zion Theme → Contul meu**: Atelier, Studio Editorial, Technical, Contrast Premium și Focus Account. Cinci preview-uri aprobate, selector, lightbox accesibil, salvare Ajax cu spinner, reset de secțiune și import/export.
- [x] Un singur `account_design` global în `zion_theme_settings`; implicit Atelier. Același cont pentru 3D și Software, fără utilizatori, adrese sau comenzi duplicate.
- [x] Renderer comun peste `[woocommerce_my_account]`, independent de slugul paginii. Navigație, autentificare, recuperare parolă, comenzi, descărcări, adrese și formularele de cont rămân native WooCommerce. Linkuri în administrare către pagina nativă și Site Editor; conținutul existent nu este rescris.
- [x] Dashboard cu acces rapid, ultimele trei comenzi ale utilizatorului curent, stare goală și linkuri reale spre fișiere/adrese. Funcțiile eliminate din meniul nativ de extensii nu sunt reintroduse de dashboard.
- [x] Stiluri responsive și focus vizibil, tokenuri din `theme.json`, variantă Contrast Premium. Traduceri de rezervă pentru etichetele native ale contului; traducerile instalate și formatarea datelor WooCommerce au prioritate. Fără JavaScript nou pe frontend și fără suprascrieri de template-uri PHP WooCommerce.
- [x] PHPUnit: 45 teste / 378 aserțiuni. Testul dedicat verifică cele cinci variante la 390/768/1024/1440 px pentru clienți și vizitatori, endpointurile native și izolarea comenzilor între clienți.
- [x] Suita completă Playwright: **51 scenarii trecute**, inclusiv salvarea detaliilor contului și logoutul nativ. Build, WPCS, integrarea și migrarea/rollback-ul pe date sintetice trecute. ZIP-urile 1.1.14 generate.
- [x] Theme și Core 1.1.14 instalate pe staging după backup privat. Cinci designuri × 390/768/1024/1440 px: navigație/conținut unice, zero overflow și stare goală corectă pentru contul fără comenzi. Comenzi, Descărcări, Adrese și Detalii cont verificate la 390 și 1440 px; selector, preview-uri, lightbox și revenirea focusului funcționale. Fără erori în consolele taburilor verificate. Selecția a revenit la Moștenește / Atelier; nu s-au trimis formulare de profil, comenzi sau plăți pe staging și nu s-a modificat producția.

Ghid: [designurile contului](docs/account-designs.md).

## Administrare compactă și taguri categorii 1.1.13

- [x] Filtre active: un singur titlu **Categorii**, numele categoriilor afișate ca taguri, fără repetarea vizuală „Category:”. WooCommerce păstrează butoanele native, etichetele accesibile complete, parametrii și interogările.
- [x] Carduri, pagini de produs, coș și checkout: selector și variante organizate lângă un preview compact; explicațiile sunt separate de imagine.
- [x] Lightbox nativ `dialog`: imagine mare, fundal discret, închidere prin buton/Escape/fundal și revenirea focusului la preview. Fără biblioteci noi; fără JavaScript, linkul deschide în continuare imaginea originală.
- [x] Teste dedicate pentru toate cele patru secțiuni, desktop/mobil, focus, eliminare taguri, reset și navigare înapoi. Build, WPCS și 41 teste PHPUnit / 359 aserțiuni trecute.
- [x] Suita completă Playwright: **50 scenarii trecute**, inclusiv cele două teste noi pentru taguri și lightbox. Integrarea și migrarea/rollback-ul au trecut pe date sintetice.
- [x] Theme și Core 1.1.13 instalate pe staging după backup privat. Patru categorii 3D și două Software grupate corect; eliminare individuală și reset verificate. Preview compact și lightbox în toate cele patru secțiuni; Escape, buton de închidere și focus restaurat, plus lightbox checkout la 390 px. Fără erori de consolă în taburile verificate. Alegerile persistente și coșul nu au fost modificate; producția a rămas neatinsă.
- [x] Cinci concepte vizuale **Contul meu** prezentate pentru alegere: Atelier, Studio Editorial, Technical, Contrast Premium, Focus Account.
- [x] Cele cinci variante Contul meu au fost aprobate și implementate în 1.1.14, pe baza funcțiilor native WooCommerce.

Ghid: [preview-uri și filtre](docs/admin-previews-and-filter-tags.md).

## Checkout și corecție card Technical 1.1.12

- [x] **Aspect → Zion Theme → Finalizare comandă**: Atelier, Studio Editorial, Technical, Contrast Premium și Focus Checkout. Selector nativ, cinci imagini aprobate, preview imediat, salvare Ajax cu spinner, reset, import/export și verificarea reviziei.
- [x] Un singur `checkout_design` global în registrul/opțiunea existente. Atelier implicit. Coșul și checkoutul au alegeri independente; produsele 3D și software folosesc același checkout WooCommerce.
- [x] Formularul, validarea, câmpurile extensiilor, cupoanele, livrarea, taxele, plățile și totalurile rămân native. Variantele adaptează conceptele la structura blocului; butonul de plasare rămâne în formularul real. Fără formular sau motor financiar duplicat.
- [x] Șablon HTML `page-checkout`, editabil în Site Editor, care păstrează conținutul paginii. Linkuri directe spre editor și avertizare pentru shortcode-ul clasic.
- [x] Card Technical: butoanele nu se mai întind la apariția linkului „Vezi coșul”. Corecția comună acoperă homepage-ul și ambele magazine, inclusiv celelalte preseturi.
- [x] Build și WPCS; PHPUnit: 41 teste / 359 aserțiuni. Integrarea verifică toate cele cinci clase de checkout și păstrarea conținutului salvat. Migrarea/rollback-ul pe date sintetice au trecut.
- [x] Suita completă Playwright: 48 scenarii trecute, inclusiv 20 de randări checkout, 12 cazuri Technical după adăugare, validare nativă, totaluri, cupoane și checkout local cu ramburs, taxă și livrare.
- [x] Zion Theme și Zion Core 1.1.12 instalate pe staging după backup privat. Selector și preview-uri funcționale; cinci checkouturi × 390/768/1024/1440 px, formular/acțiune unice, zero overflow, total nativ păstrat la 356,99 lei. Corecția Technical verificată după adăugare Ajax în ambele magazine la 390 și 1440 px: butoane de 44 px, aliniate pe desktop. Presetul 3D Compact Studio, presetul Software Technical și coșul existent au fost păstrate; checkoutul a revenit la Moștenește / Atelier. Fără comenzi sau plăți trimise și fără modificări în producție. Gateway-urile externe rămân în afara acestei validări.

Ghid: [designurile checkoutului](docs/checkout-designs.md).

## Coș cu selector vizual 1.1.11

- [x] **Aspect → Zion Theme → Coș de cumpărături**: Atelier, Studio Editorial, Technical, Contrast Premium și Focus Checkout, cu cele cinci imagini aprobate, preview imediat și salvare Ajax cu spinner.
- [x] Un singur câmp `cart_design` în registrul și opțiunea existente `zion_theme_settings.global`, cu Atelier implicit, validare, reset de secțiune și import/export. Un singur coș comun pentru produsele fizice și software; nu există totaluri sau configurații comerciale duplicate.
- [x] Blocul Coș WooCommerce își păstrează cantitățile, eliminarea produselor, cupoanele, taxele, livrarea, plățile expres și linkul către checkout. Designurile schimbă numai prezentarea; sumele și rândurile din rezumat sunt cele furnizate de WooCommerce.
- [x] Șablonul HTML `page-cart` rămâne editabil în Site Editor și afișează conținutul paginii native de coș, fără să îl rescrie. Linkuri directe în panou către Site Editor și editorul paginii. Pentru shortcode-ul clasic se afișează o explicație: designurile necesită blocul nativ Coș.
- [x] Tokenuri derivate din `theme.json`, imagini centrate, tipografie moderată, controale accesibile, variantă cu suprafață deschisă și focus vizibil. Preview-urile sunt încărcate numai în administrare.
- [x] Build, WPCS, PHPUnit (37 teste / 340 aserțiuni), integrarea catalogului și migrarea/rollback-ul pe date sintetice trecute. Playwright: 46 scenarii în suita completă, plus reluarea testului dedicat după ajustările finale ale imaginilor, traducerilor și alinierii celulelor.
- [x] Matrice coș: cinci designuri × 1440/390/768/1024 px, fără overflow sau suprapunere titlu/total, un singur CTA de checkout, cantități Ajax cu indicator de încărcare, aplicare/eliminare cupoane, totaluri identice între designuri, checkout și coș gol. Studio Editorial utilizează imaginile complete din store-ul WooCommerce deja încărcat, fără interogări suplimentare de produse.
- [x] Zion Theme și Zion Core 1.1.11 instalate pe staging după backup privat. Cele cinci variante verificate la 390/768/1024/1440 px cu un coș mixt 3D + software: fără overflow sau suprapuneri, total identic de 142,99 lei. Cantitate Ajax 1 → 2 → 1: total 142,99 → 237,99 → 142,99 lei; checkout nativ în română, eliminare produse și coș gol verificate. Selectorul a revenit la Moștenește / Atelier și sesiunea de test a fost golită. Producția nu a fost modificată; nu s-au trimis comenzi sau plăți. Gateway-urile nu sunt disponibile pe staging și nu sunt validate prin acest test.

Ghid: [designurile coșului și configurarea nativă](docs/cart-designs.md).

## Pagini individuale de produs 1.1.10

- [x] Cinci designuri implementate în componenta comună: **Atelier**, **Technical**, **Immersive**, **Focus cumpărare**, **Editorial Premium**.
- [x] Selector vizual cu cele cinci imagini aprobate, preview imediat și salvare Ajax cu spinner în **Aspect → Zion Theme → Pagini individuale produse**. Destinații distincte: **Global**, **Single Product 3D Print**, **Single Product Software**.
- [x] Designul se salvează în `product_design`, în registrul/opțiunea existente. Precedență: produs → vitrină → global → Atelier implicit. Presetul de conținut `product_preset` rămâne independent: Auto/Physical 3D/Software/Minimal/Premium.
- [x] Override per produs: **Produse → Setări Zion** sau **Instrumente → ID produs**. Alege designul, apoi salvează. „Moștenește” elimină doar alegerea explicită; resetarea secțiunii unei vitrine nu afectează cealaltă vitrină.
- [x] Galerie nativă la lățimea componentei, miniaturi orizontale sau verticale conform variantei, zoom/lightbox WooCommerce. Fără miniaturi, fotografia folosește întregul spațiu. Formular unic pentru prețuri, variații, cantități și cumpărare; imaginile conceptuale nu sunt încărcate pe frontend.
- [x] Technical: galerie cu miniaturi verticale pe desktop; Immersive: scenă panoramică; Focus: trei coloane de la 1200 px, două la tabletă și una pe mobil; Editorial: introducere centrată, galerie lată și acces rapid la formularul real prin bara sticky implicită. Controalele explicite de vizibilitate și stil rămân prioritare.
- [x] Produsele încadrate în ambele magazine sau neclasificate folosesc designul Global, dacă nu au override explicit; nu alegem designul după referrer.
- [x] Software utilizează datele WooCommerce și metadatele Zion deja existente pentru versiune, compatibilitate, fișiere, changelog și documentație. Nu adaugă automat informații fără date și nu modifică regulile comerciale.
- [x] Build și WPCS fără erori; PHPUnit: 34 teste, 323 aserțiuni; integrarea catalogului și repetiția de migrare/rollback pe date sintetice trecute.
- [x] 45 scenarii Playwright verificate: 43 regresii generale, matricea celor cinci designuri pe ambele vitrine la 1440/390/768/1024 px (inclusiv miniaturi la tastatură, reset și override) și clasificarea în masă. Cumpărarea variabilă Ajax este verificată pentru fiecare design la 390 și 1440 px.
- [x] **Staging 1.1.10 verificat** pe `zion-stage.on-forge.com`: cinci designuri × două vitrine × 390/1440 px, 20 randări fără overflow și cu un singur formular. Salvarea Ajax și preview-urile funcționează; alegerile 3D și Software sunt independente. Galerie reală cu șapte imagini verificată la tastatură, vertical pe desktop și orizontal pe mobil; offset final sub 1 px în Technical și Editorial.
- [x] Backup privat înainte de instalare, numai pe staging. Selecțiile temporare de design au revenit la **Moștenește / Atelier**; configurația cardurilor și celelalte setări au fost păstrate. Nu s-au creat comenzi pe staging și nu s-a modificat producția.

## Carduri și Control Center 1.1.9

- [x] Cinci variante în rendererul comun WooCommerce — Atelier, Editorial, Compact Studio, Technical și Premium Focus. Atelier este valoarea implicită. Preseturile anterioare și configurațiile exportate rămân acceptate.
- [x] În **Aspect → Zion Theme → Carduri produse** alegi separat **Homepage**, **Magazin 3D**, **Magazin Software** sau configurația globală. Alegerile folosesc aceeași opțiune zion_theme_settings, cu prioritatea pagină → vitrină → global → implicit.
- [x] Selector vizual cu cele cinci imagini conceptuale generate, preview instant și deschidere la rezoluția originală. Panoul are navigație laterală responsive și salvare Ajax cu progres vizibil, validare, nonce, capabilități și detectarea configurațiilor modificate între timp.
- [x] Fotografia ocupă corect cadrul, titlurile sunt moderate și fără subliniere, stocul este evidențiat și acțiunile sunt aliniate. Technical afișează atribute WooCommerce existente. Compact Studio trece la format vertical când cardul este îngust.
- [x] Comenzile, prețurile și disponibilitatea sunt gestionate prin API-urile WooCommerce. Preview-urile sunt încărcate doar în administrare.
- [x] Build și WPCS, 33 teste PHPUnit cu 307 aserțiuni, integrare și migrare, 44 teste Playwright și reverificarea selectorului lat. Pe staging au fost verificate toate cele cinci variante la 390 px și pe desktop, preview-urile originale și independența homepage / Magazin 3D / Magazin Software. Alegerile inițiale au fost restaurate după test.

## Homepage 1.1.7

- [x] Hero cu grafică tehnică proprie, trasee verzi animate și paralaxă CSS, reutilizate din paginile de servicii. Preferințele de mișcare redusă sunt respectate.
- [x] O singură colecție cu ultimele șase produse vizibile din magazinul 3D, ordonate descrescător după data publicării. Cardurile compacte folosesc rendererul comun, trei coloane pe desktop și două pe mobil. Dacă există mai puține produse eligibile, apar doar cele disponibile.
- [x] Două direcții de explorare, șapte servicii, exemple de utilizare, pașii colaborării, articole recente și FAQ editabil. Colecțiile repetate și recenziile de pe homepage au fost înlocuite cu această compoziție.
- [x] Compoziția se editează în **Aspect → Editor → Șabloane → Prima pagină**. Pattern-ul **Zion · Homepage / Creative engineering** poate fi inserat și editat în Gutenberg.
- [x] Verificarea pe staging confirmă șase produse în aceeași ordine ca în catalogul 3D sortat după dată, fără overflow la 390, 768, 1024 și 1440 px. Linkurile produselor, FAQ-ul și varianta cu mișcare redusă au fost verificate în Chrome.
- [x] Validare locală pentru 1.1.7 cu build, WPCS, 32 teste PHPUnit și 285 aserțiuni, integrare și migrare, plus 43 teste Playwright. Verificările includ editarea nativă a homepage-ului în Site Editor și sortarea colecției de produse.

## Footer și fundalul paginilor de servicii 1.1.6

- [x] Footerul Zion Nexus folosește compoziția din referința zion3d.ro, cu CTA, cub tehnic, patru grupuri de legături, contact, parteneri și zona legală. Se editează în **Aspect → Editor → Modele → Părți de șablon → Footer**.
- [x] Pagina Servicii și cele șapte pagini individuale au trasee verzi animate în hero și în benzile de conținut. Browserele cu CSS scroll timelines deplasează fundalul la derulare. Preferința de mișcare redusă a sistemului și opțiunea din Zion Control Center lasă fundalul static.

## Servicii 1.1.4

- Cele șapte pagini individuale au hero cu panou tematic, grilă luminoasă de beneficii, exemple pe fundal întunecat și proces în trei pași. Pe staging, `wp eval-file scripts/staging-editorial.php details apply` actualizează doar paginile individuale.

- Pagina generală reproduce compoziția din referință: hero întunecat cu obiect lime, benzi luminoase, șase carduri în două coloane, card AI pe toată lățimea, proces și FAQ. Pe staging, `wp eval-file scripts/staging-editorial.php overview apply` actualizează doar rutele paginii generale.

- Pagina Servicii și cele șapte pagini individuale au pattern-uri Gutenberg editabile și template-ul **Zion · Servicii**. Layouturile includ panoul grafic, exemple practice, etapele colaborării și legături spre Contact. Pagina generală include și FAQ.
- Testele din browser urmăresc legăturile din pagina principală până la fiecare dintre cele șapte pagini, unde verifică titlul, template-ul și butonul de contact.
- Pentru o clonă de staging, fă un backup și previzualizează schimbările cu `wp eval-file scripts/staging-editorial.php services`. Adaugă `apply` pentru a înlocui conținutul paginilor de servicii și a aplica template-ul. Rutele existente sunt păstrate.

## Magazin 1.1.1

- [x] Încărcare automată la derulare în cele două vitrine, inclusiv când sari la final cu tasta End. În **Aspect → Zion Theme → WooCommerce** poți alege derularea automată sau doar paginarea numerică, global, per vitrină sau per pagină. Extinderea Ajax afișează maximum 96 de produse în lista curentă. Butoanele de paginare rămân disponibile cât timp există alte pagini. Înapoi și Înainte păstrează selecția și lotul de produse din istoricul browserului.
- [x] Sortarea pornește la selectare, păstrează filtrele și confirmă actualizarea printr-un mesaj scurt. În vitrine, formularul și linkurile funcționează și cu JavaScript dezactivat.
- [x] Pagina produsului adaugă în coș prin Ajax, inclusiv pentru variații. WooCommerce gestionează selecția, prețul, stocul și răspunsurile formularului. Acțiunea „Cumpără acum” continuă către checkout.
- [x] Etichetele uzuale WooCommerce au o traducere implicită în română, inclusiv pentru mini-coș, coș și formularul de comandă. Pentru catalogul complet al mesajelor și al țărilor, setează **Limba site-ului → Română** și instalează pachetele WordPress și WooCommerce. Textele extensiilor și ale metodelor de plată configurate se traduc în setările lor.

- [x] Indicator de încărcare pentru sortare, derulare, navigația AJAX a catalogului și solicitările WooCommerce din coș și produs. Routerul este încărcat la prima interacțiune, după inițializarea controalelor native, iar indicatorul respectă preferința pentru mișcare redusă.
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
| `zion-theme-1.1.9.zip` | **Aspect → Teme → Adaugă o temă → Încarcă tema**, apoi activează Zion Theme. |
| `zion-core-1.1.9.zip` | **Module → Adaugă un modul → Încarcă modulul**, apoi activează Zion Core. Activează WooCommerce pentru vitrine, pagini de produs și colecții. |

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
