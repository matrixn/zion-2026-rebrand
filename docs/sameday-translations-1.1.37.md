# Sameday / easybox · traduceri 1.1.37

## Implementare

Tema completează numai traducerile lipsă prin filtrul nativ `gettext_samedaycourier-shipping`, în `includes/commerce-translations.php`.

| Text original | Traducere |
| --- | --- |
| Show Locations Map | Alege un punct de ridicare pe hartă |
| Ship to | Livrare la |
| Please choose your EasyBox Locker ! | Alege un locker easybox pentru livrare. |
| Select easyBox | Alege un locker easybox |
| Please wait for easyBox list to be populated | Se încarcă lista punctelor easybox… |

Contractul a fost verificat în sursa pluginului instalat: `CheckoutBlocksIntegration::get_script_data()` traduce etichetele înainte de expunerea configurației către scriptul propriu Sameday. Șabloanele clasice și validarea serverului folosesc același domeniu gettext. Nu există înlocuire de DOM, observer suplimentar sau script nou în Zion.

Se păstrează traducerile instalate, mesajele neenumerate, administrarea și limbile diferite de `ro_RO` / `en_US`. Pentru localele implicit engleze, fallbackul românesc urmează convenția existentă a interfeței comerciale Zion. Numele și adresele lockerelor, identificatorii, tarifele, selecția/persistența și validarea rămân ale furnizorului. Core primește numai sincronizarea antetului de versiune.

Referință pentru contractul public al furnizorului: [CheckoutBlocksIntegration](https://github.com/sameday-courier/woocommerce-plugin/blob/master/classes/Infrastructure/Woo/Blocks/CheckoutBlocksIntegration.php). Sursa instalată pe live a fost inspectată separat înainte de aplicare.

## Validare

- [x] WPCS și build/package trecute.
- [x] PHPUnit: **61 teste / 436 aserțiuni** trecute.
- [x] Integrare catalog trecută, inclusiv **14 verificări Sameday**: cinci mesaje în două locale, traduceri existente/domenii independente, mesaje necunoscute, altă limbă și administrare.
- [x] Playwright în `localhost:8917`: regresia livrării, selecție cu mouse/tastatură, totaluri native și posibilitatea alegerii facturării separate, trecută.
- [x] Checkout live autentificat, **390/768/1024/1440 px**, zero overflow, texte românești în buton, rezumat și mesajul nativ al lockerului.
- [x] Etichetele persistă după reîncărcarea checkoutului; schimbarea prin Ajax către livrarea la adresă elimină interfața easybox, iar revenirea la easybox afișează textele tot în română.

Mesajul de validare este tradus în configurația furnizorului; vizibilitatea lui rămâne controlată nativ de Sameday. Nu s-a apăsat butonul de plasare a comenzii și nu s-au introdus date de card. Vitrina este în modul Coming soon pentru vizitatorii anonimi; această setare nu este schimbată de livrare.

Coșul inițial era gol. Produsul adăugat temporar pentru QA a fost eliminat la final, iar metoda de livrare la adresă a fost restabilită. Nu au fost create comenzi.

Serverul era în reboot la începutul intervenției, confirmat de proprietar; instalarea s-a făcut după revenirea WordPress și Forge.

## Livrare și rollback

Backup privat al Theme/Core 1.1.36 și hashurilor configurației înainte de actualizare:

`/home/forge/.zion-deploy/sameday-1.1.37-20261114T131020Z`

Pachete WordPress 1.1.37:

- `build/zion-theme-1.1.37.zip`
- `build/zion-core-1.1.37.zip`

Actualizare prin instalatorul WordPress finalizată cu succes. Verificarea finală confirmă **149 de fișiere distribuite identice cu ZIP-urile**, zero diferențe, cele cinci hashuri/configurații păstrate și cele **623 de fișiere ale pluginului Sameday neschimbate**. Cache-ul invalidat este numai cel dedicat zion3d.ro.

Rollback: restaurează componentele Theme/Core din copia privată sau ZIP-urile 1.1.36, fără restaurarea bazei de date. Comenzile și datele comerciale curente rămân ale WooCommerce.

Dovezi de QA în directorul local ignorat `build/sameday-1.1.37/`; nici credențialele, nici datele de producție nu intră în Git sau în pachete.
