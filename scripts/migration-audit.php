<?php
/**
 * Read-only inventory for a local or staging WordPress clone.
 *
 * @package Zion_Theme
 */

/**
 * Identify likely XStore widget and shortcode names.
 *
 * @param string $name Candidate identifier.
 * @return bool
 */
function zion_theme_migration_is_xstore( string $name ): bool {
	return (bool) preg_match( '/^(?:xstore|etheme|et[_-]|8theme)/i', $name );
}

/**
 * Collect shortcode names without expanding content.
 *
 * @param string $content WordPress content or a legacy builder field.
 * @return array
 */
function zion_theme_migration_shortcodes( string $content ): array {
	preg_match_all( '/(?<!\[)\[([a-z][a-z0-9_-]*)(?=[\s\/\]])/i', $content, $matches );
	return array_map( 'strtolower', $matches[1] );
}

/**
 * Collect legacy dependency indicators without rendering content or executing shortcodes.
 *
 * @param WP_Post $post Content to inspect.
 * @return array
 */
function zion_theme_migration_dependencies( WP_Post $post ): array {
	$elementor  = get_post_meta( $post->ID, '_elementor_data', true );
	$data       = is_string( $elementor ) ? json_decode( $elementor, true ) : $elementor;
	$widgets    = array();
	$shortcodes = zion_theme_migration_shortcodes( $post->post_content );
	if ( is_array( $data ) ) {
		array_walk_recursive(
			$data,
			static function ( mixed $value, string|int $key ) use ( &$widgets, &$shortcodes ): void {
				if ( 'widgetType' === $key && is_string( $value ) ) {
					$widgets[] = sanitize_key( $value );
				} elseif ( is_string( $value ) && str_contains( $value, '[' ) ) {
					$shortcodes = array_merge( $shortcodes, zion_theme_migration_shortcodes( $value ) );
				}
			}
		);
	}
	$widgets    = array_values( array_unique( array_filter( $widgets ) ) );
	$shortcodes = array_values( array_unique( $shortcodes ) );
	$builder    = 'builder' === get_post_meta( $post->ID, '_elementor_edit_mode', true );
	$stored     = ! empty( $data ) || ( is_string( $elementor ) && ! in_array( trim( $elementor ), array( '', '[]', '{}' ), true ) );
	return array(
		'elementor'         => $builder || $stored,
		'elementor_builder' => $builder,
		'elementor_data'    => $stored,
		'widgets'           => $widgets,
		'xstore_candidates' => array_values( array_filter( array_merge( $widgets, $shortcodes ), 'zion_theme_migration_is_xstore' ) ),
		'shortcodes'        => $shortcodes,
	);
}

/**
 * Read the SEO fields commonly stored by WordPress SEO plugins.
 *
 * @param int $id Post ID.
 * @return array
 */
function zion_theme_migration_seo( int $id ): array {
	$keys = array(
		'_yoast_wpseo_title',
		'_yoast_wpseo_metadesc',
		'_yoast_wpseo_canonical',
		'_yoast_wpseo_meta-robots-noindex',
		'rank_math_title',
		'rank_math_description',
		'rank_math_canonical_url',
		'rank_math_robots',
		'_seopress_titles_title',
		'_seopress_titles_desc',
		'_seopress_robots_canonical',
		'_seopress_robots_index',
	);
	$seo  = array();
	foreach ( $keys as $key ) {
		$value = get_post_meta( $id, $key, true );
		if ( is_string( $value ) && '' !== $value ) {
			$seo[ $key ] = wp_html_excerpt( wp_strip_all_tags( $value ), 2048, '' );
		} elseif ( 'rank_math_robots' === $key && is_array( $value ) ) {
			$seo[ $key ] = array_values( array_map( 'sanitize_key', array_filter( $value, 'is_string' ) ) );
		}
	}
	return $seo;
}

/**
 * Inventory native WooCommerce configuration without initializing payment gateways.
 *
 * @return array
 */
