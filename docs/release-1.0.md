# Raport de migrare și release 1.0.0

**Stare. Pregătire locală pe date sintetice. Lansarea pe zion3d.ro rămâne neaprobată.**

Mediu de referință pentru teste. WordPress 6.9.4, WooCommerce 10.8.1, PHP 8.3 în wp-env și MariaDB 10.11. Inventarul folosește o pagină de test cu markeri Elementor și XStore. Datele, fișierele și setările site-ului real nu au fost accesate.

## Verificat local

- [x] Build și PHPCS. PHPUnit 32 de teste și 283 de aserțiuni. Integrare catalog 51 de verificări.
- [x] Playwright 40 de teste în suita completă. Cele patru verificări afectate de corecțiile de accesibilitate au trecut din nou pe buildul final, inclusiv captura vizuală la 1024 px.
- [x] Auditul JSON inventariază conținutul, dependențele detectabile, URL-urile, SEO, taxonomiile, tipurile de produs și setările WooCommerce relevante.
- [x] Repetiția automată de migrare a trecut 18 verificări.
- [x] Dry-run fără scrieri. Aplicarea cere hashul planului, un export SQL cu hash verificat și un jurnal privat. Testul verifică refuzul URL-ului live și al aplicării fără aprobarea planului sau fără backup.
- [x] Repetiție aplicare și rollback. Pagina sintetică a devenit homepage, un produs a fost atribuit ambelor vitrine, iar rollbackul a restaurat conținutul, markerul Elementor, setarea WordPress și clasificarea. Prețul și metadatele SEO au fost păstrate. Repetiția folosește jurnalul pentru revenire și exportul SQL pentru verificarea precondiției.
- [x] Diff automat și crawl HTTP pentru demo. Schimbarea paginii frontale a modificat cele două rute așteptate în repetiție. Crawl-ul separat a găsit 22 URL-uri cu 200 și redirecționarea checkoutului fără coș către coș.
- [x] Checkout COD local cu adresă sintetică, produs fizic, zonă de livrare, taxă și cupon. WooCommerce a persistat comanda și totalurile. Testul a folosit gateway-ul offline WooCommerce.
- [x] Comandă completată în HPOS prin WooCommerce CRUD. Contul sintetic a primit exact o permisiune de descărcare. Testul verifică dreptul de acces, iar verificarea transferului fișierelor comerciale rămâne pe staging.
- [x] Arhivele Zion Theme și Zion Core 1.0.0. Verificarea CRC și compararea cu fișierele sursă au trecut pentru toate cele 62 de fișiere.

Pentru rezultatele detaliate locale, consultă `build/migration/` pentru audit, plan, diff, crawl, repetiție și checksum-uri, respectiv `build/lighthouse/` pentru audituri de laborator.

## Lighthouse local

| Pagină | Profil | Performanță | Accesibilitate | SEO | LCP | CLS | TBT |
| --- | --- | ---: | ---: | ---: | ---: | ---: | ---: |
| Homepage | Mobil | 96 | 100 | 100 | 2.228 ms | 0 | 15 ms |
| Homepage | Desktop | 100 | 100 | 100 | 539 ms | 0 | 0 ms |
| Magazin 3D | Mobil | 94 | 100 | 100 | 2.571 ms | 0 | 7 ms |
| Magazin 3D | Desktop | 100 | 100 | 100 | 627 ms | 0 | 0 ms |
| Produs software demo | Mobil | 93 | 100 | 100 | 2.865 ms | 0 | 46 ms |
| Produs software demo | Desktop | 100 | 100 | 100 | 615 ms | 0 | 0 ms |

Auditul inițial a semnalat elemente focalizabile în mini-coșul închis și o treaptă sărită în titlurile filtrelor. Am corectat ambele și am rerulat auditul și testele vizuale. LCP mobil a depășit 2,5 s pe catalog și produs. Aceste valori provin din laboratorul local. INP și celelalte Core Web Vitals în teren necesită trafic și măsurare pe mediul reprezentativ.

## Porți de staging

- [ ] Importă o copie autorizată și izolată a site-ului. Confirmă inventarul, pluginurile comerciale și toate dependențele de Elementor/XStore.
- [ ] Reconstruiește și aprobă conținutul real și cele șapte servicii. Compară URL-urile, sitemapul, redirecționările și SEO cu referința. Confirmă taxonomia fiecărui produs.
- [ ] Testează plăți în sandbox, webhookuri și refund, tarife reale, transferul fișierelor, contul și comenzile. Dacă există abonamente, verifică reînnoirile și anularea. Confirmă compatibilitatea HPOS a extensiilor instalate.
- [ ] Măsoară performanța și accesibilitatea cu date reprezentative, cache-ul real și CDN-ul. Core Web Vitals de teren rămân nemăsurate în această fază.
- [ ] Exersează restaurarea completă a bazei de date și fișierelor pe o clonă de sacrificiu. Execută checklistul pre/post-deploy și stabilește aprobarea de lansare.

Urmează [procedura de migrare și rollback](migration.md) pe staging.
