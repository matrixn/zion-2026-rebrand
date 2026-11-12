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

Pe staging, formularul existent a fost identificat din interfața pluginului. La inventarul inițial avea zero câmpuri și trei erori în configurația Mail. Verificarea strictă dinaintea instalării a detectat însă un formular și setări actualizate între timp; instalarea s-a oprit și a fost creat un snapshot nou. Formularul actual, valid, a fost păstrat: nume/e-mail/mesaj obligatorii, companie/telefon opționale. Au fost adăugate etichete permanente și containere de prezentare, eliminat un `</label>` izolat și corectat placeholderul telefonului opțional. Tipurile și numele câmpurilor, configurațiile Mail/Mail 2, destinatarul și identitatea nativă rămân aceleași. Nu se modifică formularul separat de abonare.

După backup privat și instalarea componentelor noi, scriptul staging se rulează în doi pași:

```sh
wp eval-file scripts/contact-page.php FORM_ID
wp eval-file scripts/contact-page.php FORM_ID apply /cale/privata/backup-editorial.json
```

Scriptul refuză producția, cere pagina Contact și formularul selectat și validează configurația CF7 propusă. Preview-ul nu scrie. Apply verifică exact pagina și metadatele formularului față de backupul privat din afara rădăcinii publice, păstrează o revizie și schimbă numai conținutul Contact și configurația formularului selectat. Titlul, slugul, destinatarul, identitatea CF7 și metadatele SEO rămân păstrate. Adresa este extrasă recursiv din paragrafele native, inclusiv când acestea sunt în grupuri Gutenberg imbricate. O pagină Contact deja reconstruită nu este rescrisă la reaplicare.

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

Cele zece teste Contact acoperă 390/768/1024/1440 px, patru referințe vizuale hero/formular, pauză, actualizarea consolei, spinner și mesaje native, POST AJAX și fără JavaScript, conținutul mesajului capturat, corectarea datelor invalide și validitatea blocurilor în editor. Integrarea verifică identitatea CF7, păstrarea datelor, încărcarea condițională, fallbackul când pluginul/formularul lipsește și extragerea adresei din grupuri imbricate. Suita completă locală are **88/88 teste trecute**, iar CI pentru `06c3abc` este verde. Rezultatele și verificarea corecției finale se urmăresc în README.

## Verificarea stagingului

Theme/Core 1.1.23 sunt instalate pe WordPress 7.1.4 / WooCommerce 11.2.2 / CF7 6.2.5. La 390/768/1024/1440 px: zero overflow, trei scene, un H1 și câmpuri de minimum 48 px. Pauza comună, reduced motion, progresul formularului, meniul mobil și lipsa erorilor JS au fost verificate. Asseturile CF7 lipsesc de pe homepage, About, Servicii și cele două cataloage; scenele Continuum existente rămân 6/2/10.

Testul de trimitere folosește temporar un destinatar rezervat `.test`, un marker sintetic și un hook limitat la formularul selectat și hostname-ul staging. Captura se scrie în directorul privat din afara public; eșecul scrierii raportează eșec CF7. Au fost verificate validarea, spinnerul, succesul, conținutul capturii și un eșec de transport simulat. La final, Mail/Mail 2 sunt restaurate integral, iar hookul temporar și helperul de cache sunt eliminate. Acest test nu expediază e-mailuri externe.

**Etapă ulterioară, amânată explicit de utilizator:** configurarea și testarea livrării reale de e-mail. PHP-FPM nu are sendmail executabil și nici SMTP configurat. Pentru mesaje reale trebuie configurat un transport de e-mail separat, cu credențiale în afara repository-ului. Nu se declară livrare în inbox sau formular pregătit pentru producție pe baza capturii de test.

Adresa din paragraful imbricat al paginii anterioare a fost restaurată punctual, cu revizie. Identitatea paginii și formularului, metadatele Contact și ambele configurații Mail sunt păstrate. Scriptul corectat din `5df532a` a fost reluat pe staging: preview și apply raportează `changed=false`, zero erori de configurare CF7 și păstrează exact snapshotul final al celor 39 de înregistrări cu metadate. Fișierele CSS/JS instalate au aceleași SHA-256 ca sursa și ZIP-ul local. Nu există helper sau hook QA rămas în public/MU-plugins.

CI a expus și o tranziție reduced motion în motorul comun: preferința nativă era activă, dar starea statică nu fusese aplicată înaintea opririi animației. Ultimul cadru sincronizează acum starea și desenul static înainte să oprească schedulerul; nu există timer sau buclă suplimentară. Scenariul live a trecut apoi în 20/20 execuții. Testul suplimentar suprimă numai notificarea media, păstrează preferința reală a browserului și verifică starea statică a tuturor scenelor și oprirea desenului.

După corecție au trecut buildul, WPCS, 46 teste PHPUnit / 394 aserțiuni, integrarea și migrarea/rollback, plus toate cele **35 de teste Contact/About/homepage/Servicii** în 2,7 minute. Referințele vizuale sunt păstrate.

Testele About folosesc contextul gestionat de Playwright, evitând închiderea manuală în timpul colectării trace-ului. Verificările hero/pointer/pauză și atelier/scroll/tastatură/viewport sunt scenarii independente; toate aserțiunile și limita de 30 s sunt păstrate. Au trecut 12/12 execuții ale scenariului gestionat și apoi **20/20 execuții** ale celor cinci scenarii separate în 56,5 s. Suita completă are acum 91 de scenarii. Această organizare a testelor nu schimbă componentele instalate.

Corecția `206b357` este instalată pe staging, cu rendererul anterior păstrat privat. SHA-256 al JavaScriptului servit prin HTTP este identic cu sursa și ZIP-ul final. Toate cele 39 de înregistrări cu metadate, setările Zion și de citire sunt identice cu snapshotul final. Au fost reluate tranzițiile static/animat pe Contact la 390/768/1024/1440 px și homepage la 390/1440 px, plus pauza comună; zero overflow și fără erori JS în paginile verificate. Niciun test suplimentar de e-mail real pe staging.

**Validare finală:** [GitHub Actions 43240756538](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43240756538), pentru `4e9843b`, este verde: **91/91 teste Playwright** în 9,2 minute, **46 teste PHPUnit / 394 aserțiuni**, build reproductibil, WPCS, integrare și migrare/rollback trecute. Arhivele Theme/Core 1.1.23 și raportul sunt finalizate pe `main`. Configurarea transportului și testarea e-mailurilor reale rămân pentru etapa ulterioară cerută de utilizator; producția nu a fost modificată.

Pagina Contact lipsea din vechile fixture-uri locale. Crearea ei activează automat legăturile native Contact din header și footer; cele nouă referințe generale au fost actualizate pentru această schimbare intenționată, fiecare având înălțimea mărită cu exact 32 px pentru rândul nou din footer. Pe staging destinația și aceste legături existau deja; nu sunt rescrise meniurile sau footerul.

## Revenire

Backupurile SQL, Theme/Core, configurație și snapshotul celor 39 de înregistrări editoriale/CF7 rămân private pe staging. Revenirea restaurează componentele anterioare și numai pagina/formularul schimbate din snapshot sau revizia verificată. Nu se rulează o restaurare SQL completă peste alte modificări între timp. Producția nu este modificată.
