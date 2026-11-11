# Designurile meniului Zion

În **Aspect → Zion Theme → Header și navigație**, alege **Design meniu și header**, apoi salvează. Imaginea compactă se actualizează imediat și se deschide în lightbox; Escape închide imaginea și readuce focusul la preview. Salvarea și resetarea secțiunii folosesc același flux Ajax cu spinner ca celelalte selectoare. Exportul JSON include alegerea.

| Variantă | Prezentare |
| --- | --- |
| Atelier | Header compact și dropdownuri întunecate de 320 px. Opțiunea existentă Submeniuri late îl poate extinde la două coloane. |
| Nexus Mega | Panouri pe lățimea containerului, coloane cu iconițe și descrieri. Grupurile secundare se deschid în aceeași coloană. |
| Capsule | Header flotant rotunjit și dropdownuri compacte; separatorul lime rămâne pe întreaga lățime a paginii. |
| Technical Contrast | Două rânduri pe desktop; submeniuri pe suprafața Paper cu text Ink și selecție lime. |
| Signature | Mega meniu editorial, două coloane și un accent CAD decorativ derivat din paleta temei. |

Imaginile sunt conceptele aprobate. Numărul de coloane ocupate, înălțimea și textele reale urmează linkurile și nivelurile meniului salvat. Nu se generează produse, servicii sau linkuri fictive pentru a umple un preview.

## Editare nativă

Linkul **Editează Header, logo, meniu și CTA** deschide Template Part-ul Header în Site Editor. Acolo modifici linkurile, ordinea, grupurile, logo-ul și butonul. Designul se aplică aceleiași structuri, inclusiv unui meniu existent salvat ca `wp_navigation`. Nu există o copie a meniului în opțiunile Zion.

Păstrează clasele `zion-header` pe grupul principal și `zion-navigation` pe blocul Navigație dacă reconstruiești Header-ul. `zion-header-tools` grupează căutarea, contul, coșul și CTA. Controalele native rămân în Site Editor; `header_design` este o singură setare globală din registrul existent. Adăugarea ei nu cere migrarea schemei v2: configurațiile vechi moștenesc Atelier, iar selecția explicită se validează la import/salvare.

Logo-ul folosește Site Logo și identitatea WordPress. Fișierul de brand `assets/brand/zion-logo.webp` provine din logo-ul public Zion furnizat ca referință; seed-ul local îl importă numai când nu există un logo configurat. Instalarea temei nu înlocuiește automat un logo existent.

Meniul implicit oferă Servicii, Software cu WordPress & WooCommerce și Synology DSM când există paginile corespunzătoare, Zion Food și Resurse. Paginile lipsă nu produc linkuri nefuncționale. Un Header/meniu personalizat salvat în baza de date are în continuare prioritate asupra modelului implicit.

## Stare activă și interacțiune

WordPress furnizează linkurile și marcajele serverului. Scriptul Zion completează selecția după originea și calea URL, ignorând query parameters și slash-ul final. Pagina exactă primește `aria-current="page"`; nivelurile părinte primesc accentul vizual. Grupurile fără URL și linkurile externe nu sunt confundate cu pagina curentă. Marcajul se recalculează la revenirea prin istoricul browserului. Pentru produse, opțiunea existentă **Evidențiază vitrina activă** controlează evidențierea contextuală suplimentară a magazinului.

La maximum 1100 px se folosește meniul mobil, astfel încât meniurile cu multe destinații și iconițe să rămână lizibile. WordPress gestionează deschiderea și închiderea overlay-ului, Escape și blocarea derulării. Zion adaugă acordioane pe butoanele native, extinde traseul curent și ajustează ciclul Tab/Shift+Tab pentru linkurile vizibile. La revenirea la desktop, overlay-ul mobil este închis prin controlul nativ. Fără JavaScript, pe mobil linkurile se afișează în fluxul paginii.

Pentru utilizatorii autentificați, butonul de închidere și începutul listei rezervă înălțimea barei WordPress prin variabila nativă `--wp-admin--admin-bar--height`. Bara de administrare nu acoperă controalele overlay-ului.

Iconițele decorative sunt SVG-uri originale, fără font de iconițe sau bibliotecă externă. Destinațiile cunoscute sunt potrivite după eticheta meniului; etichetele personalizate rămân intacte și au un simbol neutru când nu există o potrivire. Descrierile vizuale sunt ascunse cititoarelor de ecran pentru a păstra numele scurte ale linkurilor.

## Stiluri și verificări

Culorile folosesc exclusiv presetările `theme.json`. Separatorul de 1 px este pe exteriorul Header-ului; conținutul poate rămâne în `zion-shell`. Preview-urile se încarcă numai în administrare. Accentul CAD este folosit numai de Signature. Animația overlay-ului respectă reducerea mișcării.

Technical are o singură suprascriere `!important`, limitată la fundalul submeniului din overlay: WordPress impune aici fundal transparent cu `!important`, incompatibil cu suprafața Paper. Restul prezentării folosește selectori de componente și specificitate normală.

Din 1.1.18, Nexus Mega și Signature păstrează hover-ul în spațiul dintre linkurile Header-ului și panoul aliniat la marginea lui de jos. Puntea transparentă aparține elementului nativ de meniu, apare numai la hover și ocupă spațiul de sub declanșatoarele de 44 px, fără a acoperi linkurile. Nu există temporizatoare sau un motor JavaScript suplimentar. Regresia este testată cu mouse-ul pe trasee verticale și diagonale la 1440 și 1920 px, inclusiv ieșirea din panou și click pe un link de nivel trei.

Testele verifică cele cinci preseturi la 390, 768, 1024 și 1440 px, cu un meniu WordPress salvat și trei niveluri. Acoperă păstrarea conținutului meniului, linkul activ și părinții, panouri mega aliniate la container, contrastul Technical, logo, zero overflow, separator complet, acordioane, tastatură, lightbox, salvare Ajax, reset și fallback mobil fără JavaScript. Referințele vizuale se generează și se verifică în browserul Docker descris în [ghidul CI](ci.md).
