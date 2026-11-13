# Zion Theme

Temă WordPress bazată pe blocuri, cu identitatea Zion 3D și două vitrine care folosesc același WooCommerce. Obiectivul este înlocuirea XStore și Elementor prin blocuri native, Zion Studio și un panou de configurare dedicat.

## Implementat

- [x] **Zion Theme 1.1.28** cu presetări native în `theme.json` v3, homepage negru-lime, navigație responsivă și header și footer editabile.
- [x] **Zion Control Center** în **Aspect → Zion Theme**, cu setări globale, pe vitrină și pe produs sau pagină, plus resetare și import/export JSON.
- [x] **Șabloane editoriale** pentru pagini, articole, jurnal, arhive, căutare și 404, cu carduri, paginare și stări fără rezultate.
- [x] **Șabloane WooCommerce** pentru catalog, căutare de produse și pagina individuală. Arhivele de categorii, etichete și branduri folosesc layoutul catalogului, iar coșul și checkoutul folosesc șabloanele WooCommerce.
- [x] **Zion Core 1.1.28** cu taxonomia `zion_shop`, clasificare individuală și în masă, câmpuri editoriale și interogări comune pentru colecții.
- [x] **Vitrine configurabile separat** cu grilă sau listă, coloane responsive, Product Collection și Product Filters WooCommerce, sortare, paginare și opțiunea „Încarcă mai multe”.
- [x] **Carduri de produs** cu un renderer comun și cinci preseturi responsive. Imaginea, badge-urile, ordinea, informațiile și acțiunile se configurează global sau pe vitrină.
- [x] **Pagini de produs** cu preseturi Physical 3D, Software, Minimal și Premium, secțiuni reordonabile și suprascrieri per produs. Formularul, variațiile și stocul folosesc WooCommerce.
- [x] **Navigație și șabloane generale** cu logo și CTA în Zion Studio, căutare de produse, cont și mini-coș nativ, footer Zion Nexus, jurnal configurabil și sidebar editabil.
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

## Design pe pagini și feedback coș · 1.1.28

- [x] **Aspect → Zion Theme → Identitate și design → Destinație design**: Global, Homepage, Magazin 3D și Magazin Software. Paletă, fonturi, dimensiuni, spațiere, lățimi și rotunjiri independente, cu colorpicker și preview înainte de salvare la 390/768/1024/1440 px.
- [x] Moștenire Global → destinație, resetare individuală sau a destinației, import/export JSON v2 cu destinație explicită și protecție împotriva salvărilor concurente. Același document nativ `wp_global_styles` și aceleași variabile din `theme.json`; fără setări duplicate în opțiunea Zion. Headerul, footerul și scenele Canvas Continuum preiau paleta paginii curente; produsul, coșul și alte pagini păstrează Global.
- [x] **Vezi coșul flotant** pe cardurile comune: pictogramă, cantitate și acces direct, fără rând nou și fără salt în înălțimea cardului. Pe imagini înguste devine compact. Starea folosește cartStore WooCommerce și revine după refresh; legăturile native din afara cardurilor au stil de buton Zion.
- [x] Hamburger după coș și în ordinea DOM pe mobil. Panoul meniului are fundal continuu și derulare completă, inclusiv cu submeniuri lungi; păstrează acordeoanele și Escape native.
- [x] Teste noi pentru cele trei profiluri, moștenire, resetare, import/export, revizii între destinații și validare atomică. Regresii pentru carduri pe cele trei pagini și cele cinci meniuri, la patru lățimi, inclusiv 25 de sublinkuri.
- [x] Build, WPCS, **61 teste PHPUnit / 436 aserțiuni**, integrare și migrare/rollback trecute. Suita completă locală: **128/128 Playwright** în 17,7 minute; matrice pentru carduri și cele cinci meniuri, inclusiv persistență după refresh și submeniuri lungi. Verificarea finală Studio: **9/9**, inclusiv culoarea pixelilor Canvas pentru profilurile Homepage/Software.
- [x] Theme/Core **1.1.28** instalate pe staging: **12/12 combinații** pentru homepage/3D/Software la patru lățimi, un H1 și zero overflow. Culori/fonturi/spațiere în preview testate fără salvări pe datele reale, plus colorpicker mobil. Adăugare în coș de test, fără plasarea unei comenzi, card cu înălțime stabilă și link flotant păstrat după refresh; selecția inițială din coș restaurată. Meniul mobil complet extins se derulează până la Contact, cu fundal continuu și Escape funcțional.
- [x] Audit staging: **43 de înregistrări editoriale**, metadate, termeni, comentarii, configurație Zion, citire, permalinkuri și identitate păstrate. Produsele/variațiile și metadatele au același hash. Zero documente native noi; helperul temporar eliminat. Cele **132 / 9 fișiere** instalate corespund pachetelor din sursa publicată. Producția nu a fost modificată.
- [x] Preview-ul static păstrează și alinierea coș/hamburger pe un singur rând; verificare Studio finală **9/9**, plus WPCS și **61 teste PHPUnit / 436 aserțiuni**.
- [x] Ajustarea finală instalată din `dc0be37`: preview mobil cu coș/hamburger pe un singur rând, matricea de **12/12** pagini/lățimi repetată și trecută, fără erori JavaScript observate. Fișierele instalate au fost comparate cu pachetele finale; CSS-ul compilat are același SHA-256 ca sursa locală.
- [x] Ambele ZIP-uri **1.1.28** generate cu `npm run package` și verificate intrare cu intrare: **132 fișiere Theme / 9 fișiere Core**, conforme sursei. Browserul Docker propriu oprit; viewporturile și preferințele temporare din browser restabilite.
- [x] **CI final verde** pentru `dc0be37`: [rularea 43401661898](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43401661898), cu **128/128 teste Playwright în 11,3 minute**, **61 teste PHPUnit / 436 aserțiuni**, build reproductibil, WPCS, integrare și migrare/rollback trecute. README, ghidurile și pachetele finale 1.1.28 sunt finalizate; implementarea și raportul sunt publicate pe `main`.

