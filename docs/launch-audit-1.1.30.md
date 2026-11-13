# Audit de lansare · Zion 1.1.30

Audit efectuat la 13 noiembrie 2026 exclusiv pe `https://zion-stage.on-forge.com`. Producția și subdomeniul ZionFood nu au fost modificate. Stagingul rămâne **noindex**, conform alegerii proprietarului.

## Corecții aplicate

- Backup privat SQL, Theme/Core, wp-config, Nginx și snapshot de **125 de înregistrări**, cu toate metadatele și taxonomiile. Plan editorial cu dry-run, jurnal și verificare strictă înainte/după aplicare.
- **46 de înregistrări**: titluri și diacritice, descrieri editoriale, referințe interne, ancore externe retrase, URL de imagine duplicat, H1 dublu în Termeni, canonical-uri moștenite și patru pointeri de șablon invalizi înlocuiți cu șabloane native.
- „Elementor #5378”, „Quiz” și vechea pagină exemplu „Refund and Returns Policy” sunt Draft. Nu au fost șterse. URL-ul greșit `/termeni-si-condiitii/` redirecționează spre termenii actuali; `/shop/` și `/servicii-printare-3d/` au destinații native explicite.
- Cinci etichete de categorii corectate: Acasă, Mentenanță, Piese Semănătoare, Plantator răsaduri, Vitrină Magazine. ID-urile, slugurile, relațiile, numărătorile și metadatele termenilor sunt păstrate.
- WooCommerce a sincronizat nativ **30 de titluri de variații** cu titlul corectat al celor cinci produse părinte. Comparația a acceptat separat numai substituția exactă a prefixului părinte; toate sufixele și celelalte câmpuri au rămas identice. Snapshoturile originale sunt păstrate.
- Nu s-au schimbat URL-uri de produse, autori, prețuri, stocuri, atribute, clasificări, comenzi sau reguli financiare.
- Traduceri native românești WordPress/WooCommerce/CF7; corecție punctuală pentru entitatea invalidă `&lbquo;` din etichetele WooCommerce și „VÂLCEA” în homepage.

## SEO și sharing

Metadate server-side, descrieri și canonical-uri, Open Graph/Twitter Card, alternates reciproce RO/EN pentru ReleaseStation și JSON-LD comercial nativ WooCommerce. Tema evită un al doilea set de metadate dacă se activează un provider SEO compatibil. Conținutul protejat nu este expus prin fallback.

Homepage-ul are o copertă nouă **1734 × 907 px**, negru/lime, fabricație 3D/CAD/software, distinctă de logo. Celelalte pagini folosesc imaginea nativă sau fallbackul Zion. Assetul și promptul image_gen sunt documentate în [ghidul de lansare](launch-readiness.md).

Paginile utilitare, filtrele/sortarea și arhivele de tag/autor/dată rămân noindex. Aliasul vechi Servicii este exclus din sitemap numai dacă are canonical explicit către pagina publicată actuală. O instalare nouă cu pagină individuală `/printare-3d/` nu este colapsată automat.

## Server și administrare

`WP_HTTP_BLOCK_EXTERNAL=true` este păstrat; allowlist limitat la `api.wordpress.org,downloads.wordpress.org,s.w.org,wordpress.org`. API-urile oficiale răspund 200. În administrare au fost verificate **8 carduri de pluginuri și 20 de teme**, fără eroarea de conectare. Nu s-au instalat pluginuri sau teme terțe.

Regula Nginx exactă pentru `/robots.txt` ajunge acum la WordPress: **200 text/plain**. Nu s-a adăugat un robots.txt static cu domeniul staging. Sitemapul nativ rămâne dezactivat cât timp `blog_public=0`; indexarea se activează numai pe destinația live după validarea mutării.

## Teste

| Verificare | Rezultat |
| --- | --- |
| Build și stylesheet generat reproductibil | Trecut |
| WordPress Coding Standards | Trecut |
| PHPUnit | **61 teste / 436 aserțiuni** |
| Integrare catalog, SEO, template preflight și traduceri | Trecut, **24 aserțiuni noi** |
| Migrare, rollback și HPOS pe date sintetice | Trecut |
| Crawler și JSON-LD, Python standard library | **7/7** |
| Playwright complet local | **132/132**, 18,5 minute |
| Browser după ultimele corecții de text | **10/10**, SEO/homepage/catalog |
| GitHub Actions pentru sursa finală `1535113` | **132/132 Playwright**, 12,1 minute; restul verificărilor verzi |
| Staging: 64 pagini/articole/produse × 390/768/1024/1440 px | **256/256**, zero overflow, un H1 și o imagine OG |
| Recontrol final homepage și ambele magazine la cele patru lățimi | **12/12**, fără overflow sau entități greșite în etichete |
| Instalare comparată SHA-256 cu arhivele | **133 fișiere Theme / 12 fișiere Core**, toate identice |
| Comparație finală cu backup și planuri explicite | **125/125 înregistrări**, toate câmpurile neplanificate păstrate |
| Sitemap nativ simulat numai în procesul CLI | **4 sitemapuri**, `blog_public=0` păstrat în baza de date |

