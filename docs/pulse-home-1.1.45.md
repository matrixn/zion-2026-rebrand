# Zion Pulse · homepage și meniu · 1.1.45

În submeniul **Servicii**, Zion Pulse primește descrierea **„Electronică, senzori și automatizări.”** și o iconiță de semnal realizată în același sistem SVG ca restul meniului. Sunt păstrate linkul, eticheta, directivele WordPress și stările de pagină curentă.

Homepage-ul adaugă **Zion Pulse** ca al optulea card de servicii, cu titlul secțiunii **„Opt puncte de plecare.”** Cardul are numărul 08, descriere, tehnologii și un circuit animat compact, adaptat din pagina serviciului. Nodurile din animația generală Continuum folosesc acum numărul real al cardurilor.

## Arhitectură și editare

- Pattern Gutenberg nou: **Zion · Homepage / Pulse**, cu titlu, descriere și etichete în blocuri native, plus shortcode-ul `[zion_pulse type="preview"]` pentru ilustrație.
- Previzualizarea folosește același fișier `pulse/hero.php`, aceeași animație și aceleași presetări native; nu există un renderer sau o paletă de brand duplicată.
- Filtrul pentru blocul nativ Shortcode execută numai shortcode-ul Zion Pulse inclus în șabloane, eliminând învelișul automat de paragraf. Sunt evitate atât afișarea codului literal, cât și celulele goale care ar rupe alinierea cardului.
- Cardul și titlul de opt servicii sunt incluse când pagina Pulse este publicată și accesibilă. În lipsa ei, patternul păstrează cele șapte servicii de bază.
- CSS/JS Pulse se încarcă anticipat în homepage pentru un layout stabil. Datele și interacțiunile comerciale nu sunt modificate.
- Animația are pauză proprie și respectă pauza generală Continuum, preferința de mișcare redusă, vizibilitatea scenei și a documentului. Butonul de pauză rămâne independent de linkul extins al cardului.
- Pe live, homepage-ul folosește șablonul `front-page` din temă și patternul `zion-theme/home`; conținutul salvat al paginii este gol. Integrarea nu necesită rescrierea paginii sau a șabloanelor salvate în baza de date.

## Verificări

- [x] Build și WPCS curate; PHPUnit **61 teste / 436 assertions**, integrare WordPress **248 verificări**, inclusiv **13** pentru Pulse.
- [x] **5 scenarii Playwright** pentru card, geometrie responsive, navigație, pauză proprie/generală și mișcare redusă.
- [x] **22 scenarii Playwright** de regresie pentru homepage Continuum, biblioteca de componente și pagina Pulse, inclusiv randarea fără JavaScript.
- [x] Verificare reală în Chrome pe live la **390/768/1024/1440 px**, fără overflow orizontal, erori de consolă sau excepții JavaScript capturate. Linkul cardului deschide `/zion-pulse/`; meniul mobil păstrează deschisă ramura activă Servicii. Descrierea și iconița sunt prezente în meniu, iar desktopul afișează descrierea în aceeași formă ca restul serviciilor.
- [x] ZIP-uri Theme/Core **1.1.45** generate, CRC valid; actualizare prin instalatorul nativ WordPress, păstrând componentele active. Inventarul celor **163 fișiere** instalate coincide cu pachetele: SHA-256 `bd14b4621d5a79a6fffe5cc241ddd9e765a86515c89a3e3d128a19b3cce52b1c`.

Verificarea anonimă a răspunsurilor HTTP a confirmat **200** pentru homepage, Pulse, Servicii și ambele magazine, descrierea și iconița meniului, exact opt carduri și o singură previzualizare Pulse în homepage, fără shortcode literal. Canonicalul Pulse rămâne corect. Cache-ul Nginx al acestui site a fost invalidat.

Starea protejată are aceeași amprentă înainte și după instalare: conținutul paginilor publicate, șabloanelor, părților de șablon, navigațiilor și headerului, configurația Zion Theme / Zion Privacy, lista modulelor active, indexarea, URL-urile site-ului și configurația WooCommerce urmărită. Nu s-a rescris conținut editorial în baza de date pentru această integrare.

Rulările preliminare au identificat învelișul de paragraf al blocului Shortcode și selectori de test care nu țineau cont de limba WordPress și ramura mobilă deja deschisă. Acestea au fost corectate; cele cinci scenarii dedicate au fost relansate cu succes după cele 22 de regresii reușite. O rulare cu wp-env oprit a fost reluată după pornirea mediului; nu reprezintă un rezultat al site-ului live.

## Backup și revenire

Backup privat înainte de actualizare: `/home/forge/.zion-deploy/home-pulse-1.1.45-20261117T203107Z/`. Conține componentele **1.1.44** și starea protejată a paginilor, șabloanelor, navigațiilor și configurației site-ului. Pentru revenire, se reinstalează numai cele două componente anterioare prin fluxul nativ WordPress și se invalidează cache-ul site-ului; nu este necesară restaurarea bazei de date.

Arhivele de livrare: `build/zion-theme-1.1.45.zip` și `build/zion-core-1.1.45.zip`. Capturile și măsurătorile se păstrează separat, în directorul ignorat `build/home-pulse-1.1.45/`.