Ghiduri: [personalizarea Zion Studio](docs/zion-studio.md), [feedback coș și navigație mobilă](docs/cart-feedback.md).

## Zion Studio · administrare 1.1.27

- [x] **Aspect → Zion Theme → Identitate și design**: colorpicker WordPress și HEX, contrast, fonturi de sistem, dimensiuni, spațiere, containere, rotunjiri, logo și pictogramă prin biblioteca media. Culorile și tokenurile provin din `theme.json` și folosesc același document nativ `wp_global_styles`, fără duplicare în opțiunea Zion.
- [x] Preview fără salvare pentru pagini publicate, la **390/768/1024/1440 px**, cu header compact. Cadrul este static, fără navigare, formulare sau cumpărări. Resetare individuală/globală a tokenurilor, import JSON în preview, export și validare pe server înainte de scriere; detectarea modificărilor concurente.
- [x] **Pagini și componente / Zion Studio**: creare de pagini, articole și meniuri, editare Gutenberg cu inspector, header, footer și șabloane native, imagini, rezumat și șablon de pagină. Calendar dată/oră în fusul site-ului și publicare programată. Linkuri și submeniuri cu ordonare accesibilă; conectarea meniului în header se face în același panou.
- [x] Comutatoare, glisoare numerice, liste cu bife, drag-and-drop și butoane sus/jos, căutare în setări și indicatoare de salvare. Selectoarele vizuale cu miniaturi și lightbox rămân disponibile. Selectoarele cu titluri lungi și legăturile de administrare se încadrează pe mobil.
- [x] **CI verde** pentru `a39e68f`: [rularea 43371470272](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43371470272), cu **126/126 teste Playwright în 10,8 minute**, **56 teste PHPUnit / 417 aserțiuni**, build reproductibil, WPCS, integrare și migrare/rollback trecute. Cele **10 aserțiuni noi de integrare** verifică persistența nativă, validarea atomică, permisiunile și conflictele. Cele **7 scenarii Studio** includ creare/publicare, media, resetare, meniuri/submeniuri, titluri lungi, colorpicker și zero overflow.
- [x] Theme/Core **1.1.27** instalate pe staging, cu tema finală din `a39e68f`. Design: zero overflow la **390/768/1024/1440 px**, inclusiv cu pickerul deschis pe mobil. Editorul Contact, calendarul Europe/Bucharest, headerul nativ, comutatoarele și cele patru glisoare Software au fost verificate, fără salvări pe datele reale și fără erori JavaScript observate în ecranele verificate.
- [x] Backup privat SQL, Theme/Core și snapshot editorial. Audit final: **43 de înregistrări editoriale**, metadate, termeni, comentarii, configurație Zion, identitate, citire și permalinkuri păstrate. Produsele/variațiile și metadatele au același hash; nu au fost create documente native noi. Scriptul Studio și CSS-urile instalate corespund sursei, iar helperul temporar a fost eliminat și absența lui verificată. **Producția nu a fost modificată.**
- [x] README și ghid actualizate; ambele ZIP-uri **1.1.27** generate și verificate: **131 fișiere Theme / 9 fișiere Core**, inclusiv biblioteca Studio, cu fiecare intrare identică sursei. Implementarea și raportul sunt publicate pe `main`.

Ghid: [Zion Studio și controale native](docs/zion-studio.md).

## Zion Continuum · Software 1.1.26

