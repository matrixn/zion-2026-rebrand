# Laborator editorial Continuum · 1.1.46

Componentă opțională pentru articole: o ilustrație schematică a trecerii de la model la culoare și descoperire. Folosește același bloc dinamic `zion/editorial`, cu `part: learning_lab`, și patternul Gutenberg **Zion · Articol / laborator de curiozitate**. Nu introduce un editor sau un sistem de persistență paralel.

## Comportament

- Trei butoane native controlează etapele și anunță starea printr-o regiune `aria-live`.
- Colorarea, întrebarea cu două răspunsuri și resetarea sunt independente pentru fiecare instanță. Nu există cereri de rețea, stocare, telemetrie sau date despre cititor.
- Animația SVG are pauză, respectă preferința de mișcare redusă și setarea existentă a temei și rulează numai când componenta este vizibilă. La ascunderea documentului este oprită.
- Fără JavaScript, toate explicațiile rămân lizibile, iar controalele sunt dezactivate. Schemă și nota editorială precizează că ilustrația nu reproduce un produs comercial sau un quiz oficial.
- Scriptul se încarcă numai când blocul este randat. CSS-ul este limitat la `.zion-learning-story` și `.zion-learning-lab`, cu presetările native din `theme.json`.
- Blocurile de text, imagini, linkuri și acordeoane rămân native. Galeria poate folosi lightbox-ul WordPress.

## Conținut editorial și confidențialitate

Articolul solicitat este salvat exclusiv ca **draft** în WordPress. Are rezumat, categorie, etichete, imagine reprezentativă și fotografii cu text alternativ și credit pentru sursa publică. Nu este publicat de acest release.

Conținutul efectiv al ciornei, corespondența, PDF-ul, fotografiile partenerului și rapoartele de previzualizare nu sunt incluse în repository sau în arhivele Theme/Core. Testele folosesc numai un articol sintetic, cu verificări împotriva executării pe un domeniu de producție.

## Validare și livrare

- [x] Build și WPCS curate.
- [x] **61 teste PHPUnit / 436 assertions** și **255 verificări de integrare WordPress**, dintre care șapte pentru încărcarea condițională, identificatori accesibili unici, ierarhie și fallback.
- [x] Șase scenarii Playwright pentru componentă, inclusiv 390/768/1024/1440 px: tastatură, colorare, răspunsuri, resetare, instanțe independente, pauză, mișcare redusă, confidențialitatea draftului și lipsa JavaScript.
- [x] **27 regresii Continuum/Pulse/bibliotecă** și **nouă regresii editoriale**. Total: **42 scenarii**, cu noua suită editorială relansată fără actualizarea capturilor și trecută integral.
- [x] Previzualizare autentificată pe live la **390/768/1024/1440 px**: zero overflow orizontal, trei fotografii încărcate cu text alternativ și imagini responsive, lightbox nativ, FAQ la mouse/tastatură și demonstrație funcțională. Zero erori de consolă sau excepții JavaScript capturate. Toate cele **opt linkuri** editoriale au răspuns HTTP **200**.
- [x] Draft verificat după reîncărcarea editorului, cu imagine reprezentativă, rezumat, categorie și etichete persistate. Accesul anonim la previzualizare răspunde **404**, fără corpul articolului. Homepage-ul și jurnalul public nu conțin draftul sau scriptul nou.
- [x] ZIP-uri Theme/Core **1.1.46**, CRC valid. Cele **166 fișiere** instalate au același inventar SHA-256 ca pachetele: `396f8c5f25032bf2b53fdfa23f085519023aa95dad139d5b9736e9f27e771260`. WordPress live: **7.1.5**. Configurația Zion Theme / Zion Privacy / WooCommerce urmărită, modulele active, indexarea, conținutul publicat, navigațiile și șabloanele au amprenta păstrată.

Capturile editoriale anterioare nu mai corespundeau tipografiei existente. Verificarea A/B a hero-ului de jurnal la 390 px a produs aceeași imagine cu CSS-ul anterior și cel nou (SHA-256 `900cc94a8986dc91d475bd441454c287590bdbd23e9152fe15edfb60db224727`). Au fost revizuite și reîmprospătate numai cele opt referințe PNG; nu s-au modificat stilurile existente ale blogului. Suita de nouă teste a trecut apoi cu comparațiile vizuale active, fără `--update-snapshots` sau `--ignore-snapshots`.

Verificările inițiale au identificat o prioritate CSS prea mică pentru pauză și mișcare redusă, corectată printr-o variabilă de animație și reguli cu aceeași specificitate. Actualizarea automată a mediului local în timpul primei rulări a fost eliminată din mediul reproductibil; rerularea a confirmat WordPress **7.1.4**, versiunea fixată. Nu au fost schimbate setările de actualizare ale producției.

Mediul wp-env păstrează WordPress 7.1.4 și versiunile fixate ale pluginurilor. `AUTOMATIC_UPDATER_DISABLED` este activ numai în configurația locală, pentru a evita actualizările automate și răspunsurile de mentenanță în mijlocul testelor. Configurația live a actualizărilor nu este modificată.

Backup privat al componentelor înainte de instalare: `/home/forge/.zion-deploy/learning-story-1.1.46-20261119T092234Z/`, versiunea anterioară **1.1.45**. Inventarul anterior a fost comparat cu arhivele versiunii 1.1.45; nu au fost suprascrise modificări de fișiere necunoscute. Revenirea reinstalează aceste componente, păstrând articolul ca draft și toate datele comerciale.

Arhive: `build/zion-theme-1.1.46.zip` și `build/zion-core-1.1.46.zip`. Capturi și rapoarte locale: `build/learning-story-1.1.46/` (ignorat de Git).