function zion_theme_migration_commerce(): array {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return array( 'active' => false );
	}
	$gateways = array();
	$options  = wp_load_alloptions();
	foreach ( array_keys( (array) get_option( 'woocommerce_gateway_order', array() ) ) as $name ) {
		$key             = 'woocommerce_' . sanitize_key( $name ) . '_settings';
		$options[ $key ] = get_option( $key, array() );
	}
	foreach ( $options as $key => $value ) {
		if ( preg_match( '/^woocommerce_([a-z0-9_-]+)_settings$/D', $key, $match ) ) {
			$value = maybe_unserialize( $value );
			if ( is_array( $value ) && 'yes' === ( $value['enabled'] ?? '' ) ) {
				$gateways[] = $match[1];
			}
		}
	}
	return array(
		'active'                 => true,
		'version'                => WC_VERSION,
		'hpos'                   => \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled(),
		'taxes'                  => wc_tax_enabled(),
		'shipping'               => wc_shipping_enabled(),
		'coupons'                => wc_coupons_enabled(),
		'gateway_candidates'     => array_values( array_unique( $gateways ) ),
		'subscription_post_type' => post_type_exists( 'shop_subscription' ),
		'store_pages'            => array_map( 'wc_get_page_id', array( 'shop', 'cart', 'checkout', 'myaccount' ) ),
	);
}

/**
 * Read pages, posts, templates, taxonomy links and product types in bounded batches.
 *
 * @return array
 */
function zion_theme_migration_audit(): array {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	$plugins = array();
	foreach ( get_plugins() as $file => $plugin ) {
		$plugins[] = array(
			'file'       => $file,
			'name'       => $plugin['Name'],
			'version'    => $plugin['Version'],
			'active'     => is_plugin_active( $file ),
			'commercial' => (bool) preg_match( '/woo|payment|shipping|tax|subscription|stripe|paypal|commerce|xstore/i', $file . ' ' . $plugin['Name'] ),
		);
	}
	$rows  = array();
	$types = array();
	$page  = 1;
	do {
		$posts = get_posts(
			array(
				'post_type'      => array( 'page', 'post', 'product', 'wp_template', 'wp_template_part', 'elementor_library' ),
				'post_status'    => array( 'publish', 'private', 'draft', 'pending', 'future' ),
				'posts_per_page' => 100,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'paged'          => $page++,
			)
		);
		foreach ( $posts as $post ) {
			$product = 'product' === $post->post_type && function_exists( 'wc_get_product' ) ? wc_get_product( $post->ID ) : false;
			$terms   = array();
			foreach ( get_object_taxonomies( $post->post_type ) as $taxonomy ) {
				$slugs = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'slugs' ) );
				if ( is_array( $slugs ) && $slugs ) {
					$terms[ $taxonomy ] = $slugs;
				}
			}
			if ( $product ) {
				$type           = $product->get_type();
				$types[ $type ] = 1 + ( $types[ $type ] ?? 0 );
			}
			$rows[] = array(
				'id'             => $post->ID,
				'type'           => $post->post_type,
				'status'         => $post->post_status,
				'title'          => wp_strip_all_tags( $post->post_title ),
				'url'            => 'publish' === $post->post_status && is_post_type_viewable( $post->post_type ) && ! $post->post_password ? get_permalink( $post ) : '',
				'content_sha256' => hash( 'sha256', $post->post_content ),
				'dependencies'   => zion_theme_migration_dependencies( $post ),
				'taxonomies'     => $terms,
				'seo'            => zion_theme_migration_seo( $post->ID ),
				'product'        => $product ? array(
					'type'         => $product->get_type(),
					'virtual'      => $product->is_virtual(),
					'downloadable' => $product->is_downloadable(),
				) : null,
			);
		}
		$batch_size = count( $posts );
	} while ( 100 === $batch_size );
	$sidebars = array();
	foreach ( wp_get_sidebars_widgets() as $sidebar => $instances ) {
		if ( 'wp_inactive_widgets' !== $sidebar && is_array( $instances ) ) {
			$sidebars[ $sidebar ] = array_values( array_filter( $instances, 'is_string' ) );
		}
	}

	return array(
		'generated_at'              => gmdate( 'c' ),
		'site'                      => home_url( '/' ),
		'theme'                     => array(
			'stylesheet' => get_stylesheet(),
			'template'   => get_template(),
			'version'    => wp_get_theme()->get( 'Version' ),
		),
		'reading'                   => array(
			'show_on_front'  => get_option( 'show_on_front' ),
			'page_on_front'  => (int) get_option( 'page_on_front' ),
			'page_for_posts' => (int) get_option( 'page_for_posts' ),
		),
		'permalinks'                => get_option( 'permalink_structure' ),
		'plugins'                   => $plugins,
		'sidebar_widgets'           => $sidebars,
		'xstore_sidebar_candidates' => array_values( array_unique( array_filter( array_merge( ...array_values( $sidebars ) ), 'zion_theme_migration_is_xstore' ) ) ),
		'commerce'                  => zion_theme_migration_commerce(),
		'product_types'             => $types,
		'content'                   => $rows,
	);
}
