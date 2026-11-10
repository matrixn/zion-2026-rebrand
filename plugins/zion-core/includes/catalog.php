<?php
/**
 * Storefront taxonomy and native WooCommerce product listings.
 *
 * @package Zion_Core
 */

/**
 * Register the independent storefront classification.
 */
function zion_core_register_shops(): void {
	if ( ! post_type_exists( 'product' ) ) {
		return;
	}

	register_taxonomy(
		'zion_shop',
		'product',
		array(
			'labels'            => array(
				'name'          => __( 'Zion shops', 'zion-core' ),
				'singular_name' => __( 'Zion shop', 'zion-core' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
		)
	);
}

/**
 * Normalize storefront options.
 *
 * @param array|string $attributes Shortcode input.
 * @return array
 */
function zion_core_catalog_options( array|string $attributes ): array {
	$attributes = is_array( $attributes ) ? $attributes : array();
	$shop       = $attributes['shop'] ?? '';
	$limit      = $attributes['limit'] ?? 12;
	$columns    = $attributes['columns'] ?? 3;

	return array(
		'shop'    => in_array( $shop, array( '3d', 'software' ), true ) ? $shop : '',
		'limit'   => is_numeric( $limit ) ? max( 1, min( 48, (int) $limit ) ) : 12,
		'columns' => is_numeric( $columns ) ? max( 1, min( 4, (int) $columns ) ) : 3,
	);
}

/**
 * Render a shop using WooCommerce's product loop and visibility rules.
 *
 * @param array|string $attributes Shortcode input.
 * @return string
 */
function zion_core_catalog( array|string $attributes ): string {
	$options = zion_core_catalog_options( $attributes );
	if ( ! taxonomy_exists( 'zion_shop' ) || ! shortcode_exists( 'products' ) || '' === $options['shop'] ) {
		return '';
	}

	$marker = 'zion-catalog-' . $options['shop'];
	$filter = static function ( array $query, array $product_options ) use ( $options, $marker ): array {
		if ( ( $product_options['class'] ?? '' ) === $marker ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Keep WooCommerce visibility constraints and add the storefront term.
			$query['tax_query'] = array(
				'relation' => 'AND',
				$query['tax_query'],
				array(
					'taxonomy' => 'zion_shop',
					'field'    => 'slug',
					'terms'    => $options['shop'],
				),
			);
		}

		return $query;
	};

	add_filter( 'woocommerce_shortcode_products_query', $filter, 10, 2 );
	try {
		return do_shortcode(
			sprintf(
				'[products class="%s" limit="%d" columns="%d" paginate="true" cache="false" orderby="title" order="ASC"]',
				$marker,
				$options['limit'],
				$options['columns']
			)
		);
	} finally {
		remove_filter( 'woocommerce_shortcode_products_query', $filter, 10 );
	}
}