- [x] Un singur șablon nativ, editabil în Site Editor, pentru **șapte pagini**: hub Software, Magazin Software, SMS Confirmation, Privacy, Multi-Currency și ReleaseStation RO/EN. Hero și diagrame adaptate fiecărui concept, culori native `theme.json`, pauză, reduced motion și fallback fără JS.
- [x] Secțiuni H2, carduri H3 și cuprins nativ construite numai la afișare, cu păstrarea fiecărui bloc original și a ordinii sale. Carduri pentru legăturile hubului, cod accesibil cu derulare locală și marcaj de limbă pentru pagina engleză. Șablonul legacy al hubului este tratat ca alias nativ fără modificarea metadatelor.
- [x] Catalog WooCommerce păstrat: clasificare, filtre, sortare AJAX, paginare, prețuri și cumpărare native. Cele trei CTA-uri Multi-Currency fără destinație validă devin solicitări clare de detalii către Contact; nu se inventează produse sau licențe.
- [x] Backup privat staging SQL, Theme/Core și **43 de înregistrări editoriale**, cu metadate, termeni, comentarii și setări. Conținutul real rămâne în afara repository-ului; testele creează fixture-uri sintetice temporare într-un proiect Playwright separat.
- [x] Build, WPCS, **46 teste PHPUnit / 394 aserțiuni**, integrare și migrare/rollback trecute. **10/10 teste Software** și **7/7 regresii de catalog/cumpărare** trecute local, cu 14 referințe vizuale și verificări la 390/768/1024/1440 px.
- [x] **14 aserțiuni noi de integrare**, inclusiv aliasul legacy și prioritatea unui alt șablon ales de autor. Verificarea finală locală Software a trecut **10/10** în 1,4 minute, fără actualizarea referințelor; textele și șabloanele salvate ale fixture-urilor rămân identice.
- [x] **CI verde** pentru `3add04a`: [rularea 43354915141](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43354915141), cu **119/119 teste Playwright** în 12,1 minute, **46 teste PHPUnit / 394 aserțiuni**, build reproductibil, WPCS, integrare și migrare/rollback trecute.
- [x] Theme/Core **1.1.26** instalate pe staging; tema finală din `3add04a`. **28/28 combinații** pentru cele șapte pagini la 390/768/1024/1440 px: un H1, zero overflow, fără erori PHP/JS în verificările efectuate. Cuprins, tastatură, meniu mobil, pauză/reduced motion, ReleaseStation RO/EN, CTA-urile reparate, sortarea AJAX, Back, lipsa rezultatelor și resetarea filtrelor verificate.
- [x] Testele vechi de încărcare condițională folosesc acum Magazin 3D, care nu conține scene Continuum. Magazin Software are intenționat noul hero animat; cele patru aserțiuni vechi nu mai cer absența rendererului pe această pagină.
- [x] Planurile migrate cu link-rezumat înainte de H3 sunt grupate lângă propriul titlu; acțiunea afișată devine „Vezi produsul”, cu numele planului pentru cititoarele de ecran. Blocurile și ordinea salvate rămân intacte. Două aserțiuni suplimentare de integrare acoperă asocierea și prezentarea.
- [x] Noul titlu Software păstrează integrarea cu opțiunea existentă „Legătură Acasă” și override-ul per pagină, verificată de scenariul Control Center.
- [x] Verificare locală finală după aceste ajustări: **10/10 Software** în 1,7 minute, **1/1 Control Center** în 51,2 s și **4/4 verificări de încărcare condițională**. Build, WPCS, PHPUnit, **16 aserțiuni Software de integrare** și migrare/rollback trecute.
- [x] Clona avea indexul WooCommerce de produse gol: refăcute nativ **82 de rânduri derivate**, cu backup și verificarea identității tuturor produselor/variațiilor și metadatelor. Sortarea celor opt produse reale funcționează acum după preț în ambele sensuri; prețurile și stocurile sursă sunt păstrate.
- [x] Cele **opt fișe native de produse Software** au fost verificate la 390/1440 px: **16/16** combinații, un H1, formular WooCommerce prezent și zero overflow. Nu au fost plasate comenzi. Homepage/About/Servicii/Contact/blog/FAQ/Magazin 3D păstrează **6/2/10/3/1/2/0 scene**, fără aplicarea stilurilor Software.
- [x] Audit final: **43 de înregistrări editoriale**, metadate, termeni, comentarii, configurație Zion, citire și permalinkuri păstrate exact. Toate produsele/variațiile și metadatele lor sunt identice cu snapshotul anterior refacerii indexului. Șablonul Software este nativ din temă, fără copie în Site Editor. CSS/JS instalate corespund sursei locale, iar ambele helperuri temporare au fost eliminate și absența verificată. Capturi și rapoarte QA în `build/`.
- [x] README și raport finalizate; ZIP-uri Theme/Core **1.1.26** regenerate și verificate: **128 / 9 fișiere**, antete corecte și fiecare intrare identică sursei. Implementarea, ajustările și raportul sunt publicate pe `main`. Producția nu a fost modificată.

Ghid: [Software / Continuum](docs/software-continuum.md).

## Zion Continuum · Întrebări frecvente 1.1.25

- [x] Șablon nativ pentru `/intrebari/` și șablon `faq` selectabil pentru alte pagini, cu aceeași compoziție editabilă în Site Editor. Hero cu rețea de întrebări, explicații laterale și CTA cu releu Idee → Dialog → Direcție. Header/footer native păstrate.
- [x] Acordeoane `core/details`: mai multe răspunsuri deschise, Enter/Space și funcționare fără JavaScript. Căutare în întrebări/răspunsuri fără diacritice, contor accesibil, ștergere, stare fără rezultate și comenzi de deschidere/închidere numai pentru rezultatele vizibile. Controalele funcționează și fără Canvas.
- [x] Două scene în motorul Continuum comun, cu reacție la focus și la răspunsurile deschise, pauză comună, reduced motion și încărcare condițională. Tokenuri `theme.json`, focus vizibil și controale de minimum 44 px; zero dependențe noi.
- [x] Pagina reală are **zece întrebări native**; conținutul și metadatele nu sunt rescrise. Backup privat SQL, Theme/Core și snapshot editorial cu **43 de înregistrări**, taxonomii, comentarii, setări Zion, citire și permalinkuri. Local se folosesc numai zece exemple sintetice, create dacă pagina lipsește.
- [x] Build, WPCS, **46 teste PHPUnit / 394 aserțiuni**, integrare și migrare/rollback trecute. **Șapte aserțiuni noi de integrare** și **9/9 teste FAQ** trecute local, inclusiv patru referințe vizuale și verificări la 390/768/1024/1440 px.
- [x] **18/18 verificări** locale pentru FAQ și cele nouă capturi complete existente, după verificarea diferențelor din footer: noua pagină demo adaugă legătura FAQ și 32 px în footer, fără schimbarea conținutului principal. Pe staging legătura exista deja. Numerotarea întrebărilor filtrate păstrează corespondența cu nodurile diagramei.
- [x] Theme/Core **1.1.25** instalate pe staging din `5eafffe`. Cele zece întrebări reale: **390/768/1024/1440 px**, un H1, două scene, controale de minimum 44 px și zero overflow inclusiv cu toate răspunsurile deschise. Enter/Space, căutare fără diacritice și în răspunsuri, numere stabile, stare fără rezultate, comenzi limitate la rezultatele vizibile, pauză comună, reduced motion și meniu mobil verificate. Capturi desktop/mobil salvate în `build/`.
- [x] Audit final strict: **43 de înregistrări cu metadate și termeni**, comentarii și setări păstrate exact. Întrebările au același SHA-256, metadatele paginii rămân `default`, șablonul este nativ din temă, fără copie salvată în Site Editor. CSS și cele două scripturi instalate corespund sursei locale/ZIP-urilor; helperul temporar a fost eliminat și absența verificată. Homepage/About/Servicii/Contact/blog păstrează **6/2/10/3/1 scene**; FAQ JS este absent pe aceste pagini, iar catalogul nu încarcă rendererul. Fără erori JS în paginile verificate și fără modificări în producție.
- [x] **CI verde** pentru `5eafffe`: [rularea 43330474147](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43330474147), cu **109/109 teste Playwright** în 11,2 minute, **46 teste PHPUnit / 394 aserțiuni**, build reproductibil, WPCS, integrare și migrare/rollback trecute. Verificarea locală finală FAQ: **9/9** în 30,7 s, fără actualizarea referințelor în acea rulare.
- [x] README și ghid finalizate, ZIP-uri Theme/Core **1.1.25** regenerate și verificate: **125 / 9 fișiere**, antete și fiecare intrare conforme cu sursa locală. Implementarea și raportul final sunt publicate pe `main`. Producția nu a fost modificată.

