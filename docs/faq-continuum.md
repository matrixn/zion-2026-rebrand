# Întrebări frecvente / Zion Continuum 1.1.25

Pagina `/intrebari/` folosește un șablon nativ de tip block theme. WordPress păstrează conținutul paginii, URL-ul, metadatele și comportamentul blocurilor `core/details`. Nu este necesară o migrare a întrebărilor sau importarea codului Elementor/XStore.

## Editare

- Întrebările și răspunsurile se editează în **Pagini → Întrebări**, cu blocurile native **Detalii**. Fiecare bloc are un rezumat și conținutul răspunsului; se pot adăuga paragrafe, liste și legături native. Sunt permise mai multe răspunsuri deschise simultan.
- Pentru pagina existentă `/intrebari/`, deschide **Editează site-ul** din pagina respectivă și editează șablonul automat `page-intrebari`. Pentru alt URL se selectează **Zion · Întrebări frecvente / Continuum** (`faq`) în editorul paginii și se editează acel șablon în Site Editor. Ambele pornesc de la patternul `faq-layout`; personalizările salvate ulterior pentru fiecare șablon sunt independente, conform comportamentului nativ WordPress.
- Headerul și footerul rămân Template Parts native. Hero-ul, explicațiile laterale și CTA-ul sunt grupuri, titluri, paragrafe și butoane editabile. Blocul Conținut articol afișează întrebările paginii curente.
- Patternul **Zion FAQ / Demonstration questions** este un punct de pornire cu zece exemple sintetice. Seederul local creează pagina numai dacă lipsește și nu suprascrie o pagină existentă. Seederul nu se rulează pe staging sau în producție.

## Comportament și animație

`includes/faq.php` aplică decorarea numai grupurilor marcate și numai la randarea frontend. `faq.js` adaugă progresiv căutarea în întrebări și răspunsuri, comenzi de deschidere/închidere a rezultatelor vizibile, ștergerea căutării, contor și stare fără rezultate. Căutarea ignoră diacriticele și cere toate cuvintele introduse. Nu face cereri AJAX pentru cele zece răspunsuri deja disponibile în document și nu modifică datele salvate.

Acordeoanele sunt elementele native `details`/`summary`. Enter și Space păstrează comportamentul browserului, iar răspunsurile se pot deschide fără JavaScript. Controalele suplimentare apar numai după inițializarea scriptului. Ascunderea prin căutare păstrează starea `open`; deschiderea/închiderea în masă afectează numai rezultatele vizibile. Starea rezultatelor este anunțată prin `role=status`, fără mutarea focusului în timpul tastării.

Motorul comun `continuum.js` desenează două scene originale: o rețea de semnale care reacționează la întrebarea focalizată/deschisă și un releu Idee → Dialog → Direcție lângă CTA. Folosește culorile din `theme.json`, un singur scheduler, limitarea cadrelor și vizibilitatea scenelor. Pauza este comună ambelor scene; reduced motion păstrează un desen static, actualizat la schimbarea întrebărilor. Deschiderea unui răspuns și animația pictogramei folosesc CSS progresiv, cu tranziții oprite la reduced motion. Lipsa Canvas nu blochează controalele sau răspunsurile.

CSS-ul este limitat la clase Zion. Tastatura are focus vizibil, controalele au minimum 44 px, iar la 800 px compoziția trece într-o coloană. Nu există o paletă alternativă sau dependențe noi.

## Validare și livrare

```sh
npm run build
composer lint
composer test
npm run demo:test
npm run test:integration
npm run test:migration
bash scripts/ci-browser.sh start
CI=1 ZION_PLAYWRIGHT_WS_ENDPOINT=ws://127.0.0.1:9347 npx playwright test --workers=2
npm run package
bash scripts/ci-browser.sh stop
```

Cele nouă scenarii FAQ verifică 390/768/1024/1440 px, patru referințe vizuale, tastatura, mai multe răspunsuri deschise, căutarea fără diacritice, starea fără rezultate, căutarea în conținutul răspunsului, animația reală/pauza/reduced motion, lipsa JavaScript/Canvas, încărcarea condițională și validitatea în Site Editor. Cele șapte aserțiuni de integrare verifică șabloanele native, răspunsurile fără JS, decorarea și păstrarea datelor.

