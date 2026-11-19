# CTA editorial · 19 noiembrie 2026

Articolul [Zion 3D Print × LovePaint: curiozitatea prinde formă](https://zion3d.ro/2026/11/19/zion3d-print-lovepaint-colaborare-kituri-educative-3d/) era deja publicat la începutul acestei actualizări. Este salvat în continuare ca **Publicat**, cu data și canonicalul existente.

Butonul **„Explorează kiturile LovePaint ↗”**, cu același design nativ ca acțiunea de la final, apare imediat după H1 și înaintea metadatelor articolului. Destinația este `https://lovepaint.qtech.hr/kits`; navigarea a fost verificată efectiv în browser. Sunt prezente două legături către catalog: în hero și la finalul articolului.

## Implementare nativă

- Șablon particular WordPress **Articol · parteneriat LovePaint**, ID `900142`, slug `articol-parteneriat-love-paint`, aplicat articolului `900137` prin selectorul nativ de șabloane.
- Structura este preluată din șablonul curent **Articole singulare**, păstrând clasele Continuum și referințele către header/footer. Șablonul **Articole singulare** rămâne cel comun al celorlalte articole.
- CTA-ul este alcătuit din blocurile native Buttons și Button și folosește stilurile existente ale temei. Nu există CSS/JS sau opțiuni suplimentare pentru acest link editorial.
- Salvarea șablonului particular a fost verificată prin API-urile native WordPress și pe frontend. Amprenta corpului articolului a fost verificată înainte și după salvarea finală; textul a rămas identic.
- Conținutul și șablonul particular sunt în baza de date WordPress și sunt păstrate de un backup complet al site-ului. ZIP-urile Theme/Core distribuie în continuare componentele **1.1.46**, cu sursele neschimbate.

## Verificare live

Chrome, prima încărcare a URL-ului public, `scrollY = 0`:

| Viewport | Poziția verticală a butonului | Vizibil fără scroll | Overflow orizontal |
| --- | --- | --- | --- |
| 390 × 844 | 408–460 px | Da | Nu |
| 768 × 1024 | 372–424 px | Da | Nu |
| 1024 × 900 | 383–435 px | Da | Nu |
| 1440 × 900 | 443–494 px | Da | Nu |

Verificarea HTTP anonimă a confirmat **200**, canonicalul neschimbat, o singură acțiune de partener în hero și prezența CTA-ului original din corp. Headerul și footerul sunt prezente o singură dată. Consola paginii nu a raportat erori în verificarea efectuată. Blocurile șablonului au fost validate în editorul WordPress.

Validarea acestei actualizări editoriale constă în verificări live de afișare, navigare și HTML public. Codul Theme/Core este același ca în release-ul 1.1.46; raportul testelor acelui release rămâne în [documentația componentei](learning-story-1.1.46.md).

Capturi și rapoarte: `build/learning-story-cta-20261119/` (ignorat de Git). Copia privată a articolului, a șablonului particular și a șablonului de bază: `/home/forge/.zion-deploy/article-cta-20261119125849/`. Cache-ul Nginx al acestui site a fost invalidat după salvare.

Pentru revenirea la aspectul anterior, se selectează șablonul implicit **Articole singulare** pentru acest articol. Textul și URL-ul rămân aceleași.
