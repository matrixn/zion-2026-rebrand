# Descriere magazin Software · 1.1.40

Descrierea din `/magazin-software/` moștenea `max-width: 65ch` de la paragrafele editoriale Software. Pe live, ocupa aproximativ **651 px** dintr-un container de **1320 px**.

Regula dedicată `.zion-software-content > .zion-store-intro` folosește `width: 100%`, `max-width: none` și `box-sizing: border-box`. Descrierea ocupă acum întregul container, inclusiv paddingul și bordura, fără extindere în afara lui. Celelalte paragrafe își păstrează limita editorială. Sursa și CSS-ul compilat sunt sincronizate; nu există JavaScript nou sau schimbări de conținut.

- [x] Build/package, WPCS și **61 teste PHPUnit / 436 aserțiuni** trecute.
- [x] Integrare catalog trecută.
- [x] **4 scenarii Playwright** pentru ambele cataloage, la 390/768/1024/1440 px; introducerea Software este egală cu lățimea părintelui, fără overflow.
- [x] Theme/Core **1.1.40** instalate prin WordPress și verificate live la aceleași lățimi. Pe desktop: **1320 / 1320 px**.
- [x] **150 de fișiere distribuite** identice cu ZIP-urile. Cele șapte verificări ale paginii Software, configurațiilor și fișierelor Sameday confirmă că au rămas intacte.

Backup privat al componentelor 1.1.39: `/home/forge/.zion-deploy/software-intro-1.1.40-20261114T141234Z`. Pentru rollback se restaurează numai Theme/Core, fără înlocuirea bazei de date. Cache-ul invalidat este exclusiv cel dedicat zion3d.ro.

Arhive: `build/zion-theme-1.1.40.zip` și `build/zion-core-1.1.40.zip`. Core primește numai actualizarea antetului de versiune. Capturi, măsurători și raport de integritate: `build/software-intro-1.1.40/`, excluse din Git. QA live este autentificat; modul Coming soon existent nu a fost modificat.
