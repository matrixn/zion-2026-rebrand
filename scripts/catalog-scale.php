<?php
/**
 * Build or remove a synthetic catalog on the isolated wp-env test site.
 *
 * @package Zion_Core
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

if ( 'local' !== wp_get_environment_type() || 8917 !== wp_parse_url( home_url(), PHP_URL_PORT ) || ! taxonomy_exists( 'zion_shop' ) ) {
	WP_CLI::error( 'Use the isolated wp-env tests-cli site on port 8917.' );
}

/**
 * Create or resolve a term reserved for the performance fixture.
 *
 * @param string $taxonomy Native taxonomy.
 * @param string $slug Reserved slug.
 * @param string $label Visible term name.
 * @return int
 */
function zion_core_scale_term( string $taxonomy, string $slug, string $label ): int {
	$term = get_term_by( 'slug', $slug, $taxonomy );
	if ( $term ) {
		if ( 'Zion catalog benchmark fixture' !== $term->description ) {
			WP_CLI::error( 'A fixture term slug is already in use.' );
		}
		return $term->term_id;
	}
	$created = wp_insert_term(
		$label,
		$taxonomy,
		array(
			'slug'        => $slug,
			'description' => 'Zion catalog benchmark fixture',
		)
	);
	if ( is_wp_error( $created ) ) {
		WP_CLI::error( $created->get_error_message() );
	}
	return $created['term_id'];
}

/**
 * Prepare a native WooCommerce attribute and its fixture terms.
 *
 * @param string $slug Attribute slug.
 * @param string $label Attribute label.
 * @param array  $choices Term slugs.
 * @return array
 */
function zion_core_scale_attribute( string $slug, string $label, array $choices ): array {
	$attribute_id = wc_attribute_taxonomy_id_by_name( $slug );
	if ( ! $attribute_id ) {
		$attribute_id = wc_create_attribute(
			array(
				'slug'         => $slug,
				'name'         => $label,
				'type'         => 'select',
				'has_archives' => false,
			)
		);
		if ( is_wp_error( $attribute_id ) ) {
			WP_CLI::error( $attribute_id->get_error_message() );
		}
	} elseif ( wc_get_attribute( $attribute_id )->name !== $label ) {
		WP_CLI::error( 'A fixture attribute slug is already in use.' );
	}
	$taxonomy = wc_attribute_taxonomy_name( $slug );
	if ( ! taxonomy_exists( $taxonomy ) ) {
		register_taxonomy(
			$taxonomy,
			'product',
			array(
				'hierarchical' => true,
				'show_ui'      => true,
			)
		);
	}
	$terms = array();
	foreach ( $choices as $choice ) {
		$terms[ $choice ] = zion_core_scale_term( $taxonomy, $choice, strtoupper( $choice ) );
	}
	return array(
		'id'       => (int) $attribute_id,
		'taxonomy' => $taxonomy,
		'terms'    => $terms,
	);
}

/**
 * Create the fixture's two variations if a previous seed did not complete.
 *
 * @param WC_Product_Variable $variable Variable product.
 * @param array               $attribute Native attribute metadata.
 * @param string              $sku Parent SKU.
 */
function zion_core_scale_variations( WC_Product_Variable $variable, array $attribute, string $sku ): void {
	foreach ( array_keys( $attribute['terms'] ) as $index => $choice ) {
		$child_sku = $sku . '-V' . $index;
		$id        = wc_get_product_id_by_sku( $child_sku );
		if ( $id ) {
			$existing = wc_get_product( $id );
			if ( ! $existing instanceof WC_Product_Variation || $existing->get_parent_id() !== $variable->get_id() || 'phase-05' !== $existing->get_meta( '_zion_scale_fixture' ) ) {
				WP_CLI::error( 'A fixture variation SKU is already in use.' );
			}
			continue;
		}
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $variable->get_id() );
		$variation->set_sku( $child_sku );
		$variation->set_status( 'publish' );
		$variation->set_attributes( array( $attribute['taxonomy'] => $choice ) );
		$variation->set_regular_price( (string) ( 65 + 35 * $index ) );
		if ( 0 === $index ) {
			$variation->set_sale_price( '55' );
		}
		$variation->update_meta_data( '_zion_scale_fixture', 'phase-05' );
		$variation->save();
	}
	WC_Product_Variable::sync( $variable->get_id() );
}

/**
 * Remove only synthetic catalog records and their reserved terms.
 */
function zion_core_scale_clean(): void {
	$ids = get_posts(
		array(
			'post_type'   => array( 'product', 'product_variation' ),
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Cleanup runs only in the isolated test database.
			'meta_query'  => array(
				array(
					'key'   => '_zion_scale_fixture',
					'value' => 'phase-05',
				),
			),
		)
	);
	foreach ( array_reverse( $ids ) as $id ) {
		$product = wc_get_product( $id );
		if ( $product ) {
			$product->delete( true );
		}
	}
	foreach ( array( 'product_cat', 'product_brand' ) as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		foreach ( array( 'zion-scale-3d', 'zion-scale-software', 'zion-scale-shared', 'zion-scale-brand-3d', 'zion-scale-brand-software' ) as $slug ) {
			$term = get_term_by( 'slug', $slug, $taxonomy );
			if ( $term && 'Zion catalog benchmark fixture' === $term->description ) {
				wp_delete_term( $term->term_id, $taxonomy );
			}
		}
	}
	foreach ( array(
		'zion-scale-material' => 'Zion Scale Material',
		'zion-scale-platform' => 'Zion Scale Platform',
	) as $slug => $label ) {
		$id = wc_attribute_taxonomy_id_by_name( $slug );
		if ( $id && wc_get_attribute( $id )->name === $label ) {
			wc_delete_attribute( $id );
		}
	}
	WP_CLI::success( 'Removed the synthetic catalog.' );
}

