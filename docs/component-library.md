# Homepage și biblioteca Zion

Șablonul **Front Page** folosește 11 pattern-uri Gutenberg din categoria **Zion · Componente**. Secțiunile pot fi mutate, înlocuite sau inserate și în alte pagini. CTA-urile către servicii și vitrine folosesc ancorele `servicii` și `magazine`. Secțiunea de recenzii afișează recenzii aprobate pentru produse publice sau citate editoriale adăugate explicit. Demonstrația locală marchează conținutul fictiv ca exemplu.

În **Aspect → Zion Theme → Instrumente → Creează paginile de servicii lipsă** poți publica Printare 3D, Proiectare 3D, Software personalizat, Platforme SaaS, Pluginuri și integrări, Aplicații Windows/Linux și Integrări AI. Pagini cu aceleași sluguri sunt păstrate. Paginile create conțin blocuri Gutenberg editabile și o descriere scurtă pentru SEO.

## Blocuri și shortcode-uri

| Bloc Gutenberg | Exemplu de shortcode |
| --- | --- |
| Zion Product Grid | `[zion_products shop="3d" limit="8" columns="4"]` |
| Zion Featured Products | `[zion_featured_products limit="4"]` |
| Zion Products by Shop | `[zion_products_by_shop shop="software" limit="6"]` |
| Zion New Arrivals | `[zion_new_products limit="8"]` |
| Zion On Sale | `[zion_on_sale limit="4"]` |
| Zion Related Products | `[zion_related_products]` |
| Zion Services Grid | `[zion_services]` |
| Zion CTA | `[zion_cta title="Discută proiectul" label="Vezi serviciile" url="/#servicii"]` |
| Zion Testimonials | `[zion_testimonials limit="3"]` |

`[zion_products shop="software" limit="6"]` folosește același motor. Colecțiile acceptă `shop="3d"` sau `shop="software"`, 1–24 produse și 1–5 coloane desktop. Folosește `paginate="true"` pentru paginare numerică. Related Products este limitat la prima pagină și folosește produsul curent sau un `product_id` explicit. Un slug invalid de vitrină produce un rezultat gol. Featured și On Sale citesc starea WooCommerce, iar New Arrivals ordonează după data publicării.

După `npm run demo:test`, deschide `/componente-zion-demo/` pentru cele nouă exemple și o comparație bloc–shortcode. Configurarea blocurilor se face în inspectorul Gutenberg. Colecțiile păstrează Product Collection, vizibilitatea WooCommerce, taxonomia Zion și cardul Zion comun. [Product Collection](https://developer.woocommerce.com/docs/block-development/extensible-blocks/product-collection-block/)

## Performanță, accesibilitate și SEO

Stilurile de colecții și cele editoriale se încarcă pe paginile care randează componentele respective. Imaginile colecțiilor încorporate folosesc încărcare întârziată, iar scriptul Quick View se încarcă acolo unde acțiunea este activă. Animația CAD respectă preferința de mișcare redusă și setarea temei. Paginarea și cumpărarea folosesc controalele native.

WordPress gestionează adresa canonică. Tema adaugă o descriere de rezervă când un modul SEO cunoscut nu preia această funcție și restabilește datele structurate Product din WooCommerce pentru șablonul Zion. Filtrele `zion_theme_meta_description_enabled` și `zion_theme_native_product_schema_enabled` permit delegarea către un alt furnizor SEO.

În wp-env, mediana a cinci răspunsuri HTTP după încălzire a fost 325 ms pentru homepage, 201 ms pentru jurnal și 269 ms pentru demonstrație. HTML-ul decodat a avut 158, 95 și respectiv 184 KiB. Jurnalul a încărcat zero fișiere ale bibliotecii. Măsurătorile locale exclud procesarea browserului și transferul imaginilor, CSS și JavaScript. Homepage-ul extins randează pe server trei colecții și livrează mai mult HTML decât versiunea 0.8.
