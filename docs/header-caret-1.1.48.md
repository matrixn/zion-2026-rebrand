# Cursor terminal în bara de noutăți · 1.1.48

Cursorul din 1.1.47 era un pseudo-element inline cu lățime și margine proprii. La completarea unui titlu care umplea exact lățimea disponibilă, acesta ajungea pe rândul următor.

## Corecție

- Textul animat și cursorul sunt două noduri distincte. Scrierea actualizează numai nodul text, păstrând ancora cursorului.
- Ancora are lățime și înălțime zero, pe aceeași linie de bază cu ultimul caracter. Cursorul vizibil este poziționat absolut în dreptul ei, într-un spațiu rezervat de `.5em`; nu consumă lățime în fluxul textului.
- În timpul scrierii cursorul este continuu. După completarea titlului, o animație CSS cu perioadă de **1 secundă**, în trepte, alternează vizibilitatea la jumătatea ciclului.
- Clipirea se oprește la pauza explicită, la închiderea barei și pentru `prefers-reduced-motion`. În modul de mișcare redusă cursorul rămâne vizibil și static.
- Cursorul este decorativ și ascuns cititoarelor de ecran; numele accesibil al linkului rămâne complet. Nu există timer suplimentar pentru clipire.
- Se păstrează selecția articolelor/produselor, toate linkurile și cheia preferinței `zion-header-updates-v1`.

Implementare: `assets/blocks/announcements.js`, `assets/src/zion.css` și stylesheet-ul generat `assets/dist/zion.css`. Zion Core primește numai antetul 1.1.48 pentru sincronizarea arhivelor.

## Verificări

- [x] Build CSS și WPCS.
- [x] PHPUnit: **61 teste / 436 aserțiuni**.
- [x] Integrare WordPress/WooCommerce: **259 verificări**.
- [x] **27/27 teste Playwright** în 2,3 minute, în Chromium-ul oficial folosit de CI: aliniere față de ultimul caracter în timpul și după scriere, cele patru titluri la toate cele patru lățimi, faze vizibilă/invizibilă ale clipirii, pauză și mișcare redusă, persistență, tastatură, no-JS și regresii de navigare și pagini de produs.
- [x] Cele patru comparații vizuale ale paginilor de produs au trecut fără regenerarea referințelor sau modificarea toleranței.
- [x] Instalare și verificare live, ZIP-uri validate, README și raport actualizate.

## Livrare

Arhive: `build/zion-theme-1.1.48.zip` și `build/zion-core-1.1.48.zip`, CRC valid și conținut identic cu sursele distribuite. Inventar SHA-256 al celor **167 fișiere**: `d86a9374aaa130b2853c0aca4abb33786650f2fb257a6ad751d4649af6ef3f5d`.

## Verificare live · 23 noiembrie 2026

- [x] Inventar inițial 1.1.47 verificat; backup privat al Theme/Core și Headerului în `/home/forge/.zion-deploy/caret-1.1.48-20261123T002157Z/`.
- [x] Zion Core și Zion Theme **1.1.48** instalate prin înlocuirea nativă WordPress. Toate cele **167 fișiere instalate** corespund inventarului arhivelor.
- [x] Headerul salvat, conținutul editorial și setările protejate au hash-uri identice înainte și după instalare. Indexarea rămâne activă; nu s-au modificat setări comerciale.
- [x] Invalidat exclusiv cache-ul Nginx dedicat zion3d.ro: **34 fișiere temporare**. GET anonim pe homepage, cele două magazine și articolul LovePaint: **HTTP 200**, scriptul 1.1.48 prezent și vechiul anunț despre plăți absent.
- [x] **16 măsurători live**: câte patru titluri la **390/768/1024/1440 px**, folosind geometria ultimului caracter prin DOM Range. Ancora cursorului este aliniată cu marginea ultimului caracter și cu aceeași linie de bază; cursorul vizibil încape în spațiul rezervat. Zero overflow orizontal.
- [x] Clipire naturală confirmată în tabul Chrome activ: `opacity=1` și `opacity=0`, animație de **1 secundă**. Stilurile au fost citite direct din Chrome pentru a evita folosirea unui cadru CSS memorat în captura DOM a automatizării.
- [x] La pauză și pentru mișcare redusă: `animation-name=none`, `opacity=1`; cursorul rămâne vizibil și static.
- [x] X închide panoul la înălțime zero, iar clopoțelul îl redeschide. Nicio eroare JavaScript capturată în tabul verificat.
- [x] La final au fost eliminate override-urile de viewport și mișcare redusă; homepage-ul rămâne deschis cu animația pornită. Terminalul web a fost închis.

Capturi: `build/header-updates-1.1.48/live/mobile-caret.jpg` și `desktop-caret.jpg`. Cursorul a fost oprit temporar prin controlul de pauză pentru capturarea clară a poziției; clipirea a fost testată separat și restabilită înainte de predare. Raportul local este `caret-qa.json`; inventarul și raportul serverului sunt păstrate în backupul privat.

Rollbackul reinstalează numai componentele 1.1.47 din backup, urmat de invalidarea cache-ului dedicat. Această corecție nu necesită restaurarea bazei de date.
