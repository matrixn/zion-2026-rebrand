# Banner de migrare · conținut 1.0.1

Corecție cerută de utilizator la 14 noiembrie 2026: perioada activă a bannerului este **14–15 noiembrie 2026**.

> **Site în migrare · 14–15 noiembrie 2026.** Plățile online sunt momentan indisponibile. Ne cerem scuze pentru disconfort. Pentru comenzi, scrie-ne pe WhatsApp.

Sursa actuală: `config/announcements/migration-2026-11.html`. Pe live se înlocuiește exclusiv textul perioadei din Header; restul conținutului, stilurilor și linkul `https://wa.me/40770675597` se păstrează. Backup privat înainte de modificare, verificare a diferenței exacte și invalidarea exclusivă a cache-ului site-ului.

## Verificarea livrării

- [x] Header **900088** actualizat prin API WordPress, ca administrator: o singură înlocuire și verificare că inversarea exclusivă a datei reproduce exact conținutul anterior.
- [x] Backup privat: `/home/forge/.zion-deploy/migration-notice-date-20261114T112009Z/header-before.json`.
- [x] Cache dedicat site-ului invalidat: **11 fișiere temporare**, fără erori. Celelalte aplicații și stagingul nu au fost modificate.
- [x] Live la **390/768/1024/1440 px**: un singur banner, perioada noiembrie corectă, link WhatsApp păstrat, zero overflow orizontal.
- [x] GET anonim: homepage, cele două magazine, Contact și coș **200**, cu perioada corectată în HTML, inclusiv răspunsurile cache.
- [x] Build/package trecut; arhiva de conținut 1.0.1 și cele două ZIP-uri WordPress regenerate, integritatea ZIP verificată.

Dovadă locală: `build/migration-notice-1.0.1/desktop.jpg` și `responsive.json`. Validarea acestei corecții de text constă în build/package și verificările live de mai sus; verificările inițiale ale implementării rămân documentate în raportul 1.0.0.

Arhivă de conținut: `build/zion-migration-announcement-1.0.1.zip`, cu blocul HTML și acest ghid. Nu este un plugin instalabil. Pachetele WordPress sursă `build/zion-theme-1.1.31.zip` și `build/zion-core-1.1.31.zip` se regenerează prin `npm run package`; versiunea componentelor live nu se schimbă pentru această corecție de text.

Implementarea, verificările inițiale și eliminarea bannerului sunt documentate în [raportul 1.0.0](migration-announcement-1.0.0.md). Nu există expirare automată.
