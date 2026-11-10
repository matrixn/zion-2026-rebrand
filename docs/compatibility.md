# Compatibilitate și migrare

Mediul de referință folosește WordPress 6.9.4, WooCommerce 10.8.1, PHP 8.3 și MariaDB 10.11, configurate în `.wp-env.json`. Instanțele de dezvoltare și test pornesc din același fișier și au baze de date separate. Folosește fișierele lock pentru dependențele npm și Composer.

Tema compune catalogul, căutarea de produse și pagina de produs din blocuri WooCommerce. Catalogul păstrează interogarea nativă pentru magazin, categorii, etichete și branduri. Coșul, checkoutul și confirmarea comenzii folosesc șabloanele WooCommerce. Cele două vitrine sunt pagini WordPress suplimentare, legate de aceeași instanță WooCommerce. Testele verifică și comportamentul shortcode-ului standard după afișarea unei vitrine. [Ierarhia șabloanelor WooCommerce](https://developer.woocommerce.com/docs/theming/block-theme-development/template-hierarchy/)

Înainte de actualizarea versiunilor de referință, rulează build-ul, WPCS, PHPUnit, integrarea și Playwright. Verifică editorul de site și diferențele vizuale. Pentru o lansare pe un magazin existent, verifică pe staging metodele de plată și de livrare, taxele, tipurile reale de produse și extensiile active.

Faza 0.2 acoperă șabloanele și designul temei. Zion Control Center, filtrele avansate și reconstruirea paginilor Elementor sunt etape separate. Paginile existente care depind de widgeturi Elementor/XStore se refac în blocuri și se validează individual înainte de înlocuire.
