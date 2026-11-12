# Contact · Zion Continuum 1.1.23

Pagina folosește `templates/page-contact.html`, patternul `zion-theme/contact` și compoziția din `includes/contact.php`. Textele, legăturile și secțiunile sunt blocuri Gutenberg native. Formularul este blocul oficial `contact-form-7/contact-form-selector`, cu identitatea și shortcode-ul generate de CF7. În Site Editor se selectează formularul nativ; câmpurile, validarea, mesajele și e-mailurile se editează în **Contact → Contact Forms**. Nu există setări duplicate în Control Center.

## Design și comportament

- Hero antracit/lime cu emițător, receptor și conexiune proiectate în Canvas 2D; date de contact directe și secțiune Paper cu formular încadrat în antracit.
- Trei scene în același renderer `assets/blocks/continuum.js`: canalul de comunicare, consola formularului și traseul colaborării. Progresul consolei reflectă câmpuri completate, nu înlocuiește validarea nativă CF7. O consolă goală nu este animată continuu.
- Scena consolei reacționează la evenimentele native CF7 pentru trimitere și rezultat; numai răspunsul pluginului stabilește succesul. Spinnerul și mesajul de încărcare rămân vizibile, inclusiv ca indicator static la reduced motion.
- Scene vizibile, rezoluție limitată pe mobil, pauză comună, preferința OS și opțiunea de mișcare redusă din temă. Fără JavaScript rămân compoziția editorială, contactele directe și POST-ul nativ CF7.
- Layout de formular pe două coloane desktop și o coloană pe mobil, câmpuri de minimum 48 px, focus vizibil și feedback în română. Sloturile de eroare rezervate împiedică mutarea butonului între apăsare și eliberarea mouse-ului la corectarea erorilor CF7.
- Parametrul public `serviciu` este acceptat numai din registrul unic `zion_theme_services()` și numai dacă există opțiunea corespunzătoare în formular. Nu suprascrie o selecție deja completată.
- CSS limitat la `.zion-contact`, cu paleta `theme.json`. Nu sunt încărcate hărți, video sau frameworkuri externe.

## Configurarea formularului

`config/contact-form.php` este un preset de autorare CF7, fără destinatar sau credențiale: nume/e-mail/serviciu/mesaj obligatorii și telefon opțional, plus informarea despre confidențialitate. `scripts/contact-form-config.php` generează lista serviciilor din registrul existent și combină proprietățile prin API-ul CF7. Formularul salvat rămâne administrat integral de plugin; schimbarea temei nu șterge datele sale.

Stilizarea rămâne în temă, conform [recomandării CF7](https://contactform7.com/styling-contact-form/). Configurarea e-mailului urmează [documentația oficială](https://contactform7.com/setting-up-mail/): expeditor din domeniul site-ului, destinatarul existent și `Reply-To` din câmpul de e-mail validat. Formularul nu configurează un serviciu SMTP și confirmarea CF7 nu dovedește primirea în inbox.

Pe staging, formularul existent a fost identificat din interfața pluginului. Înaintea schimbării avea zero câmpuri și trei erori în configurația Mail. Presetul repară un formular gol; când există deja câmpuri, scriptul păstrează structura lor. Nu modifică formularul separat de abonare.

După backup privat și instalarea componentelor noi, scriptul staging se rulează în doi pași:

```sh
wp eval-file scripts/contact-page.php FORM_ID
wp eval-file scripts/contact-page.php FORM_ID apply /cale/privata/backup-editorial.json
```

Scriptul refuză producția, cere pagina Contact și formularul selectat și validează configurația CF7 propusă. Preview-ul nu scrie. Apply verifică exact pagina și metadatele formularului față de backupul privat din afara rădăcinii publice, păstrează o revizie și schimbă numai conținutul Contact și configurația formularului selectat. Titlul, slugul, destinatarul, identitatea CF7 și metadatele SEO rămân păstrate. O pagină Contact deja reconstruită nu este rescrisă la reaplicare.

## Dezvoltare și testare

wp-env fixează WordPress 7.1.4, CF7 6.2.5, WooCommerce 10.8.1 și PHP 8.3. CF7 6.2.5 necesită WordPress 7.1+, de aceea versiunea de core a mediului reproduce stagingul existent. Seederul creează doar formularul sintetic **Zion Contact Demo** și pagina Contact lipsă; nu rescrie o pagină deja editată sau alte formulare. Fișierele media demo lipsă după înlocuirea core-ului wp-env sunt restaurate numai din fixture-urile cunoscute, în uploads local, fără schimbarea ID-urilor.

```sh
npm run env:start
npm run demo:test
npm run build
composer lint
composer test
npm run test:integration
npm run test:migration
bash scripts/ci-browser.sh start
CI=1 ZION_PLAYWRIGHT_WS_ENDPOINT=ws://127.0.0.1:9347 npx playwright test --workers=2
npm run package
bash scripts/ci-browser.sh stop
```

`scripts/local-contact-mail.php` este instalat ca MU-plugin **numai de seederul local**. Interceptează exclusiv formularul sintetic cu destinatar `recipient@example.test`, salvează mesajele sintetice și raportează eșec dacă scrierea capturii nu reușește. Markerul sintetic `ZION-QA-FAIL` verifică starea nativă de transport eșuat. Nu este inclus în ZIP-uri și nu este un transport pentru site-uri reale.

Cele zece teste Contact acoperă 390/768/1024/1440 px, patru referințe vizuale hero/formular, pauză, actualizarea consolei, spinner și mesaje native, POST AJAX și fără JavaScript, conținutul mesajului capturat, corectarea datelor invalide și validitatea blocurilor în editor. Integrarea verifică identitatea CF7, păstrarea datelor, încărcarea condițională și fallbackul când pluginul/formularul lipsește. Rezultatele complete de staging și CI se adaugă în README după executare.

Pagina Contact lipsea din vechile fixture-uri locale. Crearea ei activează automat legăturile native Contact din header și footer; cele nouă referințe generale au fost actualizate pentru această schimbare intenționată, fiecare având înălțimea mărită cu exact 32 px pentru rândul nou din footer. Pe staging destinația și aceste legături existau deja; nu sunt rescrise meniurile sau footerul.

## Revenire

Backupurile SQL, Theme/Core, configurație și snapshotul celor 39 de înregistrări editoriale/CF7 rămân private pe staging. Revenirea restaurează componentele anterioare și numai pagina/formularul schimbate din snapshot sau revizia verificată. Nu se rulează o restaurare SQL completă peste alte modificări între timp. Producția nu este modificată.
