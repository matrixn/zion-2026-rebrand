<?php
/**
 * Exercise the classifier against actual WooCommerce product types.
 *
 * @package Zion_Core
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || 'local' !== wp_get_environment_type() ) {
	return;
}

/**
 * Snapshot the WooCommerce fields that classification must preserve.
 *
 * @param WC_Product $product Product before or after a change.
 * @return array
 */
function zion_core_test_commerce_fields( WC_Product $product ): array {
	return array(
		$product->get_type(),
		$product->get_status(),
		$product->get_regular_price(),
		$product->get_stock_quantity(),
		$product->get_virtual(),
		$product->get_downloadable(),
		array_map( static fn ( WC_Product_Download $download ): string => $download->get_file(), $product->get_downloads() ),
		$product->get_category_ids(),
		$product->get_children(),
	);
}

$zion_core_initial_actor  = get_current_user_id();
$zion_core_admins         = get_users(
	array(
		'role'   => 'administrator',
		'number' => 1,
		'fields' => 'ID',
	)
);
$zion_core_journal_args   = array(
	'post_type'   => 'zion_shop_journal',
	'post_status' => 'private',
	'fields'      => 'ids',
	'numberposts' => -1,
);
$zion_core_initial_events = get_posts( $zion_core_journal_args );
$zion_core_ids            = array();
$zion_core_checks         = array();

