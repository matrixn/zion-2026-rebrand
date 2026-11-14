# Migrare producție · 14 noiembrie 2026

Restaurarea integrală pe **https://zion3d.ro/** este finalizată. Utilizatorul a autorizat explicit înlocuirea datelor live cu backupul staging, inclusiv conturi, clienți și comenzi, apoi a acceptat termenii și golirea bazei la pasul final Duplicator. Stagingul și celelalte aplicații nu au fost modificate.

## Sursa și destinația

- Backup: `20261113_zion3dprint_ecb51931510e0ae07941_20261113192559_archive.daf`, **351.064.169 bytes**.
- SHA-256 local și server: `2bd7f7e8d453d31ee1818eb353be51e63864ee89bd5b22051a5cbf58618173f2`.
- Verificare cu clasele DupArchive ale instalatorului, izolată și fără bootstrap WordPress: **15.554 fișiere, 15.709 chunk-uri, zero erori CRC**.
- Upload prin **Duplicator Pro → Import Backups**, cu transfer fragmentat; nu a fost necesar FTP și nici publicarea manuală a fișierului installer PHP.
- Destinație exclusivă: `/home/forge/zion3d.ro/public`, baza `forge`, site Forge `3191917`, PHP 8.4.
- Acțiuni native: **Remove all files except addon sites and extract** și **Empty Database**. Setul public precedent a fost înlocuit cu cel din backup; directoarele de backup au fost mutate ulterior în afara document root.
- Instalate din backup: **Zion Theme / Zion Core 1.1.30**, WooCommerce **11.2.2**, Contact Form 7 **6.2.5**, Duplicator Pro **5.1.1**. Sunt publicate **28 pagini și 32 produse**. Elementor și XStore nu mai sunt în setul instalat.
- Repository-ul și ZIP-urile sursă rămân **1.1.31**: acea versiune adaugă materialele de mentenanță. Nu s-a pretins instalarea lor peste backupul 1.1.30 ales de utilizator.

## Backup și rollback

Înainte de înlocuire s-a creat un backup separat, privat, pe server:

`/home/forge/.zion-deploy/20261113T2200Z`

| Fișier | Bytes | SHA-256 |
| --- | ---: | --- |
| `production.sql` | 99.375.857 | `846857b02075ec35a0bef21f35a57e29b0bd3a35e57fc584e7b2e7cc6ee7eb7c` |
| `production-files.tar.gz` | 453.752.800 | `062c4e952c2bbdd22a84bc2d2b4fec23b84674ef1046b4a8680afea4c069583b` |

SQL-ul are footer de export complet, **139 tabele și 19 view-uri**. Arhiva gzip a trecut verificarea CRC și comparația SHA-256 pentru **39.452 fișiere**, fără diferențe de conținut față de live înainte de migrare. Au fost excluse copiile Duplicator, cache-urile și logurile; configurația WordPress este inclusă. Avertismentul tar pentru mtime-ul directorului `wp-content` a fost investigat prin verificarea tuturor fișierelor, nu ignorat.

Backupul nou Duplicator pornit din interfața veche a eșuat cu „Communication to AJAX is blocked”; copia SQL + fișiere verificată mai sus este copia de rollback. Fișierul experimental `production-verified.sql` nu este copia validată și **nu trebuie folosit**.

Rollbackul integral presupune fereastră de mentenanță, salvarea stării curente și a comenzilor apărute între timp, verificarea hashurilor, restaurarea fișierelor și a `production.sql` în aceeași destinație, apoi revenirea configurației Nginx și verificările HTTP/WooCommerce. **Nu s-a exersat o restaurare completă a acestui backup de producție**. Nu importa SQL-ul vechi peste comenzi noi fără un plan de păstrare a acestora.

## Configurație după restaurare

- [x] Instalatorul: **zero probleme** la General, Files, Database Migration, Search & Replace și Plugins; **56 tabele / 5.902 rânduri importate**, 616 celule actualizate la conversia URL-urilor.
- [x] `home` și `siteurl`: `https://zion3d.ro`; `WP_ENVIRONMENT_TYPE=production`, `DB_CHARSET=utf8mb4`, collation implicită; debug, afișarea și logarea debug dezactivate.
- [x] `blog_public=1` pe live. Stagingul păstrează politica separată noindex. Coșul și contul păstrează noindex pentru pagini utilitare.
- [x] WP-Cron nativ reactivat; nu s-a creat un job extern de cron.
- [x] La momentul restaurării, accesul extern a fost păstrat restrâns, cu allowlist WordPress.org. **Ulterior, la cererea explicită a utilizatorului, restricția de staging a fost eliminată de pe live pentru serviciile comerciale**: vezi [intervenția HTTP/WooPayments](live-http-2026-11-14.md).
- [x] Rewrite rules regenerate cu pluginurile active, inclusiv WooCommerce.
- [x] Nginx: `robots.txt` este trimis către WordPress; bypass cache pentru `/cos/` și `/contul-meu/`, alături de checkout și rutele sensibile existente. Cache key separat pentru acest deploy evită răspunsurile vechi din timpul instalării; nu s-au șters cache-urile altor aplicații.
- [x] Curățare nativă a instalatorului. Backupurile/importurile/logurile Duplicator rămase au fost mutate în `removed-public-backup-folders` sub directorul privat de rollback; DAF, installer și bootstrap log răspund public **404**.
- [x] Utilizatorul s-a autentificat manual după eșecul auto-login Duplicator. Nu a fost schimbată parola.