Ghid: [Întrebări frecvente / Continuum](docs/faq-continuum.md).

## Zion Continuum · Jurnal și articole 1.1.24

- [x] Blog, arhive și căutare explicită de articole în stilul Zion Continuum: hero cu pagini proiectate în perspectivă, categorii native cu stare activă, căutare limitată la `post`, carduri editoriale și paginare numerică WordPress. Căutarea generală și cea WooCommerce își păstrează scopul.
- [x] Articol cu titlu și metadate native, copertă, introducere explicită, legătură directă către lectură și coloană Paper lizibilă. Diagrama laterală și progresul răspund numai derulării; animația continuă este limitată la hero vizibil. Pauză comună, reduced motion, controale de tastatură și fallback fără JavaScript, în motorul comun.
- [x] Un singur pattern de card pentru jurnal și articole asociate; metadatele și vizibilitatea folosesc setările existente și override-urile per articol. WordPress păstrează interogările, paginarea, stările public/private/protejate și formularul de comentarii. Formularul nativ are etichete în română; nu există editor sau motor de comentarii paralel.
- [x] Conținutul, URL-urile, imaginile, categoriile, datele și metadatele SEO nu sunt rescrise. H1 din corp este afișat ca H2 prin tokenizare WordPress; textul, atributele, codul, SVG-urile și zonele raw-text rămân intacte. Coperta și fragmentele proprii nu expun articole protejate prin parolă.
- [x] Backup privat staging SQL, Theme/Core și **43 de înregistrări editoriale cu metadate și termeni**, plus comentarii, taxonomii, setări Zion, citire și permalinkuri. Patru articole publicate; șabloanele provin din temă, fără override-uri în Site Editor. Auditul dedicat este numai pentru citire și refuză producția.
- [x] Build, WPCS, **46 teste PHPUnit / 394 aserțiuni**, integrare și migrare/rollback trecute; **14 aserțiuni noi de integrare** pentru șabloane, renderer, păstrarea datelor, restaurarea contextului WordPress și protecția prin parolă.
- [x] **9/9 teste Playwright noi** trecute în 55 s, cu opt referințe vizuale la 390/1440 px și verificări de layout la 390/768/1024/1440 px. Acoperă categorie, căutare limitată la articole, paginare, back/forward, draft/private/parolă, animație/pauză/progres, setările existente, fără JavaScript și validitate în Site Editor. Scenariile care creează articole rulează izolat, între storefront și control, și restaurează datele/configurația.
- [x] **CI verde** pentru `6166c27`: [rularea 43299459462](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43299459462), cu **100/100 teste Playwright** în 9,1 minute, **46 teste PHPUnit / 394 aserțiuni**, build reproductibil, WPCS, integrare și migrare/rollback trecute.
- [x] Cele două teste Control Center adaptate la metadatele separate ale articolului și cardurilor asociate: **2/2 trecute local** în 47,9 s și în suita completă CI. Verifică override-ul articolului și păstrarea autorului/datei în cardul altui articol.
- [x] Theme/Core **1.1.24** instalate pe staging din `ccce371`. Blogul și toate cele patru articole reale: **390/768/1024/1440 px**, zero overflow și un H1. Căutare exclusiv în articole, arhivă cu categorie activă, stare fără rezultate, meniu mobil, animație reală, pauză comună, reduced motion și progres până la **100%** verificate. Fără erori JS în paginile verificate; comentariile nu au fost trimise.
- [x] Reparare punctuală a clonei: recalculare nativă a **26 de contoare** de categorii/etichete și refacerea a **două derivate de imagine absente**, din originalele deja existente. Conținutul și metadatele articolelor/atașamentelor nu sunt rescrise; asignările și celelalte câmpuri ale termenilor sunt identice. Snapshotul original este păstrat, al doilea diferă exclusiv prin contoarele recalculate. Audit final strict: **43 de înregistrări**, configurație Zion, citire, permalinkuri și comentarii păstrate; **28 de imagini din conținut prezente**, zero fișiere lipsă.
- [x] CSS/JS instalate au aceleași SHA-256 ca sursa locală. Helperul de cache autentificat s-a eliminat, iar absența este verificată. Homepage/About/Servicii/Contact păstrează **6/2/10/3 scene**; catalogul nu încarcă CF7 sau rendererul. Capturi desktop/mobil salvate în `build/`. Producția nu a fost modificată.
- [x] README și raport finalizate; ambele ZIP-uri **1.1.24** regenerate, cu **119 fișiere Theme / 9 Core**, antete și fiecare intrare verificate față de sursa locală. Implementarea `ccce371`, verificările `6166c27` și raportul final sunt publicate pe `main`. Producția nu a fost modificată.

Ghid: [Jurnal / Continuum](docs/journal-continuum.md).

## Zion Continuum · Contact 1.1.23

