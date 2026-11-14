<?php
/**
 * Empty-cart native block composition preserves merchant content and cart data.
 *
 * @package Zion_Theme
 */

( static function (): void {
	$id                     = wc_get_page_id( 'cart' );
	$content                = get_post_field( 'post_content', $id );
	$cart                   = parse_blocks( $content )[0];
	$empty                  = array_values( array_filter( $cart['innerBlocks'], static fn ( array $block ): bool => 'woocommerce/empty-cart-block' === $block['blockName'] ) )[0];
	$stock                  = parse_blocks( '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">Înapoi la magazin</a></div><!-- /wp:button --></div><!-- /wp:buttons -->' )[0];
	$custom                 = parse_blocks( '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">Acțiune editorială proprie</a></div><!-- /wp:button --></div><!-- /wp:buttons -->' )[0];
	$empty['innerBlocks'][] = $stock;
	$empty['innerBlocks'][] = $custom;
	array_splice( $empty['innerContent'], -1, 0, array( null, null ) );
	$empty['innerContent'][0] .= '<p class="merchant-empty-note">Conținut editorial păstrat.</p>';
	$transformed               = zion_theme_cart_empty_cta( $empty );
	$html                      = render_block( $transformed );
	$cta                       = render_block( $transformed['innerBlocks'][0] );
	$tags                      = new WP_HTML_Tag_Processor( $cta );
	$links                     = array();
	while ( $tags->next_tag( 'A' ) ) {
		$links[] = $tags->get_attribute( 'href' );
	}
	zion_core_test_assert( array( zion_theme_shop_url( 'magazin-3d' ), zion_theme_shop_url( 'magazin-software' ) ) === $links, 'Empty cart CTA has exactly two native links to the published storefronts' );
	zion_core_test_assert( ! str_contains( $html, 'Înapoi la magazin' ) && str_contains( $html, 'Acțiune editorială proprie' ) && str_contains( $html, 'merchant-empty-note' ), 'Only the stock single-shop action is removed; custom actions and raw merchant markup survive' );
	zion_core_test_assert( str_contains( $html, 'wp-block-woocommerce-product-new' ), 'Empty-cart native product recommendations remain present' );
	zion_core_test_assert( zion_theme_cart_empty_cta( $transformed ) === $transformed, 'An explicit or already-rendered native empty-cart CTA is never duplicated' );
	zion_core_test_assert( zion_theme_cart_empty_cta( $cart ) === $cart && get_post_field( 'post_content', $id ) === $content, 'Filled-cart structure and saved cart page are not rewritten' );
	$authored = parse_blocks( '<!-- wp:woocommerce/empty-cart-block --><div class="wp-block-woocommerce-empty-cart-block"><!-- wp:heading --><h2 class="wp-block-heading">Mesaj personalizat</h2><!-- /wp:heading --></div><!-- /wp:woocommerce/empty-cart-block -->' )[0];
	zion_core_test_assert( zion_theme_cart_empty_cta( $authored ) === $authored, 'A fully custom empty-cart heading is preserved rather than silently replaced' );
	register_block_pattern(
		'zion-theme/test-empty-reference',
		array(
			'title'   => 'Synthetic Woo empty state',
			'content' => '<!-- wp:heading --><h2 class="wp-block-heading">Coșul tău este gol</h2><!-- /wp:heading -->' . serialize_block( $stock ),
		)
	);
	try {
		$reference = parse_blocks( '<!-- wp:woocommerce/empty-cart-block --><div class="wp-block-woocommerce-empty-cart-block"><!-- wp:pattern {"slug":"zion-theme/test-empty-reference"} /--><!-- wp:paragraph --><p>Conținut după pattern.</p><!-- /wp:paragraph --><!-- wp:woocommerce/product-new {"columns":4,"rows":1} /--></div><!-- /wp:woocommerce/empty-cart-block -->' )[0];
		$rendered  = render_block( zion_theme_cart_empty_cta( $reference ) );
		zion_core_test_assert( str_contains( $rendered, 'zion-empty-cart-cta' ) && ! str_contains( $rendered, 'Înapoi la magazin' ) && str_contains( $rendered, 'Conținut după pattern.' ) && str_contains( $rendered, 'wp-block-woocommerce-product-new' ), 'WooCommerce pattern references expand through the native API without losing subsequent child slots' );
	} finally {
		unregister_block_pattern( 'zion-theme/test-empty-reference' );
	}
} )();
