<?php
/**
 * Title: Zion · Coș gol, două magazine
 * Slug: zion-theme/empty-cart
 * Categories: zion-library
 * Inserter: yes
 *
 * @package Zion_Theme
 */

?>
<!-- wp:group {"className":"zion-empty-cart-cta","layout":{"type":"default"}} -->
<div class="wp-block-group zion-empty-cart-cta">
<!-- wp:heading {"className":"zion-empty-cart-state"} -->
<h2 class="wp-block-heading zion-empty-cart-state">Coșul tău este gol.</h2>
<!-- /wp:heading -->
<!-- wp:heading {"level":3,"className":"zion-empty-cart-title"} -->
<h3 class="wp-block-heading zion-empty-cart-title">Următoarea idee începe aici.</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"zion-empty-cart-copy"} -->
<p class="zion-empty-cart-copy">Descoperă piese fabricate 3D și instrumente software construite să ducă proiectele mai departe. Alege direcția potrivită pentru tine.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"className":"zion-empty-cart-actions"} -->
<div class="wp-block-buttons zion-empty-cart-actions">
<!-- wp:button {"className":"zion-empty-cart-shop zion-empty-cart-shop--3d"} -->
<div class="wp-block-button zion-empty-cart-shop zion-empty-cart-shop--3d"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( zion_theme_shop_url( 'magazin-3d' ) ); ?>">Magazin 3D <span aria-hidden="true">↗</span></a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"zion-empty-cart-shop zion-empty-cart-shop--software"} -->
<div class="wp-block-button zion-empty-cart-shop zion-empty-cart-shop--software"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( zion_theme_shop_url( 'magazin-software' ) ); ?>">Magazin Software <span aria-hidden="true">↗</span></a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
<!-- wp:paragraph {"className":"zion-empty-cart-note"} -->
<p class="zion-empty-cart-note">Obiecte reale. Instrumente digitale. Același spirit Zion.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