- [x] Pagină Contact nativă, editabilă în Site Editor: hero cu nod de comunicare Continuum, e-mail/telefon/WhatsApp, adresă existentă, îndrumări pentru proiect și traseul colaborării. Trei scene în motorul comun, cu perspectivă, semnale, progres contextual, pauză globală și mișcare redusă.
- [x] Formularul este blocul **Contact Form 7**, cu selectorul și shortcode-ul gestionate de plugin, fără un al doilea motor de validare sau trimitere. Presetul demo are patru câmpuri obligatorii și serviciu preselectat din linkurile existente. Pe staging este păstrat formularul autorului: nume, e-mail și mesaj obligatorii, companie și telefon opționale. Etichete permanente, grilă responsive, mesaje în română și spinner AJAX; sloturile de eroare păstrează butonul stabil la corectare.
- [x] Asseturi CF7 numai pentru formulare efectiv randate, inclusiv în template parts; încărcare după înregistrarea nativă a scripturilor. Lipsa/dezactivarea formularului păstrează contactele directe. Pagina și formularul rămân funcționale fără JavaScript.
- [x] Mediu reproductibil fixat la **WordPress 7.1.4 / CF7 6.2.5**, versiunile existente pe staging. Seeder exclusiv local, formular și transport de test sintetice; repară imaginile demo lipsă după schimbarea directorului de core wp-env, păstrând ID-urile atașamentelor.
- [x] Build, WPCS, **46 teste PHPUnit / 394 aserțiuni**, integrare și migrare/rollback trecute. **10/10 teste Contact** și patru referințe vizuale; teste reale ale endpointului CF7, conținutului mailului capturat, erorilor, retrimiterii după corectare, accesibilității, reduced motion și lipsei JavaScript.
- [x] Noua pagină demo activează legăturile Contact deja prevăzute în navigația și footerul native. Cele nouă referințe vizuale generale au fost actualizate pentru această destinație nouă; fiecare imagine crește cu exact 32 px, rândul suplimentar din footer. Structura și stilurile celorlalte pagini rămân păstrate.
- [x] Backup privat staging SQL, componente, configurație și **39 înregistrări editoriale/CF7 cu metadate**, înaintea oricărei modificări. Script dedicat cu preview, backup obligatoriu și reaplicare fără rescrierea conținutului deja editat. Destinatarul existent nu este introdus în Git.
- [x] ZIP-uri Theme/Core **1.1.23** generate.
- [x] Suita locală completă: **88/88 teste Playwright** în 14 minute. **GitHub Actions verde** pentru `06c3abc`: [rularea 43229067855](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43229067855), cu build, WPCS, PHPUnit, integrare, migrare și E2E trecute.
- [x] Theme/Core **1.1.23** instalate pe staging, preview verificat fără scriere și apply idempotent. **390/768/1024/1440 px**, un H1, trei scene, controale de minimum 48 px și zero overflow. Pauză comună, reduced motion, progres **0 → 3**, meniu mobil și încărcare condițională CF7 verificate. Homepage păstrează șase scene, About două, Servicii zece; cataloagele nu încarcă CF7 sau rendererul. Fără erori JS în paginile verificate.
- [x] CF7 nativ pe staging: câmpuri lipsă, adresă invalidă, corectare/retrimitere, spinner vizibil și stările de succes/eșec verificate cu mesaje sintetice și captură privată izolată. Conținutul mesajului capturat a fost verificat. Destinatarul și configurațiile Mail/Mail 2 originale restaurate integral; hookul QA și helperul de cache eliminate. **37 înregistrări neimplicate cu metadate**, configurația Zion și setările de citire păstrate. Niciun e-mail extern trimis și nicio modificare în producție.
- [x] Detectarea adresei existente include paragrafe din grupuri Gutenberg imbricate, cu trei verificări de integrare suplimentare. Configurația actuală a formularului și setările schimbate între snapshotul inițial și instalare au fost păstrate printr-un backup nou, fără restaurarea lor la valori vechi.
- [x] Adresa din grupul vechi a fost restaurată punctual, cu revizie. Scriptul final din `5df532a` a fost verificat din nou pe staging: preview și apply raportează **changed=false**, zero erori CF7, iar snapshotul final al celor **39 de înregistrări cu metadate** rămâne identic după reaplicare. URL-ul, identitatea CF7, metadatele Contact și Mail/Mail 2 sunt păstrate; helperul și hookul QA sunt absente. Arhivele au antetele **1.1.23**, iar fișierele de runtime din ZIP corespund sursei și celor instalate.
- [x] Corecția reduced motion din `206b357` instalată pe staging, cu copie privată a rendererului anterior. SHA-256 al JS servit prin HTTP corespunde sursei și ZIP-ului final. Confirmate din nou **39 de înregistrări cu metadate**, setările Zion și de citire, ambele versiuni **1.1.23**. Tranziții static/animat Contact la toate cele patru lățimi, homepage la 390/1440 px, pauză comună și zero overflow; fără erori JS. E-mailurile reale nu au fost testate, conform amânării cerute.
- [x] **CI final verde** pentru `4e9843b`: [rularea 43240756538](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43240756538), cu **91/91 teste Playwright** în 9,2 minute, **46 teste PHPUnit / 394 aserțiuni**, build reproductibil, WPCS, integrare și migrare/rollback trecute. README și ghid finalizate, ambele ZIP-uri **1.1.23** regenerate și verificate; implementarea și raportul sunt publicate pe `main`. Producția nu a fost modificată.
- [x] Corecție în motorul comun pentru schimbarea live la reduced motion: aplică starea și desenul static înainte de oprirea ultimului cadru, inclusiv dacă notificarea media întârzie. Eșecul CI din `5df532a` a fost reprodus local cu preferința nativă activă și rendererul încă marcat animated; după corecție, **20/20 execuții** ale scenariului au trecut, fără timeouturi mărite sau retry-uri. Un test separat verifică oprirea și desenul static cu notificarea media suprimată, păstrând preferința reală a browserului.
- [x] După corecția motorului comun: build, WPCS, **46 teste PHPUnit / 394 aserțiuni**, integrare și migrare/rollback trecute din nou. **35/35 teste Playwright** Contact/About/homepage/Servicii în 2,7 minute, cu referințele vizuale păstrate și cazul de notificare întârziată inclus.
- [x] Testele About folosesc acum contextul gestionat de Playwright pentru lifecycle și trace. Verificările hero/pointer/pauză și atelier/scroll/tastatură/viewport sunt scenarii independente, cu toate aserțiunile păstrate și limita existentă de 30 s. **12/12 execuții** ale scenariului gestionat au trecut, apoi **20/20 execuții** ale celor cinci scenarii separate au trecut în 56,5 s. Suita completă are acum **91 de scenarii**; fișierele instalate pe staging rămân cele verificate din `206b357`.
- [ ] **Etapă ulterioară, amânată explicit de utilizator:** configurarea transportului și testarea e-mailurilor reale. PHP-FPM staging folosește `mail`, fără sendmail executabil și fără SMTP configurat. Formularul și endpointul CF7 sunt validate prin captură sintetică; livrarea în inbox nu este verificată. Transportul se configurează separat de temă, cu credențialele în afara Git.

