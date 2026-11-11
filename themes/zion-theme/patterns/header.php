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
<!-- wp:site-logo {"width":56,"className":"zion-header-logo"} /-->
<!-- wp:site-title {"level":0,"className":"zion-wordmark"} /-->
<!-- wp:navigation {"ariaLabel":"Meniu principal","overlayMenu":"mobile","className":"zion-navigation","layout":{"type":"flex","justifyContent":"right"}} -->
<?php echo zion_theme_primary_navigation(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Serialized native blocks with sanitized URLs. ?>
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
