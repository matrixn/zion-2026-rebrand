# Contul meu Zion · 1.1.14

## Configurare

În **Aspect → Zion Theme → Contul meu**, alege Atelier, Studio Editorial, Technical, Contrast Premium sau Focus Account. Preview-ul compact se actualizează imediat; click pe imagine deschide lightboxul. Salvează setările prin Ajax. Moștenește și resetarea secțiunii revin la Atelier, păstrând celelalte secțiuni.

| Variantă | Prezentare |
| --- | --- |
| Atelier | Navigație laterală și panouri cu colțuri discrete |
| Studio Editorial | Navigație laterală deschisă, separatoare și tipografie generoasă |
| Technical | Navigație orizontală, contururi precise și detalii CAD |
| Contrast Premium | Navigație întunecată, conținut deschis și contrast ridicat |
| Focus Account | Compoziție centrală, navigație orizontală și conținut concentrat |

## Arhitectură

`account_design` este o alegere globală în registrul existent și în `zion_theme_settings.global`. Nu acceptă override-uri per produs, pagină sau vitrină. Schema rămâne 2; configurațiile anterioare folosesc Atelier. Validarea, capabilitățile, nonce-ul, revizia, importul/exportul și resetarea sunt cele comune Control Center.

`includes/account-design.php` decorează rădăcina shortcode-ului nativ `[woocommerce_my_account]`. Nu depinde de ID-ul sau slugul paginii și nu modifică rutele, nonce-urile, formularele, permisiunile sau mecanismul de autentificare. Pagina și header/footer rămân editabile prin WordPress și Site Editor; nu există un al doilea editor. Dacă shortcode-ul lipsește, administrarea afișează o explicație fără a rescrie pagina.

Dashboardul folosește un singur renderer și API-urile WooCommerce. Interogarea `wc_get_orders` este limitată la trei comenzi și `customer_id = get_current_user_id()`. Nu sunt afișate comenzile generale ale magazinului unui administrator conectat ca utilizator. Vizitatorii nu primesc date private. Navigația și linkurile respectă meniul WooCommerce, inclusiv extensiile și eliminarea unor endpointuri.

Cardurile Fișierele tale și Adresele tale trimit la endpointurile reale; nu promit descărcări, licențe sau adrese inexistente. Autorizarea și livrarea fișierelor rămân native. Nu se interoghează toate descărcările pentru a construi un contor. Comenzile, plățile și datele contului nu sunt copiate în tabele sau metadata Zion.

Conceptele aprobate sunt adaptate conținutului real și permisiunilor native. Nu se afișează statisticile sau comenzile demonstrative din imagini. Pentru lipsa comenzilor se afișează o stare goală. Datele și sumele sunt formatate de WordPress/WooCommerce; tema nu impune o altă monedă sau format de dată.

## Compatibilitate și validare

Stilurile sunt limitate la `.zion-account`, folosind tokenuri din `theme.json`. Toate variantele împart aceleași formulare și componente. Nu există template-uri PHP WooCommerce copiate în temă sau JavaScript nou pentru cont. Înregistrarea și recuperarea parolei urmează setările WooCommerce și infrastructura de e-mail existentă; tema nu activează înregistrarea și nu schimbă parole în fundal.

PHPUnit: 45 teste / 378 aserțiuni pentru registru, valori implicite, validare, contexte și import/export, împreună cu regresiile existente. Browser: cinci designuri × patru lățimi × client/vizitator; endpointuri orders, downloads, edit-address, edit-account și view-order pe mobil/desktop. Sunt verificate izolarea între doi clienți sintetici, lipsa datelor private pentru vizitatori, formularul de recuperare, salvarea detaliilor și logoutul nativ. Datele sintetice și setările temporare sunt curățate/restaurate.

Suita completă Playwright a trecut: **51 scenarii**. Build, WPCS, integrarea și migrarea/rollback-ul sintetic au trecut. Nu sunt declarate testate inboxuri, linkuri e-mail de resetare, gateway-uri sau tranzacții reale prin această verificare a designului contului.

## Staging verificat

Zion Theme și Zion Core **1.1.14** au fost instalate pe `zion-stage.on-forge.com` din commitul `a84c3b1b0b0968d519d7e9e314820f4b8dd4b5d8`. Backupurile private sunt în `.zion-migration/account-before-1114.sql`, `account-settings-before-1114.json` și `account-components-before-1114.tar.gz`. Instalarea și versiunile sunt în jurnalul `account-deploy-1114.log`; Zion Core este activ. Producția nu a fost modificată.

- Pagina `/contul-meu/` conține shortcode-ul nativ. Conținutul salvat și rutele sale au fost păstrate.
- Toate cele cinci variante au fost verificate autentificat la 390, 768, 1024 și 1440 px: 20 de randări cu o singură navigație și zonă de conținut, fără overflow. Contul verificat nu avea comenzi proprii și a afișat starea goală corectă.
- Comenzi, Descărcări, Adrese și Detalii cont au fost verificate suplimentar la 390 și 1440 px. Formularul nativ de detalii este unic. Nu au fost observate erori fatale în paginile verificate sau erori în consolele taburilor de cont și administrare.
- Cele cinci preview-uri și salvarea Ajax au funcționat. Lightboxul a încărcat imaginea mare; Escape a închis dialogul și a restaurat focusul.
- La final, `account_design` a revenit la **Moștenește / Atelier**, iar emularea viewportului a fost eliminată. Nu au fost trimise formulare de profil, comenzi sau plăți și nu a fost deconectată sesiunea utilizatorului de staging. Testele de autentificare, logout, salvare profil și izolare între clienți au fost executate local, pe date sintetice.

Dovezi locale, excluse din ZIP: `build/staging-account-selector-1.1.14.png`, `build/staging-account-contrast-mobile-1.1.14.png` și `build/account-*-1.1.14.png`.
