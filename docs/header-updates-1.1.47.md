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
- [ ] Instalare și verificare live.

Prima rulare Playwright a trecut **20 scenarii funcționale** și a semnalat diferențe în cele patru referințe de produs care includ întregul header. Au fost actualizate și referințele complete pentru homepage, catalog și Despre noi. Rularea finală de **39/39** a comparat toate referințele fără actualizare, în imaginea oficială Chromium utilizată în CI. Nu s-a mărit toleranța vizuală și nu s-au ascuns noua bară sau clopoțelul.

## Livrare și publicare

Arhivele generate sunt `build/zion-theme-1.1.47.zip` și `build/zion-core-1.1.47.zip`. Inventar SHA-256 combinat: `ee9f7b44e036f2bfae242545515a56e4057e9a01846cc97612c2e702fac4a4f6`. Manifestul arhivelor și capturile headerului local sunt în `build/header-updates-1.1.47/`, excluse din Git. Acest inventar verifică arhivele locale; fișierele instalate pe live nu au fost încă comparate.

**Live nu a fost modificat în această etapă.** Verificarea HTTP anonimă a confirmat că vechiul anunț despre plăți încă există pe site. Accesul la Chrome prin extensie raportează `Local browser access is unavailable for this chat`; alternativa nativă nu a oferit o fereastră controlabilă. La reverificarea finală, Computer Use a raportat și oprirea prin tasta fizică Escape; nu s-au mai executat acțiuni UI. Publicarea și validarea live rămân în așteptarea reluării accesului de către utilizator.

După restabilirea accesului: se verifică versiunea instalată, se păstrează backup privat al celor două componente și al Headerului, se instalează nativ Zion Core și Zion Theme 1.1.47, apoi se invalidează exclusiv cache-ul zion3d.ro. Se compară inventarul SHA-256 al fișierelor instalate cu ZIP-urile și se verifică public homepage-ul, ambele magazine și un articol la 390/768/1024/1440 px, animația, X, clopoțelul, persistența, linkurile și absența textului despre plăți. Rollbackul reinstalează componentele anterioare din backup; această versiune nu migrează date comerciale.
