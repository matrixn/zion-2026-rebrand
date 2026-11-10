<?php
/**
 * Seed the isolated wp-env instance using WP-CLI.
 *
 * @package Zion_Core
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || 'local' !== wp_get_environment_type() ) {
	return;
}

if ( ! class_exists( 'WooCommerce' ) || ! taxonomy_exists( 'zion_shop' ) ) {
	WP_CLI::error( 'Activate WooCommerce and Zion Core before seeding.' );
}

$zion_core_fixture = json_decode(
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Read a local fixture in WP-CLI.
	file_get_contents( WP_CONTENT_DIR . '/zion-config/demo-catalog.json' ),
	true,
	512,
	JSON_THROW_ON_ERROR
);

switch_theme( 'zion-theme' );
update_option( 'blogname', 'Zion 3D' );
update_option( 'woocommerce_currency', 'RON' );
update_option( 'woocommerce_coming_soon', 'no' );
update_option( 'woocommerce_task_list_hidden', 'yes' );
if ( ! get_option( 'permalink_structure' ) ) {
	WP_CLI::runcommand( 'rewrite structure /%postname%/' );
}
WC_Install::create_pages();

foreach ( $zion_core_fixture['shops'] as $zion_core_shop => $zion_core_details ) {
	if ( ! term_exists( $zion_core_shop, 'zion_shop' ) ) {
		$zion_core_term = wp_insert_term( $zion_core_details['title'], 'zion_shop', array( 'slug' => $zion_core_shop ) );
		if ( is_wp_error( $zion_core_term ) ) {
			WP_CLI::error( $zion_core_term->get_error_message() );
		}
	}

	if ( ! get_page_by_path( $zion_core_details['slug'] ) ) {
		$zion_core_page = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => $zion_core_details['slug'],
				'post_title'   => $zion_core_details['title'],
				'post_content' => '<!-- wp:paragraph --><p>' . esc_html( $zion_core_details['description'] ) . '</p><!-- /wp:paragraph -->' .
					'<!-- wp:shortcode -->[zion_catalog shop="' . esc_attr( $zion_core_shop ) . '"]<!-- /wp:shortcode -->',
			),
			true
		);
		if ( is_wp_error( $zion_core_page ) ) {
			WP_CLI::error( $zion_core_page->get_error_message() );
		}
	}
}

foreach ( $zion_core_fixture['products'] as $zion_core_item ) {
	$zion_core_product_id = wc_get_product_id_by_sku( $zion_core_item['sku'] );
	if ( ! $zion_core_product_id ) {
		$zion_core_product = new WC_Product_Simple();
		$zion_core_product->set_sku( $zion_core_item['sku'] );
		$zion_core_product->set_name( $zion_core_item['name'] );
		$zion_core_product->set_description( $zion_core_item['description'] );
		$zion_core_product->set_regular_price( $zion_core_item['price'] );
		$zion_core_product->set_virtual( $zion_core_item['virtual'] );
		$zion_core_product->set_status( 'publish' );
		$zion_core_product_id = $zion_core_product->save();
	}
	$zion_core_terms = wp_set_object_terms( $zion_core_product_id, $zion_core_item['shop'], 'zion_shop', true );
	if ( is_wp_error( $zion_core_terms ) ) {
		WP_CLI::error( $zion_core_terms->get_error_message() );
	}

	$zion_core_product = wc_get_product( $zion_core_product_id );
	if ( ! $zion_core_product->get_image_id() ) {
		$zion_core_image = WP_CONTENT_DIR . '/zion-config/demo-images/' . strtolower( $zion_core_item['sku'] ) . '.png';
		WP_CLI::runcommand(
			sprintf(
				'media import %s --post_id=%d --featured_image',
				escapeshellarg( $zion_core_image ),
				$zion_core_product_id
			)
		);
	}
}

WP_CLI::success( 'Zion demo ready.' );
