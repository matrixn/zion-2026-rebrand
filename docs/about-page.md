# Pagina Despre noi

`/despre-noi/` folosește șablonul HTML nativ `page-despre-noi` și conținutul salvat al paginii. Header-ul și footer-ul rămân Template Parts; textul, titlurile, nota vizuală, coloanele și butoanele sunt blocuri Gutenberg obișnuite.

## Conținut și identitate

Pagina are un hero asimetric, o notă tipografică Zion, povestea atelierului, legătura dintre producția 3D și software, trei principii de colaborare și o invitație personală la dialog. Tonul este direct, la persoana întâi plural. Textul păstrează contextul atelierului din Râmnicu Vâlcea din conținutul existent; nu adaugă nume de fondatori, portrete, statistici sau o biografie neverificată.

Culorile provin din `theme.json`. Benzile întunecate și Paper folosesc fundalul CAD animat existent. Din 1.1.20, **Zion Continuum** adaugă o sculptură Z proiectată în Canvas 2D și o secvență de derulare în patru etape: Schiță → Straturi → Obiect → Cod. Un plan luminos traversează geometria, particulele dezvăluie volumul, iar ultima etapă desface obiectul într-o rețea digitală. Monograma din hero răspunde la cursor pe dispozitive cu pointer fin. Nu folosește fotografii, video, WebGL sau frameworkuri de animație.

Rendererul se încarcă o singură dată când există componente Continuum. Folosește culorile native, puncte deterministe, o singură buclă `requestAnimationFrame`, maximum aproximativ 30 cadre/s pe desktop și 24 pe mobil, cu rezoluția Canvas limitată. Acestea sunt limite de programare, nu o garanție de FPS pe orice dispozitiv. Randarea continuă se oprește când scenele ies din viewport sau documentul este ascuns.

Din 1.1.21, rendererul comun se numește `assets/blocks/continuum.js` și folosește handle-ul `zion-continuum`. Homepage-ul reutilizează proiecția și schedulerul cu [ilustrații adaptate propriilor secțiuni](home-continuum.md); geometria și comportamentul paginii Despre noi sunt păstrate.

Derularea este nativă, fără interceptarea rotiței sau a gesturilor. Etapele pot fi alese și din butoane, inclusiv din tastatură. Controlul **Pauză animație** din hero și din scena centrală oprește simultan geometria și fundalul CAD; alegerea manuală rămâne disponibilă. Preferința sistemului `prefers-reduced-motion`, schimbată inclusiv cu pagina deschisă, și opțiunea existentă de reducere a mișcării din temă elimină scena lungă fixată și prezintă toate textele împreună cu geometria statică. Fără JavaScript rămân toate blocurile editoriale și nota tipografică. Canvas este decorativ, cu `aria-hidden`, adăugat numai la randarea frontend, fără a invalida blocurile salvate în editor.

## Editare

Deschide pagina autentificat și folosește **Editează pagina**. În lista de blocuri găsești fiecare secțiune. Modifică textele, ordinea grupurilor și destinațiile butoanelor. Păstrează clasele `zion-about` și clasele secțiunilor pentru prezentarea Zion. Șablonul se poate edita separat în Site Editor.

Pattern-ul **Zion · Despre noi / Oameni și idei** oferă layoutul inițial. Inserarea sa produce blocuri normale, fără sincronizare cu textul din PHP. Modificările autorului sunt păstrate la actualizarea temei și la reseeding. Pe un site fără pagină Contact publicată, CTA-ul inițial folosește adresa de contact; linkurile spre servicii apar doar pentru pagini publicate.

## Local și staging

`npm run demo:test` creează pagina demonstrativă doar dacă lipsește. Nu înlocuiește conținutul unei pagini existente.

Pe clona autorizată, după backup privat al bazei de date, componentelor și paginii originale:

```sh
wp eval-file /cale/catre/repo/scripts/staging-editorial.php about
wp eval-file /cale/catre/repo/scripts/staging-editorial.php about apply
```

Prima comandă este preview, fără scriere. Modul `about` vizează exclusiv `despre-noi`; scriptul refuză producția și mediile care nu sunt staging. Aplicarea folosește `wp_update_post` și păstrează ID-ul, URL-ul, titlul, autorul, data publicării, excerptul, părintele, starea și metadatele SEO. Salvează o revizie, înlocuiește conținutul cu blocuri native și elimină de pe această pagină selecția vechiului șablon și modul Elementor. Datele Elementor originale rămân disponibile în backup și în metadatele existente. O reaplicare identică nu rescrie pagina.

Pentru revenirea conținutului, folosește revizia anterioară din editor. Selecția de șablon și modul editorului se restaurează din snapshotul privat al paginii, dacă este necesar; backupul complet al bazei și arhiva componentelor permit revenirea întregii livrări pe staging. Nu rula din nou `about apply` peste modificări editoriale fără a le salva mai întâi.

Pentru actualizarea paginii native existente la Continuum, folosește modul dedicat după același backup:

```sh
wp eval-file /cale/catre/repo/scripts/staging-editorial.php about-motion
wp eval-file /cale/catre/repo/scripts/staging-editorial.php about-motion apply
```

Acest mod inserează doar grupul nou după poveste, păstrează blocurile editate și toate metadatele și salvează o revizie. Refuză o pagină fără structura About nativă; dacă secțiunea există deja, nu rescrie conținutul. Actualizarea temei singure nu modifică automat paginile salvate. Pentru modificarea textelor scenei folosește cele patru grupuri ale secțiunii „Privește cum prinde formă.” în lista de blocuri; clasele `zion-forge-*` conectează prezentarea și rendererul.

## Verificare

Testele locale verifică blocurile înregistrate, un singur H1, păstrarea editărilor la seed și inserare, idempotența, layoutul la 390/768/1024/1440 px, navigarea din tastatură, contrastul benzilor, citirea fără JavaScript și validitatea blocurilor în editorul WordPress. Testele Continuum verifică pixeli desenați, schimbarea efectivă a geometriei la derulare și cursor, pauză/reluare, butoanele etapelor, oprirea în afara viewportului, preferința de mișcare modificată în timp real și încărcarea condițională. Capturile statice About de la 390 și 1440 px sunt referințe vizuale în browserul Docker al proiectului.
