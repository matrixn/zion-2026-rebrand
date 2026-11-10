<?php
/**
 * Verify the catalog in the wp-env tests WordPress database.
 *
 * @package Zion_Core
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || 'local' !== wp_get_environment_type() ) {
	return;
}

/**
 * Assert an integration check.
 *
 * @param bool   $condition Assertion result.
 * @param string $message Check description.
 */
function zion_core_test_assert( bool $condition, string $message ): void {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
	WP_CLI::log( 'PASS ' . $message );
}

wp( array( 'pagename' => 'magazin-3d' ) );

zion_core_test_assert( wp_is_block_theme(), 'Zion is active as a block theme' );
zion_core_test_assert( is_object_in_taxonomy( 'product', 'zion_shop' ), 'Shop taxonomy is attached to products' );
zion_core_test_assert( (bool) get_page_by_path( 'magazin-3d' ) && (bool) get_page_by_path( 'magazin-software' ), 'Both storefront pages exist' );
$zion_core_native_shop         = (int) get_option( 'woocommerce_shop_page_id' );
$zion_core_existing_pages      = array(
	'3d'       => get_page_by_path( 'magazin-3d' )->ID,
	'software' => get_page_by_path( 'magazin-software' )->ID,
);
$zion_core_provisioned         = zion_core_create_shop_pages();
$zion_core_current_native_shop = (int) get_option( 'woocommerce_shop_page_id' );
zion_core_test_assert(
	$zion_core_existing_pages === $zion_core_provisioned &&
	$zion_core_native_shop === $zion_core_current_native_shop &&
	! in_array( $zion_core_native_shop, $zion_core_existing_pages, true ),
	'Provisioning keeps existing storefronts and the single native WooCommerce Shop'
);

$zion_core_3d       = do_shortcode( '[zion_catalog shop="3d"]' );
$zion_core_software = do_shortcode( '[zion_catalog shop="software"]' );
zion_core_test_assert( str_contains( $zion_core_3d, 'Suport modular 3D' ) && ! str_contains( $zion_core_3d, 'Zion Catalog Tools' ), '3D catalog excludes Software products' );
zion_core_test_assert( str_contains( $zion_core_software, 'Zion Catalog Tools' ) && ! str_contains( $zion_core_software, 'Suport modular 3D' ), 'Software catalog excludes 3D products' );
zion_core_test_assert( str_contains( do_shortcode( '[products]' ), 'Suport modular 3D' ) && str_contains( do_shortcode( '[products]' ), 'Zion Catalog Tools' ), 'Native product query remains unfiltered' );
zion_core_test_assert( '' === do_shortcode( '[zion_catalog shop="invalid"]' ), 'Unknown storefront renders no products' );

$zion_core_product = new WC_Product_Simple();
$zion_core_product->set_name( 'Zion visibility integration fixture' );
$zion_core_product->set_status( 'publish' );
$zion_core_product->set_regular_price( '5' );
$zion_core_product->set_catalog_visibility( 'hidden' );
$zion_core_product->save();

$zion_core_visible = false;
$zion_core_hidden  = false;
try {
	wp_set_object_terms( $zion_core_product->get_id(), array( '3d', 'software' ), 'zion_shop' );
	$zion_core_hidden = ! str_contains( do_shortcode( '[zion_catalog shop="3d"]' ), $zion_core_product->get_name() );
	$zion_core_product->set_catalog_visibility( 'visible' );
	$zion_core_product->save();
	$zion_core_visible = str_contains( do_shortcode( '[zion_catalog shop="3d"]' ), $zion_core_product->get_name() )
		&& str_contains( do_shortcode( '[zion_catalog shop="software"]' ), $zion_core_product->get_name() );
} finally {
	$zion_core_product->delete( true );
}

zion_core_test_assert( $zion_core_hidden, 'WooCommerce hidden products stay hidden' );
zion_core_test_assert( $zion_core_visible, 'A visible product can belong to both stores' );
require __DIR__ . '/classification.php';
$zion_core_detail_fields = get_registered_meta_keys( 'post', 'product' );
zion_core_test_assert(
	isset( $zion_core_detail_fields['_zion_product_faq'] ) && false === $zion_core_detail_fields['_zion_product_faq']['show_in_rest'] &&
	'string' === $zion_core_detail_fields['_zion_product_version']['type'],
	'Zion editorial product fields are registered without exposing editable REST metadata'
);
zion_core_test_assert(
	'' === sanitize_meta( '_zion_product_documentation', 'javascript:alert(1)', 'post', 'product' ) &&
	'https://example.test/manual' === sanitize_meta( '_zion_product_documentation', 'https://example.test/manual', 'post', 'product' ),
	'Zion documentation metadata accepts public HTTP URLs and rejects unsafe schemes'
);
WP_CLI::success( 'Catalog integration passed.' );