Ghid: [Contact / Continuum și CF7](docs/contact-page.md).

## Zion Continuum · Servicii 1.1.22

- [x] Pagina Servicii primește o rețea spațială cu șapte discipline, șapte previzualizări de card, traseul colaborării și convergență lângă CTA. Fiecare pagină individuală are trei scene, cu ilustrație proprie: fabricație pe straturi, CAD explodat, software, SaaS, API, desktop și AI cu validare umană.
- [x] Un singur motor Canvas 2D și scheduler pentru About, homepage și servicii. Previzualizările se animă numai la hover/focus; celelalte scene numai în viewport. Pauză comună, mișcare redusă din OS sau temă, fallback editorial fără JavaScript și paleta nativă `theme.json`.
- [x] Decorații aplicate la randare, fără rescrierea paginilor, URL-urilor sau setărilor. Conținutul și CTA-urile rămân blocuri native editabile. Rezolvarea contextului acceptă atât slugurile locale, cât și rutele canonice existente pe staging.
- [x] Build, WPCS, **46 teste PHPUnit / 394 aserțiuni**, integrare și migrare/rollback trecute. **8/8 teste noi** pentru cele opt pagini la **390/768/1024/1440 px**, animație, pauză, focus, suspendare offscreen, reduced motion, lipsa JavaScript și 16 referințe vizuale hero. După verificarea focusului combinat cu hover, **4/4 scenarii de mișcare Servicii/About** trecute din nou.
- [x] Suita locală completă: **78/78 teste Playwright** trecute în 14,8 minute, cu două browsere în paralel, inclusiv administrare, checkout, meniuri și clasificare. Rularea inițială la șase browsere a atins limita de timp într-un scenariu About; verificarea separată și suita completă la două browsere au trecut, fără eliminarea aserțiunilor, creșterea timeouturilor sau retry-uri.
- [x] **GitHub Actions verde** pentru `84ce127`: [rularea 43208499969](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43208499969), cu **78/78 teste Playwright** în 7,9 minute, **46 teste PHPUnit / 394 aserțiuni**, build reproductibil, WPCS, integrare și migrare/rollback trecute. Toate cele 16 comparații vizuale Servicii au trecut în mediul CI curat.
- [x] ZIP-uri Theme/Core **1.1.22** generate și verificate. Backup privat staging SQL, componente și **37 înregistrări editoriale cu metadate** pregătit înaintea instalării.
- [x] Theme/Core **1.1.22** instalate pe staging din `84ce127`, după backup privat. Confirmate toate cele **37 de înregistrări și metadatele lor**, setările de citire și întreaga opțiune Zion, plus **8 H1, 34 H2 și 45 legături** din cele opt pagini. CSS/JS instalate au aceleași SHA-256 ca buildul local; helperul temporar a fost eliminat.
- [x] Staging WordPress **7.1.4** / WooCommerce **11.2.2**: **32/32 combinații** pentru Servicii și cele șapte destinații la **390/768/1024/1440 px**, zero overflow și cadre hero complete. Reduced motion produce numai desene statice. **16 verificări pauză/reluare** mobil/desktop, șapte previzualizări din tastatură, hover combinat cu focus, proces **0 → 3** la scroll și meniu mobil deschis/închis corect. Fără erori JS în paginile verificate; homepage păstrează șase scene, About două, iar catalogul nu încarcă rendererul. Badge-ul coșului a rămas 3. Producția nu a fost modificată.
- [x] README și ghid actualizate cu rezultatele finale, arhivele versionate Theme/Core **1.1.22** generate și verificate, implementarea și raportul publicate pe `main`.

Ghid: [Continuum pe paginile Servicii](docs/services-continuum.md).

## Zion Continuum · homepage 1.1.21

