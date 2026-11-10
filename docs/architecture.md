# Arhitectură și convenții

| Director | Rol |
| --- | --- |
| `themes/zion-theme/` | Block Theme cu `theme.json` v3, șabloane HTML, template parts, patterns și CSS propriu. |
| `plugins/zion-core/` | Taxonomia `zion_shop` și shortcode-ul `zion_catalog`. |
| `config/` | Date demonstrative fictive, independente de codul temei. |
| `scripts/` | Build CSS și seed prin WP-CLI. |
| `tests/` | PHPUnit, integrare WordPress/WooCommerce și Playwright. |
| `.github/workflows/` | CI cu același `.wp-env.json` folosit local. |

## Identitate vizuală

Homepage-ul recompune referința „CSS badge buton nou” cu blocuri native. Păstrează accentul lime, etichetele tehnice, grila inspirată de CAD, secțiunile deschise și vitrinele separate. Ilustrația CAD este un SVG original decorativ. Secțiunea „Din atelier” folosește blocul WooCommerce Product Collection. Fonturile de sistem evită cererile externe. Headerul, footerul și homepage-ul se editează în Site Editor. Culorile și fonturile pornesc din `theme.json`. CSS-ul adaugă layout și stări de interacțiune.

Presetările din `theme.json` oferă editorului aceleași culori, dimensiuni de text și spațieri folosite în componente. Varianta deschisă folosește culoarea Paper din aceeași paletă. La 820 px, grila eroului și cea de produs trec la o coloană. La 520 px, cardurile de servicii, vitrinele și footerul se așază vertical. Navigația mobilă folosește comportamentul nativ al blocului WordPress Navigation, iar tranzițiile respectă `prefers-reduced-motion`.

Jurnalul, arhivele și căutarea împart pattern-ul `post-list` cu Query Loop nativ. Catalogul și căutarea de produse împart pattern-ul `product-catalog`, iar șablonul de produs folosește blocurile WooCommerce pentru galerie, preț, adăugare în coș și detalii. Șabloanele și părțile editate și salvate în baza de date au prioritate față de fișierele temei.

## Date și vitrine

WooCommerce rămâne sursa pentru produse, vizibilitate, prețuri, stoc, coș și checkout. Zion Core adaugă o clasificare independentă de categoriile și tipurile WooCommerce. Creează termenii cu slugurile `3d` și `software` în **Products → Zion shops** dacă instanța nu folosește datele demo. Selectează unul sau ambii termeni din editorul produsului. Un produs virtual sau descărcabil poate aparține oricărei vitrine.

Pe o pagină WordPress, inserează un bloc Shortcode cu `[zion_catalog shop="3d"]` sau `[zion_catalog shop="software" limit="12" columns="3"]`. Folosește slugurile `magazin-3d` și `magazin-software` pentru linkurile din tema implicită. Shortcode-ul delegă listarea și paginarea către WooCommerce și adaugă filtrul de taxonomie doar pe durata acelei randări. Fiecare pagină de vitrină folosește o singură listare cu paginare.

Seed-ul rulează doar în WP-CLI, cu tipul de mediu WordPress `local`. Creează paginile și produsele lipsă, identifică produsele după SKU, importă ilustrațiile demonstrative și setează numele site-ului și moneda demo. Folosește-l în instanțele dedicate acestui repo.

## Convenții

Clasele CSS și simbolurile PHP folosesc prefixe Zion. Pentru layout nou, preferă blocuri, patterns și template parts. Adaugă CSS în `assets/src/zion.css`, rulează build-ul și include rezultatul din `assets/dist/`. Când modifici comportamentul unui catalog, verifică ambele vitrine și catalogul standard WooCommerce.
