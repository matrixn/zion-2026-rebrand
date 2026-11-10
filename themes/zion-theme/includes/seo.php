<?php
/**
 * SEO defaults for pages using the Zion templates.
 *
 * @package Zion_Theme
 */

/**
 * Let an installed SEO provider own meta descriptions.
 */
function zion_theme_meta_description(): void {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) ||
		defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' ) ||
		! apply_filters( 'zion_theme_meta_description_enabled', true ) ) {
		return;
	}
	if ( is_front_page() ) {
		$text = get_bloginfo( 'description' );
		if ( ! $text || 'Just another WordPress site' === $text ) {
			$text = __( 'Zion 3D dezvoltă prototipuri, piese printate 3D, software și integrări pentru fluxuri digitale.', 'zion-theme' );
		}
	} elseif ( is_singular() && ! post_password_required( get_queried_object_id() ) ) {
		$post = get_queried_object();
		$text = $post->post_excerpt ? $post->post_excerpt : $post->post_content;
	} else {
		return;
	}
	$text = trim( wp_strip_all_tags( strip_shortcodes( $text ), true ) );
	if ( $text ) {
		echo '<meta name="description" content="' . esc_attr( wp_html_excerpt( $text, 160, '…' ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'zion_theme_meta_description', 2 );

/**
 * Restore WooCommerce Product structured data when a Zion block owns the page.
 *
 * @param WC_Product $item Current product.
 */
function zion_theme_native_product_schema( WC_Product $item ): void {
	if ( 'publish' !== $item->get_status() || post_password_required( $item->get_id() ) ||
		! apply_filters( 'zion_theme_native_product_schema_enabled', true, $item ) ) {
		return;
	}
	$schema = WC()->structured_data;
	if ( $schema && ! $schema->get_structured_data( array( 'product' ) ) ) {
		$schema->generate_product_data( $item );
	}
}
