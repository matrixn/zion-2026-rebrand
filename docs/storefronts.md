# Vitrinele Zion

WooCommerce are o singură pagină Shop. `/magazin-3d/` și `/magazin-software/` sunt pagini WordPress care afișează cataloage filtrate prin taxonomia `zion_shop`.

## Configurează paginile

1. Activează WooCommerce și Zion Core. Termenii `3d` și `software` sunt inițializați automat.
2. Deschide **Produse → Vitrine Zion → Creează paginile lipsă** dacă una dintre pagini lipsește. Acțiunea păstrează paginile găsite la slugurile canonice.
3. În editorul de pagini schimbă titlul și descrierea fiecărei vitrine. Păstrează slugurile și shortcode-urile `[zion_catalog shop="3d"]` și `[zion_catalog shop="software"]`. Dacă o pagină existentă are alt conținut, adaugă shortcode-ul în editor.
4. În **Aspect → Zion Theme → Magazin 3D** sau **Magazin Software**, configurează grila sau lista, coloanele pe ecran, lățimea, spațierea și numărul de produse pe pagină. Alege bara laterală sau drawer-ul, sortarea implicită și filtrele afișate.

Dacă un URL de vitrină este deja alocat paginii Shop WooCommerce, panoul semnalează conflictul. Alege o pagină Shop separată în setările WooCommerce pentru a folosi acel URL ca vitrină.

## Alege filtrele și sortarea

- Folosește atribute globale din **Produse → Atribute** pentru filtrele specifice fiecărei vitrine. De exemplu, poți folosi `material,tehnologie` pentru 3D și `platforma,format` pentru Software, dacă slugurile respective există. `auto` afișează până la șase atribute disponibile, iar `none` le ascunde.
- WooCommerce calculează categoriile, brandurile disponibile, intervalul de preț, stocul și atributele în contextul vitrinei. Pentru branduri, folosește taxonomia **Brands** din WooCommerce.
- **Recomandate** plasează întâi produsele marcate ca evidențiate în WooCommerce, apoi păstrează ordinea manuală în fiecare grup. Alte opțiuni folosesc sortarea WooCommerce după dată, preț, vânzări, rating sau ordine manuală.
- **Încarcă mai multe** extinde prima pagină prin reîncărcare, cu cel mult 96 de carduri. Linkurile numerice rămân disponibile și păstrează selecțiile. O schimbare de filtru sau de sortare revine la dimensiunea inițială.

URL-urile folosesc `categories`, `brands`, `min_price`, `max_price`, `filter_stock_status`, `filter_<atribut>`, `on_sale` și `orderby`. Paginarea nativă folosește `query-73-page` pentru 3D și `query-74-page` pentru Software. Opțiunile ascunse dispar din interfață, iar URL-urile partajate continuă să aplice filtrele WooCommerce.

## Adaugă un produs nou

1. În **Produse → Adaugă un produs nou**, completează numele, imaginile, prețul, stocul, tipul și categoria WooCommerce.
2. În câmpul **Vitrine Zion**, bifează **Magazin 3D** pentru piese, prototipuri și fișiere STL sau **Magazin Software** pentru aplicații, pluginuri și licențe. Bifează ambele dacă produsul trebuie oferit în ambele.
3. Configurează opțiunile **Virtual** și **Descărcabil** separat. De exemplu, un STL poate fi virtual și descărcabil în Magazin 3D, iar o licență Software poate fi virtuală fără un fișier atașat.
4. Publică produsul și verifică vizibilitatea WooCommerce în catalog. Un draft, un produs privat sau un produs ascuns din catalog este exclus din vitrinele publice.
5. Verifică vitrina aleasă. Un produs fără una dintre cele două clasificări rămâne identificabil ca **Neclasificat** în lista de administrare.

## Clasifică mai multe produse

1. Deschide **Produse → Vitrine Zion**. Filtrează după categorie WooCommerce și clasificare, de exemplu **Neclasificate**.
2. Selectează până la 20 de produse din pagina curentă. Alege adăugare, eliminare sau înlocuire și vitrinele țintă. Înlocuirea cu selecția de vitrine goală elimină clasificarea Zion.
3. Folosește **Dry-run** pentru a vedea rezultatul fără a salva un plan sau a scrie în jurnal. Folosește **Previzualizează** pentru a obține un plan care poate fi confirmat timp de 15 minute.
4. Compară clasificarea înainte și după, apoi apasă **Aplică modificările confirmate**. Dacă o clasificare s-a schimbat după previzualizare, generează o previzualizare nouă.
5. Deschide **Jurnalul clasificărilor** pentru dată, operator, operație și rezultatul pe produs. Jurnalul se păstrează în WordPress și este accesibil din panoul de administrare.

Operația afectează relațiile `zion_shop`. Tipul, categoriile, stocul, prețul, descărcările și comenzile rămân administrate de WooCommerce.
