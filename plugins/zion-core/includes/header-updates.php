<?php
/**
 * Bounded public editorial and 3D catalogue updates.
 *
 * @package Zion_Core
 */

/**
 * Get two newest articles and two catalogue-visible 3D parent products.
 *
 * @return array
 */
function zion_core_header_updates(): array {
	$groups = array(
		'article' => array(
			'label' => __( 'Din jurnal', 'zion-core' ),
			'query' => array( 'post_type' => 'post' ),
		),
	);
	if ( function_exists( 'wc_get_product_visibility_term_ids' ) && taxonomy_exists( 'zion_shop' ) ) {
		$visibility = wc_get_product_visibility_term_ids();
		$excluded   = array( $visibility['exclude-from-catalog'] );
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$excluded[] = $visibility['outofstock'];
		}
		$groups['product'] = array(
			'label' => __( 'Magazin 3D', 'zion-core' ),
			'query' => array(
				'post_type' => 'product',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Bounded native taxonomy query; never loads the full catalogue.
				'tax_query' => array(
					'relation' => 'AND',
					array(
						'taxonomy'         => 'zion_shop',
						'field'            => 'slug',
						'terms'            => array( '3d' ),
						'include_children' => false,
					),
					array(
						'taxonomy' => 'product_visibility',
						'field'    => 'term_taxonomy_id',
						'terms'    => array_filter( $excluded ),
						'operator' => 'NOT IN',
					),
				),
			),
		);
	}
	$items = array();
	foreach ( $groups as $type => $group ) {
		$query = new WP_Query(
			array_merge(
				array(
					'post_status'            => 'publish',
					'has_password'           => false,
					'posts_per_page'         => 2,
					'orderby'                => array(
						'date' => 'DESC',
						'ID'   => 'DESC',
					),
					'ignore_sticky_posts'    => true,
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				),
				$group['query']
			)
		);
		foreach ( $query->posts as $post ) {
			$items[] = array(
				'id'    => $post->ID,
				'type'  => $type,
				'label' => $group['label'],
				'title' => wp_strip_all_tags( html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ) ),
				'url'   => get_permalink( $post ),
			);
		}
	}
	return $items;
}