try {
	wp_set_current_user( (int) $zion_core_admins[0] );
	$zion_core_download_mode = static fn (): string => 'disabled';
	add_filter( 'pre_option_wc_downloads_approved_directories_mode', $zion_core_download_mode );
	$zion_core_term_ids      = zion_core_shop_term_ids( true );
	$zion_core_checks['Canonical term IDs are available for both storefronts'] = ! is_wp_error( $zion_core_term_ids ) && array( '3d', 'software' ) === array_keys( $zion_core_term_ids );

	$zion_core_cases           = array(
		'Physical 3D'           => array( new WC_Product_Simple(), array( '3d' ), false, false, 'publish' ),
		'STL file'              => array( new WC_Product_Simple(), array( '3d' ), true, true, 'publish' ),
		'Downloadable software' => array( new WC_Product_Simple(), array( 'software' ), true, true, 'publish' ),
		'Software license'      => array( new WC_Product_Simple(), array( 'software' ), true, false, 'publish' ),
		'Variable item'         => array( new WC_Product_Variable(), array( '3d' ), false, false, 'publish' ),
		'Grouped item'          => array( new WC_Product_Grouped(), array( 'software' ), false, false, 'publish' ),
		'Dual store item'       => array( new WC_Product_Simple(), array( '3d', 'software' ), true, true, 'publish' ),
		'Draft item'            => array( new WC_Product_Simple(), array( '3d', 'software' ), false, false, 'draft' ),
	);
	$zion_core_commerce_fields = array();
	$zion_core_plan_groups     = array();
	$zion_core_category        = get_term_by( 'slug', 'prototipuri-demo', 'product_cat' );
	foreach ( $zion_core_cases as $zion_core_label => $zion_core_case ) {
		list( $zion_core_product, $zion_core_shops, $zion_core_virtual, $zion_core_downloadable, $zion_core_status ) = $zion_core_case;
		$zion_core_product->set_name( 'Zion Classification Fixture ' . $zion_core_label );
		$zion_core_product->set_status( $zion_core_status );
		$zion_core_product->set_virtual( $zion_core_virtual );
		$zion_core_product->set_downloadable( $zion_core_downloadable );
		if ( $zion_core_downloadable ) {
			$zion_core_extension = 'STL file' === $zion_core_label ? 'stl' : 'zip';
			$zion_core_download  = new WC_Product_Download();
			$zion_core_download->set_id( wp_generate_uuid4() );
			$zion_core_download->set_name( 'Fixture ' . $zion_core_extension );
			$zion_core_download->set_file( 'https://example.test/fixture.' . $zion_core_extension );
			$zion_core_product->set_downloads( array( $zion_core_download ) );
		}
		$zion_core_product->set_regular_price( '24' );
		if ( $zion_core_category ) {
			$zion_core_product->set_category_ids( array( $zion_core_category->term_id ) );
		}
		if ( 'Physical 3D' === $zion_core_label ) {
			$zion_core_product->set_manage_stock( true );
			$zion_core_product->set_stock_quantity( 9 );
		}
		if ( $zion_core_product instanceof WC_Product_Grouped ) {
			$zion_core_product->set_children( array( $zion_core_ids['Physical 3D'], $zion_core_ids['STL file'] ) );
		}
		$zion_core_id                      = $zion_core_product->save();
		$zion_core_ids[ $zion_core_label ] = $zion_core_id;
		if ( $zion_core_product instanceof WC_Product_Variable ) {
			$zion_core_variation = new WC_Product_Variation();
			$zion_core_variation->set_parent_id( $zion_core_id );
			$zion_core_variation->set_status( 'publish' );
			$zion_core_variation->set_regular_price( '24' );
			$zion_core_ids['Variation child'] = $zion_core_variation->save();
			WC_Product_Variable::sync( $zion_core_id );
		}
		$zion_core_commerce_fields[ $zion_core_id ]                  = zion_core_test_commerce_fields( wc_get_product( $zion_core_id ) );
		$zion_core_plan_groups[ implode( ',', $zion_core_shops ) ][] = $zion_core_id;
	}

	$zion_core_preview        = zion_core_classification_plan( $zion_core_plan_groups['3d'], 'add', array( '3d' ) );
	$zion_core_preview_events = get_posts( $zion_core_journal_args );
	$zion_core_checks['Preview calculates a plan without modifying relationships or writing a journal'] =
		! is_wp_error( $zion_core_preview ) && array() === zion_core_product_shop_ids( $zion_core_ids['Physical 3D'] ) &&
		$zion_core_initial_events === $zion_core_preview_events;

	$zion_core_plan_success = true;
	foreach ( $zion_core_plan_groups as $zion_core_membership => $zion_core_group ) {
		$zion_core_plan         = zion_core_classification_plan( $zion_core_group, 'replace', explode( ',', $zion_core_membership ) );
		$zion_core_outcome      = is_wp_error( $zion_core_plan ) ? $zion_core_plan : zion_core_apply_classification_plan( $zion_core_plan );
		$zion_core_plan_success = $zion_core_plan_success && ! is_wp_error( $zion_core_outcome ) && ! array_diff( array_column( $zion_core_outcome, 'state' ), array( 'applied' ) );
	}
	$zion_core_checks['Approved batches apply exact storefront memberships']          = $zion_core_plan_success;
	$zion_core_checks['Applying three changed batches creates three journal entries'] =
		3 === count( array_diff( get_posts( $zion_core_journal_args ), $zion_core_initial_events ) );

	$zion_core_catalog_3d       = do_shortcode( '[zion_catalog shop="3d" limit="48"]' );
	$zion_core_catalog_software = do_shortcode( '[zion_catalog shop="software" limit="48"]' );
	foreach ( $zion_core_cases as $zion_core_label => $zion_core_case ) {
		list( , $zion_core_shops, , , $zion_core_status ) = $zion_core_case;
		$zion_core_title                                  = 'Zion Classification Fixture ' . $zion_core_label;
		$zion_core_visible                                = 'publish' === $zion_core_status;
		$zion_core_in_3d                                  = str_contains( $zion_core_catalog_3d, $zion_core_title );
		$zion_core_in_software                            = str_contains( $zion_core_catalog_software, $zion_core_title );
		$zion_core_expected_3d                            = $zion_core_visible && in_array( '3d', $zion_core_shops, true );
		$zion_core_expected_software                      = $zion_core_visible && in_array( 'software', $zion_core_shops, true );
		$zion_core_checks[ $zion_core_label . ' appears only in the expected public catalogs' ] =
			$zion_core_in_3d === $zion_core_expected_3d && $zion_core_in_software === $zion_core_expected_software;
	}
	$zion_core_unchanged = true;
	foreach ( $zion_core_commerce_fields as $zion_core_id => $zion_core_fields ) {
		$zion_core_current_fields = zion_core_test_commerce_fields( wc_get_product( $zion_core_id ) );
		$zion_core_unchanged      = $zion_core_unchanged && $zion_core_fields === $zion_core_current_fields;
	}
	$zion_core_checks['Categories, price, stock, flags, downloads and product children are unchanged'] = $zion_core_unchanged;
	$zion_core_checks['STL and software fixtures retain distinct native downloadable file references'] =
		'https://example.test/fixture.stl' === array_values( $zion_core_commerce_fields[ $zion_core_ids['STL file'] ][6] )[0] &&
		'https://example.test/fixture.zip' === array_values( $zion_core_commerce_fields[ $zion_core_ids['Downloadable software'] ][6] )[0];

	$zion_core_stale = zion_core_classification_plan( array( $zion_core_ids['Physical 3D'] ), 'remove', array( '3d' ) );
	wp_set_object_terms( $zion_core_ids['Physical 3D'], $zion_core_term_ids['software'], 'zion_shop' );
	$zion_core_checks['A changed product invalidates a previously approved plan'] = ! is_wp_error( $zion_core_stale ) &&
		is_wp_error( zion_core_apply_classification_plan( $zion_core_stale ) ) &&
		array( $zion_core_term_ids['software'] ) === zion_core_product_shop_ids( $zion_core_ids['Physical 3D'] );
	$zion_core_checks['Variation IDs cannot be classified as parent products']    =
		is_wp_error( zion_core_classification_plan( array( $zion_core_ids['Variation child'] ), 'add', array( '3d' ) ) );

	wp_set_current_user( 0 );
	$zion_core_checks['An anonymous actor cannot build or apply classification plans'] =
		is_wp_error( zion_core_classification_plan( array( $zion_core_ids['Physical 3D'] ), 'add', array( '3d' ) ) ) &&
		is_wp_error( zion_core_apply_classification_plan( $zion_core_stale ) );
} finally {
	remove_filter( 'pre_option_wc_downloads_approved_directories_mode', $zion_core_download_mode );
	wp_set_current_user( (int) $zion_core_admins[0] );
	foreach ( array_reverse( $zion_core_ids ) as $zion_core_id ) {
		$zion_core_product = wc_get_product( $zion_core_id );
		if ( $zion_core_product ) {
			$zion_core_product->delete( true );
		}
	}
	foreach ( array_diff( get_posts( $zion_core_journal_args ), $zion_core_initial_events ) as $zion_core_event_id ) {
		wp_delete_post( $zion_core_event_id, true );
	}
	wp_set_current_user( $zion_core_initial_actor );
}

foreach ( $zion_core_checks as $zion_core_check => $zion_core_passed ) {
	zion_core_test_assert( $zion_core_passed, $zion_core_check );
}