[CI final verificat](https://github.com/matrixn/zion-2026-rebrand/actions/runs/43448603756). Automatizarea folosește WordPress 7.1.4, WooCommerce 10.8.1 și PHP 8.3 fixate în wp-env. Stagingul are WordPress 7.1.4, WooCommerce 11.2.2, CF7 6.2.5 și PHP-FPM 8.5; verificările HTTP/browser pe date reale au fost efectuate acolo. Aceste verificări nu echivalează cu validarea plăților sau abonamentelor pe runtimeul staging.

## Raportul de linkuri și discrepanțe

Crawl complet anonim: **506 URL-uri / 143 răspunsuri HTML înainte**, **495 URL-uri / 143 răspunsuri HTML după**. Inventarul include cele 64 de entități publicate și variantele de URL găsite, arhivele, resursele și destinațiile externe. Paginile Draft nu sunt reintroduse în seedul public. Cele trei rute retrase sunt testate explicit pentru redirect.

Rezultat final: **zero probleme detectate** pentru titlu, descriere, canonical, Open Graph, limbă, H1, alt lipsă și JSON-LD; **zero ancore rupte și zero URL-uri malformate**. Nu s-au găsit alte linkuri editoriale interne cu eroare. Eticheta „zero” se referă la aceste verificări automate, nu la orice criteriu SEO posibil.

Cele șase răspunsuri speciale rămase sunt raportate explicit:

| URL | HTTP | Interpretare și acțiune |
| --- | --- | --- |
| `/wp-sitemap.xml` | 404 | Sondă de infrastructură, nelinkuită; intenționat dezactivată pe staging noindex. Se verifică 200 pe live. |
| `/sitemap_index.xml` | 404 | Sondă pentru provider SEO alternativ, nelinkuită; nu există un astfel de provider activ. Se folosește sitemapul WordPress. |
| `https://makerworld.com/` | 403 | Acces restricționat pentru crawler; nu există dovadă că destinația este moartă. |
| `https://www.printables.com/` | 403 | Acces restricționat pentru crawler; nu există dovadă că destinația este moartă. |
| `/cdn-cgi/l/email-protection` | 404 | Referință brută generată de Cloudflare; pe Contact, browserul afișează un link `mailto:` funcțional și zero linkuri protejate rămase. |
| `https://help.x.com/en/rules-and-policies/x-cookies` | 403 | Documentație oficială, acces restricționat pentru crawler; înlocuiește domeniul vechi Twitter. |

Duplicatele rămase corespund redirecturilor, aliasului Servicii cu canonical explicit, variantelor de Contact cu parametri, filtrelor/sortării, checkoutului gol redirecționat nativ spre coș și arhivelor utilitare noindex. Paginarea catalogului are canonical distinct. Aceste grupuri și toate paginile sursă sunt vizibile în raportul detaliat, nu sunt ascunse din rezultat.

Rapoarte locale complete: `build/launch-audit-before-1.1.30.json`, `build/launch-audit-after-1.1.30.json` și **`build/launch-audit-after-1.1.30.html`**. Ultimul este căutabil offline și conține toate URL-urile, statusurile, redirecturile, sursele, metadatele, duplicatele și excluderile. Rapoartele reale nu intră în ZIP-urile WordPress sau în Git.

## Limite și porți de lansare

- SMTP și livrarea e-mailului rămân pentru configurare ulterioară, conform solicitării proprietarului.
- Gateway-uri în sandbox, descărcări reale, abonamente dacă sunt folosite și restaurare completă pe clonă izolată.
- Datele comerciale/legale trebuie confirmate de proprietar.
- Reconcilierea comenzilor și clienților apărute pe producție după clonare; baza veche de staging nu trebuie să suprascrie comenzile recente.
- După Duplicator: domeniu/HTTPS, `WP_ENVIRONMENT_TYPE`, integrări HTTP permise explicit, indexare, robots/sitemap, canonical/OG fără staging, cache WooCommerce, loguri și performanță pe destinația reală.

Checklistul complet și procedura de rollback sunt în [Pregătire lansare cu Duplicator](launch-readiness.md). Auditul nu declară proiectul production-ready și nu confirmă indexarea efectivă în Google.
