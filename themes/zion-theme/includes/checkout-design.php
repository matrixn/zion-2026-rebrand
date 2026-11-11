<?php
/**
 * Presentation for the native WooCommerce Checkout block.
 *
 * @package Zion_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Apply one validated design without moving fields or replacing checkout logic.
 *
 * @param string $html Native checkout markup.
 * @return string
 */
function zion_theme_checkout_design( string $html ): string {
	$design = Zion_Theme_Settings::resolve( zion_theme_settings_config(), 'checkout_design' )['value'];
	$tags   = new WP_HTML_Tag_Processor( $html );
	if ( $tags->next_tag( array( 'class_name' => 'wp-block-woocommerce-checkout' ) ) ) {
		$tags->add_class( 'zion-checkout' );
		$tags->add_class( 'zion-checkout--' . $design );
	}
	return $tags->get_updated_html();
}
add_filter( 'render_block_woocommerce/checkout', 'zion_theme_checkout_design' );
