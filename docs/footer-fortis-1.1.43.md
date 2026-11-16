# Footer · Fortis Build · 1.1.43

Adăugat **Fortis Build ↗** cu destinația `https://fortisbuild.ro` imediat după **Hausmeisterdienst Konstanz ↗**, în navigația **Parteneri și comunități**.

Sursa este `themes/zion-theme/patterns/footer.php`. Linkul folosește rendererul nativ existent și aceeași stilizare. Footerul rămâne editabil în Site Editor. Zion Core primește doar antetul de versiune **1.1.43**, pentru a păstra pachetele sincronizate.

## Verificări și livrare

- [x] Build, WPCS, **61 teste PHPUnit / 436 aserțiuni** și integrare în WordPress.
- [x] **5 teste Playwright**: șabloane la 390/768/1024/1440 px și navigație mobilă, pe instanța de teste cu date sintetice.
- [x] Publicare live cu backup privat și verificarea fișierelor modificate; Headerul, părțile de șablon salvate, navigațiile și setările protejate sunt păstrate, cu hash identic înainte și după.
- [x] Link vizibil în ordinea cerută, URL exact și fără overflow la **390/768/1024/1440 px**. GET anonim pe homepage, Magazin 3D și Magazin Software: **200**, cu o singură apariție a linkului Fortis, imediat după Hausmeisterdienst Konstanz.
- [x] `npm run package`: `build/zion-theme-1.1.43.zip` și `build/zion-core-1.1.43.zip`, cu integritatea CRC verificată.

Nu sunt modificate notificarea de plăți, produsele, coșul, checkoutul sau configurarea comercială.

## Publicare și rollback

Doar trei fișiere distribuite diferă de 1.1.42: patternul footerului și cele două antete de versiune. După publicarea lor, verificarea din browser încă afișa patternul vechi; tema a fost reaplicată prin încărcarea și înlocuirea nativă WordPress pentru reîmprospătarea versiunii servite. Publicarea a fost verificată ulterior, inclusiv în răspunsurile anonime. Verificarea HTTP inițială cu User-Agent Python a fost respinsă cu 403; verificarea finală cu User-Agent-ul de release a trecut.

Cele **152 de fișiere** instalate coincid cu ZIP-urile, inventar SHA-256 `881b618249752224f4c13de5cdbcb50aedccfc9934618d3d76b669d0d997ec53`. După înlocuirea nativă au fost invalidate exclusiv **5 fișiere** din cache-ul Nginx dedicat zion3d.ro.

Backup privat: `/home/forge/.zion-deploy/fortis-1.1.43-20261116T101154Z/`, cu cele trei fișiere anterioare și hashul configurației protejate. Pentru rollback se restaurează exclusiv acele fișiere, după compararea cu starea curentă, și se reîmprospătează cache-ul temei. Capturile sunt în `build/footer-fortis-1.1.43/desktop.jpg` și `mobile.jpg`, excluse din Git.