/**
 * Seed 1,000 native WooCommerce parent products in two storefronts.
 *
 * @throws RuntimeException If a reserved fixture SKU belongs to another product.
 */
function zion_core_scale_seed(): void {
	$shop_ids     = zion_core_shop_term_ids();
	$attributes   = array(
		'3d'       => zion_core_scale_attribute( 'zion-scale-material', 'Zion Scale Material', array( 'pla', 'petg' ) ),
		'software' => zion_core_scale_attribute( 'zion-scale-platform', 'Zion Scale Platform', array( 'wordpress', 'linux' ) ),
	);
	$category_ids = array(
		'3d'       => zion_core_scale_term( 'product_cat', 'zion-scale-3d', 'Zion Scale 3D' ),
		'software' => zion_core_scale_term( 'product_cat', 'zion-scale-software', 'Zion Scale Software' ),
		'shared'   => zion_core_scale_term( 'product_cat', 'zion-scale-shared', 'Zion Scale Shared' ),
	);
	$brand_ids    = array();
	if ( taxonomy_exists( 'product_brand' ) ) {
		foreach ( array( '3d', 'software' ) as $shop ) {
			$brand_ids[ $shop ] = zion_core_scale_term( 'product_brand', 'zion-scale-brand-' . $shop, 'Zion Scale Brand ' . $shop );
		}
	}
	$defer = wp_defer_term_counting();
	wp_defer_term_counting( true );
	add_filter( 'pre_option_woocommerce_attribute_lookup_direct_updates', 'zion_core_scale_lookup_mode' );
	try {
		for ( $index = 0; $index < 1000; ++$index ) {
			$shop      = 0 === $index % 2 ? '3d' : 'software';
			$position  = intdiv( $index, 2 );
			$slot      = $position % 10;
			$sku       = sprintf( 'ZION-SCALE-%04d', $index + 1 );
			$attribute = $attributes[ $shop ];
			$id        = wc_get_product_id_by_sku( $sku );
			if ( $id ) {
				$product = wc_get_product( $id );
				if ( 'phase-05' !== $product->get_meta( '_zion_scale_fixture' ) ) {
					throw new RuntimeException( 'A fixture product SKU is already in use.' );
				}
			} else {
				$product = 0 === $position ? new WC_Product_Variable() : new WC_Product_Simple();
				$product->set_name( sprintf( 'Zion Scale %s %03d', '3d' === $shop ? '3D' : 'Software', $position + 1 ) );
				$product->set_sku( $sku );
				$product->set_status( 'publish' );
				$product->set_virtual( 'software' === $shop );
				$product->set_featured( 0 === $slot || 9 === $slot );
				$product->set_menu_order( $position );
				$product->set_category_ids( array( $category_ids[ $slot < 8 ? $shop : 'shared' ] ) );
				$product->update_meta_data( '_zion_scale_fixture', 'phase-05' );
				$native_attribute = new WC_Product_Attribute();
				$native_attribute->set_id( $attribute['id'] );
				$native_attribute->set_name( $attribute['taxonomy'] );
				$native_attribute->set_visible( true );
				$native_attribute->set_variation( $product instanceof WC_Product_Variable );
				$native_attribute->set_options( $product instanceof WC_Product_Variable ? array_values( $attribute['terms'] ) : array( array_values( $attribute['terms'] )[ $slot % 2 ] ) );
				$product->set_attributes( array( $native_attribute ) );
				if ( $product instanceof WC_Product_Simple ) {
					$regular = 40 + intdiv( $position, 10 ) % 10 * 10 + $slot * 10;
					$product->set_regular_price( (string) $regular );
					$product->set_stock_status( 1 === $slot % 3 ? 'outofstock' : 'instock' );
					$product->set_total_sales( $position );
					$product->set_average_rating( (string) ( 3 + $slot % 3 ) );
					if ( in_array( $slot, array( 0, 2, 4 ), true ) ) {
						$product->set_sale_price( (string) ( $regular - 10 ) );
					}
				}
				$product->save();
			}
			wp_set_object_terms( $product->get_id(), $shop_ids[ $shop ], 'zion_shop' );
			if ( isset( $brand_ids[ $shop ] ) ) {
				wp_set_object_terms( $product->get_id(), $brand_ids[ $shop ], 'product_brand' );
			}
			if ( $product instanceof WC_Product_Variable ) {
				zion_core_scale_variations( $product, $attribute, $sku );
			}
			if ( 0 === ( $index + 1 ) % 100 ) {
				WP_CLI::log( sprintf( '%d products ready', $index + 1 ) );
			}
		}
	} finally {
		remove_filter( 'pre_option_woocommerce_attribute_lookup_direct_updates', 'zion_core_scale_lookup_mode' );
		wp_defer_term_counting( $defer );
	}
	wc_delete_product_transients();
	WP_CLI::success( '1,000 products ready, split evenly across both storefronts.' );
}

/**
 * Update native attribute lookup data during the fixture load.
 *
 * @return string
 */
function zion_core_scale_lookup_mode(): string {
	return 'yes';
}

if ( 'clean' === ( $args[0] ?? 'seed' ) ) {
	zion_core_scale_clean();
} else {
	zion_core_scale_seed();
}
