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
	$zion_core_hidden         = ! str_contains( do_shortcode( '[zion_catalog shop="3d"]' ), $zion_core_product->get_name() );
	$zion_core_library_hidden = ! str_contains( do_shortcode( '[zion_products shop="3d"]' ), $zion_core_product->get_name() );
	$zion_core_product->set_catalog_visibility( 'visible' );
	$zion_core_product->save();
	$zion_core_visible         = str_contains( do_shortcode( '[zion_catalog shop="3d"]' ), $zion_core_product->get_name() )
		&& str_contains( do_shortcode( '[zion_catalog shop="software"]' ), $zion_core_product->get_name() );
	$zion_core_library_visible = str_contains( do_shortcode( '[zion_products shop="3d"]' ), $zion_core_product->get_name() );
	$zion_core_product->set_status( 'draft' );
	$zion_core_product->save();
	$zion_core_library_draft_hidden = ! str_contains( do_shortcode( '[zion_products shop="3d"]' ), $zion_core_product->get_name() );
} finally {
	$zion_core_product->delete( true );
}

zion_core_test_assert( $zion_core_hidden, 'WooCommerce hidden products stay hidden' );
zion_core_test_assert( $zion_core_visible, 'A visible product can belong to both stores' );
zion_core_test_assert( $zion_core_library_hidden && $zion_core_library_visible, 'Embedded collections honor native WooCommerce catalog visibility' );
zion_core_test_assert( $zion_core_library_draft_hidden, 'Embedded collections include published products only' );
zion_core_test_assert( '' === do_shortcode( '[zion_products shop="invalid"]' ), 'An invalid library shop cannot fall back to all products' );
zion_core_test_assert( ! str_contains( do_shortcode( '[zion_cta url="javascript:alert(1)"]' ), 'javascript:' ), 'CTA URLs reject unsafe schemes' );
$zion_core_collection_limits = zion_core_collection_options(
	array(
		'limit'    => '900',
		'columns'  => '900',
		'paginate' => 'yes',
	)
);
zion_core_test_assert(
	24 === $zion_core_collection_limits['limit'] && 5 === $zion_core_collection_limits['columns'] && ! $zion_core_collection_limits['paginate'],
	'Library limits are capped and pagination only accepts a known true value'
);
zion_core_test_assert(
	array(
		'created' => 0,
		'failed'  => 0,
	) === zion_theme_create_service_pages(),
	'Provisioning service pages preserves all existing Gutenberg pages'
);
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
$zion_core_checkout_id      = wc_get_page_id( 'checkout' );
$zion_core_checkout_content = get_post_field( 'post_content', $zion_core_checkout_id );
$zion_core_checkout_html    = '<div class="wp-block-woocommerce-checkout"><form data-native="preserved"><input name="native-test"></form></div>';
$zion_core_checkout_before  = get_option( Zion_Theme_Settings::OPTION, Zion_Theme_Settings::empty_config() );
try {
	foreach ( array_keys( Zion_Theme_Settings::checkout_designs() ) as $zion_core_checkout_design ) {
		$zion_core_checkout_config                              = Zion_Theme_Settings::empty_config();
		$zion_core_checkout_config['global']['checkout_design'] = $zion_core_checkout_design;
		update_option( Zion_Theme_Settings::OPTION, $zion_core_checkout_config );
		$zion_core_checkout_rendered = zion_theme_checkout_design( $zion_core_checkout_html );
		zion_core_test_assert(
			str_contains( $zion_core_checkout_rendered, 'zion-checkout--' . $zion_core_checkout_design ) &&
			str_contains( $zion_core_checkout_rendered, '<form data-native="preserved"><input name="native-test"></form>' ) &&
			get_post_field( 'post_content', $zion_core_checkout_id ) === $zion_core_checkout_content,
			'Checkout design ' . $zion_core_checkout_design . ' preserves native children and saved page content'
		);
	}
} finally {
	update_option( Zion_Theme_Settings::OPTION, $zion_core_checkout_before );
}
require __DIR__ . '/about.php';
require __DIR__ . '/home-continuum.php';
require __DIR__ . '/services-continuum.php';
require __DIR__ . '/contact.php';
require __DIR__ . '/journal.php';
require __DIR__ . '/faq.php';
require __DIR__ . '/software.php';
require __DIR__ . '/studio.php';
require __DIR__ . '/customer-location.php';
require __DIR__ . '/seo.php';
require __DIR__ . '/sameday-translations.php';
require __DIR__ . '/empty-cart.php';
WP_CLI::success( 'Catalog integration passed.' );
