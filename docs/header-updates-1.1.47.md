# Noutăți automate în header · 1.1.47

## Comportament

Bara se află înaintea rândului `.zion-header` la toate lățimile. Clopoțelul rămâne în instrumentele headerului, între cont și coș, și controlează aceeași bară ca butonul X din capătul ei. Preferința deschis/închis este păstrată în `localStorage`, cheia `zion-header-updates-v1`, inclusiv la navigare, refresh și Back/Forward. Titlurile noi nu resetează preferința. Blocarea stocării nu împiedică folosirea controalelor în pagina curentă.

Ordinea este: ultimele două articole, apoi ultimele două produse 3D, fiecare grup ordonat descrescător după data publicării și ID. Titlurile sunt linkuri native către conținut. Dacă sunt disponibile mai puține rezultate, se afișează numai acestea; un flux complet gol nu încarcă scriptul.

Animația scrie textul pe grafeme, păstrând diacriticele, așteaptă 3,6 secunde după scriere și mută linia în sus în 260 ms. Toate titlurile păstrează o zonă comună de înălțime, astfel încât meniul nu se deplasează la schimbarea noutății. Utilizatorul poate opri animația sau trece manual la următoarea noutate. Mișcarea se oprește la hover, focus, închiderea barei și ascunderea tabului. Titlul curent devine integral lizibil la hover/focus.

Cu `prefers-reduced-motion`, titlul este afișat complet, fără scriere, rotație automată sau tranziție; butonul săgeată permite parcurgerea manuală. Fără JavaScript toate cele patru linkuri sunt vizibile. Numele accesibil al linkului rămâne complet pe durata scrierii; fiecare caracter nu este anunțat cititoarelor de ecran. Controalele au ținte de minimum 44 px, iar panoul închis și rezultatele inactive sunt `inert`.

## Arhitectură și compatibilitate

- `plugins/zion-core/includes/header-updates.php`: două interogări WordPress limitate la câte două rezultate. Numai articole și produse părinte publicate, fără parolă; taxonomia `zion_shop=3d`, independentă de categoriile comerciale. Termenii nativi de vizibilitate WooCommerce exclud produsele ascunse și cele vizibile numai în căutare, plus produsele fără stoc când comerciantul a ales ascunderea lor. Produsele variabile cu variații disponibile și produsele atribuite ambelor magazine sunt eligibile.
- `themes/zion-theme/includes/announcements.php`: HTML cu escapare contextuală și script încărcat condițional. Blocul editorial nativ `announcements` este inclus în Headerul implicit și are etichetă în Site Editor.
- Compatibilitatea cu Headerul deja salvat în baza de date înlocuiește la randare vechiul `<aside class="zion-migration-announcement">` cu fluxul nou. Textul vechi despre plăți nu mai este trimis în pagina publică după instalare. Nu se rescriu automat blocurile, șabloanele sau setările salvate; istoricul editorial rămâne recuperabil.
- `assets/blocks/announcements.js`: controler fără dependențe noi; numai timerul activ, fără polling, servicii externe sau încărcarea catalogului în browser.
- `assets/src/zion.css` și `assets/dist/zion.css`: stiluri limitate la `.zion-updates`, cu paleta existentă din `theme.json`.

Datele sunt citite la randarea paginii, fără un cache suplimentar al aplicației. Cache-ul de pagină/CDN al instalării trebuie invalidat conform fluxului obișnuit după publicarea sau retragerea conținutului și la instalarea versiunii. Nu se schimbă coșul, stocurile, comenzile, plățile sau setările de indexare.

## Verificări

- [x] Build CSS.
- [x] PHPUnit: **61 teste / 436 aserțiuni**.
- [x] Integrare WordPress/WooCommerce: **259 verificări**, inclusiv nouă verificări pentru selecție, produse variabile, vizibilitate, stoc, randare comună, retragerea textului vechi și actualizarea după dezpublicare. Fixture-urile sunt sintetice și sunt eliminate inclusiv la eșec.
- [x] WPCS final fără erori.
- [x] **39/39 scenarii Playwright** trecute în 3,5 minute, cu Chromium oficial **1.65.0-jammy**, la 390/768/1024/1440 px: 12 scenarii ale noutăților (inclusiv Headerul WordPress real), șabloane, Site Editor, meniu, căutare, mini-coș, cumpărare și layouturile homepage/Despre noi/produs/catalog. Zero overflow verificat, zero erori JS în scenariile care capturează erorile.
- [x] **Nouă referințe vizuale** pentru pagini complete actualizate pentru noul header, inspectate și reverificate fără `--update-snapshots`.
- [x] Ambele ZIP-uri generate; CRC și comparație byte-cu-byte cu sursele validate pentru toate cele **167 fișiere** distribuite.
- [x] Instalare și verificare live la **23 noiembrie 2026**, detaliate mai jos.

Prima rulare Playwright a trecut **20 scenarii funcționale** și a semnalat diferențe în cele patru referințe de produs care includ întregul header. Au fost actualizate și referințele complete pentru homepage, catalog și Despre noi. Rularea finală de **39/39** a comparat toate referințele fără actualizare, în imaginea oficială Chromium utilizată în CI. Nu s-a mărit toleranța vizuală și nu s-au ascuns noua bară sau clopoțelul.

## Livrare și publicare

Arhivele generate sunt `build/zion-theme-1.1.47.zip` și `build/zion-core-1.1.47.zip`. Inventar SHA-256 combinat: `ee9f7b44e036f2bfae242545515a56e4057e9a01846cc97612c2e702fac4a4f6`. Manifestul arhivelor și capturile headerului local sunt în `build/header-updates-1.1.47/`, excluse din Git. Același inventar a fost confirmat pentru toate cele **167 fișiere instalate pe live**, după actualizare.

