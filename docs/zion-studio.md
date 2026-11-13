# Zion Studio · administrare 1.1.27

Intrarea este **Aspect → Zion Theme**. Panoul reunește designul, documentele native și configurațiile Zion existente. Nu este necesar să deschizi Site Editor pentru operațiile de mai jos.

## Design și identitate

În **Identitate și design** găsești:

- Colorpicker WordPress și cod HEX pentru cele șapte culori ale paletei temei. Indicatorul de contrast avertizează când textul/fundalul scade sub 4,5:1.
- Selectoare de fonturi de sistem, dimensiunile native de text, înălțimea rândurilor și spațierile din `theme.json`.
- Lățimea conținutului și lățimea extinsă, rotunjirea butoanelor și a cardurilor Zion.
- Preview pentru paginile publicate, la 390/768/1024/1440 px. Modificările se văd înainte de salvare. Cadrul este intenționat static: nu execută scripturi, navigare, formulare sau cumpărări. Verificarea interacțiunilor se face pe pagina normală.
- Resetare individuală sau a tokenurilor gestionate, import JSON în preview și export JSON. Importul nu salvează automat; este validat și la salvare.
- Numele site-ului, descrierea, logo-ul și pictograma prin biblioteca media nativă. Identitatea are propriul buton de salvare și necesită `manage_options`.

Valorile implicite vin din `theme.json`. Modificările vizuale folosesc **același document nativ `wp_global_styles`** ca WordPress. Nu sunt copiate în `zion_theme_settings`. Sunt modificate numai tokenurile din registru, păstrând presetările și stilurile de bloc neadministrate aici. Resetarea elimină suprascrierea, nu copiază valorile implicite în baza de date.

Fonturile sunt locale, de sistem; panoul nu descarcă fonturi externe. Dimensiunile acceptă valori limitate în px/rem; containerele acceptă 240–2400 px și lățimea extinsă trebuie să fie cel puțin egală cu cea de conținut. Câmpurile invalide nu produc scrieri parțiale.

## Conținut, header, footer și meniuri

În **Zion Studio**, alege tipul documentului, caută și selectează documentul. Lista este paginată, cu maximum 50 de rezultate pe pagină.

- **Pagini și articole:** canvas Gutenberg, inserare de blocuri, inspector nativ, titlu, stare, calendar dată/oră în fusul site-ului, imagine reprezentativă, rezumat unde există și șablonul paginii. Data nu este rescrisă dacă nu o modifici. Programarea folosește publicarea nativă WordPress.
- **Header, footer și componente:** aceleași `wp_template_part`, editate cu blocuri native. Pattern-urile temei sunt rezolvate pentru canvas; salvarea creează suprascrierea nativă, păstrată la actualizarea temei.
- **Șabloane:** aceleași `wp_template` și blocurile lor. Blocurile dinamice și entitățile asociate continuă să folosească API-urile WordPress.
- **Meniuri:** formular structurat pentru etichete, adrese, pagini publicate, filă nouă, submeniuri și ordinea linkurilor prin butoane accesibile. Datele sunt blocuri native în același `wp_navigation`; afișarea pe site rămâne rendererul WordPress. Blocurile nerecunoscute sunt păstrate.
- **Navigația din header:** selectezi meniul nativ conectat și opțiunile mobile/submeniuri în inspector. Legătura „Editează linkurile…” deschide meniul în altă filă Zion Studio, fără să pierzi headerul curent. Meniurile incluse direct în header rămân blocuri native editabile în canvas.

Salvarea este explicită. Pentru documentul principal, o revizie bazată pe câmpurile editabile detectează modificările concurente și refuză o suprascriere învechită. Erorile rămân vizibile, iar modificările nesalvate avertizează la părăsirea paginii. Salvarea mai multor entități native asociate nu este o tranzacție de bază de date; o eroare intermediară poate necesita reîncărcare și verificarea entităților deja salvate.

Nu se creează un al doilea editor de produse, motor de blocuri sau depozit de meniuri. Blocurile, media, șabloanele, stilurile globale și identitatea folosesc API-urile lor native. Produsele, stocurile, comenzile, taxele, livrarea și plățile rămân în WooCommerce. Instalarea acestei versiuni nu modifică automat paginile, meniurile sau culorile existente.

## Controalele Zion existente

Celelalte secțiuni păstrează aceeași opțiune `zion_theme_settings` și același registru de validare. Panoul adaugă:

- Comutatoare pentru valori booleene, cu control de tastatură și stare accesibilă.
- Glisoare sincronizate cu câmpurile numerice și limitele registrului.
- Liste de ordonare cu bife, drag-and-drop și butoane sus/jos. Ordinea salvată rămâne formatul canonic existent.
- Căutare în setările secțiunii, fără pierderea valorilor ascunse.
- Selectoarele vizuale existente, miniaturi, lightbox, salvare AJAX și indicatoare de încărcare.

Nu sunt adăugate calendare sau colorpickere fără efect real. Calendarul se aplică publicării; culorile se aplică tokenurilor efective ale temei.

## Implementare și verificare

`class-zion-theme-design.php` derivează registrul vizual din `theme.json`. `studio.php` expune punți REST autentificate către Global Styles și documente; cere `edit_theme_options`, nonce REST și permisiunile controllerelor native. `assets/admin/studio.js` folosește pachetele WordPress deja instalate. Asseturile de authoring sunt încărcate numai în cele două secțiuni Zion relevante, fără dependențe de frontend noi.

Comenzi: `npm run build`, `composer lint`, `composer test`, `npm run test:integration`, `npm run test:migration`, `npm run test:e2e`, `npm run package`. Proiectul Playwright `studio` folosește exclusiv instanța sintetică `localhost:8917`; refuză alte ținte și restaurează stilurile/identitatea/configurația și elimină documentele proprii.

Referințe API: [editor de blocuri personalizat](https://developer.wordpress.org/block-editor/how-to-guides/platform/custom-block-editor/), [pachetul Block Editor](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/), [stiluri globale native](https://developer.wordpress.org/block-editor/reference-guides/filters/global-styles-filters/).
