# Arhitectură și convenții

| Director | Rol |
| --- | --- |
| `themes/zion-theme/` | Block Theme cu `theme.json` v3, șabloane HTML, template parts, patterns și CSS propriu. |
| `plugins/zion-core/` | Taxonomia `zion_shop`, shortcode-ul `zion_catalog`, instrumentul de clasificare și jurnalul. |
| `config/` | Date demonstrative fictive, independente de codul temei. |
| `scripts/` | Build CSS și seed prin WP-CLI. |
| `tests/` | PHPUnit, integrare WordPress/WooCommerce și Playwright. |
| `.github/workflows/` | CI cu același `.wp-env.json` folosit local. |

## Identitate vizuală

Homepage-ul recompune referința „CSS badge buton nou” cu blocuri native. Păstrează accentul lime, etichetele tehnice, grila inspirată de CAD, secțiunile deschise și vitrinele separate. Ilustrația CAD este un SVG original decorativ. Secțiunea „Din atelier” folosește blocul WooCommerce Product Collection. Fonturile de sistem evită cererile externe. Headerul, footerul și homepage-ul se editează în Site Editor. Culorile și fonturile pornesc din `theme.json`. CSS-ul adaugă layout și stări de interacțiune.

Presetările din `theme.json` oferă editorului aceleași culori, dimensiuni de text și spațieri folosite în componente. Varianta deschisă folosește culoarea Paper din aceeași paletă. La 820 px, grila eroului și cea de produs trec la o coloană. La 520 px, cardurile de servicii, vitrinele și footerul se așază vertical. Navigația mobilă folosește comportamentul nativ al blocului WordPress Navigation, iar tranzițiile respectă `prefers-reduced-motion`.

Jurnalul, arhivele și căutarea împart pattern-ul `post-list` cu Query Loop nativ. Catalogul și căutarea de produse împart pattern-ul `product-catalog`, iar șablonul de produs folosește blocurile WooCommerce pentru galerie, preț, adăugare în coș și detalii. Șabloanele și părțile editate și salvate în baza de date au prioritate față de fișierele temei.

## Date și vitrine

### Setările temei

Control Center înregistrează prin WordPress Settings API o singură opțiune, `zion_theme_settings`. Registrul din `includes/class-zion-theme-settings.php` definește tipurile, valorile implicite, limitele, secțiunile, dependențele și contextele. Formularele și resolverul folosesc același registru.

Schema 2 stochează numai valorile explicite în `global`, `shops` și `items`. În `items`, valorile sunt grupate după tipul de conținut și ID. Un export cu schema 1 și câmpul `settings` este migrat în `global`. Versiunile necunoscute și valorile incompatibile sunt respinse înainte de scriere.

Pentru o setare aplicabilă, resolverul încearcă valoarea explicită de pe produs sau pagină, apoi vitrina, valoarea globală și valoarea implicită. O pagină de vitrină își primește contextul din slug, iar un shortcode din atributul `shop`. Un produs atribuit exact unei vitrine o moștenește. Produsele cu două vitrine sau fără clasificare folosesc setările globale în lipsa unui override explicit.

Formularele folosesc nonce și capabilitatea `edit_theme_options`. Pentru un override, contul trebuie să poată edita și elementul WordPress. Exportul și importul lucrează cu același document versionat. Importul verifică tipul și permisiunile pentru fiecare ID și acceptă până la 256 KiB. Revizia formularului detectează dacă setările s-au schimbat de când a fost deschis.

### Catalog

WooCommerce rămâne sursa pentru produse, vizibilitate, prețuri, stoc, coș și checkout. Zion Core inițializează termenii `3d` și `software` și expune clasificarea în editorul și lista de produse. Un produs virtual sau descărcabil poate aparține oricărei vitrine.

Pe o pagină WordPress, inserează un bloc Shortcode cu `[zion_catalog shop="3d"]` sau `[zion_catalog shop="software" limit="12" columns="3"]`. Folosește slugurile `magazin-3d` și `magazin-software` pentru linkurile din tema implicită. Shortcode-ul păstrează interogarea WooCommerce pentru vizibilitate, sortare și paginare și adaugă relația cu vitrina și categoria selectată doar pe durata randării. Tema transmite setările de layout și filtru din Control Center. Fiecare pagină de vitrină folosește o singură listare cu paginare.

Planurile de clasificare au cel mult 20 de produse și sunt asociate operatorului timp de 15 minute prin Transients API. Dry-run calculează aceleași rezultate în memorie. La confirmare, Zion Core verifică din nou permisiunile și relațiile fiecărui produs, înregistrează intenția într-o intrare WordPress privată și salvează rezultatul fiecărei scrieri. Jurnalul folosește tipul intern `zion_shop_journal` și API-urile WordPress pentru conținut. [Instrucțiuni pentru vitrine](storefronts.md)

Seed-ul rulează doar în WP-CLI, cu tipul de mediu WordPress `local`. Creează paginile și produsele lipsă, identifică produsele după SKU, importă ilustrațiile demonstrative și setează numele site-ului și moneda demo. Folosește-l în instanțele dedicate acestui repo.

## Convenții

Clasele CSS și simbolurile PHP folosesc prefixe Zion. Pentru layout nou, preferă blocuri, patterns și template parts. Adaugă CSS în `assets/src/zion.css`, rulează build-ul și include rezultatul din `assets/dist/`. Când modifici comportamentul unui catalog, verifică ambele vitrine și catalogul standard WooCommerce.
