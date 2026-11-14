# Notificări în header · Theme/Core 1.1.42

Butonul notificării devine un clopoțel circular cu badge lime, integrat în rândul iconițelor din header. Este plasat **între cont și coș**, pe mobil și desktop. Pe telefon, aspectul de bulă cu umbră discretă îl integrează în meniul existent. Controalele contului, coșului și meniului nativ sunt păstrate.

Anunțul se deschide prin glisare verticală **sub rândul meniului**. Butonul își păstrează poziția și rămâne disponibil pentru redeschidere. Și pe PC se poate închide/deschide anunțul; nu mai este forțat deschis la trecerea pe desktop.

## Stare și accesibilitate

- Se folosește aceeași cheie `localStorage` calculată din conținutul original al blocului. Preferințele din 1.1.41 rămân valide și se aplică acum la toate lățimile.
- Starea deschis/închis se păstrează între pagini, la refresh, în Back/Forward și la redimensionare. Prima afișare, fără preferință, este deschisă. Un mesaj modificat începe o preferință nouă.
- Buton de **44 × 44 px**, clopoțel SVG decorativ, etichetă accesibilă pentru acțiunea curentă, focus vizibil, `aria-expanded` și `aria-controls`. Panoul închis este `inert` și ascuns tehnologiilor asistive.
- Animație de 260 ms, eliminată pentru mișcare redusă. Culorile folosesc tokenurile `theme.json`.
- Dacă stocarea este blocată, butonul funcționează în pagina curentă. Fără JavaScript, anunțul original și linkul rămân vizibile. Fără structura standard a headerului, controlul rămâne lângă propriul anunț.

## Implementare

Scriptul mută un singur buton în `.zion-header-tools`, înaintea blocului coșului, și mută același `<aside>` după rândul `.zion-header`. Nu clonează butonul, textul, contul, coșul sau meniul. Suportă atât linkul către coș, cât și containerul nativ de mini-coș, precum și un cont ascuns prin setările temei.

Filtrul nativ WordPress și cheia conținutului sunt păstrate. Mesajul rămâne editabil în blocul HTML original din Site Editor. Core primește numai actualizarea antetului de versiune. Nu există modificări în logica de cumpărare sau în datele comerciale.

## Verificări

- [x] Build și WPCS.
- [x] **61 teste PHPUnit / 436 aserțiuni** și integrare WordPress.
- [x] **10 teste Playwright**: comportament mobil, tastatură, ordinea cont–notificări–coș la 768/1024/1440 px, ascundere pe desktop, stări persistente la navigare/refresh/istoric pe mobil și desktop, redimensionare, mini-coș, cont ascuns, header alternativ, stocare blocată și funcționare fără JavaScript.
- [x] **2 regresii Playwright** ale navigației native desktop și mobile.

## Verificare pe live

- [x] Theme/Core **1.1.42** instalate prin încărcarea și înlocuirea nativă WordPress, după backup privat al versiunii 1.1.41.
- [x] **15 măsurători** pe homepage, cele două magazine și Contact: bula de 44 × 44 px este între cont și coș, iar panoul este după rândul meniului, fără overflow la **390/768/1024/1440 px**. Verificare suplimentară la 320 px fără overflow.
- [x] Pe desktop, închiderea golește complet înălțimea panoului, menținând clopoțelul în același loc. Starea închisă rămâne la navigare, refresh și Back/Forward și este respectată la trecerea pe telefon.
- [x] Redeschidere pe telefon, păstrarea stării deschise pe cealaltă vitrină și pe homepage desktop. Meniul mobil se deschide și se închide normal.
- [x] Aceeași cheie a notificării ca în 1.1.41; fără rescrierea preferinței la redimensionare. Textul perioadei și linkul WhatsApp sunt păstrate.
- [x] Headerul salvat în baza de date și configurațiile protejate au hash-uri identice înainte și după publicare.
- [x] **152 de fișiere** distribuite identice cu arhivele. Inventar SHA-256: `a9311ff908e0bb2463354413a71a9a3a9b098cfa1db26a5e015bad2b1eec4bc1`.
- [x] Invalidat exclusiv cache-ul Nginx dedicat zion3d.ro: **5 fișiere temporare**. GET anonim pe homepage, cele două magazine și Contact: **200**, cu noul script 1.1.42 și cheia notificării în HTML.
- [x] Fără erori JavaScript capturate în tabul folosit pentru verificarea live. Nu s-au efectuat plăți, comenzi sau trimiteri de formulare.

## Livrare

Arhivele WordPress: `build/zion-theme-1.1.42.zip` și `build/zion-core-1.1.42.zip`. Rapoartele și capturile locale sunt păstrate în `build/notification-1.1.42/`, excluse din Git.

Dovezi: `header-desktop.png`, `desktop-open.png`, `desktop-closed.png`, `mobile-open.png`, `mobile-closed.png`, `live-qa.json` și `package.json`.

Backup privat: `/home/forge/.zion-deploy/notification-1.1.42-20261114T193152Z/`. Rollbackul restaurează exclusiv cele două componente 1.1.41; mesajul și configurațiile nu necesită restaurare deoarece nu au fost rescrise. Comportamentul numai pe mobil din 1.1.41 este documentat în raportul său istoric.
