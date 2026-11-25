# Header fără spațiu gol · 1.1.49

## Cauză și corecție

Reproducere live la 390 px: după scroll și închiderea noutăților, panoul avea înălțimea zero, dar meniul rămânea la 46 px de marginea superioară. WordPress mută bara de administrare din poziție fixă în poziție absolută la maximum 600 px. Headerul sticky folosea în continuare înălțimea acelei bare, deși aceasta ieșise din ecran.

`--zion-header-inset` este acum zero implicit și primește înălțimea nativă WordPress numai pe paginile cu clasa `admin-bar`. Pentru headerul sticky pe mobil, insetul revine la zero. La începutul paginii, spațiul normal rezervat de WordPress permite afișarea barei reale; după scroll nu rămâne un spațiu suplimentar. Pe tabletă și desktop rămâne offsetul pentru bara fixă de administrare.

Implementare în `themes/zion-theme/assets/src/zion.css`, cu stylesheet generat în `assets/dist/zion.css`. Nu sunt necesare JavaScript nou sau schimbări în persistența preferinței notificărilor. Core primește numai antetul 1.1.49 pentru sincronizarea distribuției.

## Verificări

- [x] Testele noi de 390 px au eșuat înainte de corecție cu valoarea 46 în loc de zero, atât pentru administrator, cât și pentru vizitator cu variabila WordPress prezentă.
- [x] Matrice de regresie la 320/390/600/601/768/782/783/1024/1440 px, pentru vizitatori și administratori. Testele folosesc exclusiv instanța locală de teste 8917 și date sintetice.
- [x] Build CSS și WPCS.
- [x] PHPUnit: **61 teste / 436 aserțiuni**. Integrare WordPress/WooCommerce: **259 verificări**.
- [x] **45/45 teste Playwright** în 2,4 minute: noutăți, cursor, persistență, tastatură, no-JS, matrice sticky și regresii ale navigării și paginilor de produs.
- [x] Cele patru comparații vizuale existente au trecut fără regenerarea referințelor.
- [x] Instalare live, compararea fișierelor și verificări responsive, detaliate mai jos.

## Backup și rollback

Backup privat al componentelor 1.1.48 și al stării protejate: `/home/forge/.zion-deploy/header-gap-1.1.49-20261125T074650Z/`. Rollbackul reinstalează numai componentele 1.1.48 și invalidează cache-ul dedicat zion3d.ro. Corecția nu necesită restaurarea bazei de date.

## Verificare live · 25 noiembrie 2026

- [x] Theme/Core **1.1.49** instalate prin actualizarea nativă WordPress. **167 fișiere** identice cu sursele distribuite în ZIP-uri; CRC-urile arhivelor sunt valide.
- [x] Hash-urile setărilor protejate, conținutului editorial și headerului salvat coincid înainte și după instalare. Indexarea rămâne activă.
- [x] Invalidat numai cache-ul Nginx al zion3d.ro: **23 fișiere temporare**. GET anonim pe homepage, magazinul 3D, magazinul Software și articolul LovePaint: **HTTP 200**, asseturi 1.1.49 și vechea notificare despre plăți absentă. Feedul conține două articole și două produse.
- [x] Geometrie verificată în Chrome autentificat, cu viewport responsive la **320/390/600/601/768/782/783/1024/1440 px**: panoul închis are înălțimea zero, headerul are exact înălțimea meniului, fără overflow orizontal. După scroll, meniul este la zero până la 600 px inclusiv; la 601–782 px este imediat sub bara reală WordPress de 46 px; de la 783 px, sub bara reală de 32 px.
- [x] X și clopoțelul închid complet panoul, iar clopoțelul îl redeschide pe mobil și desktop. Măsurători după încheierea animației: panou 97 px deschis pe mobil și 53 px pe desktop, zero închis. Preferința închisă persistă la navigare către homepage și la reîncărcare.
- [x] La începutul homepage-ului mobil, cei 46 px conțin bara WordPress vizibilă. După scroll, aceasta dispare, iar meniul se lipește de marginea ecranului fără spațiu gol.
- [x] Nicio eroare JavaScript capturată în tabul verificat. Override-urile de viewport au fost eliminate, preferința inițială deschisă a fost restabilită, iar terminalul web a fost închis.

Acoperirea anonimă în browser provine din fixture-urile locale; verificarea live în Chrome folosește sesiunea autentificată existentă. Verificările responsive sunt emulări de viewport, nu teste pe telefoane fizice.

## Arhive și dovezi

- `build/zion-theme-1.1.49.zip`
- `build/zion-core-1.1.49.zip`
- Inventar SHA-256 comun: `be062f38d0e55737b9d5dcf9d37403ed4066963f8b6e34c8dc06510e4cc8c087`.
- `build/header-updates-1.1.49/live/qa.json`: geometrie înainte/după și persistență.
- Capturi `before-mobile-closed.jpg`, `after-mobile-closed.jpg`, `after-desktop-closed.jpg` în același director.
- Loguri locale: `build/gap-before.log` și `build/gap-final-*.log`. Raportul comparativ al instalării este în backupul privat de pe server.
