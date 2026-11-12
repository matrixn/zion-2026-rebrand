# Zion Continuum pe Servicii · 1.1.22

Paginile rămân compoziții Gutenberg native. `includes/continuum.php` adaugă numai la randarea frontend scene decorative cu `aria-hidden`; nu scrie în baza de date. Editorul și răspunsurile REST păstrează blocurile originale. Geometria este desenată în Canvas 2D de motorul comun `assets/blocks/continuum.js`, încărcat o singură dată numai când o componentă Continuum este prezentă. Nu sunt adăugate dependențe de runtime.

## Scene și contexte

| Pagină | Scena principală |
| --- | --- |
| Servicii | Nucleu spațial și șapte module conectate, secțiune luminoasă și impulsuri |
| Printare 3D | Piesă stratificată, traseu și duză de fabricație |
| Proiectare 3D | Carcasă parametrică explodată, puncte de control și cote |
| Software personalizat | Panouri de cod suprapuse și flux de execuție |
| Platforme SaaS | Nucleu comun cu stive pentru echipe distincte |
| Pluginuri și integrări | Conectori API și pachete între sisteme |
| Aplicații Windows/Linux | Ecran proiectat, terminal și legături locale |
| Integrări AI | Rutare pe straturi până la validarea umană |

Directorul are zece scene: hero, șapte miniaturi pe carduri, proces și CTA. Paginile individuale au câte trei: hero, proces și CTA. Miniaturile refolosesc aceeași geometrie contextuală și pornesc la hover sau focus pe legătura nativă. Nu primesc butoane decorative și nu schimbă ordinea de navigare. Procesul preia numărul real al cardurilor native, păstrând toate textele vizibile și evidențiind etapa la scroll. Nu există scroll-jacking.

Contextul provine din clasa `zion-service--{slug}` pentru patternurile noi sau din slugul paginii interogate pentru conținutul salvat anterior. Maparea folosește definițiile existente `zion_theme_services()` și `zion_theme_service_paths()`. Nu modifică permalinks. Previzualizările acceptă legături relative sau ale aceluiași site, nu pagini externe cu nume asemănătoare.

## Mișcare, accesibilitate și performanță

- Un singur scheduler pentru pagină, maximum aproximativ 24 cadre/s pe mobil și 30 pe desktop; rezoluție Canvas limitată la 1× pe mobil și 1,5× pe desktop.
- `IntersectionObserver` suspendă scenele care nu sunt vizibile. Miniaturile neactivate nu sunt redesenate continuu. Documentele ascunse suspendă bucla.
- Pauza oricăreia dintre cele trei scene principale oprește întreaga pagină și fundalul CAD. Butoane native de minimum 44 px, focus vizibil și stare `aria-pressed` sincronizată.
- Preferința OS `prefers-reduced-motion` și opțiunea temei pentru mișcare redusă afișează desene statice și ascund butoanele inutile. Schimbarea preferinței se aplică în timp real.
- Fără JavaScript sau fără context Canvas rămân simbolurile și compoziția editorială originale. Textele, formularele și acțiunile WooCommerce nu depind de animație.
- Culorile provin din presetările `theme.json`; miniaturile și procesul preiau culoarea textului secțiunii pentru contrast pe Paper, antracit sau lime. CSS este limitat la `.zion-service-continuum`; cadrele hero folosesc `border-box` și sunt măsurate explicit în teste.

## Verificare și livrare

Comenzi din rădăcina proiectului, cu mediul wp-env pentru teste pornit:

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

Opt teste noi parcurg directorul și toate cele șapte destinații native. Sunt verificate cele patru lățimi, raportul Canvas, desenarea efectivă, cadrul hero complet, zero overflow, fallback, focus, pauză, suspendare offscreen și preferințele de mișcare. Cele 16 imagini de referință acoperă fiecare hero la 390 și 1440 px, în Chromium-ul Docker fixat de proiect. Integrarea verifică scenele, rutele canonice, blocurile native și păstrarea înregistrărilor și setărilor.

Staging: `https://zion-stage.on-forge.com`. Înainte de instalare se păstrează SQL, Theme/Core, configurația Zion și snapshotul paginilor, template-urilor, părților, navigațiilor, stilurilor globale și metadatelor în directorul privat `.zion-migration`, în afara rădăcinii publice. Actualizarea instalează cele două ZIP-uri dintr-un commit publicat imuabil. Nu se rulează seed sau migrare editorială. Verificarea compară toate înregistrările, metadatele și setările cu snapshotul. Producția nu este modificată.

Rezultatele finale de staging și CI se consemnează în README după executare. Revenirea acestei schimbări de prezentare înseamnă restaurarea componentelor arhivate și invalidarea punctuală a fișierelor PHP schimbate; nu este necesară restaurarea SQL pentru simpla dezactivare a decorațiilor.