## Instalare și verificare live · 23 noiembrie 2026

- [x] Verificare inițială: Zion Theme/Core **1.1.46**, active, WordPress **7.1.5**, WooCommerce **11.3.1**. Inventarul celor 166 fișiere anterioare coincide cu distribuția verificată 1.1.46; nu au fost suprascrise modificări de surse necunoscute.
- [x] Backup privat al ambelor componente și al Headerului salvat: `/home/forge/.zion-deploy/header-news-1.1.47-20261122T234441Z/`. Data din nume este UTC; instalarea și QA au fost efectuate în 23 noiembrie, ora României. Arhivele TAR, starea inițială și inventarul fișierelor au fost verificate înainte de actualizare.
- [x] **Zion Core, apoi Zion Theme 1.1.47** instalate prin încărcarea ZIP-urilor și înlocuirea nativă WordPress. Tema și pluginul rămân active.
- [x] Toate cele **167 fișiere instalate** coincid cu arhivele: `ee9f7b44e036f2bfae242545515a56e4057e9a01846cc97612c2e702fac4a4f6`.
- [x] Hash-ul conținutului editorial și al setărilor protejate este identic înainte și după actualizare. Headerul salvat în WordPress, șabloanele particulare, titlurile, conținutul, slugurile și selecțiile de template sunt păstrate; nu a fost necesară rescrierea blocului HTML vechi în baza de date. Compatibilitatea de randare elimină mesajul vechi din pagina publică.
- [x] Indexarea rămâne activă (`blog_public=1`). Nu s-au modificat opțiunile comerciale protejate, plățile sau datele de cumpărare.
- [x] Invalidat exclusiv cache-ul Nginx `/var/cache/nginx/zion3d`: **52 fișiere temporare**, cu verificarea căii și fără traversarea symlinkurilor. Nu s-au golit cache-uri ale celorlalte aplicații.
- [x] GET anonim pe homepage, Magazin 3D, Magazin Software și articolul LovePaint: **HTTP 200**, fluxul nou și scriptul 1.1.47 prezente, anunțul despre migrare/plăți absent.
- [x] Cele patru linkuri efective din bară răspund cu **HTTP 200**, fără redirecționare către alt URL. Titlurile sunt cele două articole recente și primele două produse din catalogul 3D ordonat după dată: fața de bord CFMOTO și pâlnia Reform Semo 88.

### Verificare în Chrome conectat

QA a folosit sesiunea autentificată Chrome și verificări HTTP anonime separate, fără deconectarea utilizatorului din WordPress.

| Lățime | Înălțime bară deschisă | Poziție | Overflow orizontal |
| --- | --- | --- | --- |
| 390 px | 97 px | deasupra meniului | 0 |
| 768 px | 53 px | deasupra meniului | 0 |
| 1024 px | 53 px | deasupra meniului | 0 |
| 1440 px | 53 px | deasupra meniului | 0 |

- [x] Clopoțel de **44 × 44 px**, între cont și coș; rămâne accesibil cu bara închisă.
- [x] X închide bara pe desktop și mobil, reduce înălțimea panoului la **0**, îl marchează `inert` și mută focusul pe clopoțel. Redeschidere din clopoțel, inclusiv prin Enter.
- [x] Clopoțelul închide și redeschide bara pe ambele dispozitive. Preferința închisă rămâne la trecerea de pe homepage în Magazin 3D, refresh și Back/Forward. Preferința deschisă se păstrează la trecerea pe Magazin Software și în articol.
- [x] Scriere progresivă observată pe desktop și mobil, urmată de faza de ieșire și de următorul titlu. Poziția meniului rămâne constantă între titluri; pe mobil măsurătorile sunt identice și la parcurgerea manuală a celor patru rezultate.
- [x] Cu mișcare redusă: titlu complet, rotație automată oprită, tranziții de **0 secunde**, controlul de pauză dezactivat și avans manual funcțional prin toate cele patru titluri.
- [x] Meniu mobil deschis și închis normal. Accesarea articolului LovePaint din bară funcționează la 390 px, cu un singur header de noutăți și fără overflow.
- [x] Nicio eroare JavaScript capturată în tabul de QA. Nu s-au creat comenzi WooCommerce și nu s-au efectuat plăți sau trimiteri de formulare.
- [x] La final au fost eliminate override-urile de viewport și mișcare redusă; homepage-ul a fost reîncărcat cu bara deschisă și animația pornită.

Capturi și raport local: `build/header-updates-1.1.47/live/mobile-open.jpg`, `mobile-closed.jpg`, `desktop-open.jpg`, `browser-qa.json` și `links.json`. Raportul serverului este păstrat privat în backup, în `after.json`. Capturile au fost făcute cu titlul oprit temporar pentru lizibilitate; pauza a fost eliminată înainte de predare.

Testele automate ale aceleiași distribuții au trecut înaintea commitului de implementare `7deb6f4`; această etapă adaugă instalarea și verificarea live, fără modificarea codului componentelor sau a versiunii 1.1.47.

### Acces și rollback

Comenzile Forge au executat backupul, dar interfața lor a afișat lipsa fișierului de output. Rezultatul a fost verificat independent în terminalul web Forge, care a fost folosit și pentru inventarul final și invalidarea cache-ului dedicat. Terminalul a fost închis după QA. Nu au fost schimbate serviciile, configurațiile PHP sau accesul serverului.

Rollbackul reinstalează numai componentele 1.1.46 din backupul privat, apoi invalidează cache-ul site-ului și verifică din nou versiunea și inventarul. Nu sunt necesare restaurări de date comerciale sau șabloane, deoarece acestea nu au fost migrate de versiunea 1.1.47. Revenirea ar reactiva și prezentarea vechiului mesaj, păstrat în istoricul editorial.
