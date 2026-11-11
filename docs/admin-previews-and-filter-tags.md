# Preview-uri și taguri · 1.1.13

## Selectoare în administrare

Aspect → Zion Theme: Carduri produse, Pagini individuale produse, Coș de cumpărături și Finalizare comandă folosesc aceeași componentă de preview. Variantele și explicațiile sunt lângă o imagine de maximum 480 px, cu așezare verticală pe ecrane înguste.

Click pe imagine deschide un dialog modal cu versiunea mare. Escape, butonul Închide și fundalul dialogului îl închid. Focusul revine la linkul inițial. Dialogul nativ reține focusul în modal; fără suport JavaScript rămâne linkul către original. După salvarea Ajax, componenta se inițializează din nou pe formularul actualizat. Nu se adaugă dependențe npm sau opțiuni persistente.

## Categorii active

`includes/filter-labels.php` furnizează prefixul taxonomiei native pentru blocul WooCommerce de taguri eliminabile. Scriptul condițional `assets/blocks/filter-labels.js` adaugă numai atribute și clase de prezentare. Nu introduce noduri în lista reactivă WooCommerce și nu înlocuiește textul sau acțiunile native.

CSS-ul afișează o singură etichetă Categorii și numele scurt în fiecare tag. Etichetele complete ale butoanelor de eliminare rămân native și accesibile. Numele care conțin două puncte sunt păstrate: se elimină vizual doar prefixul exact furnizat de taxonomie. Alte tipuri de filtre își păstrează etichetele. La reset, tagurile de categorie nu mai sunt afișate dacă URL-ul nativ nu conține categorii selectate. Acest lucru nu schimbă interogarea sau datele WooCommerce.

Stilurile sunt limitate la catalogul Zion. Fără JavaScript, WooCommerce afișează tagurile sale standard. Nu există un motor de filtrare suplimentar.

## Validare și următorul pas

Testele dedicate verifică preview compact, imagine mare, închidere, focus restaurat, componente încadrate pe mobil, două categorii active cu un singur titlu, eliminare individuală, reset și istoric. Suita completă Playwright a trecut: **50 scenarii**, alături de build, WPCS, 41 teste PHPUnit / 359 aserțiuni, integrare și migrare/rollback pe date sintetice. Verificarea pe staging urmează după publicarea sursei.

Cele cinci imagini pentru Contul meu sunt concepte cu date ilustrative, generate cu imagegen. Implementarea contului se face după alegerea designului; funcțiile comerciale vor rămâne cele native WooCommerce.
