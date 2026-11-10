<?php
/**
 * Title: Zion Nexus footer
 * Slug: zion-theme/footer
 * Inserter: no
 *
 * @package Zion_Theme
 */

?>
<!-- wp:group {"align":"full","className":"zion-footer"} -->
<div class="wp-block-group alignfull zion-footer">
<!-- wp:group {"className":"zion-shell zion-footer-grid"} -->
<div class="wp-block-group zion-shell zion-footer-grid">
<!-- wp:group {"className":"zion-footer-brand"} -->
<div class="wp-block-group zion-footer-brand">
<!-- wp:site-title {"level":0,"className":"zion-wordmark"} /-->
<!-- wp:paragraph {"className":"zion-lead"} -->
<p class="zion-lead">Atelier 3D, software și automatizare.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"zion-footer-links"} -->
<div class="wp-block-group zion-footer-links">
	<!-- wp:heading {"level":2,"fontSize":"card"} -->
	<h2 class="wp-block-heading has-card-font-size">Explorează</h2>
	<!-- /wp:heading -->
	<!-- wp:navigation {"ariaLabel":"Navigație în subsol","overlayMenu":"never","layout":{"type":"flex","orientation":"vertical"}} -->
		<!-- wp:navigation-link {"label":"Magazin 3D","url":"<?php echo esc_url( zion_theme_shop_url( 'magazin-3d' ) ); ?>","kind":"custom"} /-->
		<!-- wp:navigation-link {"label":"Magazin Software","url":"<?php echo esc_url( zion_theme_shop_url( 'magazin-software' ) ); ?>","kind":"custom"} /-->
		<?php if ( zion_theme_journal_url() ) : ?>
		<!-- wp:navigation-link {"label":"Jurnal","url":"<?php echo esc_url( zion_theme_journal_url() ); ?>","kind":"custom"} /-->
		<?php endif; ?>
		<?php if ( get_privacy_policy_url() ) : ?>
		<!-- wp:navigation-link {"label":"Confidențialitate","url":"<?php echo esc_url( get_privacy_policy_url() ); ?>","kind":"custom"} /-->
		<?php endif; ?>
	<!-- /wp:navigation -->
</div>
<!-- /wp:group -->
<!-- wp:search {"label":"Caută pe site","placeholder":"Ex. prototip","buttonText":"Caută","className":"zion-search"} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
