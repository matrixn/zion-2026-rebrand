# Zion Continuum pe homepage

Din 1.1.21, homepage-ul și pagina Despre noi folosesc același renderer `assets/blocks/continuum.js`, proiecție 3D și scheduler Canvas 2D, fără frameworkuri sau video.

## Scenele homepage-ului

| Secțiune | Ilustrație și interacțiune |
| --- | --- |
| Hero | Nucleu geometric, nouă secțiuni de fabricație, particule și scanare luminoasă; perspectiva răspunde cursorului, iar distanța dintre straturi urmărește derularea. |
| Magazin 3D / lumea fizică | Piesă în formă de suport, construită din straturi, cu plan de scanare. Contrast închis pe lime, din aceleași presetări native. |
| Magazin Software / lumea digitală | Panouri de cod în profunzime, conexiuni și impulsuri luminoase. |
| Cele șapte servicii | Rețea cu șapte noduri; hover sau focus pe un serviciu evidențiază nodul lui. Linkurile native rămân acțiunile principale. |
| Procesul de colaborare | Traseu Idee → Direcție → Realitate. Poziția secțiunii în derulare evidențiază etapa, fără a ascunde textele. |
| Întrebări frecvente | Linii care converg într-un nucleu luminos, lângă invitația de a începe o idee. Întrebările își păstrează comportamentul nativ. |

Produsele, fotografiile și butoanele comerciale nu primesc Canvas și nu sunt acoperite de efecte. Catalogul recent continuă să folosească aceeași colecție WooCommerce cu ultimele șase produse 3D eligibile. Celelalte secțiuni păstrează fundalul CAD existent, legând vizual întreaga pagină.

## Editare și instalare

Textele, secțiunile și destinațiile rămân blocuri Gutenberg și Template Parts obișnuite. `includes/continuum.php` adaugă ilustrațiile exclusiv la randarea frontend a claselor existente `zion-home-*`; nu schimbă conținutul salvat, setările, produsele sau interogările. Nu rulează în admin sau la randare REST. SVG-ul din hero rămâne fallback și este ascuns numai după inițializarea reușită.

Actualizarea temei este suficientă pentru homepage-ul Zion existent, inclusiv un șablon salvat în Site Editor care păstrează clasele componentelor. Nu se rulează reseeding sau o înlocuire editorială pe staging. Înaintea instalării, salvează privat baza de date, componentele, setările și starea paginilor, navigației, stilurilor și șabloanelor. Compară starea editorială după actualizare. Revenirea la arhiva componentelor precedente elimină decorația fără să necesite restaurarea conținutului.

## Mișcare și resurse

Fiecare ilustrație are un buton accesibil de pauză/reluare, care controlează toate scenele și fundalul CAD al homepage-ului. Există o singură buclă `requestAnimationFrame`; se desenează numai scenele vizibile, iar documentele ascunse opresc bucla. Schedulerul limitează frecvența la aproximativ 30 de cadre/s pe desktop și 24 pe mobil; performanța efectivă depinde de dispozitiv. Numărul particulelor și rezoluția sunt limitate pe mobil. Paleta este citită din variabilele `theme.json`.

Preferința `prefers-reduced-motion`, inclusiv schimbată cu pagina deschisă, și setarea existentă de reducere a mișcării afișează ilustrații statice. Fără JavaScript sau fără suport Canvas, rămân SVG-ul original și conținutul complet. Nu există scroll-jacking, secțiuni fixate suplimentare, animații ale textului sau modificări ale navigării și ale cumpărării.

Rendererul se încarcă o singură dată pe homepage sau Despre noi. Cataloagele și paginile fără aceste componente nu îl încarcă.

## Verificări

Testele browser verifică toate cele șase desene, proporțiile Canvas, 390/768/1024/1440 px, lipsa overflow-ului, păstrarea produselor și serviciilor, pauză/reluare din tastatură, oprirea scenelor nevizibile, etapele procesului la scroll, preferințele de mișcare și fallbackul fără JavaScript. Cadrul hero este măsurat separat față de viewport, deoarece un container cu `overflow-x: clip` poate ascunde o margine ieșită chiar când documentul nu raportează overflow. Testele existente continuă să valideze homepage-ul în editorul nativ, ordinea produselor și pagina Despre noi. Integrarea verifică lipsa modificărilor în conținut și setări la randare.
