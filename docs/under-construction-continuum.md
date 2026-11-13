# Under Construction — Zion Continuum

Livrare 2026-11-13: conținut **1.0.0**, pachete Theme/Core **1.1.31**. Pagina a fost aplicată în pluginul deja activ pe **zion3d.ro**, conform cererii explicite. Nu reprezintă migrarea temei de staging în producție.

## Design și implementare

Sursele sunt `themes/zion-theme/maintenance/continuum.html`, `.css` și `.js`. Un nucleu geometric este construit din 11 secțiuni hexagonale, cu deformare discretă, proiecție în perspectivă, lumini care parcurg perimetrele și plan CAD. Pointerul adaugă perspectivă pe dispozitive cu mouse; derularea rămâne nativă. Nu există countdown, progres inventat, telemetrie proprie sau biblioteci externe.

Paleta negru/antracit/lime este derivată din `theme.json`; buildul refuză divergențele celor șase tokenuri de culoare. CSS-ul este dedicat documentului autonom și claselor Zion; nu este introdus în stylesheetul global al magazinului. Canvas folosește aceleași tokenuri. Logo-ul existent este servit de zion3d.ro, iar numărul public și WhatsApp au fost verificate din pagina actuală a site-ului.

Un singur `requestAnimationFrame`, maximum 30 FPS desktop / 24 FPS pentru scena mobilă și DPR maximum 1,75. Scenele se opresc în afara viewportului și când documentul este ascuns. Pauza funcționează cu tastatura; `prefers-reduced-motion` este respectat la încărcare și la schimbarea preferinței. SVG-ul asigură ilustrația fără JS/Canvas. Niciun conținut sau contact nu depinde de animație.

## Aplicare și rollback

Backupurile private înainte/după sunt în afara repository-ului, în directorul local `.zion-private/under-construction-2026-11-13`. Nu conțin cookie-uri, nonce-uri sau credențiale. Configurația a fost citită din formularul WordPress și scrisă folosind editorul și butonul nativ de salvare.

Comparația confirmă schimbarea exclusivă a celor cinci valori din `zion_uc_settings`: `title`, `description`, `html`, `css`, `js`. Au fost păstrate: activarea, HTTP 503, Retry-After 3600, noindex, linkul de autentificare și opțiunea de păstrare a datelor la dezinstalare. Nu au fost rescrise pagini, produse, prețuri, comenzi sau setări comerciale. Theme/Core 1.1.31 sunt pachete locale de livrare; nu au fost instalate pe producție.

Pentru restaurare, intră în **Setări → Under Construction**, restaurează cele cinci câmpuri din `settings-before.json` și salvează. Nu opri mentenanța decât atunci când lansarea este aprobată. Pentru reutilizare, arhiva `zion-continuum-maintenance-1.0.0.zip` conține cele trei surse și instrucțiunile. Este un pachet de conținut, nu un plugin instalabil.

Widgetul WhatsApp flotant duplicat este ascuns numai în acest document; linkul principal folosește același număr. Interfața de consimțământ și mecanismele de recuperare/autentificare rămân disponibile. Nu au fost trimise mesaje sau efectuate comenzi/teste de plată.

## Verificări

- Build și WPCS trecute; PHPUnit **61 teste, 436 aserțiuni** și **7 teste Python** trecute.
- Integrare catalog, setări, editor, SEO și localizare trecute; migrare/dry-run/rollback și HPOS/permisiuni de descărcare trecute pe datele sintetice din instanța tests.
- **6/6** teste noi: patru lățimi, pauză/reluare, schimbarea dinamică a mișcării reduse, fallback fără JavaScript și contacte care nu inițiază cereri singure.
- **8/8** repetări paralele ale testului de mișcare. Prima suită completă a avut un timeout la schimbarea preferinței de mișcare redusă, în timpul unei alte rulări simultane pe același browser de test. Testul a trecut separat; suita completă a fost reluată fără alt proces de test simultan: **138/138 teste trecute în 21,4 minute**, cu doi workers.
- Live anonim: **390, 768, 1024, 1440 px**, zero overflow, un H1, logo încărcat, scenă inițializată. Pauza produce `aria-pressed=true` și mod static; reluarea revine la mod animat.
- Homepage și `/printare-3d/`: **503**, `Retry-After: 3600`, `X-Robots-Tag: noindex, nofollow, noarchive`, `Cache-Control: no-store` și lipsa servirii din cache în cererile verificate. `/wp-login.php`: **200**, formular prezent. Sesiunea administratorului vede site-ul normal.
- Conținutul HTML/CSS/JS salvat este identic sursei; răspunsul HTTP conține integral cele trei surse, după normalizarea LF/CRLF. Arhivele au **137 / 12 / 4 intrări**, fără intrări corupte și identice surselor.

Capturile reale desktop și mobil sunt în `build/maintenance-qa/`, iar verificările HTTP și de integritate sunt în directorul privat. Buildul generează și `build/zion-continuum-maintenance-1.0.0.html` pentru previzualizare locală. Rulare locală: `python -m http.server 8941 --bind 127.0.0.1 --directory build`.

## Limită observată în producție

Pluginul existent execută hook-urile globale `wp_head` și `wp_footer`. Acestea încarcă și integrarea veche Elementor/XStore, CAPTCHA și widgeturi ale altor pluginuri. Consola live a raportat **`elementorFrontendConfig is not defined`** din `plugins/elementor/assets/js/frontend.min.js?ver=4.3.6`; această eroare nu este în scriptul Continuum și nu împiedică noua scenă/contactele. Nu declarăm „zero erori JS” pentru întregul document de producție. Remedierea trebuie făcută separat la încărcarea asseturilor legacy sau în pluginul de mentenanță; nu au fost dezactivate pluginuri globale și nu au fost introduse obiecte globale false pentru a ascunde eroarea.

Verificările HTTP confirmă răspunsurile observate, nu toate nodurile/cache-urile CDN, toate browserele sau eventuale sesiuni deja deschise înainte de mentenanță. Fișierele statice publice rămân accesibile potrivit comportamentului pluginului existent.
