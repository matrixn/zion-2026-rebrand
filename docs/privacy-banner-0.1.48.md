# Banner Zion Privacy · 0.1.48

Configurat pe zion3d.ro din interfața WordPress **Zion Privacy → Banner**, la 14 noiembrie 2026. Pluginul este întreținut separat în [matrixn/zion-privacy-client](https://github.com/matrixn/zion-privacy-client). Această lucrare nu modifică sursele Theme/Core 1.1.40.

## Aspect și texte salvate

Banner Bar, centrat jos, lățime maximă 1160 px, colțuri 16 px, font 15 px moștenit de la site, umbră și hover discret. Pictograma de redeschidere rămâne în dreapta jos. Animațiile respectă preferința de mișcare redusă.

| Element | Culoare |
| --- | --- |
| Fundal | `#141F16` |
| Text | `#F3F6EF` |
| Text secundar | `#B4C3B3` |
| Buton principal | `#D9FB4F` |
| Text pe butonul principal | `#080D09` |
| Butoane secundare | `#080D09` |
| Text pe butoane secundare | `#F3F6EF` |
| Bordură | `#435D29` |

Paleta provine din `theme.json`; nu s-a creat un sistem vizual separat în temă. Culorile sunt salvate în setările proprii pluginului prin câmpurile HEX din panou.

Titlu: **Confidențialitatea ta contează.**

Mesaj: „Cookie-urile necesare ajută site-ul și coșul de cumpărături să funcționeze. Tu alegi dacă accepți categoriile opționale. Poți reveni oricând la preferințele cookie.”

Acțiuni: **Doar necesare**, **Refuză opționalele**, **Personalizează**, **Acceptă toate**, **Salvează preferințele**. Linkul existent către politica de confidențialitate este păstrat.

Selector: **Preferințe cookie**. Introducere: „Alege categoriile de cookie-uri pe care le permiți. Cele necesare rămân active pentru funcționarea site-ului. Poți modifica oricând aceste alegeri.”

Este activ rezumatul cu categorii și contoare; lista individuală de detalii din fereastra publică este ascunsă pentru un selector compact. Această opțiune nu șterge și nu reclasifică inventarul. Numele și descrierile tehnice primite de la API rămân în forma originală în administrare; traducerea lor editorială nu face parte din această actualizare.

## Actualizarea clientului

- [x] Catalog JSON românesc încărcat prin `wp_set_script_translations`, cu un helper comun pentru banner și preview-ul administrativ.
- [x] Traduse etichetele fixe, numele categoriilor, instrucțiunile implicite, închidere/anulare, etichetele accesibile și formele „1 cookie”, „2 cookie-uri”, „20 de cookie-uri”. Textele configurabile au prioritate.
- [x] Câmpuri HEX validate lângă selectoarele native de culoare; modalul și pictograma folosesc paleta configurată.
- [x] Corectată poziționarea modalului în raport cu bannerul transformat și poziționarea centrată pe mobil. Focus vizibil și suprafețe de interacțiune pentru comutatoare și butoane.
- [x] Configurația frontend folosește JSON nativ tipizat. `wp_localize_script` transforma anterior valorile `false` în text gol, astfel încât controalele dezactivate puteau rămâne vizibile. Noua transmitere păstrează valorile booleene, fără schimbarea alegerilor salvate.
- [x] Pachetul de producție respectă Composer lock și exclude testele, rapoartele și dependențele npm.

Commituri pe `main`: [f69b72c](https://github.com/matrixn/zion-privacy-client/commit/f69b72c94139d31b4403a6314e009f2f2fa53ab3) și [2760680](https://github.com/matrixn/zion-privacy-client/commit/276068007b420e63a9ff3fd7d8f531ea0aafa8ae). Instalare prin încărcarea ZIP-ului în mecanismul nativ WordPress, după publicarea codului. Nu s-a creat un tag sau un release GitHub.

## Verificări și limite

- [x] Build-ul administrativ de producție, validare Composer și sintaxă PHP pentru cele 14 fișiere proprii și de integrare verificate.
- [x] **11/11 teste Playwright** pe date sintetice: patru lățimi, traduceri/plurale, fallback englezesc, texte personalizate, rezumat compact, payload-uri de consimțământ și izolarea preview-ului.
- [x] Integrare în instanța locală WordPress de teste: încărcătorul nativ găsește catalogul românesc, iar scriptul emis păstrează `true` și `false`. Fără activare sau conectare a pluginului în instanța de teste.
- [x] Verificări live autentificate la **390/768/1024/1440 px**: banner și modal în interiorul ecranului, fără overflow orizontal. Categoria necesară este activă și blocată, cele opționale sunt oprite implicit pentru GDPR.
- [x] Preview administrativ și pictogramă pe pagina normală: română, lime și redeschidere/închidere funcționale. Pe live nu s-a trimis o nouă alegere de consimțământ.
- [x] Cele **90 de fișiere** instalate coincid cu arhiva finală; inventar SHA-256 `aa0ca4e716ea650d6f933a2f6ea154d71602db130d11ab58628bf71f047c2269`.
- [x] Hash-urile configurațiilor nelegate de aspect, reviziei consimțământului, licenței și clasificărilor sunt păstrate. Nu s-au reînnoit consimțămintele și nu s-a reconfigurat conexiunea API; panoul afișează „API connected”.
- [x] Invalidat exclusiv cache-ul Nginx dedicat zion3d.ro: 75 de fișiere, fără erori de acces. Backup privat al pluginului anterior și verificări de stare păstrate în afara webrootului.

Comparația brută a opțiunii criptate de credențiale diferă după accesarea panoului. Fluxul existent `saveBranding()` recriptează opțiunea cu IV aleatoriu la refresh; egalitatea octeților nu demonstrează egalitatea sau schimbarea secretului. Nu s-a solicitat reconectarea și nu s-au introdus credențiale. Nu afirmăm o comparație înainte/după a secretelor decriptate.

Acest control acoperă aspectul și fluxul interfeței, nu certifică respectarea juridică sau blocarea tuturor serviciilor externe. Clientul nu are o suită PHPUnit configurată sau WPCS pregătit; aceste două verificări nu sunt declarate trecute. `npm ci` a reușit cu avertismente ale toolchainului existent, inclusiv 82 de vulnerabilități raportate de audit; actualizarea lor nu a fost inclusă, iar `node_modules` nu este distribuit.

Arhiva clientului: `C:/Users/radut/Workspace/zion-privacy-client/.dist/zion-privacy-client-0.1.48.zip`, SHA-256 `739eda1201ac344eb20bb81e0092d2db72ca8f43605b35ba61909690b42fa29c`.

Capturi și măsurători locale: `C:/Users/radut/Workspace/zion-privacy-client/.dist/proofs/`, excluse din Git. ZIP-urile Theme/Core regenerate, fără schimbarea versiunilor sau reinstalare: `build/zion-theme-1.1.40.zip` și `build/zion-core-1.1.40.zip`.
