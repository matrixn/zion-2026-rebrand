<?php
/**
 * Title: Zion header
 * Slug: zion-theme/header
 * Inserter: no
 *
 * @package Zion_Theme
 */

?>
<!-- wp:group {"className":"zion-shell zion-header","anchor":"zion-top"} -->
<div class="wp-block-group zion-shell zion-header" id="zion-top">
<!-- wp:site-title {"level":0,"className":"zion-wordmark"} /-->
<!-- wp:navigation {"overlayMenu":"mobile","className":"zion-navigation","layout":{"type":"flex","justifyContent":"right"}} -->
<!-- wp:navigation-link {"label":"Magazin 3D","url":"<?php echo esc_url( zion_theme_shop_url( 'magazin-3d' ) ); ?>","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"Magazin Software","url":"<?php echo esc_url( zion_theme_shop_url( 'magazin-software' ) ); ?>","kind":"custom"} /-->
<?php if ( zion_theme_journal_url() ) : ?>
<!-- wp:navigation-link {"label":"Jurnal","url":"<?php echo esc_url( zion_theme_journal_url() ); ?>","kind":"custom"} /-->
<?php endif; ?>
<!-- /wp:navigation -->
</div>
<!-- /wp:group -->
