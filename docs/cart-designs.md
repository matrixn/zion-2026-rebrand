# Designurile coșului Zion · 1.1.11

## Configurare

1. Deschide **Aspect → Zion Theme → Coș de cumpărături**.
2. Alege Atelier, Studio Editorial, Technical, Contrast Premium sau Focus Checkout din select sau din butoanele vizuale. Imaginea conceptuală și explicația se actualizează imediat.
3. Apasă **Salvează setările**. Salvarea este Ajax, cu spinner, nonce, capabilitatea `edit_theme_options` și verificarea reviziei configurației.
4. Verifică pagina nativă de coș. **Moștenește** și **Resetează secțiunea** revin la Atelier, fără să reseteze setările cardurilor, produselor sau vitrinelor.

Există o singură setare globală `cart_design` în `zion_theme_settings`. Cartul este comun produselor 3D și software, inclusiv când sunt combinate într-o comandă. Schema rămâne versiunea 2; exporturile anterioare folosesc valoarea implicită dacă nu conțin noul câmp. Importul validează strict cele cinci valori și respinge folosirea câmpului ca override de produs, pagină sau vitrină.

## Variante

| Design | Prezentare |
| --- | --- |
| Atelier | Produse în listă, rezumat lateral pe desktop, flux vertical pe mobil |
| Studio Editorial | Carduri cu fotografii mari, rezumat lateral; o coloană în spațiile înguste |
| Technical | Tabel pe toată lățimea, contur tehnic și rezumat sub listă |
| Contrast Premium | Panou de produse deschis, rezumat întunecat, culori din aceeași paletă WordPress |
| Focus Checkout | Listă centrală, rezumat la bază și total lângă CTA pe desktop; flux vertical pe mobil |

Preview-urile aprobate sunt concepte cu date ilustrative. Structura este adaptată blocului WooCommerce nativ; liniile efective de taxe, subtotal, reduceri și livrare depind de WooCommerce și de blocurile configurate. Nu sunt afișate promisiuni de livrare, taxe, reduceri sau sume inventate.

## Arhitectură

- `includes/cart-design.php` adaugă numai clase de prezentare la blocul `woocommerce/cart`. Nu introduce endpointuri de coș, un renderer comercial paralel sau copii ale totalurilor.
- CSS-ul componentelor este în `assets/src/zion.css`, compilat în `assets/dist/zion.css`. Tokenurile `--cart-*` sunt derivate din `theme.json`; nu reprezintă o paletă independentă. Stilurile sunt limitate la clase Zion.
- Numai Studio Editorial încarcă `assets/blocks/cart-images.js`. Scriptul citește imaginile din store-ul nativ `wc/store/cart`, deja folosit de bloc, și ajustează `src`, `srcset` și `sizes` pentru suprafețele mari. Nu face cereri de produse, nu modifică store-ul și nu interceptează operațiile comerciale.
- Native WooCommerce gestionează cantitățile, stocul, cupoanele, eliminarea produselor, livrarea, taxele, metodele expres și trecerea la checkout. Indicatorul Ajax comun rămâne activ și pentru cererile Store API.
- Imaginile de preview sunt în `assets/admin/previews/cart-*.png` și sunt solicitate numai de administrare. Registrul și rendererul de preview sunt comune cardurilor, paginilor de produs și coșului.

## Editor și compatibilitate

`templates/page-cart.html` este un șablon HTML pentru Site Editor și păstrează blocul `core/post-content`: nu rescrie conținutul paginii de coș. Panoul are link către șablon și către pagina nativă WooCommerce. Personalizările existente salvate în Site Editor au prioritate față de fișierul temei.

Aceste designuri cer blocul **Coș WooCommerce**. Pentru shortcode-ul clasic apare o explicație în administrare; conținutul nu este convertit automat. Un coș bazat pe blocuri necesită JavaScript, conform funcționării native WooCommerce.

Etichetele implicite ale coșului gol sunt localizate în română la randare, păstrând textele personalizate și conținutul salvat. Nu se modifică date despre clienți, comenzi, gateway-uri sau reguli financiare.

## Verificare locală

- Build și WPCS trecute; PHPUnit: 37 teste și 340 aserțiuni.
- Integrarea catalogului și repetiția migrării/rollback-ului au trecut pe baza sintetică de test.
- Suita completă Playwright: 46 scenarii trecute. Testul coșului a fost reluat cu succes după ultimele ajustări ale prezentării.
- Pentru fiecare design: 1440, 390, 768 și 1024 px; două produse de tipuri diferite, un singur CTA, fără overflow și fără suprapunerea titlului cu totalul. Totalurile Store API rămân identice la schimbarea designului.
- Cantități Ajax fără navigare, indicator vizibil în timpul unei cereri întârziate, cupon nativ aplicat/eliminat și total restabilit, trecere în checkout, eliminarea produselor și coș gol, resetarea selectorului și încărcarea preview-urilor.
- Imaginile mari sunt verificate împotriva sursei complete din Store API. Aceste teste nu validează tranzacții reale sau gateway-uri externe.

## Staging

Zion Theme și Zion Core **1.1.11** au fost instalate pe `zion-stage.on-forge.com`, după backup privat al bazei și configurației. Sursa instalată: commit `9c60d0931ca73bcc4a3a5d7e67d6cb03900c212c`. Producția nu a fost modificată.

- Selectorul, cele cinci preview-uri, salvarea Ajax și linkurile native către editoare au fost verificate. Șablonul `page-cart` este utilizat efectiv, iar pagina păstrează blocul nativ Coș.
- Atelier, Studio Editorial, Technical, Contrast Premium și Focus Checkout au fost verificate fiecare la **390, 768, 1024 și 1440 px**: două produse în coș, fără overflow orizontal sau suprapunerea titlului peste total. Totalul nativ a rămas **142,99 lei** în toate cele 20 de randări.
- Coșul mixt a inclus o piesă 3D și Zion Privacy Pro. Regula nativă „vândut individual” pentru software a fost păstrată. Cantitatea piesei fizice a trecut prin Ajax de la 1 la 2 și înapoi la 1, cu totalurile **142,99 → 237,99 → 142,99 lei**.
- Linkul nativ a deschis formularul de checkout, cu etichete în română. Nu s-au trimis comenzi sau plăți. Stagingul raportează că nu sunt disponibile metode de plată; testul nu validează gateway-uri, livrare sau calcul fiscal pentru adrese reale. Cupoanele au fost verificate în mediul sintetic local, fără modificarea regulilor comerciale de pe staging.
- Eliminarea ambelor produse a restabilit coșul gol. Titlul, continuarea cumpărăturilor și produsele noi sunt localizate în română. Nu au fost înregistrate erori în consola tabului de test.
- La final, `cart_design` a revenit la **Moștenește / Atelier**, coșul sesiunii de test este gol, iar emularea viewportului a fost eliminată. Celelalte setări ale temei au fost păstrate.

Captura selectorului verificat este disponibilă local în `build/staging-cart-selector-1.1.11.png`. Dovezile de browser și rapoartele locale din `build/` nu sunt incluse în ZIP-urile WordPress.