## Verificări efectuate

- **493 URL-uri / 138 pagini HTML**, crawl complet, anonim, fără formulare sau operațiuni comerciale. Zero probleme detectate de verificările automate pentru metadate/H1/OG/JSON-LD și zero ancore rupte.
- Cele cinci răspunsuri speciale: `sitemap_index.xml` **404** este o sondă nelinkuită, deoarece sitemapul activ este `/wp-sitemap.xml`; MakerWorld, Printables și X cookies au răspuns **403** crawlerului; endpointul Cloudflare email-protection apare brut cu **404**, dar pe Contact browserul îl decodează în `mailto:contact@zion3d.ro`, fără link protejat rămas.
- Homepage, `robots.txt` și sitemap nativ **200**, domeniu live corect, fără referință staging în verificările HTTP respective. Coș/checkout/cont: **BYPASS** cache; checkoutul cu coș gol redirecționează normal spre coș.
- **44 combinații pagină/lățime** la **390/768/1024/1440 px**, fără overflow orizontal: homepage, cele două magazine, Servicii, Printare 3D, Blog, Despre noi, Contact, Întrebări, produs variabil și checkout cu produs.
- Meniu mobil deschis, submeniul Servicii extins și închis. Sortare Ajax după preț: spinner vizibil, URL `?orderby=price`, produse reordonate de la 9 lei.
- Sesiune anonimă: adăugare produs simplu de 95 lei, navigare în coș, refresh cu produs păstrat, checkout cu rezumat și total corecte, România selectată prima și texte românești. Produsul de probă a fost eliminat; badge-ul a revenit la **0**. Nu s-a plasat comandă.
- PLA Basic Refill: culoarea Turquoise selectată, disponibilitate **2 în stoc**, preț **115 lei**, buton activ, fără navigare la alegerea variației.
- Fără mesaje JS warning/error în tabul public de verificare. Aceasta nu reprezintă verificarea tuturor logurilor PHP sau a tuturor integrărilor comerciale.
- Build/package, WPCS, **61 teste PHPUnit / 436 aserțiuni**, integrare catalog, migrare/rollback/HPOS pe date sintetice și **9 teste Python** trecute. Suita completă Playwright nu a fost relansată pentru această schimbare de documentație și crawler; verificările browser de producție de mai sus sunt distincte de testele locale.

Rapoarte locale: `build/deploy-2026-11-14/live-audit.json`, `live-audit.html`, `responsive-qa.json`, capturi homepage și checkout. Configurații, manifeste și dovezi private: `C:\Users\radut\Workspace\.zion-private\deploy-2026-11-13`. Datele și credențialele de producție nu sunt comise.

## Ce mai trebuie configurat

- [ ] **Plăți:** nicio metodă activă; checkoutul afișează explicit că nu este disponibilă o metodă de plată. Magazinul nu poate finaliza comenzi în această stare. Alegerea și configurarea gateway-urilor trebuie făcute separat.
- [ ] **Livrare și fiscalitate:** livrarea și taxele sunt activate în setările sursă, dar sunt **zero zone de livrare configurate**. Nu au fost inventate metode, tarife sau cote fiscale. Validarea comercială rămâne necesară.
- [ ] **E-mail/SMTP:** configurare ulterioară; testarea livrării a fost exclusă explicit de utilizator. Nu au fost trimise mesaje de test.
- [ ] **Duplicator:** notificarea `EncryptedSettingsReset` arată că setările criptate de storage au fost resetate la migrare. Reconfigurează destinația backupurilor și programările cu credențialele potrivite; nu presupune funcționarea vechiului SFTP.
- [ ] Teste reale/sandbox pentru plăți, transport, taxe, descărcări și eventuale abonamente; verificare Core Web Vitals în teren. Migrarea reușită nu este o certificare production-ready pentru aceste integrări.

## Audit live repetabil

```sh
python3 scripts/audit-launch.py https://zion3d.ro build/live-audit.json --live
python3 scripts/render-launch-report.py build/live-audit.json build/live-audit.html
```

`--live` permite numai originea HTTPS exactă `zion3d.ro`, după autorizarea operatorului. Fără flag se acceptă exclusiv stagingul. Porturile, credențialele, subdomeniile, căile și query-urile în originea de pornire sunt respinse; limitele de crawl, blocarea acțiunilor și excluderea ZionFood rămân active.
