<?php
/**
 * Title: Zion Nexus footer
 * Slug: zion-theme/footer
 * Inserter: no
 *
 * @package Zion_Theme
 */

$zion_theme_contact     = get_page_by_path( 'contact' );
$zion_theme_contact_url = $zion_theme_contact && 'publish' === $zion_theme_contact->post_status ? get_permalink( $zion_theme_contact ) : '';
$zion_theme_privacy     = get_privacy_policy_url();
if ( ! $zion_theme_privacy ) {
	$zion_theme_privacy = zion_theme_published_page_url( 'politica-de-confidentialitate' );
}
$zion_theme_terms_id = function_exists( 'wc_terms_and_conditions_page_id' ) ? wc_terms_and_conditions_page_id() : 0;
$zion_theme_terms    = $zion_theme_terms_id && 'publish' === get_post_status( $zion_theme_terms_id ) ? get_permalink( $zion_theme_terms_id ) : '';
?>
<!-- wp:group {"align":"full","className":"zion-footer"} -->
<div class="wp-block-group alignfull zion-footer">
<!-- wp:group {"className":"zion-shell zion-footer-grid"} -->
<div class="wp-block-group zion-shell zion-footer-grid">
<!-- wp:group {"className":"zion-footer-brand"} -->
<div class="wp-block-group zion-footer-brand">
<!-- wp:paragraph {"className":"zion-kicker"} -->
<p class="zion-kicker">Zion Nexus</p>
<!-- /wp:paragraph -->
<!-- wp:site-title {"level":0,"className":"zion-wordmark"} /-->
<!-- wp:paragraph {"className":"zion-lead"} -->
<p class="zion-lead">Atelier 3D, software și automatizare.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"className":"zion-footer-cta"} -->
<div class="wp-block-buttons zion-footer-cta">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $zion_theme_contact_url ? $zion_theme_contact_url : home_url( '/#magazine' ) ); ?>">Explorează Zion</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"zion-footer-links zion-footer-resources"} -->
<div class="wp-block-group zion-footer-links zion-footer-resources">
	<!-- wp:heading {"level":2,"fontSize":"card"} -->
	<h2 class="wp-block-heading has-card-font-size">Resurse</h2>
	<!-- /wp:heading -->
	<!-- wp:navigation {"ariaLabel":"Navigație în subsol","overlayMenu":"never","layout":{"type":"flex","orientation":"vertical"}} -->
			<?php
			$zion_theme_footer_links = array(
				'Acasă'                   => home_url( '/' ),
				'Toate serviciile'        => zion_theme_published_page_url( 'servicii' ),
				'Magazin 3D'              => zion_theme_published_page_url( 'magazin-3d' ),
				'Magazin Software'        => zion_theme_published_page_url( 'magazin-software' ),
				'WordPress & WooCommerce' => zion_theme_published_page_url( 'pluginuri-woocommerce' ),
				'Zion Food'               => 'https://food.zion3d.ro',
				'Zion Storage Cloud'      => 'https://storage.zion3d.ro',
				'Despre noi'              => zion_theme_published_page_url( 'despre-noi' ),
				'Blog'                    => zion_theme_journal_url(),
				'Întrebări frecvente'     => zion_theme_published_page_url( 'intrebari' ),
				'Contact'                 => $zion_theme_contact_url,
				'Contul meu'              => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '',
			);
			foreach ( $zion_theme_footer_links as $zion_theme_label => $zion_theme_url ) {
				echo zion_theme_nav_item( $zion_theme_label, $zion_theme_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Serialized native blocks with sanitized URLs.
			}
			?>
	<!-- /wp:navigation -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"zion-footer-connect"} -->
<div class="wp-block-group zion-footer-connect">
<?php if ( $zion_theme_contact_url ) : ?>
<!-- wp:group {"className":"zion-footer-contact"} -->
<div class="wp-block-group zion-footer-contact">
<!-- wp:heading {"fontSize":"card"} -->
<h2 class="wp-block-heading has-card-font-size">Contact</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><a href="<?php echo esc_url( $zion_theme_contact_url ); ?>">Contactează echipa Zion</a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<?php endif; ?>
<!-- wp:search {"label":"Caută pe site","placeholder":"Ex. prototip","buttonText":"Caută","className":"zion-search zion-footer-search"} /-->
<!-- wp:social-links {"className":"zion-footer-social"} -->
<ul class="wp-block-social-links zion-footer-social"></ul>
<!-- /wp:social-links -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"zion-shell zion-footer-meta"} -->
<div class="wp-block-group zion-shell zion-footer-meta">
<!-- wp:paragraph {"className":"zion-footer-copyright"} -->
<p class="zion-footer-copyright">© <?php echo esc_html( wp_date( 'Y' ) . ' ' . get_bloginfo( 'name' ) ); ?></p>
<!-- /wp:paragraph -->
<?php if ( $zion_theme_privacy || $zion_theme_terms ) : ?>
<!-- wp:navigation {"ariaLabel":"Linkuri legale","overlayMenu":"never","className":"zion-footer-legal"} -->
	<?php if ( $zion_theme_privacy ) : ?>
<!-- wp:navigation-link {"label":"Confidențialitate","url":"<?php echo esc_url( $zion_theme_privacy ); ?>","kind":"custom"} /-->
<?php endif; ?>
	<?php if ( $zion_theme_terms ) : ?>
<!-- wp:navigation-link {"label":"Termeni și condiții","url":"<?php echo esc_url( $zion_theme_terms ); ?>","kind":"custom"} /-->
<?php endif; ?>
<!-- /wp:navigation -->
<?php endif; ?>
<!-- wp:zion/editorial {"part":"back_top"} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