- [x] Șase ilustrații contextuale: nucleu de fabricație în hero, piesă 3D pe straturi, panouri software, rețea pentru cele șapte servicii, traseul colaborării și convergență luminoasă lângă FAQ. Perspectivă la cursor, geometrie la scroll și legătură între focusul serviciilor și noduri.
- [x] Un singur renderer și scheduler pentru homepage și About, cu asset condițional, scene vizibile, pauză globală și limitarea detaliilor pe mobil. Variante statice la reduced motion și SVG-ul inițial când JavaScript lipsește. Paleta rămâne în `theme.json`.
- [x] Decorații adăugate numai la randare. Conținutul și șabloanele salvate rămân editabile și nu sunt rescrise; produsele, imaginile și acțiunile WooCommerce sunt păstrate.
- [x] Build, WPCS, **46 teste PHPUnit / 394 aserțiuni**, integrare și migrare/rollback trecute. **52/52 teste storefront**, inclusiv opt teste noi pentru homepage, trecute; comparațiile About păstrate și cele două referințe vizuale homepage actualizate. ZIP-uri Theme/Core **1.1.21** generate.
- [x] Suita locală completă: **70/70 teste Playwright** trecute în 11,6 minute. După corectarea cadrului hero pe mobil, **15 verificări homepage/Continuum** au trecut din nou; testul măsoară acum și marginea cadrului, nu doar overflow-ul documentului. Reluarea About este verificată cu desenul în viewport, unde rendererul este intenționat activ, cu **8 execuții repetate** trecute.
- [x] Scenariile CI existente selectează explicit facturarea separată când WooCommerce folosește implicit adresa de livrare și navighează cu Tab după finalizarea deschiderii reactive WordPress. Păstrează verificările câmpurilor native, totalurilor și submeniurilor; componentele livrate pe staging sunt neschimbate de această ajustare a testelor.
- [x] Ambele scenarii CI ajustate au trecut de două ori local: **4/4 execuții** pentru checkout și cele cinci meniuri, cu validarea totalurilor și a navigării native.
- [x] Theme/Core **1.1.21** instalate pe staging după backup privat SQL, arhiva componentelor și snapshotul editorial. Confirmate **37 de înregistrări și toate metadatele lor**, setările de citire, întreaga configurație Zion, cele **6 produse**, **7 subtitluri** și **53 de legături** ale homepage-ului. CSS/JS instalate au aceleași SHA-256 ca buildul local; helperul temporar a fost eliminat. Ajustarea ulterioară a testelor nu schimbă fișierele componentelor instalate.
- [x] Staging WordPress **7.1.4** / WooCommerce **11.2.2**, la **390/768/1024/1440 px**: zero overflow, cadru hero întreg, șase scene active, pauză comună din tastatură și mouse, focus pe servicii, proces evidențiat la scroll și meniu mobil deschis/închis corect. Reduced motion afișează șase desene statice și oprește fundalul CAD. About păstrează cele două scene, catalogul Software nu încarcă rendererul, fără erori JS în paginile verificate. Badge-ul coșului a rămas 3; producția nu a fost modificată.
- [x] **GitHub Actions verde** pentru `bea8306`: [rularea 43108991661](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43108991661), cu **70/70 teste Playwright** în 8,9 minute, **46 teste PHPUnit / 394 aserțiuni**, build, WPCS, integrare și migrare/rollback trecute. README și ghid actualizate, ZIP-uri Theme/Core 1.1.21 generate și verificate; livrare pe `main`.

Ghid: [Continuum pe homepage](docs/home-continuum.md).

## Zion Continuum · animație spațială About 1.1.20

