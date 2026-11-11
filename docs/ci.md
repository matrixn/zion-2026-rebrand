# CI și browser reproductibil

Workflow-ul **Zion checks** verifică buildul și concordanța CSS generat, WPCS, PHPUnit, integrarea WordPress/WooCommerce, migrarea/rollback-ul și Playwright. Rulează la schimbări de cod prin push/PR și manual din Actions. Documentația Markdown și `docs/` sunt excluse din declanșarea automată. Un commit nou pe aceeași ramură anulează rularea depășită; erorile testelor nu sunt ignorate.

## Chromium fără instalare APT pe runner

Eroarea raportată în noiembrie 2026 era `Temporary failure resolving 'azure.archive.ubuntu.com'` în `playwright install --with-deps chromium`, înaintea testelor. `scripts/ci-browser.sh` folosește `mcr.microsoft.com/playwright:v<versiunea instalată>-jammy`, cu browser și biblioteci de sistem preinstalate. Versiunea este citită din pachetul instalat prin `npm ci`, fixat în lockfile; nu folosește `latest`. [Documentația oficială Playwright Docker](https://playwright.dev/docs/docker)

Node/npm și wp-env rulează pe host. Numai serverul de browser rulează în container, cu rețeaua host pentru acces la instanța de test de pe `localhost:8917`. Portul serverului este legat la `127.0.0.1:9347`; directorul proiectului este montat read-only. Containerul are nume unic pe rulare și este oprit inclusiv după eșec. Se păstrează trace-uri și capturi pentru testele eșuate.

Testele care descarcă exporturi citesc streamul Playwright, fără să depindă de calea de fișier de pe serverul browserului. Nu se execută pe datele staging/producție: testele de administrare acceptă numai instanța locală izolată.

Seed-ul local fixează datele articolului demonstrativ, ale articolului inițial WordPress `hello-world` și ale recenziei demo la 10 noiembrie 2026. Capturile nu depind de ziua instalării runnerului. Guard-ul `WP_ENVIRONMENT_TYPE=local` păstrează acest seed în afara stagingului și producției. Testul homepage așteaptă și randarea imaginilor decodate înainte de captură.

## Reproducere locală pe Linux / WSL

După instalarea dependențelor și pornirea Docker/wp-env:

```bash
npm ci
composer install
npm run build
composer lint
composer test
npm run env:start
npm run demo:test
npm run test:integration
npm run test:migration
bash scripts/ci-browser.sh start
CI=1 ZION_PLAYWRIGHT_WS_ENDPOINT=ws://127.0.0.1:9347 npm run test:e2e
bash scripts/ci-browser.sh stop
```

Rulează oprirea și dacă testele eșuează. Browserul local rămâne disponibil prin `npm run test:e2e` fără variabila endpoint, dar capturile de referință sunt generate în imaginea oficială, pentru aceleași fonturi și aceeași randare în CI. Pentru modificări vizuale intenționate, rulează `npm run test:visual:update` cu același endpoint, inspectează imaginile și rulează apoi suita normală. Nu modifica pragurile ca să ascunzi diferențe.

## Livrare

Actualizează README, păstrează versiunile Theme/Core sincronizate, rulează `npm run package` pentru ambele ZIP-uri și publică commitul pe `main`. Verifică rezultatul noii rulări Actions. O rulare veche rămâne marcată failed în istoric; nu este necesară relansarea ei cu workflow-ul vechi. Setările de notificări ale contului GitHub nu sunt modificate de proiect.
