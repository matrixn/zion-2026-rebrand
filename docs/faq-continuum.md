# Întrebări frecvente / Zion Continuum 1.1.25

Pagina `/intrebari/` folosește un șablon nativ de tip block theme. WordPress păstrează conținutul paginii, URL-ul, metadatele și comportamentul blocurilor `core/details`. Nu este necesară o migrare a întrebărilor sau importarea codului Elementor/XStore.

## Editare

- Întrebările și răspunsurile se editează în **Pagini → Întrebări**, cu blocurile native **Detalii**. Fiecare bloc are un rezumat și conținutul răspunsului; se pot adăuga paragrafe, liste și legături native. Sunt permise mai multe răspunsuri deschise simultan.
- Compoziția se editează în **Aspect → Editor → Șabloane → Zion · Întrebări frecvente / Continuum**. Șablonul automat pentru slugul `intrebari` folosește aceeași compoziție. Pentru alt URL se selectează șablonul `faq` în editorul paginii.
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

Rezultatele finale și rularea CI sunt consemnate în README. Producția nu face parte din această livrare.