- [x] Monogramă Z cu geometrie proiectată, particule, plan luminos și perspectivă care răspunde cursorului. Secvență centrală legată de derulare: **Schiță → Straturi → Obiect → Cod**, cu etape selectabile din tastatură. Paleta existentă negru/lime și texte Gutenberg editabile.
- [x] Canvas 2D propriu, fără frameworkuri sau video. Asset condițional, o singură buclă pentru ambele scene, număr redus de particule și rezoluție limitată pe mobil; randarea continuă se suspendă în afara viewportului și în documente ascunse.
- [x] Pauză/reluare pentru întreaga animație About, inclusiv fundalul CAD. Preferința OS și opțiunea temei pentru mișcare redusă afișează geometrie statică și toate textele; fără JavaScript rămâne pagina editorială completă. Derulare nativă, fără scroll-jacking.
- [x] Actualizare staging `about-motion`: preview fără scriere, inserare punctuală cu revizie și păstrarea blocurilor editate și a metadatelor; reaplicarea nu dublează scena.
- [x] Build, WPCS, **46 teste PHPUnit / 394 aserțiuni**, integrare și migrare/rollback trecute. **44 teste storefront** trecute, dintre care opt pentru About și Continuum; referințele vizuale About actualizate. ZIP-uri Theme/Core **1.1.20** generate.
- [x] Suita locală completă: **62/62 teste Playwright** trecute (14,9 minute). După ajustarea proporțiilor Canvas din hero, toate cele **8 teste About/Continuum** au fost repetate cu succes, inclusiv raportul corect între pixelii Canvas și dimensiunea afișată.
- [x] Theme/Core 1.1.20 instalate pe staging din sursa `ce7e3f3`, după backup privat SQL, arhiva componentelor și snapshotul paginii. Preview fără scriere, inserare unică și reaplicare ignorată. Confirmate toate metadatele și câmpurile originale ale paginii, conținutul editorial din afara scenei și întreaga opțiune a temei. Helperul temporar de cache a fost eliminat. CSS/JS instalate au aceleași SHA-256 ca fișierele locale.
- [x] QA staging pe WordPress **7.1.4** / WooCommerce **11.2.2**, la **390/768/1024/1440 px**: zero overflow, un H1, scenă și controale în viewport, etape din tastatură, trecere la Cod prin derulare, reacție reală la cursor, pauză și reduced motion cu toate cele patru texte vizibile. Fără erori JavaScript în pagina verificată. Catalogul nu încarcă rendererul; badge-ul coșului a rămas 3. Producția nu a fost modificată.
- [x] README și ghid actualizate, arhivele versionate Theme/Core 1.1.20 generate și codul publicat pe `main`.
- [ ] **CI încă neconfirmat:** [rularea 43098276316](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43098276316) pentru `ce7e3f3` este în coadă la **11 noiembrie 2026, 20:56 UTC**. [API-ul oficial GitHub Status](https://www.githubstatus.com/api/v2/summary.json) raportează `major_outage` pentru Actions (actualizare la 20:48 UTC). Rezultatele locale de mai sus sunt trecute; această rulare CI nu este declarată verde.

Ghid tehnic și actualizare: [Despre noi / Continuum](docs/about-page.md).

## Despre noi, cu vocea Zion 1.1.19

- [x] Pagină editorială cu hero „Oameni curioși. Idei care prind formă.”, notă tipografică Zion, poveste, atelier și software, principii de lucru și invitație la dialog. Negru, lime, Paper și același fundal CAD animat, cu reducerea mișcării.
- [x] Conținut din blocuri Gutenberg native, pattern reutilizabil și șablon HTML `page-despre-noi`. Header/footer editabile în Site Editor; seed-ul local creează doar pagina lipsă și păstrează editările existente. Fără asseturi JavaScript noi.
- [x] Mod staging `about` pentru preview și aplicare exclusiv pe această pagină, după backup. Actualizarea nativă păstrează URL-ul, ID-ul, autorul, data publicării, excerptul și metadatele SEO; salvează revizia și elimină selecția vechiului editor/șablon de pe pagina reconstruită.
- [x] Cinci teste About: 390/768/1024/1440 px, zero overflow, ancoră din tastatură, contrast, reduced motion, fără JavaScript și blocuri valide în editorul nativ. Integrare pentru conținut nativ și păstrarea modificărilor la seed.
- [x] Build, WPCS, **46 teste PHPUnit / 394 aserțiuni**, integrare și migrare/rollback trecute. **41/41 teste storefront** trecute. Referințe About noi și referințele existente actualizate pentru linkul Despre noi introdus în meniul și footer-ul demo.
- [x] Theme/Core 1.1.19 și pagina nouă instalate pe staging după backup privat complet și snapshot al paginii originale. Preview-ul nu a modificat conținutul; reaplicarea identică a fost ignorată. Confirmate păstrarea ID-ului, URL-ului, titlului, autorului, datei, excerptului, câmpurilor SEO și setărilor temei. Helperul temporar pentru cache a fost eliminat.
- [x] Staging verificat la 390/768/1024/1440 px: zero overflow, un singur H1, două secțiuni atelier/software, trei principii, CTA către Contact, animație CAD și dezactivarea ei la reduced motion. Ancora din tastatură lasă titlul poveștii sub headerul fix. Fără erori de consolă; badge-ul coșului a rămas 3 și producția nu a fost modificată.
- [x] **GitHub Actions verde** pentru `992f573`: [rularea 43090615085](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43090615085), cu **59/59 teste Playwright** în 8,3 minute, **46 teste PHPUnit / 394 aserțiuni**, build, WPCS, integrare și migrare/rollback trecute. README actualizat, ZIP-urile Theme/Core 1.1.19 generate; livrare pe `main`.

Ghid: [pagina Despre noi](docs/about-page.md).

## Hover continuu pentru mega meniuri 1.1.18

- [x] Corectarea zonei dintre declanșator și panoul Nexus Mega / Signature. O suprafață transparentă, limitată la spațiul de sub linkurile Header-ului și activă numai pentru meniul survolat, menține traseul mouse-ului către submeniu. Panoul păstrează poziția și dimensiunile; interacțiunea rămâne nativă WordPress.
- [x] Regresie reprodusă automat pe versiunea anterioară: Nexus Mega / Servicii se închidea la primul pas sub declanșator. Testul verifică trasee verticale și diagonale, linkurile panoului, închiderea la ieșire și accesarea cu mouse-ul a unui link de nivel trei, pentru ambele variante la 1440 și 1920 px.
- [x] Build, WPCS, PHPUnit (**46 teste / 394 aserțiuni**), integrare și migrare/rollback trecute. **36/36 teste storefront**, inclusiv comparațiile vizuale existente, plus testul extins al celor cinci meniuri. Acesta verifică și că logo-ul, coșul și linkurile headerului rămân accesibile cu mouse-ul, iar mini-coșul nativ se afișează deasupra meniului și se închide cu Escape. ZIP-urile Theme/Core 1.1.18 generate.
- [x] Theme/Core 1.1.18 instalate pe staging după backup privat. Problema a fost reprodusă și pe Signature pe datele reale (spațiu de aproximativ 27 px). După actualizare, hover-ul a rămas activ între declanșator și panou în Nexus Mega și Signature, iar linkul serviciului a putut fi apăsat. Verificate și traseul Software → WordPress & WooCommerce → Zion Privacy, Resurse și meniul mobil la 390 px, fără overflow sau erori de consolă. Selecția inițială **Signature** a fost păstrată; badge-ul coșului a rămas **3**. CSS-ul din ZIP, buildul local și instalarea de staging au același SHA-256. Producția nu a fost modificată.
- [x] **GitHub Actions verde** pentru `9bf435a`: [rularea 43082704286](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43082704286), cu **54/54 teste Playwright** în 6,8 minute și toate verificările build/WPCS/PHPUnit/integrare/migrare trecute. README actualizat, ZIP-urile Theme/Core 1.1.18 generate și verificate; livrare pe `main`.

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
- [x] Theme/Core 1.1.17 instalate pe staging după backup privat. Toate cele cinci variante verificate la 390/768/1024/1440 px: zero overflow, logo Zion, iconițe, pagină/părinți activi, separator complet și aceleași 22 de destinații unice. Nexus Mega și Signature se aliniază la întregul container; Technical păstrează contrastul deschis inclusiv pe mobil. Traseu real pe trei niveluri verificat pe Zion Privacy, cu acordioane, Escape și revenirea focusului. Butonul de închidere rămâne sub bara administratorului la 390/768/1024 px.
- [x] Cele cinci preview-uri, lightboxul și salvarea din administrare verificate pe staging. Setările au fost restaurate integral la valorile inițiale, inclusiv selecția „Moștenește / Atelier”, Mini-coș „Nu” și Badge „Da”. Coșul a rămas la 3 produse; fără modificări ale coșului, comenzi sau plăți. Header-ul nu a fost rescris în baza de date. CSS-ul instalat are același SHA-256 ca buildul local; fișierele temporare au fost eliminate. Fără erori de consolă; producția nu a fost modificată.
- [x] **GitHub Actions verde** pentru commitul `e6cc2ba`: [rularea 43076820538](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43076820538), cu **54/54 teste Playwright** în 6,3 minute, **46 teste PHPUnit / 394 aserțiuni**, build, WPCS, integrare și migrare/rollback trecute. README actualizat și ZIP-urile Theme/Core 1.1.17 generate și verificate; livrare pe `main`, exclusiv local și staging.

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