Demo-ul local include acum pagina Întrebări, astfel că footerul nativ afișează și legătura prevăzută pentru aceasta. Cele nouă referințe vizuale complete pentru About/homepage/catalog/produse sunt actualizate după verificarea diferențelor: fiecare pagină are 32 px în plus în footer, cu întregul conținut principal păstrat. Pe staging legătura exista deja. Cele patru referințe FAQ sunt noi; diagrama are o zonă de desen separată de etichetele text pe mobil.

Înaintea instalării pe staging sunt salvate privat SQL-ul, componentele anterioare și un snapshot al celor **43 de înregistrări editoriale**, inclusiv întrebările, metadatele, asignările, comentariile și opțiunile. Scriptul existent `scripts/journal-audit.php` este un comparator editorial reutilizabil, strict și numai pentru citire:

```sh
wp eval-file scripts/journal-audit.php /cale/privata/faq-continuum-before-1125.json
```

Instalarea actualizează numai Theme/Core, fără editarea paginii Întrebări. Cache-ul PHP al temei se invalidează punctual printr-un helper temporar autentificat și restricționat la hostname-ul de staging; acesta se elimină după utilizare. Rollback-ul extrage componentele anterioare într-un director privat temporar, generează ZIP-urile corespunzătoare și le reinstalează prin WordPress cu `--force`, astfel încât să fie eliminate și fișierele noi ale versiunii curente. Apoi se revalidează snapshotul. Nu este necesară restaurarea SQL peste eventuale editări independente.

## Staging verificat

Theme/Core **1.1.25** instalate din `5eafffe`, cu backup înaintea actualizării. Auditul final confirmă identitatea celor **43 de înregistrări**, a metadatelor și asignărilor, taxonomiilor, comentariilor, întregii configurații Zion, citirii și permalinkurilor. Cele zece întrebări au SHA-256 `54d8690acd1f47cd06e5c7b0b753911547ebfdbdb30949ee1ab85460c18f3776`, identic înainte/după. Pagina păstrează `_wp_page_template=default`; `page-intrebari` provine din temă, cu `wp_id=0`. Nu a fost creat un override în baza de date.

Layoutul este verificat la **390/768/1024/1440 px**, atât închis, cât și cu toate răspunsurile deschise: un H1, două scene, controale de minimum 44 px și zero overflow. Căutarea `piesa rupta` găsește întrebarea 03, iar `STL` găsește răspunsul potrivit. Filtrarea păstrează stările ascunse: închiderea singurului rezultat vizibil lasă celelalte nouă răspunsuri deschise când se șterge căutarea. Starea fără rezultate, Escape, Enter/Space pentru două răspunsuri simultane, meniul mobil, pauza comună și reduced motion sunt verificate.

Homepage, About, Servicii, Contact și blog păstrează **6/2/10/3/1 scene**, fără scriptul FAQ. Catalogul nu încarcă FAQ, Continuum sau CF7; CF7 rămâne numai pe Contact. Aceste pagini au zero overflow la 390/1440 px și nu au erori JS în verificarea efectuată. Nu s-au trimis formulare sau e-mailuri și nu s-a modificat producția.

Helperul autentificat a invalidat numai fișierele PHP ale temei și s-a eliminat; absența sa este verificată. SHA-256 ale CSS/JS instalate corespund buildului local și arhivelor. ZIP-urile au antetele **1.1.25**, cu **125 fișiere Theme / 9 Core**, fiecare intrare verificată față de sursă și fără fișiere operaționale sau date private. Capturile sunt `build/faq-staging-desktop-1.1.25.png` și `build/faq-staging-mobile-1.1.25.png`.

**CI final verde** pentru `5eafffe`: [rularea 43330474147](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43330474147), cu **109/109 teste Playwright** în 11,2 minute, **46 teste PHPUnit / 394 aserțiuni**, build reproductibil, WPCS, integrare și migrare/rollback trecute. Local, cele 18 verificări FAQ/footer au trecut în 37,7 s, apoi verificarea finală FAQ cu numerotarea stabilă a trecut **9/9** în 30,7 s, fără actualizarea referințelor vizuale. Producția nu face parte din această livrare.
