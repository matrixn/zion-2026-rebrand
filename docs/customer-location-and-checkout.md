# Țară, contrast și sesiuni de vizitator · 1.1.29

## Țara inițială

Setarea canonică este **WooCommerce → Settings → General → Default customer location → Geolocate**, în opțiunea nativă `woocommerce_default_customer_address`. Mediul demo o configurează prin `npm run demo:test`; pe staging se configurează explicit, după backup. Zion nu creează o opțiune paralelă pentru aceeași proprietate.

Zion Core oferă România drept fallback înainte ca WooCommerce să aplice geolocalizarea. Un rezultat valid și permis al geolocalizării are prioritate. La lipsa unui rezultat sau la un cod invalid, se folosește `RO`, cu județ necompletat. Țara sediului magazinului rămâne independentă. Adresele deja salvate în cont sau introduse în sesiunea clientului sunt administrate de WooCommerce și au prioritate față de această valoare inițială.

Geolocalizarea folosește infrastructura nativă WooCommerce: surse geo ale serverului și integrarea MaxMind. Disponibilitatea rezultatului depinde de configurarea acestor surse; fallbackul RO funcționează și când ele nu furnizează un rezultat. Vezi [documentația oficială MaxMind/WooCommerce](https://woocommerce.com/document/maxmind-geolocation-integration/).

România apare prima între țările disponibile, iar restul păstrează sortarea localizată. Listele clasice folosesc `WC_Countries`. Blocurile WooCommerce 10.8 reconstruiesc alfabetic obiectul `countryData`; un asset mic Zion Core, dependent de `wc-settings`, îi reordonează cheile înainte de inițializarea componentelor native de adresă. Valorile, stările și permisiunile de facturare/livrare din acel obiect rămân aceleași. Ordinea este o regulă de prezentare; restricțiile de vânzare/livrare WooCommerce sunt respectate, inclusiv atunci când RO este exclusă.

Modurile native „Shop base” și „No location” rămân respectate dacă administratorul le selectează ulterior. Este suportat și modul nativ `geolocation_ajax` pentru configurări cu cache de pagină. Taxele, livrarea și totalurile continuă să fie calculate de WooCommerce pentru adresa efectiv aleasă.

## Coș în sesiuni anonime/private

Problema reprodusă pe staging era setarea WooCommerce **Coming soon**, nu pierderea produsului: catalogul Zion era accesibil, dar pagina nativă de coș era înlocuită pentru vizitatori cu ecranul de pre-lansare. După backup privat, `woocommerce_coming_soon` este configurată `no` **numai pe staging**. Setarea de vizibilitate nu este ocolită prin codul temei sau activarea pluginului.

Un client nou primește sesiunea și cookie-urile native WooCommerce. Testele folosesc contexte de browser independente și verifică adăugarea, navigarea în Coș/checkout, refresh-ul și izolarea față de un alt vizitator. Închiderea tuturor ferestrelor private elimină în mod normal cookie-urile acelei sesiuni; coșul se păstrează cât timp browserul păstrează sesiunea nativă.

## Contrast și navigație Software

Cantitatea din cerculețul rezumatului comenzii folosește accentul nativ ca fundal și Ink pentru cifra din interior. Câmpurile de cupon, textul, placeholderul și eticheta folosesc tokenurile locale ale coșului/checkoutului, inclusiv pe varianta Contrast. Testele măsoară contrastul text/fundal, cu pragul 4,5:1, pe preseturi și dimensiuni responsive.

Cuprinsul Software are scrollbar nativ subțire, cu track Surface, thumb Muted și accent la hover; fallbackul WebKit are colțuri rotunjite. Sunt păstrate derularea la tastatură, touch și focusul linkurilor. În modul sistemului cu culori forțate, browserul gestionează culorile scrollbarului.

## Verificare

Comenzi: `npm run build`, `composer lint`, `composer test`, `npm run test:integration`, `npm run test:migration`, `npm run test:e2e`, `npm run package`. Scenariile care modifică date rulează pe instanța sintetică de teste, la portul 8917, și restaurează configurația/documentele proprii. Verificarea pe staging folosește backup, audit al datelor și coș de vizitator separat; nu plasează comenzi.
