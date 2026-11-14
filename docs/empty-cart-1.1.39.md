# Coș gol · două magazine · 1.1.39

## Comportament

În locul mesajului și butonului generic spre Shop, coșul gol afișează un CTA Zion:

- **Coșul tău este gol.**
- **Următoarea idee începe aici.**
- Text despre piesele fabricate 3D și instrumentele software.
- Două butoane: **Magazin 3D** și **Magazin Software**, cu permalinkurile paginilor publicate.

Butoanele stau alăturat pe desktop/tabletă și pe rânduri separate sub 600 px. Fundalul tehnic discret, conturul, tipografia și contrastul folosesc tokenurile existente din `theme.json` și variabilele presetului de coș. Focusul rămâne vizibil, iar efectul decorativ este static, fără animație sau JavaScript suplimentar.

## Arhitectură și editare

Pattern-ul **Zion · Coș gol, două magazine**, slug `zion-theme/empty-cart`, este construit din Group, Heading, Paragraph, Buttons și Button native Gutenberg. Poate fi inserat explicit în blocul Coș gol pentru editarea conținutului; o instanță explicită înlocuiește compoziția automată.

Filtrul `render_block_data` din `includes/cart-design.php` modifică numai starea goală nativă WooCommerce, în timpul randării. Înlocuiește headingul standard și elimină numai grupul standard cu un singur buton către Shop. Păstrează recomandările, separatorul, acțiunile editoriale proprii și celelalte blocuri. Un mesaj complet personalizat nu este înlocuit automat.

WooCommerce poate salva headingul și butoanele fie ca blocuri directe, fie ca `core/pattern`. Referințele sunt extinse prin `resolve_pattern_blocks()`, care menține sloturile `innerContent` și protecția nativă la referințe recursive. Această variantă a fost identificată și verificată pe WooCommerce 11.3.0 de pe live.

Pagina salvată și logica WooCommerce nu sunt rescrise. Starea coșului, ștergerea produselor și trecerea prin Ajax la coș gol rămân native. Nu există un al doilea coș, listener sau endpoint.

## Validare

- [x] Build și ambele ZIP-uri WordPress generate.
- [x] WPCS trecut; **61 teste PHPUnit / 436 aserțiuni** trecute.
- [x] Integrare catalog trecută, inclusiv **7 verificări noi**: linkuri, eliminarea doar a acțiunii standard, păstrarea recomandărilor/conținutului, lipsa duplicării, coș plin și pagină salvate intacte, mesaj personalizat și referințe native la pattern-uri.
- [x] Playwright: cele **5 preseturi** × **4 lățimi** (390, 768, 1024, 1440 px), contrast minimum 4,5:1, butoane accesibile, zero overflow, ambele destinații și lipsa erorilor JavaScript.
- [x] Același scenariu verifică ștergerea ultimului produs prin Store API, apariția CTA fără navigare/reîncărcare și persistența după refresh.
- [x] Theme/Core **1.1.39** instalate prin WordPress. QA live la cele patru lățimi: un singur CTA, două linkuri corecte, butoane de aproximativ 66 px, zero overflow și recomandări păstrate.
- [x] Ambele butoane deschid efectiv magazinul corespunzător. După eliminarea ultimului produs, CTA-ul apare și rămâne după refresh; produsul temporar a fost eliminat și coșul inițial gol a fost restabilit.

Datele automate sunt sintetice în instanța locală `localhost:8917`. QA live este autentificat, deoarece modul Coming soon existent nu este modificat. Nu se introduc date de plată și nu se plasează comenzi pentru această verificare.

## Livrare și rollback

Backup privat făcut la începutul intervenției, cu componentele inițiale 1.1.37:

`/home/forge/.zion-deploy/empty-cart-1.1.38-20261114T135507Z`

Numele directorului identifică începutul intervenției. Include arhivele Theme/Core, hashurile configurațiilor și amprenta paginii de coș înaintea livrării finale.

Arhive:

- `build/zion-theme-1.1.39.zip`
- `build/zion-core-1.1.39.zip`

Core primește numai sincronizarea versiunii. Actualizarea prin instalatorul WordPress este finalizată: cele **150 de fișiere distribuite** corespund ZIP-urilor, fără diferențe. Toate cele șapte comparații sunt valide: destinația de facturare, configurația plăților, conținutul paginii de coș, checkoutul, headerul, setările Zion și fișierele pluginului Sameday. Cache-ul invalidat este exclusiv cel dedicat zion3d.ro.

Rollback: restaurează numai Theme/Core din copia privată sau pachetele 1.1.37, apoi invalidează cache-ul site-ului. Nu restaura SQL-ul migrării și nu înlocui comenzile sau setările comerciale curente.

Capturile live și măsurătorile sunt în `build/empty-cart-1.1.39/`, inclusiv `desktop.jpg`, `mobile-page.jpg`, `responsive.json` și `integrity-live.txt`; sunt excluse din Git. Pachetele nu includ date sau credențiale de producție.
