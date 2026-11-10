# Pagini de produs Zion

Blocul dinamic **Pagină produs Zion** randează șablonul implicit **Single Product**. Headerul, footerul și blocurile din jur rămân editabile în Site Editor. Pentru un șablon personalizat anterior, inserează blocul în zona de conținut a șablonului și elimină formularul vechi din acel șablon pentru a evita două formulare pe pagină.

## Configurare

1. În **Aspect → Zion Theme → Pagini individuale produse**, alege presetul și stilul global.
2. În secțiunea **Magazin 3D** sau **Magazin Software**, alege suprascrierile magazinului.
3. Din **Produse**, folosește acțiunea **Setări Zion** pentru a seta excepții pe un produs. Fiecare câmp este rezolvat în ordinea produs, vitrină, global și valoare implicită.

**Auto** folosește Physical 3D sau Software când produsul aparține unei singure vitrine. Pentru un produs din ambele vitrine sau neclasificat, folosește tipul fizic sau virtual WooCommerce. **Minimal** afișează informațiile de cumpărare. **Premium** adaugă stil accentuat și toate secțiunile editoriale disponibile.

Reordonează secțiunile și componentele rezumatului prin chei separate prin virgulă. De exemplu, `version,hero,faq` pune versiunea înaintea galeriei și a rezumatului, apoi FAQ. Secțiunile omise din listă sunt adăugate în ordinea implicită. Poți alege și stilul panourilor sau cheile secțiunilor care primesc un contur accent.

## Conținut și cumpărare

- Galeria, zoomul, lightboxul și formularul folosesc componente WooCommerce. Variația, cantitatea și trimiterea la coș rămân în același formular. Ascunderea selectorului de variații ascunde formularul pe acel produs variabil. Pentru cantitate ascunsă se păstrează valoarea implicită, iar produsele grupate își păstrează câmpurile de cantitate pentru fiecare produs.
- Bara opțională și **Cumpără acum** sunt disponibile pentru produse simple și variabile cumpărabile și în stoc. Bara trimite formularul nativ când selecția este validă și duce la selector când lipsește o opțiune. **Cumpără acum** deschide checkoutul după ce WooCommerce confirmă adăugarea în coș.
- În editorul WooCommerce, caseta **Informații produs Zion** conține compatibilitate, beneficii, fișiere și formate, versiune software, changelog, URL de documentație și FAQ. Pentru FAQ, scrie câte o linie în formatul `Întrebare | Răspuns`. Folosește câmpul WooCommerce de descărcări pentru fișiere protejate. În pagina publică pot apărea numele fișierelor, iar URL-urile protejate rămân în WooCommerce.
- Specificațiile folosesc atributele și datele native despre dimensiuni. Recomandările folosesc produsele asociate, upsells și cross-sells WooCommerce și apar doar când există produse publicate și vizibile.
- Fără JavaScript, imaginile rămân vizibile, formularul simplu poate adăuga produsul și bara rămâne un link către formular. Selecția dinamică a variațiilor cere JavaScript. [Galeria WooCommerce](https://woocommerce.com/document/adding-product-images-and-galleries/) · [Variațiile WooCommerce](https://woocommerce.com/document/variable-product/)
