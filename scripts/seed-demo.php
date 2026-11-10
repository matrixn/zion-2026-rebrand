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

$zion_core_journal_pages = array(
	'acasa-zion' => 'Acasă',
	'jurnal'     => 'Jurnal',
);
$zion_core_page_ids      = array();
foreach ( $zion_core_journal_pages as $zion_core_slug => $zion_core_title ) {
	$zion_core_existing = get_page_by_path( $zion_core_slug );
	$zion_core_page_id  = $zion_core_existing ? $zion_core_existing->ID : wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_name'   => $zion_core_slug,
			'post_title'  => $zion_core_title,
		),
		true
	);
	if ( is_wp_error( $zion_core_page_id ) ) {
		WP_CLI::error( $zion_core_page_id->get_error_message() );
	}
	$zion_core_page_ids[ $zion_core_slug ] = $zion_core_page_id;
}

if ( 'posts' === get_option( 'show_on_front' ) ) {
	update_option( 'page_on_front', $zion_core_page_ids['acasa-zion'] );
	update_option( 'show_on_front', 'page' );
}
if ( ! get_option( 'page_for_posts' ) ) {
	update_option( 'page_for_posts', $zion_core_page_ids['jurnal'] );
}

if ( ! get_page_by_path( 'jurnal-prototip-zion', OBJECT, 'post' ) ) {
	$zion_core_post_id = wp_insert_post(
		array(
			'post_status'  => 'publish',
			'post_name'    => 'jurnal-prototip-zion',
			'post_title'   => 'Jurnal de prototipare Zion',
			'post_content' => '<!-- wp:paragraph --><p>Un prototip demonstrativ, de la model la prima probă de montaj.</p><!-- /wp:paragraph -->',
		),
		true
	);
	if ( is_wp_error( $zion_core_post_id ) ) {
		WP_CLI::error( $zion_core_post_id->get_error_message() );
	}
}

$zion_core_shop_ids = zion_core_shop_term_ids( true );
if ( is_wp_error( $zion_core_shop_ids ) ) {
	WP_CLI::error( $zion_core_shop_ids->get_error_message() );
}
foreach ( zion_core_create_shop_pages() as $zion_core_page ) {
	if ( is_wp_error( $zion_core_page ) ) {
		WP_CLI::error( $zion_core_page->get_error_message() );
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
	$zion_core_terms = wp_set_object_terms( $zion_core_product_id, $zion_core_shop_ids[ $zion_core_item['shop'] ], 'zion_shop', true );
	if ( is_wp_error( $zion_core_terms ) ) {
		WP_CLI::error( $zion_core_terms->get_error_message() );
	}

	if ( 'ZION-DEMO-3D-01' === $zion_core_item['sku'] ) {
		$zion_core_category = get_term_by( 'slug', 'prototipuri-demo', 'product_cat' );
		$zion_core_term     = $zion_core_category ? array( 'term_id' => $zion_core_category->term_id ) : wp_insert_term( 'Prototipuri demo', 'product_cat', array( 'slug' => 'prototipuri-demo' ) );
		if ( is_wp_error( $zion_core_term ) ) {
			WP_CLI::error( $zion_core_term->get_error_message() );
		}
		$zion_core_terms = wp_set_object_terms( $zion_core_product_id, (int) $zion_core_term['term_id'], 'product_cat', true );
		if ( is_wp_error( $zion_core_terms ) ) {
			WP_CLI::error( $zion_core_terms->get_error_message() );
		}
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
