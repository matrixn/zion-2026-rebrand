# Anunț de migrare pe mobil · Theme/Core 1.1.41

**Raport istoric.** Poziționarea curentă în header și posibilitatea de restrângere pe desktop sunt documentate în [1.1.42](migration-announcement-1.1.42.md).

Pe telefon, anunțul de migrare poate fi restrâns la butonul centrat **Notificări · 1**. Butonul rămâne în afara panoului și este disponibil în ambele stări. Apăsarea lui deschide sau închide panoul printr-o animație verticală de 260 ms. Mesajul nu poate fi eliminat definitiv din interfață.

## Comportament

- Sub 768 px: buton de minimum 44 px, cu focus vizibil, număr de notificări și indicator de deschidere. Panoul folosește `aria-expanded`, `aria-controls`, `aria-hidden` și `inert`; linkul ascuns nu intră în navigarea Tab.
- De la 768 px: anunțul este integral vizibil, iar butonul mobil este ascuns. Preferința mobilă este păstrată la schimbarea dimensiunii ecranului.
- Starea deschis/închis se salvează în `localStorage`, pe același domeniu și în același browser, inclusiv între pagini, la refresh și la revenirea prin istoricul browserului. Nu există expirare impusă; ștergerea datelor browserului resetează alegerea.
- Cheia este calculată din conținutul blocului HTML la randarea WordPress. Același mesaj are aceeași cheie în toate paginile. Editarea mesajului începe o preferință nouă și afișează anunțul deschis.
- Dacă stocarea browserului este blocată, butonul rămâne funcțional în pagina curentă, dar alegerea nu poate fi păstrată între pagini. Fără JavaScript, mesajul original și WhatsApp rămân accesibile.
- Animația este eliminată pentru `prefers-reduced-motion: reduce`. Culorile și spațierea folosesc presetările native din `theme.json`.

## Arhitectură și administrare

Conținutul rămâne blocul HTML original din Header, documentat în [versiunea de conținut 1.0.1](migration-announcement-1.0.1.md). Tema adaugă doar o cheie stabilă și încarcă `assets/blocks/announcements.js` când randarea nativă găsește acel `<aside class="zion-migration-announcement">`. În administrare și REST, blocul rămâne nemodificat. Scriptul păstrează nodurile mesajului și linkul original în panoul animat; nu clonează meniul sau Headerul.

Stilurile sunt în `assets/src/zion.css` și în fișierul compilat. Nu există biblioteci noi, cereri Ajax, opțiuni WordPress duplicate sau schimbări ale coșului, checkoutului și plăților. Core primește exclusiv actualizarea antetului de versiune pentru cele două pachete 1.1.41.

Pentru editare sau eliminare se folosește blocul HTML din Header în Site Editor. Eliminarea lui oprește și încărcarea scriptului dedicat. Nu există un al doilea editor sau o copie a mesajului în codul JavaScript.

## Verificări locale

- [x] Build și WPCS.
- [x] **61 teste PHPUnit / 436 aserțiuni**.
- [x] **7 teste Playwright** în instanța locală de teste: deschidere/închidere cu animație, tastatură și link ascuns, persistența ambelor stări la navigare/refresh/istoric, desktop la 768/1024/1440 px, stocare blocată, mișcare redusă și funcționare fără JavaScript.
- [x] Integrare WordPress: încărcare condițională, cheie stabilă între pagini, cheie nouă pentru mesaj modificat și păstrarea conținutului/linkului originale.
- [x] **2 regresii Playwright** pentru submeniul desktop accesibil la tastatură și deschidere/închidere meniu mobil.

## Verificarea pe live

- [x] Theme/Core **1.1.41** instalate prin mecanismul nativ de încărcare și înlocuire WordPress. Core are numai antetul de versiune schimbat.
- [x] La 390 px, butonul este centrat în lățimea utilă a paginii și are **44 px** înălțime; anunțul restrâns păstrează butonul, iar panoul are înălțime zero.
- [x] Starea închisă păstrată între homepage, Magazin 3D, Magazin Software și refresh. Starea deschisă păstrată pe Contact și la navigarea Back/Forward. Cheia randată este identică pe aceste pagini.
- [x] Meniul mobil se deschide și se închide cu anunțul prezent. La **768/1024/1440 px**, anunțul este integral vizibil. Zero overflow orizontal la toate cele patru lățimi.
- [x] Mesajul **14–15 noiembrie 2026** și linkul WhatsApp original sunt păstrate. Conținutul Headerului din baza de date și configurațiile protejate au aceleași hash-uri înainte și după publicare.
- [x] **152 de fișiere** instalate identice cu arhivele. Inventar SHA-256: `ad78499239422e7c84af115b73ffb22faf5ef9556dc676a0c74fb35cf2394512`.
- [x] Invalidat exclusiv cache-ul Nginx dedicat zion3d.ro: **18 fișiere temporare**. GET anonim pe homepage, cele două magazine și Contact: **200**, cu cheia notificării și scriptul 1.1.41 în HTML.
- [x] Fără erori JavaScript capturate în verificarea browserului live. Nu s-au efectuat comenzi, plăți sau trimiteri de formulare.

## Livrare

Arhive: `build/zion-theme-1.1.41.zip` și `build/zion-core-1.1.41.zip`. Preferința clientului nu este salvată în HTML-ul paginii și nu depinde de cache-ul serverului.

Backup privat al componentelor 1.1.40: `/home/forge/.zion-deploy/notification-1.1.41-20261114T191029Z/`. Pentru rollback se restaurează numai Theme/Core; Headerul și setările nu au fost rescrise. Dacă Headerul este editat ulterior, acele editări trebuie păstrate.

Dovezi locale excluse din Git: `build/notification-1.1.41/mobile-open.png`, `mobile-closed.png`, `desktop.png`, `live-qa.json` și `package.json`. Testele automate folosesc exclusiv date sintetice și instanța locală de teste.
