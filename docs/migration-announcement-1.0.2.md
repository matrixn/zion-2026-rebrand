# Notificare de plăți · conținut 1.0.2

Raport istoric: perioada de mai jos este păstrată, dar formularea despre indisponibilitatea plăților a fost înlocuită în [conținut 1.0.3](migration-announcement-1.0.3.md).

La cererea utilizatorului din **16 noiembrie 2026**, perioada notificării este prelungită de la 14–15 la **14–20 noiembrie 2026**, inclusiv ziua de 20.

> **Site în migrare · 14–20 noiembrie 2026.** Plățile online sunt momentan indisponibile. Ne cerem scuze pentru disconfort. Pentru comenzi, scrie-ne pe WhatsApp.

Sursa actuală este `config/announcements/migration-2026-11.html`. Textul public este în blocul HTML din partea de șablon Header. Interfața clopoțelului și panoul animat rămân cele din Theme/Core **1.1.42**. Nu există expirare automată a anunțului.

Cheia preferinței de afișare este calculată din conținutul notificării. După schimbarea perioadei, mesajul actualizat are o cheie nouă și este afișat inițial deschis; ulterior alegerea deschis/închis se păstrează normal între pagini.

## Livrare și verificare

- [x] Sursa de conținut actualizată; aceeași destinație WhatsApp.
- [x] Header **900088** actualizat prin `wp_update_post`, cu administratorul autentificat în WP-CLI, verificare `edit_post` și protecție față de modificări concurente. Inversarea exclusivă a perioadei reproduce exact conținutul anterior, inclusiv referința originală la pattern. Setările protejate au hash identic înainte și după modificare.
- [x] Site Editor a expandat patternul la salvarea inițială; actualizarea finală a păstrat structura din backup și a schimbat strict perioada. Nu a fost păstrată rescrierea automată a restului Headerului.
- [x] Live la **390/768/1024/1440 px**: noua perioadă, aceeași legătură WhatsApp, fără overflow orizontal. Clopoțelul închide și redeschide panoul. Cheia noului mesaj este `zion-notice-253a050cd35cea74`.
- [x] Invalidat exclusiv cache-ul Nginx dedicat zion3d.ro: **24 de fișiere temporare**. GET anonim pe homepage, cele două magazine, Contact și coș: **200**, cu noua perioadă și fără data veche în HTML. Controllerul rămâne **1.1.42**.
- [x] Build, WPCS, **61 teste PHPUnit / 436 aserțiuni**, integrare în WordPress local și **12 teste Playwright** trecute. Instanța locală de teste, oprită inițial, a fost pornită înaintea verificărilor de integrare și browser; nu au fost folosite date de producție în teste.
- [x] Arhiva de conținut **1.0.2** și ambele ZIP-uri WordPress **1.1.42** generate; integritatea CRC verificată. Conținutul componentelor WordPress coincide cu versiunea instalată anterior; nu este necesară reinstalarea lor.

Arhivele de livrare sunt `build/zion-migration-announcement-1.0.2.zip`, `build/zion-theme-1.1.42.zip` și `build/zion-core-1.1.42.zip`. Prima este o **arhivă de conținut**, nu un plugin instalabil. ZIP-urile Theme/Core nu includ datele din baza de date live și nu necesită reinstalare pentru această actualizare de text.

Backup privat: `/home/forge/.zion-deploy/notice-date-20261116-090932Z/header-before.json`. Pentru revenire se inversează doar perioada, păstrând orice alte editări ulterioare. Dovada vizuală și măsurătorile locale sunt în `build/migration-notice-1.0.2/desktop.jpg` și `responsive.json`, excluse din Git.
