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
<!-- wp:site-logo {"width":112,"className":"zion-header-logo"} /-->
<!-- wp:site-title {"level":0,"className":"zion-wordmark"} /-->
<!-- wp:navigation {"ariaLabel":"Meniu principal","overlayMenu":"mobile","className":"zion-navigation","layout":{"type":"flex","justifyContent":"right"}} -->
<!-- wp:navigation-link {"label":"Magazin 3D","url":"<?php echo esc_url( zion_theme_shop_url( 'magazin-3d' ) ); ?>","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"Magazin Software","url":"<?php echo esc_url( zion_theme_shop_url( 'magazin-software' ) ); ?>","kind":"custom"} /-->
<?php if ( zion_theme_journal_url() ) : ?>
<!-- wp:navigation-link {"label":"Jurnal","url":"<?php echo esc_url( zion_theme_journal_url() ); ?>","kind":"custom"} /-->
<?php endif; ?>
<!-- wp:navigation-submenu {"label":"Explorează","kind":"custom"} -->
<!-- wp:navigation-link {"label":"Toate produsele","url":"<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : zion_theme_shop_url( 'magazin-3d' ) ); ?>","kind":"custom"} /-->
<!-- wp:navigation-link {"label":"Despre vitrine","url":"<?php echo esc_url( home_url( '/#magazine' ) ); ?>","kind":"custom"} /-->
<!-- /wp:navigation-submenu -->
<!-- /wp:navigation -->
<!-- wp:group {"className":"zion-header-tools"} -->
<div class="wp-block-group zion-header-tools">
<?php if ( class_exists( 'WooCommerce' ) ) : ?>
<!-- wp:details {"className":"zion-header-search"} -->
<details class="wp-block-details zion-header-search"><summary>Caută produse</summary>
<!-- wp:search {"label":"Caută produse","placeholder":"Nume produs…","buttonText":"Caută","className":"zion-search","query":{"post_type":"product"}} /-->
</details>
<!-- /wp:details -->
<!-- wp:woocommerce/customer-account {"displayStyle":"icon_only","className":"zion-header-account"} /-->
<!-- wp:woocommerce/mini-cart {"hasHiddenPrice":true,"className":"zion-header-cart"} /-->
<?php endif; ?>
<!-- wp:buttons {"className":"zion-header-cta"} -->
<div class="wp-block-buttons zion-header-cta">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/#magazine' ) ); ?>">Explorează vitrinele</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
