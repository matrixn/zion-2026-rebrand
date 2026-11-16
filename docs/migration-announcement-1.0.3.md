# Plăți în testare · conținut 1.0.3

La cererea utilizatorului din **16 noiembrie 2026**, notificarea nu mai anunță indisponibilitatea plăților. Mesajul public este:

> **Site în migrare · 14–20 noiembrie 2026.** Plățile online sunt active, în curs de testare. Dacă întâmpini dificultăți la finalizarea plății, contactează-ne pe WhatsApp.

Sursa este `config/announcements/migration-2026-11.html`, iar copia publică este în blocul HTML al părții de șablon Header. Perioada, linkul WhatsApp și interfața notificării rămân aceleași. Nu există expirare automată. Cheia preferinței este calculată din conținut: noul mesaj pornește deschis, apoi alegerea vizitatorului este păstrată între pagini și la refresh.

Formularea descrie starea comunicată de proprietarul site-ului. Această intervenție nu configurează gateway-uri și nu verifică încasarea banilor. Nu a fost efectuată nicio tranzacție reală.

## Publicare și verificare

- [x] Header **900088** actualizat prin API-ul nativ `wp_update_post`, folosind administratorul WP-CLI și verificarea capabilității `edit_post`. Precondiții: tipul corect al părții de șablon, o singură apariție a textului anterior și perioada așteptată.
- [x] Backup privat înainte de editare: `/home/forge/.zion-deploy/notice-payment-testing-20261116-095047Z/header-before.json`. Verificarea după salvare confirmă exact schimbarea textului, o singură apariție a mesajului nou și păstrarea referinței native la pattern.
- [x] Hashurile setărilor protejate sunt identice înainte și după: Zion Theme, Zion Privacy, pluginurile active, vizibilitatea în motoarele de căutare, URL-urile site-ului, moneda și țara implicită WooCommerce.
- [x] Invalidat exclusiv cache-ul Nginx al zion3d.ro: **54 de fișiere temporare**. GET anonim pe `/`, `/magazin-3d/`, `/magazin-software/`, `/contact/` și `/cos/`: toate **200**, cu noul mesaj și fără textul anterior despre indisponibilitate.
- [x] Verificare live la **390/768/1024/1440 px**: text complet, fără overflow orizontal, același clopoțel între cont și coș, închidere și redeschidere funcționale. Starea închisă s-a păstrat la navigarea de pe homepage în Magazin 3D.
- [x] Build, WPCS și **61 teste PHPUnit / 436 aserțiuni** trecute. Integrarea WordPress și cele **12 teste Playwright** au trecut după pornirea mediului local de teste, oprit inițial. Verificările automate folosesc instanța de teste cu date sintetice.
- [x] `npm run package`: ambele arhive WordPress **1.1.42** regenerate. Integritatea CRC și conținutul arhivelor verificate; cele **152 de fișiere** distribuite sunt identice cu versiunea precedentă. Nu este necesară reinstalarea Theme/Core.
- [x] Arhiva de conținut **1.0.3**, cu fragmentul HTML și acest raport, generată și verificată pentru mesajul nou și absența celui anterior.

Arhive locale:

- `build/zion-migration-announcement-1.0.3.zip` — arhivă de conținut, nu plugin instalabil.
- `build/zion-theme-1.1.42.zip` — tema instalabilă, fără datele din baza de date live.
- `build/zion-core-1.1.42.zip` — pluginul instalabil, fără date comerciale.

Dovada vizuală mobilă este în `build/migration-notice-1.0.3/mobile.jpg`, exclusă din Git. Nu sunt modificate checkoutul, taxele, livrarea, comenzile sau configurarea plăților. Pentru rollback se inversează numai schimbarea mesajului, comparând cu backupul și păstrând eventualele editări ulterioare ale Headerului.
