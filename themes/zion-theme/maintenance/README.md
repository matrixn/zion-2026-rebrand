# Zion Continuum — Under Construction

Version: 1.0.0

Pagină autonomă pentru pluginul **Zion · Under Construction**. Nu este un plugin instalabil și nu activează mentenanța prin simpla instalare a temei.

În **Setări → Under Construction**, salvează întâi conținutul existent. Copiază:

1. `continuum.html` în **HTML + shortcodes** (fără document HTML complet).
2. `continuum.css` în **CSS** (fără tag `style`).
3. `continuum.js` în **JavaScript** (fără tag `script`).

Titlu recomandat: **Zion 3D — Construim ce urmează**. Păstrează HTTP **503**, Retry-After **3600**, noindex și accesul administratorului. Salvează și verifică pagina într-o sesiune anonimă. Pentru rollback, restaurează cele cinci câmpuri HTML/CSS/JS/titlu/descriere din backup; nu dezactiva accidental mentenanța înainte de lansare.

Logo-ul și numărul public de contact aparțin zion3d.ro. Verifică-le înainte de reutilizarea în alt mediu. Acțiunile sunt linkuri native WhatsApp și telefon; nu trimit automat mesaje, nu capturează date și nu depind de un transport SMTP. Controalele native de consimțământ ale site-ului rămân disponibile; widgetul WhatsApp flotant duplicat este ascuns numai în documentul de mentenanță.

Paleta este cea din `theme.json`, verificată la build. Animația Canvas are un singur scheduler, maximum 30 FPS desktop / 24 FPS mobil, DPR maximum 1,75, pauză, oprire în tab ascuns și în afara viewportului. Mișcarea redusă este respectată inclusiv la schimbarea preferinței în timpul vizitei; fără JS/Canvas rămâne ilustrația SVG și tot conținutul.

`npm run build` generează `build/zion-continuum-maintenance-1.0.0.html` pentru preview local. `npm run package` generează arhiva de conținut și cele două pachete WordPress. Testele noi sunt în `tests/e2e/maintenance.spec.mjs` și folosesc exclusiv documente sintetice pe originea instanței tests.

Pluginul existent execută și hook-urile WordPress `wp_head`/`wp_footer`. Alte pluginuri pot încărca asseturi și widgeturi; pagina nu dezactivează global Elementor, XStore, analytics, CAPTCHA sau pluginuri comerciale.
