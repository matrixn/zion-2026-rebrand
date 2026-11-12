# Jurnal / Zion Continuum 1.1.24

Blogul folosește `templates/home.html`, arhivele `archive.html`, iar articolele `single.html`. Căutarea explicită `post_type=post` primește `search-posts.html` prin ierarhia nativă WordPress; căutările generale și WooCommerce păstrează șabloanele existente. Headerul, footerul, Query Loop, căutarea, imaginile, titlurile, metadatele și comentariile sunt blocuri native, editabile în Site Editor.

## Compoziție și configurare

- `journal-hero`, `journal-art` și `journal-tools` sunt pattern-uri native. Categoriile trimit către arhivele WordPress; starea activă folosește `aria-current`. Căutarea și paginarea sunt navigări native, funcționale și fără JavaScript.
- `post-card` este unicul pattern de prezentare pentru jurnal și recomandări. Recomandările păstrează interogarea WordPress existentă, cu maximum trei articole publice din categorii comune și fără parolă. Blocurile primesc contextul postului și contextul nativ al buclei, restaurat exact după randare.
- Zion Control Center → Blog păstrează Grid/List, numărul articolelor, imaginea, categoria, data, autorul, timpul de citire, rezumatul, recomandările, distribuirea și comentariile. Override-urile per articol se rezolvă în rendererul existent; nu sunt adăugate opțiuni duplicate. Numărul nativ WordPress este respectat când setarea Zion nu are override explicit.
- Articolul are hero antracit, copertă care păstrează imaginea întreagă, metadate și introducere doar dacă există excerpt explicit. Lectura folosește suprafața Paper și o coloană de maximum 48rem. Linkul „Începe lectura” sare peste hero; codul și tabelele au overflow local, iar iconurile SVG din paragrafe nu devin ilustrații uriașe.
- Comentariile folosesc formularul, endpointul, moderarea, autentificarea și consimțământul native WordPress. Tema schimbă prezentarea și etichetele; nu introduce altă persistență sau livrare de e-mail.

## Mișcare și accesibilitate

Rendererul comun `assets/blocks/continuum.js` desenează pagini în perspectivă și o diagramă legată de progres. Hero-ul se animă numai în viewport. Scena de lectură nu intră în schedulerul continuu: se redesenează la derulare, resize și schimbarea preferințelor, iar progresul HTML folosește un element `progress` etichetat. Pauza este comună scenelor paginii; reduced motion afișează geometrie statică. Există un singur scheduler, fără dependențe noi sau scroll-jacking.

Decorarea este adăugată la randarea blocurilor marcate. Fără JavaScript rămân textele și geometria de fallback, toate linkurile, căutarea, paginarea și formularul nativ de comentarii. Progresul este ascuns când JavaScript nu este disponibil. Culorile provin exclusiv din presetările `theme.json`.

## Conținut și compatibilitate

Nu există migrare a textelor sau URL-urilor. `wp_html_split()` separă tokenurile pentru normalizarea H1 din corp la H2 doar în blocul de conținut marcat al articolului. Zonele script/style/textarea/title și celelalte zone raw-text, comentariile, atributele, SVG-urile și codul escapate rămân intacte. Datele salvate nu sunt schimbate, iar în editor autorul vede în continuare conținutul original.

Titlurile și copertele protejate urmează limitele WordPress; fragmentele proprii nu expun corpul, coperta, recomandările sau progresul unui articol protejat. Schimbarea temei nu șterge articole, termeni, imagini, comentarii sau metadata. Furnizorii SEO își păstrează controlul asupra metadatelor.

## Verificare și livrare

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

Proiectul Playwright `editorial` rulează după storefront și înainte de controalele care schimbă setările. Folosește numai instanța locală de teste de pe portul 8917, cu articole, categorii, imagini existente demonstrative și date fixe. Creează exclusiv fixture-uri sintetice, restaurează configurația și dimensiunea paginii, apoi elimină exact fixture-urile create. Astfel, imaginile și interogările homepage-ului nu se schimbă în timpul testelor sale. Pentru iterații locale: `npx playwright test --project=editorial --no-deps --workers=1`.

