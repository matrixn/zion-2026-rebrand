# Banner de migrare · conținut 1.0.0

Publicat pe **zion3d.ro** la 14 noiembrie 2026, la cererea explicită a utilizatorului. Perioada afișată este **14–15 octombrie 2026**, exact perioada solicitată; nu a fost schimbată automat în noiembrie și nu există expirare automată.

## Mesaj și implementare

> **Site în migrare · 14–15 octombrie 2026.** Plățile online sunt momentan indisponibile. Ne cerem scuze pentru disconfort. Pentru comenzi, scrie-ne pe WhatsApp.

Buton: **Comandă pe WhatsApp ↗**, către `https://wa.me/40770675597`, același număr confirmat în footerul public. Nu se trimite automat niciun mesaj.

Sursa reutilizabilă: `config/announcements/migration-2026-10.html`. Este un bloc nativ **HTML personalizat**, adăugat înaintea conținutului existent în Template Part **Header**, fără schimbarea meniurilor, a logo-ului sau a configurației WooCommerce. Headerul existent continuă să folosească pattern-ul nativ `zion-theme/header`; nu a fost copiat rendererul său.

Bannerul este static, într-un `<aside>` cu nume accesibil, text de 13 px, flex wrap și CTA cu înălțime minimă de 44 px. Folosește exclusiv tokenurile native `theme.json` pentru culori și spațiere, cu stiluri locale blocului. Fără JavaScript, animații, biblioteci sau CSS global nou. Afișarea anunțului nu modifică posibilitatea reală de cumpărare, gateway-urile, checkoutul sau indexarea.

Conținutul a fost publicat prin API-urile WordPress, cu verificarea administratorului, a domeniului live și a temei active. Nicio modificare de cod runtime în Theme/Core și niciun upgrade implicit al componentelor live.

## Administrare și rollback

În Zion Theme → Zion Studio → componentele/headerul din Site Editor, se poate edita sau elimina blocul **HTML personalizat** care conține clasa `zion-migration-announcement`. Nu șterge restul headerului.

Înainte de editare, configurația originală a Template Part-ului a fost salvată privat pe server:

`/home/forge/.zion-deploy/migration-notice-20261114T104637Z/header-before.json`

Înainte nu exista override de Header în baza de date; era folosit fișierul temei. Publicarea a creat `wp_template_part` **900088**. Conținutul anterior este păstrat integral după banner, verificat la salvare.

Dacă acel Header nu a fost modificat ulterior, revenirea override-ului la original restaurează headerul din temă. Dacă au apărut alte editări, elimină numai blocul bannerului și păstrează restul modificărilor. După orice schimbare, invalidează cache-ul dedicat site-ului pentru a nu păstra anunțul în HTML-ul vizitatorilor anonimi.

Pentru propagare au fost eliminate **51 de fișiere temporare**, fără erori, numai din cache-ul dedicat `/var/cache/nginx/zion3d`, cu verificarea căii și a zonei `ZION3D`. Nu s-au modificat Nginx sau cache-urile altor aplicații. Fișierele de backup și datele comerciale nu au fost șterse. Stagingul nu a fost modificat.

## Verificări

- [x] Un singur banner, text integral și destinație WhatsApp corectă.
- [x] **390 / 768 / 1024 / 1440 px**, fără overflow orizontal. Înălțime banner: **135,5 / 116 / 65 / 65 px**; CTA **44 px** la toate dimensiunile.
- [x] Meniul mobil se deschide și se închide cu bannerul prezent.
- [x] GET anonim fără cookie-uri: homepage, Magazin 3D, Magazin Software, Servicii, Contact și coș **200**, cu anunțul în HTML, inclusiv răspunsul cache HIT al homepage-ului. Checkoutul cu coș gol redirecționează normal la coș, unde anunțul este prezent; nu s-au adăugat produse și nu s-a plasat comandă.
- [x] Build/package, WPCS, **61 teste PHPUnit / 436 aserțiuni**, integrare catalog trecute.
- [x] **2/2 regresii Playwright** în instanța locală de teste: submeniu desktop accesibil la tastatură și deschidere/închidere meniu mobil. Verificările responsive ale noului banner au fost efectuate separat în browser pe live.

Dovezi locale: `build/migration-notice-2026-11-14/desktop.jpg`, `mobile.jpg`, `responsive.json`. Backupul și rezultatul publicării rămân în directorul privat al serverului, în afara repository-ului.

## Arhive

- `build/zion-migration-announcement-1.0.0.zip`: blocul HTML și acest ghid. **Arhivă de conținut**, nu plugin instalabil; pentru reutilizare se inserează blocul în Header și se adaptează textul, perioada și contactul.
- `build/zion-theme-1.1.31.zip` și `build/zion-core-1.1.31.zip`: arhivele WordPress sursă regenerate prin `npm run package`. Nu conțin configurarea specifică a bazei de date live și nu înlocuiesc aplicarea blocului de anunț.
