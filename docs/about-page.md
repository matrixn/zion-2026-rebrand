# Pagina Despre noi

`/despre-noi/` folosește șablonul HTML nativ `page-despre-noi` și conținutul salvat al paginii. Header-ul și footer-ul rămân Template Parts; textul, titlurile, nota vizuală, coloanele și butoanele sunt blocuri Gutenberg obișnuite.

## Conținut și identitate

Pagina are un hero asimetric, o notă tipografică Zion, povestea atelierului, legătura dintre producția 3D și software, trei principii de colaborare și o invitație personală la dialog. Tonul este direct, la persoana întâi plural. Textul păstrează contextul atelierului din Râmnicu Vâlcea din conținutul existent; nu adaugă nume de fondatori, portrete, statistici sau o biografie neverificată.

Culorile provin din `theme.json`. Benzile întunecate și Paper folosesc fundalul CAD animat existent, cu aceeași reducere a mișcării. Nota cu monograma Z și simbolurile pentru atelier/cod sunt prezentare CSS; nu se încarcă fotografii sau biblioteci JavaScript noi.

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

Prima comandă este preview, fără scriere. Modul `about` vizează exclusiv `despre-noi`; scriptul refuză producția și mediile care nu sunt staging. Aplicarea păstrează ID-ul, URL-ul, titlul, părintele, starea și metadatele SEO. Salvează o revizie, înlocuiește conținutul cu blocuri native și elimină de pe această pagină selecția vechiului șablon și modul Elementor. Datele Elementor originale rămân disponibile în backup și în metadatele existente. O reaplicare identică nu rescrie pagina.

Pentru revenirea conținutului, folosește revizia anterioară din editor. Selecția de șablon și modul editorului se restaurează din snapshotul privat al paginii, dacă este necesar; backupul complet al bazei și arhiva componentelor permit revenirea întregii livrări pe staging. Nu rula din nou `about apply` peste modificări editoriale fără a le salva mai întâi.

## Verificare

Testele locale verifică blocurile înregistrate, un singur H1, păstrarea editărilor la seed, layoutul la 390/768/1024/1440 px, navigarea la poveste din tastatură, contrastul benzilor, reducerea mișcării, citirea fără JavaScript și validitatea blocurilor în editorul WordPress. Capturile About de la 390 și 1440 px sunt referințe vizuale în browserul Docker al proiectului.
