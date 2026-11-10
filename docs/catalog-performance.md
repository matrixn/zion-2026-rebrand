# Performanța vitrinelor în faza 0.5

Măsurătorile au fost făcute pe instanța locală de teste wp-env cu WordPress 6.9.4, WooCommerce 10.8.1, PHP 8.3 și MariaDB 10.11. Catalogul conținea 1.000 de produse demo suplimentare, câte 500 în fiecare vitrină, inclusiv două produse variabile cu câte două variații. Cele patru produse din seed-ul standard au rămas active.

Pe acest mediu, încărcarea datelor extinse a durat aproximativ cinci minute, iar curățarea a durat aproximativ șapte minute. Ambele comenzi folosesc API-urile WooCommerce și operează în instanța wp-env de teste.

Pentru fiecare URL, `npm run test:catalog-scale` măsoară prima cerere din scenariu și mediana răspunsului complet pentru cinci cereri ulterioare. Prima cerere nu implică golirea cache-urilor WordPress sau ale bazei de date. Dimensiunea este cea a documentului HTML decodat, fără fișiere CSS, JavaScript sau imagini.

| Scenariu | Rezultate | Carduri în HTML | Primul TTFB (ms) | Prima cerere (ms) | Mediană repetări (ms) | HTML (KiB) |
| --- | ---: | ---: | ---: | ---: | ---: | ---: |
| 3D, prima pagină | 502 | 12 | 663 | 669 | 312 | 192 |
| Software, prima pagină | 502 | 12 | 502 | 506 | 328 | 192 |
| 3D, filtre combinate | 91 | 12 | 578 | 581 | 309 | 207 |
| 3D, filtre combinate, pagina 2 | 91 | 12 | 290 | 294 | 285 | 208 |
| 3D, categorie comună | 100 | 12 | 456 | 460 | 263 | 191 |
| Software, categorie comună | 100 | 12 | 386 | 389 | 291 | 190 |
| 3D, categorie exclusiv Software | 0 | 0 | 352 | 353 | 234 | 94 |

Scenariul combinat folosește categoria, intervalul de preț, stocul, atributul de material, reducerile și sortarea crescătoare după preț. Scriptul verifică identificatorii și totalurile între cereri, separarea vitrinelor, lipsa duplicatelor între primele două pagini și bugetul local de 3 secunde pentru mediană.

Pentru cache, cererile repetate verifică stabilitatea rezultatelor, iar Playwright verifică navigarea în istoricul browserului și reîncărcarea unei pagini filtrate. Pentru producție, cheia de cache a paginii trebuie să varieze după URL-ul complet, inclusiv parametrii de filtrare, sortare și paginare. Măsurătorile de mai sus sunt locale, fără CDN sau plugin de page cache.

[Comenzi de reproducere și curățare](../README.md#test-de-catalog-cu-1000-de-produse)
