<?php
/**
 * Storefront filters and native WooCommerce product listings.
 *
 * @package Zion_Core
 */

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
		'columns' => is_numeric( $columns ) ? max( 1, min( 5, (int) $columns ) ) : 3,
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

	$display = apply_filters(
		'zion_core_catalog_display',
		array(
			'category_filter' => zion_core_current_shop() === $options['shop'],
			'layout'          => 'grid',
			'desktop'         => $options['columns'],
			'page_size'       => $options['limit'],
		),
		$options['shop']
	);
	if ( zion_core_current_shop() === $options['shop'] && zion_core_has_storefront_blocks() && ! is_admin() && ! wp_is_serving_rest_request() ) {
		return zion_core_render_storefront_collection( $options, $display );
	}
	$show_filter = true === ( $display['category_filter'] ?? false );
	$list        = 'list' === ( $display['layout'] ?? '' );
	$category    = $show_filter ? zion_core_catalog_category() : 0;
	$marker      = 'zion-catalog-' . $options['shop'];
	$filter      = static function ( array $query, array $product_options ) use ( $options, $marker, $category ): array {
		if ( ( $product_options['class'] ?? '' ) === $marker ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Keep WooCommerce visibility constraints and add the storefront term.
			$query['tax_query'] = array(
				'relation' => 'AND',
				$query['tax_query'],
				array(
					'taxonomy'         => 'zion_shop',
					'field'            => 'slug',
					'terms'            => $options['shop'],
					'include_children' => false,
				),
			);
			if ( $category ) {
				$query['tax_query'][] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => array( $category ),
				);
			}
		}

		return $query;
	};

	$empty = static function ( array $product_options ) use ( $marker ): void {
		if ( ( $product_options['class'] ?? '' ) === $marker ) {
			echo '<p class="woocommerce-info">' . esc_html__( 'Nu există produse pentru selecția curentă.', 'zion-core' ) . '</p>';
		}
	};
	add_filter( 'woocommerce_shortcode_products_query', $filter, 10, 2 );
	add_action( 'woocommerce_shortcode_products_loop_no_results', $empty );
	try {
		$products = do_shortcode(
			sprintf(
				'[products class="%s" limit="%d" columns="%d" paginate="true" cache="false" orderby="title" order="ASC"]',
				$marker,
				$options['limit'],
				$list ? 1 : $options['columns']
			)
		);
	} finally {
		remove_filter( 'woocommerce_shortcode_products_query', $filter, 10 );
		remove_action( 'woocommerce_shortcode_products_loop_no_results', $empty );
	}

	if ( $list ) {
		$products = '<div class="zion-storefront-list">' . $products . '</div>';
	}
	return ( $show_filter ? zion_core_catalog_category_form( $category ) : '' ) . $products;
}

/**
 * Read the selected native product category from the public filter.
 *
 * @return int
 */
function zion_core_catalog_category(): int {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only public catalog filter.
	$id = isset( $_GET['zion_category'] ) && is_string( $_GET['zion_category'] ) ? absint( $_GET['zion_category'] ) : 0;
	return $id && term_exists( $id, 'product_cat' ) ? $id : 0;
}

/**
 * Render the public filter using native WooCommerce product categories.
 *
 * @param int $category Selected product category.
 * @return string
 */
function zion_core_catalog_category_form( int $category ): string {
	$dropdown = wp_dropdown_categories(
		array(
			'taxonomy'        => 'product_cat',
			'name'            => 'zion_category',
			'id'              => 'zion-category',
			'show_option_all' => __( 'Toate categoriile', 'zion-core' ),
			'selected'        => $category,
			'hierarchical'    => true,
			'echo'            => false,
		)
	);
	if ( ! $dropdown ) {
		return '';
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Preserve native catalog sorting across a read-only category change.
	$orderby = isset( $_GET['orderby'] ) && is_string( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : '';
	$id      = get_queried_object_id();
	$url     = $id && is_singular() ? get_permalink( $id ) : home_url( '/' );
	$form    = '<form class="zion-storefront-filters" method="get" action="' . esc_url( $url ) . '">';
	if ( $id && ! get_option( 'permalink_structure' ) ) {
		$form .= '<input type="hidden" name="page_id" value="' . esc_attr( (string) $id ) . '">';
	}
	if ( $orderby ) {
		$form .= '<input type="hidden" name="orderby" value="' . esc_attr( $orderby ) . '">';
	}
	return $form . '<label for="zion-category">' . esc_html__( 'Categorie produse', 'zion-core' ) . '</label>' . $dropdown .
		'<button type="submit" class="wp-element-button">' . esc_html__( 'Filtrează', 'zion-core' ) . '</button></form>';
}
