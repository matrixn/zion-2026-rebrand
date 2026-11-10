<?php
/**
 * Storefront taxonomy and canonical storefront pages.
 *
 * @package Zion_Core
 */

/**
 * Return the supported shops and initial page content.
 *
 * @return array
 */
function zion_core_shops(): array {
	return array(
		'3d'       => array(
			'slug'        => 'magazin-3d',
			'title'       => __( 'Magazin 3D', 'zion-core' ),
			'description' => __( 'Piese funcționale, prototipuri și modele pentru printare.', 'zion-core' ),
		),
		'software' => array(
			'slug'        => 'magazin-software',
			'title'       => __( 'Magazin Software', 'zion-core' ),
			'description' => __( 'Pluginuri și instrumente pentru aplicații și magazine online.', 'zion-core' ),
		),
	);
}

/**
 * Register independent storefront classification for WooCommerce products.
 */
function zion_core_register_shops(): void {
	if ( ! post_type_exists( 'product' ) ) {
		return;
	}
	register_taxonomy(
		'zion_shop',
		'product',
		array(
			'labels'               => array(
				'name'          => __( 'Vitrine Zion', 'zion-core' ),
				'singular_name' => __( 'Vitrină Zion', 'zion-core' ),
			),
			'public'               => false,
			'show_ui'              => true,
			'show_in_menu'         => false,
			'show_in_rest'         => true,
			'show_in_quick_edit'   => false,
			'hierarchical'         => true,
			'capabilities'         => array(
				'manage_terms' => 'do_not_allow',
				'edit_terms'   => 'do_not_allow',
				'delete_terms' => 'do_not_allow',
				'assign_terms' => 'edit_products',
			),
			'meta_box_cb'          => 'zion_core_shop_meta_box',
			'meta_box_sanitize_cb' => 'taxonomy_meta_box_sanitize_cb_checkboxes',
		)
	);
}

/**
 * Render the native checkbox field with product-specific guidance.
 *
 * @param WP_Post $post Product being edited.
 * @param array   $box Native meta-box configuration.
 */
function zion_core_shop_meta_box( WP_Post $post, array $box ): void {
	echo '<p>' . esc_html__( 'Alege una sau ambele vitrine. Clasificarea este independentă de categorie, produs virtual și descărcări.', 'zion-core' ) . '</p>';
	post_categories_meta_box( $post, $box );
}

/**
 * Return term IDs for the canonical slugs, optionally creating missing terms.
 *
 * @param bool $create Create missing terms during setup.
 * @return array|WP_Error Map of slug to term ID.
 */
function zion_core_shop_term_ids( bool $create = false ): array|WP_Error {
	$ids = array();
	if ( ! taxonomy_exists( 'zion_shop' ) ) {
		return $ids;
	}
	foreach ( zion_core_shops() as $slug => $shop ) {
		$term = get_term_by( 'slug', $slug, 'zion_shop' );
		if ( $term ) {
			$ids[ $slug ] = $term->term_id;
		} elseif ( $create ) {
			$result = wp_insert_term( $shop['title'], 'zion_shop', array( 'slug' => $slug ) );
			if ( is_wp_error( $result ) ) {
				// A concurrent setup may have inserted the canonical term.
				$term = get_term_by( 'slug', $slug, 'zion_shop' );
				if ( ! $term ) {
					return $result;
				}
				$ids[ $slug ] = $term->term_id;
			} else {
				$ids[ $slug ] = $result['term_id'];
			}
		}
	}
	return $ids;
}

/**
 * Create canonical terms for first activation, upgrades and late Woo activation.
 */
function zion_core_install_shops(): void {
	if ( ! taxonomy_exists( 'zion_shop' ) ) {
		zion_core_register_shops();
	}
	zion_core_shop_term_ids( true );
}

/**
 * Provision the two editorial pages without overwriting existing content.
 *
 * @return array Map of slug to page ID or creation error.
 */
function zion_core_create_shop_pages(): array {
	$result = array();
	foreach ( zion_core_shops() as $slug => $shop ) {
		$page            = get_page_by_path( $shop['slug'], OBJECT, 'page' );
		$result[ $slug ] = $page ? $page->ID : wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => $shop['slug'],
				'post_title'   => $shop['title'],
				'post_content' => wp_slash(
					'<!-- wp:paragraph --><p>' . esc_html( $shop['description'] ) . '</p><!-- /wp:paragraph -->' .
					'<!-- wp:shortcode -->[zion_catalog shop="' . esc_attr( $slug ) . '"]<!-- /wp:shortcode -->'
				),
			),
			true
		);
	}
	return $result;
}

/**
 * Return the public storefront slug for the current page, if any.
 *
 * @return string
 */
function zion_core_current_shop(): string {
	if ( ! is_page() ) {
		return '';
	}
	$path = get_page_uri( get_queried_object_id() );
	foreach ( zion_core_shops() as $slug => $shop ) {
		if ( $shop['slug'] === $path ) {
			return $slug;
		}
	}
	return '';
}