Pe staging se instalează numai componentele, după backup SQL și arhiva versiunii anterioare. Snapshotul privat include 43 de înregistrări editoriale/CF7 cu metadate și termeni, comentariile, categoriile/etichetele, întreaga opțiune Zion, setările de citire și permalinkurile. Nu este necesară rescrierea vreunui articol sau a paginii Blog. Auditul de după instalare:

```sh
wp eval-file scripts/journal-audit.php /cale/privata/blog-continuum-before-1124.json
```

Scriptul este numai pentru citire, cere mediul `staging` și hostname-ul autorizat, refuză un snapshot din rădăcina publică și raportează numai numere și starea verificării. Backupurile și datele reale rămân în afara repository-ului. Revenirea restaurează componentele anterioare; nu se restaurează SQL peste schimbări independente. Rezultatele finale de staging și CI sunt urmărite în README. Producția nu este modificată.

Validarea datelor importate a identificat contoare de categorii/etichete incorecte: articolele aveau asignările corecte, dar categoriile populate erau ascunse de `hide_empty`. S-au folosit comenzile native `wp term recount category post_tag`, după backup. Au fost corectate **26 de contoare**. O comparație separată verifică identitatea tuturor celorlalte câmpuri ale termenilor, precum și conținutul, metadatele și asignările celor 43 de înregistrări. Snapshotul original rămâne intact; un al doilea snapshot privat înregistrează exclusiv contoarele recalculate, inclusiv în obiectele termenilor asociate înregistrărilor. Auditul strict final folosește acest al doilea snapshot.

Două imagini din articolul despre materiale aveau URL-uri către derivate absente: `PETG-benchy-1-1200x803.jpg` și `71SW7dj5DgL-1200x1200.jpg`. Originalele existau deja pe staging. Au fost refăcute numai fișierele lipsă, cu editorul nativ WordPress și dimensiunile exacte, fără suprascrierea originalelor, editarea articolelor sau schimbarea metadatelor atașamentelor. Auditul fișierelor confirmă **28 de imagini din conținut prezente, zero fișiere lipsă**. Pentru revenirea strictă la starea media anterioară se elimină numai aceste două derivate create în această livrare; backupul privat păstrează contoarele anterioare.

Theme/Core **1.1.24** sunt instalate din `ccce371`, cu invalidarea punctuală a cache-ului PHP al temei, inclusiv a patternurilor. Helperul autentificat s-a eliminat singur; verificarea finală confirmă absența lui. SHA-256 ale CSS/JS instalate corespund buildului local. Auditul final păstrează toate cele **43 de înregistrări**, zero comentarii, configurația Zion, citirea și permalinkurile. Blogul și toate cele patru articole au un H1 și zero overflow la **390/768/1024/1440 px**. Arhiva Materiale are două rezultate și categorie activă; căutarea `HT-PLA` returnează un articol cu `post_type=post`, iar căutarea fără rezultate afișează mesajul nativ. Meniul mobil, pauza și reduced motion sunt verificate, progresul ajunge la 100%. Homepage/About/Servicii/Contact păstrează 6/2/10/3 scene; catalogul nu încarcă rendererul sau CF7. Nu s-au trimis comentarii ori e-mailuri și nu s-a modificat producția.

Validare: build, WPCS, **46 teste PHPUnit / 394 aserțiuni**, integrare și migrare/rollback trecute. Cele **14 aserțiuni noi de integrare** includ restaurarea exactă a contextului titlu/autor/comentarii după cardurile asociate. **9/9 teste Playwright editoriale** trecute local în 55 s, cu opt referințe vizuale și teste la toate cele patru lățimi. Cele două scenarii Control Center adaptate au trecut local în 47,9 s; verifică metadatele articolului separat de cele ale cardurilor asociate și independența override-urilor. **CI final verde: 100/100 teste Playwright** în 9,1 minute pentru `6166c27`, [rularea 43299459462](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43299459462). Toate verificările de backend și build au trecut și în acel mediu curat.

Arhivele finale `build/zion-theme-1.1.24.zip` și `build/zion-core-1.1.24.zip` au antetele 1.1.24 și **119, respectiv 9 fișiere**. Fiecare intrare este verificată SHA-256 față de sursa locală. Arhivele nu includ teste, scripturi operaționale, configurări, surse CSS, dependențe de dezvoltare sau datele private de staging.
