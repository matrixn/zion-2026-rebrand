# Preview-uri și taguri · 1.1.13

## Selectoare în administrare

Aspect → Zion Theme: Carduri produse, Pagini individuale produse, Coș de cumpărături și Finalizare comandă folosesc aceeași componentă de preview. Variantele și explicațiile sunt lângă o imagine de maximum 480 px, cu așezare verticală pe ecrane înguste.

Click pe imagine deschide un dialog modal cu versiunea mare. Escape, butonul Închide și fundalul dialogului îl închid. Focusul revine la linkul inițial. Dialogul nativ reține focusul în modal; fără suport JavaScript rămâne linkul către original. După salvarea Ajax, componenta se inițializează din nou pe formularul actualizat. Nu se adaugă dependențe npm sau opțiuni persistente.

## Categorii active

`includes/filter-labels.php` furnizează prefixul taxonomiei native pentru blocul WooCommerce de taguri eliminabile. Scriptul condițional `assets/blocks/filter-labels.js` adaugă numai atribute și clase de prezentare. Nu introduce noduri în lista reactivă WooCommerce și nu înlocuiește textul sau acțiunile native.

CSS-ul afișează o singură etichetă Categorii și numele scurt în fiecare tag. Etichetele complete ale butoanelor de eliminare rămân native și accesibile. Numele care conțin două puncte sunt păstrate: se elimină vizual doar prefixul exact furnizat de taxonomie. Alte tipuri de filtre își păstrează etichetele. La reset, tagurile de categorie nu mai sunt afișate dacă URL-ul nativ nu conține categorii selectate. Acest lucru nu schimbă interogarea sau datele WooCommerce.

Stilurile sunt limitate la catalogul Zion. Fără JavaScript, WooCommerce afișează tagurile sale standard. Nu există un motor de filtrare suplimentar.

## Validare și următorul pas

Testele dedicate verifică preview compact, imagine mare, închidere, focus restaurat, componente încadrate pe mobil, două categorii active cu un singur titlu, eliminare individuală, reset și istoric. Suita completă Playwright a trecut: **50 scenarii**, alături de build, WPCS, 41 teste PHPUnit / 359 aserțiuni, integrare și migrare/rollback pe date sintetice.

## Staging verificat

Zion Theme și Zion Core 1.1.13 au fost instalate exclusiv pe `zion-stage.on-forge.com`, din commitul `47a99f018f8a647e448501d1a8b7c3959b4ea096`. Backupul bazei, setărilor și componentelor este privat, în `.zion-migration/previews-before-1113.sql`, `previews-settings-before-1113.json` și `previews-components-before-1113.tar.gz`; jurnalul instalării este `previews-deploy-1113.log`.

- Magazin 3D: A6 C6, E82, F32 și Mercedes-Benz, un singur titlu Categorii. Eliminarea E82 a păstrat celelalte trei selecții și sortarea.
- Magazin Software: Digital Products și Wordpress Plugins au apărut în aceeași grupă. Resetarea a eliminat grupa și tagurile vizibile.
- Carduri, pagini de produs, coș și checkout: preview compact, dialog mare, Escape/buton de închidere și revenirea focusului verificate. La 390 px dialogul checkoutului s-a încadrat în ecran, iar imaginea a fost încărcată; emularea a fost eliminată la final.
- Nicio alegere de design nu a fost salvată în cursul verificării. Coșul, comenzile și producția nu au fost modificate. Consolele taburilor de administrare și catalog verificate nu au raportat erori.
- Dovezi locale, excluse din ZIP: `build/staging-admin-compact-1.1.13.png`, `build/staging-lightbox-1.1.13.png`, `build/staging-category-tags-1.1.13.png`.

Cele cinci imagini pentru Contul meu sunt concepte cu date ilustrative, generate cu imagegen. Implementarea contului se face după alegerea designului; funcțiile comerciale vor rămâne cele native WooCommerce.
