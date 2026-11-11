<?php
/**
 * Presentation variants for the native WooCommerce Cart block.
 *
 * @package Zion_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Decorate the native block without replacing its Store API or editable children.
 *
 * @param string $html Rendered cart block.
 * @return string
 */
function zion_theme_cart_design( string $html ): string {
	$config = zion_theme_settings_config();
	$design = Zion_Theme_Settings::resolve( $config, 'cart_design' )['value'];
	if ( 'editorial' === $design && ! is_admin() ) {
		wp_enqueue_script( 'zion-cart-images', get_theme_file_uri( 'assets/blocks/cart-images.js' ), array( 'wp-data', 'wc-blocks-data-store' ), wp_get_theme()->get( 'Version' ), true );
	}
	$tags = new WP_HTML_Tag_Processor( $html );
	if ( $tags->next_tag( array( 'class_name' => 'wp-block-woocommerce-cart' ) ) ) {
		$tags->add_class( 'zion-cart' );
		$tags->add_class( 'zion-cart--' . $design );
	}
	return $tags->get_updated_html();
}
add_filter( 'render_block_woocommerce/cart', 'zion_theme_cart_design' );

/**
 * Localize stock empty-cart labels without rewriting saved merchant content.
 *
 * @param string $html Native empty cart markup.
 * @return string
 */
function zion_theme_cart_empty_labels( string $html ): string {
	if ( ! function_exists( 'wc_get_page_permalink' ) || ! zion_theme_romanian_commerce( 'woocommerce' ) ) {
		return $html;
	}
	$tags     = new WP_HTML_Tag_Processor( $html );
	$eligible = false;
	while ( $tags->next_token() ) {
		if ( '#tag' === $tags->get_token_type() && in_array( $tags->get_tag(), array( 'A', 'H2', 'H3' ), true ) ) {
			$eligible = ! $tags->is_tag_closer() && ( 'A' === $tags->get_tag()
				? wc_get_page_permalink( 'shop' ) === $tags->get_attribute( 'href' )
				: ( $tags->has_class( 'wp-block-heading' ) || $tags->has_class( 'wc-block-cart__empty-cart__title' ) ) );
		} elseif ( '#text' === $tags->get_token_type() && $eligible ) {
			$text = $tags->get_modifiable_text();
			$key  = trim( $text );
			if ( in_array( $key, array( 'Browse store', 'New in store', 'Your cart is currently empty!', 'Your cart is currently empty.' ), true ) ) {
				$tags->set_modifiable_text( str_replace( $key, zion_theme_commerce_strings()[ $key ], $text ) );
			}
		}
	}
	return $tags->get_updated_html();
}
add_filter( 'render_block_woocommerce/empty-cart-block', 'zion_theme_cart_empty_labels' );
