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

Suita completă Playwright a trecut: **51 scenarii**. Build, WPCS, integrarea și migrarea/rollback-ul sintetic au trecut. Livrarea pe staging este în curs. Nu sunt declarate testate inboxuri, linkuri e-mail de resetare, gateway-uri sau tranzacții reale prin această verificare a designului contului.
