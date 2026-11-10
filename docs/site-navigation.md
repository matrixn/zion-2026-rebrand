# Navigație, footer și pagini Zion

## Header

În **Aspect → Zion Theme → Header și navigație**, controlezi headerul fix, transparența pe homepage, submeniurile late, căutarea de produse, contul, mini-coșul, badge-ul și afișarea CTA. Linkul din secțiune deschide **Header** în Site Editor, unde editezi logo-ul, structura meniului și butonul. Titlul site-ului este folosit ca marcă textuală când logo-ul lipsește.

Submeniul lat pune linkurile unui dropdown WordPress în două coloane pe desktop. Meniul mobil, deschiderea și navigarea cu tastatura folosesc blocul Navigație. Căutarea trimite `s` și `post_type=product` către WooCommerce. Contul și mini-coșul folosesc blocurile native, iar badge-ul citește cantitatea din coșul WooCommerce. [Referința blocurilor WooCommerce](https://developer.woocommerce.com/docs/block-development/reference/block-references/)

## Footer Zion Nexus

Footerul folosește grila tehnică discretă din paleta temei. În **Footer**, alegi ce grupuri se afișează. În Site Editor modifici CTA, grupurile de linkuri, contactul, Social Icons și copyrightul. Modelul implicit afișează contactul când există o pagină publicată cu slugul `contact`, politica de confidențialitate aleasă în WordPress și termenii selectați în WooCommerce. Adaugă profilurile sociale ale site-ului în blocul Social Icons.

Un Header sau Footer salvat anterior în baza de date are prioritate față de modelul din temă. Poți prelua blocurile noi în copia editată sau poți restaura modelul temei din Site Editor. Pentru un grup mutat ori reconstruit în editor, păstrează clasa Zion din model dacă vrei să o controlezi din Control Center.

## Jurnal și articole

**Blog și articole** controlează grila sau lista, câte articole încarcă interogarea WordPress pe pagină și ce metadate apar. Numărul de articole moștenește setarea WordPress de citire până când alegi explicit o valoare. Articolele asociate au categorii comune. Distribuirea include e-mail și copierea linkului, iar comentariile respectă disponibilitatea stabilită în WordPress.

Pentru un articol, deschide **Setări Zion** din lista de articole sau introdu ID-ul în **Instrumente**. Poți suprascrie afișarea metadatelor, a comentariilor, a distribuirii și a recomandărilor.

## Pagini standard

**Pagini standard** controlează lățimea, antetul, titlul, imaginea reprezentativă, breadcrumbul și bara laterală. **Setări Zion** de pe o pagină permite excepții pentru acea pagină. Breadcrumbul urmează ierarhia WordPress. **Bară laterală pagini** este un Template Part editabil separat. Funcțiile editoriale ale șablonului standard se aplică paginilor de conținut, iar coșul, checkoutul și contul folosesc propriile configurații WooCommerce.

## Performanță locală

În wp-env, am măsurat cinci cereri HTTP după o cerere de încălzire. Mediana răspunsului complet a fost 241 ms pentru homepage, 192 ms pentru jurnal și 202 ms pentru vitrina Software. Fiecare răspuns a conținut un singur mini-coș nativ. Măsurătorile exclud imaginile, CSS, JavaScript și procesarea în browser. Testele Playwright verifică separat bugetul de navigare și că headerul nu cere catalogul complet prin Store API.
