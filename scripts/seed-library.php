<?php
/**
 * Create the local demonstrations for the Zion component library.
 *
 * @package Zion_Core
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || 'local' !== wp_get_environment_type() || ! function_exists( 'zion_theme_create_service_pages' ) ) {
	return;
}

$zion_core_services = zion_theme_create_service_pages();
if ( $zion_core_services['failed'] ) {
	WP_CLI::error( 'Could not create all Zion service pages.' );
}
$zion_core_reference = wc_get_product_id_by_sku( 'ZION-DEMO-SW-01' );

if ( $zion_core_reference && ! get_comments(
	array(
		'post_id'      => $zion_core_reference,
		'type'         => 'review',
		'author_email' => 'review@example.test',
		'number'       => 1,
	)
) ) {
	wp_insert_comment(
		wp_slash(
			array(
				'comment_post_ID'      => $zion_core_reference,
				'comment_type'         => 'review',
				'comment_author'       => 'Recenzie demo Zion',
				'comment_author_email' => 'review@example.test',
				'comment_content'      => 'Exemplu demonstrativ pentru afișarea unei recenzii WooCommerce în biblioteca Zion.',
				'comment_approved'     => 1,
				'comment_meta'         => array( 'rating' => 5 ),
			)
		)
	);
}

if ( ! get_page_by_path( 'componente-zion-demo' ) ) {
	$zion_core_components = array(
		'product-grid'      => array(
			'limit'    => 1,
			'columns'  => 1,
			'paginate' => true,
		),
		'featured-products' => array( 'limit' => 4 ),
		'products-by-shop'  => array(
			'shop'    => '3d',
			'limit'   => 2,
			'columns' => 2,
		),
		'new-arrivals'      => array( 'limit' => 3 ),
		'on-sale'           => array( 'limit' => 3 ),
		'related-products'  => array(
			'productId' => $zion_core_reference,
			'limit'     => 3,
		),
		'services-grid'     => array(),
		'cta'               => array(
			'title' => 'CTA demonstrativ',
			'label' => 'Citește despre Zion',
		),
		'testimonials'      => array(),
	);
	$zion_core_content    = '';
	foreach ( zion_theme_library_blocks() as $zion_core_slug => $zion_core_block ) {
		$zion_core_content .= '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $zion_core_block[0] ) . '</h2><!-- /wp:heading -->';
		$zion_core_content .= zion_core_storefront_block( 'zion/' . $zion_core_slug, $zion_core_components[ $zion_core_slug ] );
	}
	$zion_core_content  .= '<!-- wp:heading --><h2 class="wp-block-heading">Shortcode echivalent</h2><!-- /wp:heading -->' .
		'<!-- wp:shortcode -->[zion_products shop="3d" limit="2" columns="2"]<!-- /wp:shortcode -->';
	$zion_core_demo_page = wp_insert_post(
		wp_slash(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => 'componente-zion-demo',
				'post_title'   => 'Biblioteca Zion · demonstrații',
				'post_content' => $zion_core_content,
			)
		),
		true
	);
	if ( is_wp_error( $zion_core_demo_page ) ) {
		WP_CLI::error( $zion_core_demo_page->get_error_message() );
	}
}
