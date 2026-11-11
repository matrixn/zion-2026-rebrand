<?php
/**
 * Presentation only for the native removable filter chips.
 *
 * @package Zion_Theme
 */

/**
 * Supply the native taxonomy label without changing WooCommerce filter data.
 *
 * @param string $html Native block markup.
 * @return string
 */
function zion_theme_filter_labels( string $html ): string {
	$taxonomy = get_taxonomy( 'product_cat' );
	if ( ! $taxonomy || is_admin() ) {
		return $html;
	}
	$tags = new WP_HTML_Tag_Processor( $html );
	if ( $tags->next_tag( array( 'class_name' => 'wc-block-product-filter-removable-chips' ) ) ) {
		$tags->set_attribute( 'data-zion-category-prefix', $taxonomy->labels->singular_name . ': ' );
		wp_enqueue_script( 'zion-filter-labels', get_theme_file_uri( 'assets/blocks/filter-labels.js' ), array(), wp_get_theme()->get( 'Version' ), true );
	}
	return $tags->get_updated_html();
}
add_filter( 'render_block_woocommerce/product-filter-removable-chips', 'zion_theme_filter_labels' );
