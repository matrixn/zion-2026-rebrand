# Checkout Zion · 1.1.12

## Configurare

Deschide **Aspect → Zion Theme → Finalizare comandă**, alege designul din selector sau din butoanele vizuale și apasă **Salvează setările**. Preview-ul se actualizează imediat; salvarea Ajax are spinner, nonce, capabilitatea `edit_theme_options` și protecție la modificări concurente.

| Design | Prezentare |
| --- | --- |
| Atelier | Formular aerisit, rezumat lateral pe desktop |
| Studio Editorial | Secțiuni deschise, titluri generoase, separatoare fine |
| Technical | Panouri precise, secțiuni delimitate, tipografie mono pentru titluri |
| Contrast Premium | Formular deschis la culoare și rezumat întunecat |
| Focus Checkout | Coloană centrală, rezumat deasupra formularului pe desktop |

**Moștenește** sau **Resetează secțiunea** revine la Atelier. Coșul păstrează propriul selector. Există un singur checkout pentru produsele fizice, digitale și coșurile mixte: nu se selectează după referrer sau vitrină.

## Arhitectură și compatibilitate

- `checkout_design` este global, validat în registrul `Zion_Theme_Settings` și salvat o singură dată în `zion_theme_settings.global`. Schema rămâne 2; configurațiile anterioare moștenesc Atelier. Sunt respinse valori necunoscute și override-uri pentru pagini, produse sau magazine.
- `includes/checkout-design.php` adaugă clase la blocul `woocommerce/checkout` folosind procesorul HTML WordPress. Nu înlocuiește copiii blocului, nu mută câmpurile sau butonul din formular și nu creează endpointuri, totaluri ori metode de plată.
- CSS-ul Zion din `assets/src/zion.css` este compilat în `assets/dist/zion.css`. Tokenurile derivă din `theme.json`, inclusiv suprafața deschisă. Focusul la tastatură rămâne vizibil. Nu se adaugă JavaScript pentru aceste designuri.
- Preview-urile `assets/admin/previews/checkout-*.png` sunt concepte cu date ilustrative, încărcate doar în administrare. Livrarea, câmpurile și metodele de plată efectiv afișate depind de WooCommerce, adresă, conținutul editabil și extensiile active. Nu se afișează metode sau promisiuni din imagini când acestea nu sunt disponibile.
- Adaptarea păstrează un singur buton nativ de plasare în formular. Nu duplică acțiunea lângă rezumat și nu introduce un wizard separat. Pe mobil, WooCommerce controlează rezumatul pliabil și validarea.
- `templates/page-checkout.html` conține header/footer editabile și `core/post-content`. Pagina salvată și personalizările din Site Editor nu sunt rescrise. Secțiunea de administrare trimite direct la editorul paginii native și la șablon.
- Designurile cer blocul nativ Finalizare comandă; pentru shortcode clasic se afișează avertizare. Rutarea de confirmare și plata unei comenzi existente rămân gestionate de WooCommerce.

## Corecție carduri

Linkul WooCommerce „Vezi coșul” adăugat prin Ajax mărea coloana de cumpărare, în timp ce butoanele foloseau înălțime 100%. Componenta comună aliniază acum acțiunile sus, păstrează înălțimea intrinsecă a butoanelor și rezervă un rând separat linkului. Nu ascunde confirmarea și nu înlocuiește comportamentul Ajax.

## Verificare

- Build, WPCS și PHPUnit: 41 teste / 359 aserțiuni.
- Integrare: cele cinci designuri păstrează copiii nativi și conținutul paginii. Auditul, dry-run-ul și rollback-ul sintetic trecute, inclusiv verificările HPOS și descărcări.
- Teste browser dedicate: card Technical după adăugare Ajax pe homepage / Magazin 3D / Magazin Software la 390/768/1024/1440 px; butoane aliniate, fără întindere și fără overflow.
- Checkout: cinci designuri × patru lățimi, formular unic, acțiune unică, produse și totaluri native, validare e-mail, date păstrate la redimensionare, preview-uri și salvare Ajax. Capturi locale în `build/checkout-*-1.1.12.png`.
- Suita completă Playwright: **48 scenarii trecute**, inclusiv checkout local cu livrare, taxă, cupon și comandă ramburs sintetică. Nu sunt validate gateway-uri externe sau tranzacții reale.

## Staging

Zion Theme și Zion Core **1.1.12** au fost instalate pe `zion-stage.on-forge.com` din commitul `6c0ab4b64a5d7802d82c673ba831ed07f120961f`. Producția nu a fost modificată.

- Backup privat, în afara directorului public: `.zion-migration/checkout-before-1112.sql`, `checkout-settings-before-1112.json` și `checkout-components-before-1112.tar.gz`. Jurnalul instalării: `.zion-migration/checkout-deploy-1112.log`. Ambele versiuni instalate au fost confirmate și Zion Core este activ.
- Pagina existentă conține blocul nativ Checkout. Șablonul nou este folosit efectiv, fără rescrierea paginii. Selectorul, cele cinci preview-uri și salvarea Ajax au fost verificate.
- Fiecare design la **390, 768, 1024 și 1440 px**: un singur formular și buton de plasare, două produse, zero overflow și total nativ **356,99 lei** în toate cele 20 de randări. Nu au fost înregistrate erori în consola tabului de test.
- Card Technical verificat în Magazin Software și Magazin 3D după adăugarea unui produs prin Ajax. La 1440 px butoanele au aceeași poziție verticală și înălțimea de **44 px**; linkul „Vezi coșul” este separat. La 390 px acțiunile sunt verticale, fără întindere sau overflow. Verificarea locală acoperă suplimentar homepage-ul și lățimile 768/1024 px.
- Cele două produse adăugate numai pentru test au fost eliminate. Coșul preexistent a fost comparat cu starea inițială: aceleași produse, cantități și total. Magazinul 3D a revenit la **Compact Studio**, Software a păstrat **Technical**, iar checkoutul a revenit la **Moștenește / Atelier**. Emularea viewportului a fost eliminată.
- Nu s-au trimis comenzi sau plăți pe staging. Validarea câmpurilor și fluxul comercial sintetic au fost testate local; gateway-urile externe și tranzacțiile reale nu sunt declarate validate.

Captura locală `build/staging-checkout-selector-1.1.12.png` arată secțiunea administrativă finală. Rapoartele și capturile din `build/` sunt excluse din arhivele WordPress.
