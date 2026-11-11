# Pagini de produs Zion

Blocul dinamic **Pagină produs Zion** randează șablonul implicit **Single Product**. Headerul, footerul și blocurile din jur rămân editabile în Site Editor. Pentru un șablon personalizat anterior, inserează blocul în zona de conținut a șablonului și elimină formularul vechi din acel șablon pentru a evita două formulare pe pagină.

## Configurare

1. În **Aspect → Zion Theme → Pagini individuale produse**, deschide **Global**, **Single Product 3D Print** sau **Single Product Software**.
2. Alege **Design pagină produs** din selector sau din cele cinci butoane cu preview, apoi salvează prin Ajax. Configurația vitrinei se scrie numai în `zion_theme_settings.shops[3d|software]`, fără opțiuni paralele. Presetul de conținut și stilul se configurează independent.
3. Din **Produse**, folosește acțiunea **Setări Zion** pentru a seta excepții pe un produs. Fiecare câmp este rezolvat în ordinea produs, vitrină, global și valoare implicită.

**Auto** folosește Physical 3D sau Software când produsul aparține unei singure vitrine. Pentru un produs din ambele vitrine sau neclasificat, folosește tipul fizic sau virtual WooCommerce. **Minimal** afișează informațiile de cumpărare. **Premium** adaugă stil accentuat și toate secțiunile editoriale disponibile.

Reordonează secțiunile și componentele rezumatului prin chei separate prin virgulă. De exemplu, `version,hero,faq` pune versiunea înaintea galeriei și a rezumatului, apoi FAQ. Secțiunile omise din listă sunt adăugate în ordinea implicită. Poți alege și stilul panourilor sau cheile secțiunilor care primesc un contur accent.

## Cinci designuri, din versiunea 1.1.10

| Design | Desktop | Mobil |
| --- | --- | --- |
| Atelier | Galerie și rezumat în două coloane; miniaturi orizontale | O coloană |
| Technical | Galerie cu miniaturi verticale și panou cu contur tehnic | Galerie cu miniaturi orizontale, apoi rezumat |
| Immersive | Scenă panoramică deschisă și panou întunecat de cumpărare | Galerie și panou vertical |
| Focus cumpărare | Introducere, galerie, panou sticky de la 1200 px | Introducere, galerie, cumpărare; două coloane de la 821 px |
| Editorial Premium | Introducere centrată, galerie lată și rezumat dedesubt | Flux vertical și bară de acces la cumpărare |

`product_design` folosește aceeași rezolvare ca restul setărilor: override produs → vitrină unică → global → Atelier. La un produs neclasificat sau în ambele magazine nu există o vitrină unică; alege override-ul produsului dacă designul Global nu este potrivit. URL-ul și refererul nu schimbă designul.

Focus și Editorial mută titlul și descrierea scurtă în zona de introducere, păstrând ordinea acestora; celelalte componente urmează ordinea rezumatului. Celelalte trei variante păstrează întregul rezumat în același panou. Acest lucru nu dublează titlul sau formularul. Editorial activează implicit bara sticky pentru produsele eligibile; o setare explicită Ascunde are prioritate.

Miniaturile verticale apar numai când există mai multe imagini. Galerie fără miniaturi: fotografia ocupă întreaga lățime disponibilă. Preview-urile conceptuale sunt disponibile numai în administrare; valorile și imaginile reale vin din produs. Pentru Software, conținutul rămâne cel al presetului Software/Auto și al metadatelor existente, indiferent de designul ales.

## Conținut și cumpărare

- Galeria, zoomul, lightboxul și formularul folosesc componente WooCommerce. Variația, cantitatea și trimiterea la coș rămân în același formular. Ascunderea selectorului de variații ascunde formularul pe acel produs variabil. Pentru cantitate ascunsă se păstrează valoarea implicită, iar produsele grupate își păstrează câmpurile de cantitate pentru fiecare produs.
- Bara opțională și **Cumpără acum** sunt disponibile pentru produse simple și variabile cumpărabile și în stoc. Bara trimite formularul nativ când selecția este validă și duce la selector când lipsește o opțiune. **Cumpără acum** deschide checkoutul după ce WooCommerce confirmă adăugarea în coș.
- În editorul WooCommerce, caseta **Informații produs Zion** conține compatibilitate, beneficii, fișiere și formate, versiune software, changelog, URL de documentație și FAQ. Pentru FAQ, scrie câte o linie în formatul `Întrebare | Răspuns`. Folosește câmpul WooCommerce de descărcări pentru fișiere protejate. În pagina publică pot apărea numele fișierelor, iar URL-urile protejate rămân în WooCommerce.
- Componenta **Atribute esențiale** arată cel mult trei atribute WooCommerce publice lângă cumpărare, cu cel mult trei valori fiecare. Poate fi ascunsă sau reordonată prin aceleași controale.
- Specificațiile folosesc atributele și datele native despre dimensiuni. Recomandările folosesc produsele asociate, upsells și cross-sells WooCommerce și apar doar când există produse publicate și vizibile.
- Fără JavaScript, imaginile rămân vizibile, formularul simplu poate adăuga produsul și bara rămâne un link către formular. Selecția dinamică a variațiilor cere JavaScript. [Galeria WooCommerce](https://woocommerce.com/document/adding-product-images-and-galleries/) · [Variațiile WooCommerce](https://woocommerce.com/document/variable-product/)
