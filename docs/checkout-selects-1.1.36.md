# Checkout și controale de selecție · 1.1.36

Livrare pentru zion3d.ro: integrare vizuală Sameday/WooPayments și corecția selecturilor semnalată ulterior de utilizator. Sursa stilurilor este `themes/zion-theme/assets/src/zion.css`, compilată în `assets/dist/zion.css`. Nu există JavaScript nou sau dependențe noi.

## Checkout

- Opțiuni de livrare în carduri separate, cu text/preț aliniate, radio nativ centrat și un singur badge **„✓ Selectat”** pe metoda aleasă. Schimbarea selecției folosește exclusiv starea și evenimentele WooCommerce.
- Butonul și rezumatul punctului Sameday sunt integrate în paleta Zion. Harta, locațiile, tarifele și handler-ele furnizorului rămân native.
- Panou de card cu borduri și spațiere coerente. Numărul cardului, expirarea și CVC sunt în continuare câmpurile securizate WooPayments/Stripe; nu sunt reconstruite în temă.
- **Apple Pay și Google Pay:** nu sunt ținte ale noilor reguli CSS și nu se modifică opțiunile gateway-ului sau configurarea plăților expres.
- **Aceeași adresă pentru facturare:** se păstrează opțiunea nativă `woocommerce_ship_to_destination=billing`, deja configurată pe live. Într-o sesiune nouă checkboxul este bifat; clientul poate alege o adresă separată. Nu există un script care să-l rebifeze după o alegere explicită.
- Stilurile folosesc presetările WordPress și variabilele existente ale fiecărui preset checkout, inclusiv varianta Contrast.

## Selecturi în frontend

Problema observată pe produsul Renault: controlul avea **39 px** înălțime și **11,7 px padding sus/jos**, lăsând prea puțin loc pentru text. Focusul avea un contur exterior cu offset de 4 px.

- Controale native de minimum **48 px**, text de **16 px**, padding adaptat și înălțime automată: variații, sortare și formulare, fără condiționare după tipul produsului.
- Focus rotunjit în interiorul controlului, vizibil și la tastatură; culoare de sistem în forced-colors și contrast adaptat pentru suprafețele deschise.
- Selecturile WooCommerce cu etichete flotante au minimum **56 px** și padding separat pentru a păstra lizibile atât eticheta, cât și valoarea.
- Pe mobil, etichetele variațiilor sunt deasupra selectului; pe desktop sunt aliniate cu controlul. Linkul de resetare are spațiu propriu.
- Etichetele nu rup cuvintele în mijloc la lățimi intermediare. Versiunea finală invalidează și URL-ul CSS pentru browserele care păstrau stilurile din verificările intermediare în cache.
- SelectWoo/Select2 are dimensionare și text lizibil; selectul ascuns al bibliotecii nu este suprascris. Selecturile multiple își păstrează numărul nativ de rânduri.
- Reguli limitate la frontendul Zion. Administrarea WordPress și iframe-urile furnizorilor de plăți nu sunt stilizate prin aceste reguli.

## Validare

- [x] Build/package și WPCS trecute; **61 teste PHPUnit / 436 aserțiuni**, integrare catalog trecută.
- [x] **3 scenarii Playwright** relevante trecute în instanța locală de teste: selecție livrare și totaluri; preseturi de produs cu variații reale; cinci preseturi checkout la patru lățimi. Scenariul de produs a fost extins pentru selecturi și repetat după ajustarea focusului/aliniamentului.
- [x] Fixture local de livrare cu două tarife, **8/10**: badge-ul urmărește schimbările cu mouse-ul și tastatura, totalurile native urmează tariful și debifarea adresei comune rămâne respectată.
- [x] Variații fizice și virtuale, opțiuni lungi, prețuri native, stoc, adăugare în coș și fallback fără JavaScript; sortare, țară/județ și SelectWoo din cont verificate pentru spațiu suficient textului.
- [x] QA live autentificat pentru checkout la **390/768/1024/1440 px**, cu Sameday/Easybox și iframe-ul de card, fără overflow; radio-urile au centrele aliniate cu textul.
- [x] Pe live, debifarea adresei comune afișează câmpurile de facturare separate; bifa a fost restabilită după verificare.
- [x] Produsul din captura utilizatorului, `/product/tampoane-portbagaj-renault-megane-2-cabrio/`, verificat la patru lățimi cu **Albastru Translucent (95A)**: control de 48 px, **26 px disponibili** pentru text, focus în interior și zero overflow.

Testele locale mută numai date sintetice; fixture-ul nou este limitat explicit la `localhost:8917`. În verificările live nu s-au introdus date de card și nu s-a apăsat butonul de plasare a comenzii. Verificarea vizuală nu reprezintă un test al autorizării sau decontării unei plăți.

La momentul intervenției, WooCommerce era **11.3.0**, Sameday și WooPayments erau deja instalate, iar vitrina era în modul **Coming soon** pentru vizitatorii anonimi. QA comercial pe live a fost făcut autentificat; această setare de vizibilitate nu a fost schimbată.

## Backup, instalare și integritate

Înaintea intervenției, Theme/Core și hashurile configurației au fost salvate privat în:

`/home/forge/.zion-deploy/checkout-1.1.32-20261114T113949Z`

Numele directorului identifică începutul intervenției. `theme-before.tar.gz` și `core-before.tar.gz` păstrează componentele inițiale 1.1.30; `before.json` păstrează hashuri ale opțiunilor relevante, fără expunerea valorilor gateway-ului în raport. Nu se face rollback al bazei de date pentru această schimbare de prezentare.

Pachetele **1.1.36** au fost aplicate prin actualizatorul WordPress. Hashurile tuturor celor **149 de fișiere distribuite** corespund ZIP-urilor, fără diferențe. Cele cinci comparații cu backupul confirmă păstrarea destinației de facturare, configurației plăților, conținutului checkoutului, headerului cu anunțul de migrare și setărilor Zion. Cache-ul invalidat este exclusiv cel dedicat zion3d.ro.

Dovezi locale: `build/checkout-selects-1.1.36/select-responsive.json`, `select-mobile.jpg` și `integrity-live.txt`. La toate cele patru lățimi, selectul Renault are 48 px, text de 16 px și 26 px disponibili pentru text; focusul are offset de −2 px. Eticheta este deasupra pe mobil și centrată cu o diferență de 0,5 px pe desktop/tabletă.

Pentru rollback, restaurează numai componentele din copia privată sau un pachet anterior verificat, apoi invalidează cache-ul dedicat. Păstrează comenzile și configurările curente; nu importa vechiul SQL al migrării pentru o corecție CSS.

## Arhive

- `build/zion-theme-1.1.36.zip`
- `build/zion-core-1.1.36.zip`

Core nu primește logică comercială nouă; antetul versiunii este sincronizat cu livrarea. Capturile și rapoartele detaliate de QA sunt în `build/`, excluse din Git. Fișierele private de backup și credențialele nu intră în repository sau în ZIP-uri.
