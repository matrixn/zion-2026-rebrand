<?php
/**
 * Render a dedicated Zion storefront using WooCommerce native blocks.
 *
 * @package Zion_Core
 */

/**
 * Native sorting keys mapped to Product Collection arguments.
 *
 * @return array
 */
function zion_core_storefront_sorting(): array {
	return array(
		'featured'   => array( __( 'Recomandate', 'zion-core' ), 'menu_order', 'asc' ),
		'date'       => array( __( 'Cele mai noi', 'zion-core' ), 'date', 'desc' ),
		'price'      => array( __( 'Preț crescător', 'zion-core' ), 'price', 'asc' ),
		'price-desc' => array( __( 'Preț descrescător', 'zion-core' ), 'price', 'desc' ),
		'popularity' => array( __( 'Popularitate', 'zion-core' ), 'sales', 'desc' ),
		'rating'     => array( __( 'Rating', 'zion-core' ), 'rating', 'desc' ),
		'menu_order' => array( __( 'Ordine manuală', 'zion-core' ), 'menu_order', 'asc' ),
	);
}

/**
 * Read a scalar display parameter from the public URL.
 *
 * @param string $key Query key.
 * @return string
 */
function zion_core_storefront_parameter( string $key ): string {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public display-only GET parameters.
	return isset( $_GET[ $key ] ) && is_string( $_GET[ $key ] ) ? sanitize_key( wp_unslash( $_GET[ $key ] ) ) : '';
}

/**
 * Detect the WooCommerce blocks available in the running installation.
 *
 * @return bool
 */
function zion_core_has_storefront_blocks(): bool {
	$blocks = WP_Block_Type_Registry::get_instance();
	return $blocks->is_registered( 'woocommerce/product-collection' ) && $blocks->is_registered( 'woocommerce/product-filters' );
}

/**
 * Redirect the phase 0.4 category URL to WooCommerce's native parameter.
 */
function zion_core_redirect_legacy_category(): void {
	if ( ! zion_core_current_shop() || ! zion_core_has_storefront_blocks() ) {
		return;
	}
	$id = zion_core_catalog_category();
	if ( ! $id ) {
		return;
	}
	$category = get_term( $id, 'product_cat' );
	if ( ! $category instanceof WP_Term ) {
		return;
	}

	$url = remove_query_arg( array( 'zion_category', 'product-page', 'zion_more' ) );
	if ( ! zion_core_storefront_parameter( 'categories' ) ) {
		$url = add_query_arg( 'categories', rawurlencode( $category->slug ), $url );
	}
	wp_safe_redirect( $url );
	exit;
}
add_action( 'template_redirect', 'zion_core_redirect_legacy_category' );

/**
 * Ensure that native block queries and facet calculations share a shop scope.
 *
 * @param array    $query Built query.
 * @param WP_Block $block Block with Product Collection context.
 * @return array
 */
function zion_core_storefront_query_scope( array $query, WP_Block $block ): array {
	$context = $block->context['query'] ?? array();
	$shop    = $context['zionShop'] ?? '';
	$scoped  = is_string( $shop ) && isset( zion_core_shops()[ $shop ] );
	if ( ( $scoped || ! empty( $context['zionLibrary'] ) ) && ! empty( $context['woocommerceOnSale'] ) && empty( $query['post__in'] ) ) {
		// An empty inclusion list normally means no restriction in WP_Query.
		$query['post__in'] = array( 0 );
	}
	if ( ! empty( $context['zionLibrary'] ) ) {
		$query['post_status']           = 'publish';
		$query['zion_storefront_query'] = 'library';
		if ( empty( $context['zionPaginate'] ) ) {
			$query['paged']         = 1;
			$query['no_found_rows'] = true;
		}
		if ( isset( $context['zionRelatedIds'] ) ) {
			$ids = is_array( $context['zionRelatedIds'] ) ? array_map( 'absint', array_filter( $context['zionRelatedIds'], 'is_numeric' ) ) : array();
			if ( ! empty( $query['post__in'] ) ) {
				$ids = array_intersect( $ids, $query['post__in'] );
			}
			$query['post__in'] = $ids ? array_values( $ids ) : array( 0 );
		}
	}
	if ( ! $scoped ) {
		return $query;
	}

	$query['zion_storefront_query'] = $shop;
	$query['zion_featured_first']   = $context['zionFeaturedFirst'] ?? false;
	// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- The private shop taxonomy is excluded by the core block's public-taxonomy picker.
	$query['tax_query'] = array(
		'relation' => 'AND',
		$query['tax_query'] ?? array(),
		array(
			'taxonomy'         => 'zion_shop',
			'field'            => 'slug',
			'terms'            => array( $shop ),
			'include_children' => false,
		),
	);
	return $query;
}
add_filter( 'query_loop_block_query_vars', 'zion_core_storefront_query_scope', 20, 2 );

/**
 * Make ties stable across page sizes and optionally put featured products first.
 *
 * @param array    $clauses SQL clauses.
 * @param WP_Query $query Active product query.
 * @return array
 */
function zion_core_storefront_order( array $clauses, WP_Query $query ): array {
	if ( ! $query->get( 'zion_storefront_query' ) || empty( $clauses['orderby'] ) ) {
		return $clauses;
	}
	global $wpdb;
	$clauses['orderby'] .= ", {$wpdb->posts}.ID DESC";
	if ( ! $query->get( 'zion_featured_first' ) ) {
		return $clauses;
	}
	$visibility = wc_get_product_visibility_term_ids();
	if ( empty( $visibility['featured'] ) ) {
		return $clauses;
	}
	$clauses['orderby'] = $wpdb->prepare(
		"EXISTS ( SELECT 1 FROM {$wpdb->term_relationships} AS zion_featured
			WHERE zion_featured.object_id = {$wpdb->posts}.ID AND zion_featured.term_taxonomy_id = %d ) DESC, ",
		$visibility['featured']
	) . $clauses['orderby'];
	return $clauses;
}
add_filter( 'posts_clauses', 'zion_core_storefront_order', 20, 2 );

/**
 * Build a block comment with optional inner markup.
 *
 * @param string      $name Registered block name.
 * @param array       $attributes Block attributes.
 * @param string|null $content Saved inner markup, or null for self-closing.
 * @return string
 */
function zion_core_storefront_block( string $name, array $attributes = array(), ?string $content = null ): string {
	$attrs = $attributes ? ' ' . serialize_block_attributes( $attributes ) : '';
	return null === $content ? "<!-- wp:{$name}{$attrs} /-->" : "<!-- wp:{$name}{$attrs} -->{$content}<!-- /wp:{$name} -->";
}

/**
 * Build a readable section heading for a native facet.
 *
 * @param string $title Section title.
 * @return string
 */
function zion_core_storefront_facet_heading( string $title ): string {
	return zion_core_storefront_block( 'heading', array( 'level' => 3 ), '<h3 class="wp-block-heading">' . esc_html( $title ) . '</h3>' );
}

/**
 * Render native facet blocks sharing the parent Product Collection query.
 *
 * @param array  $display Store presentation settings.
 * @param string $url Current storefront permalink.
 * @param string $page_key Native pagination parameter.
 * @param bool   $on_sale Whether the sale filter is active.
 * @return string
 */
function zion_core_storefront_facets( array $display, string $url, string $page_key, bool $on_sale ): string {
	if ( ! $display['filters'] ) {
		return '';
	}
	$facets  = '';
	$list    = zion_core_storefront_block( 'woocommerce/product-filter-checkbox-list', array(), '<div class="wc-block-product-filter-checkbox-list"></div>' );
	$options = array(
		'hideEmpty'  => (bool) $display['hide_empty'],
		'showCounts' => true,
	);

	if ( $display['active_filters'] ) {
		$facets .= zion_core_storefront_block(
			'woocommerce/product-filter-active',
			array(),
			zion_core_storefront_facet_heading( __( 'Filtre active', 'zion-core' ) ) .
			zion_core_storefront_block( 'woocommerce/product-filter-removable-chips', array(), '<div class="wc-block-product-filter-removable-chips"></div>' )
		);
	}
	if ( $display['price_filter'] ) {
		$facets .= zion_core_storefront_block(
			'woocommerce/product-filter-price',
			array(),
			zion_core_storefront_facet_heading( __( 'Preț', 'zion-core' ) ) .
			zion_core_storefront_block( 'woocommerce/product-filter-price-slider', array( 'showInputFields' => true ), '<div class="wc-block-product-filter-price-slider"></div>' )
		);
	}
	if ( $display['stock_filter'] ) {
		$facets .= zion_core_storefront_block( 'woocommerce/product-filter-status', $options, zion_core_storefront_facet_heading( __( 'Disponibilitate', 'zion-core' ) ) . $list );
	}
	foreach ( array(
		'product_cat'   => 'category_filter',
		'product_brand' => 'brand_filter',
	) as $taxonomy => $setting ) {
		$term_type = get_taxonomy( $taxonomy );
		if ( ! $display[ $setting ] || ! $term_type || ! $term_type->public || ! $term_type->show_ui ) {
			continue;
		}
		// An empty taxonomy has no hierarchy tree, even if WooCommerce still has cached facet counts.
		$term_count = wp_count_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $term_count ) || 0 === (int) $term_count ) {
			continue;
		}
		$facets .= zion_core_storefront_block(
			'woocommerce/product-filter-taxonomy',
			$options + array( 'taxonomy' => $taxonomy ),
			zion_core_storefront_facet_heading( $term_type->labels->name ) . $list
		);
	}
	$attribute_slugs = explode( ',', $display['attributes'] );
	$shown           = 0;
	foreach ( wc_get_attribute_taxonomies() as $attribute ) {
		if ( $shown >= 6 ) {
			break;
		}
		if ( 'auto' !== $display['attributes'] && ! in_array( $attribute->attribute_name, $attribute_slugs, true ) ) {
			continue;
		}
		$facets .= zion_core_storefront_block(
			'woocommerce/product-filter-attribute',
			$options + array( 'attributeId' => (int) $attribute->attribute_id ),
			zion_core_storefront_facet_heading( $attribute->attribute_label ) . $list
		);
		++$shown;
	}
	if ( $display['sale_filter'] ) {
		$target  = add_query_arg( 'on_sale', $on_sale ? '0' : '1', remove_query_arg( array( $page_key, 'product-page', 'zion_category', 'zion_more' ) ) );
		$facets .= zion_core_storefront_block(
			'html',
			array(),
			'<p class="zion-catalog-sale"><a href="' . esc_url( $target ) . '">' .
			esc_html( $on_sale ? __( 'Elimină filtrul Reduceri', 'zion-core' ) : __( 'Doar produse la reducere', 'zion-core' ) ) . '</a></p>'
		);
	}
	if ( $display['reset_filters'] ) {
		$reset_url = add_query_arg( 'orderby', zion_core_storefront_parameter( 'orderby' ), $url );
		$facets   .= zion_core_storefront_block(
			'html',
			array(),
			'<p><a class="zion-catalog-reset" href="' . esc_url( $reset_url ) . '">' . esc_html__( 'Resetează filtrele', 'zion-core' ) . '</a></p>'
		);
	}

	if ( '' === $facets ) {
		return '';
	}
	return zion_core_storefront_block( 'woocommerce/product-filters', array(), '<div class="wp-block-woocommerce-product-filters wc-block-product-filters">' . $facets . '</div>' );
}

/**
 * Render sort controls using standard WooCommerce URL keys and hidden-field handling.
 *
 * @param string $url Storefront permalink.
 * @param string $sort Effective sort.
 * @param string $page_key Native pagination query parameter.
 * @return string
 */
function zion_core_storefront_sort_form( string $url, string $sort, string $page_key ): string {
	ob_start();
	?>
	<form class="zion-catalog-sort woocommerce-ordering" method="get" action="<?php echo esc_url( $url ); ?>">
		<label for="zion-orderby"><?php esc_html_e( 'Sortează', 'zion-core' ); ?></label>
		<select id="zion-orderby" name="orderby">
			<?php foreach ( zion_core_storefront_sorting() as $key => $choice ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $sort, $key ); ?>><?php echo esc_html( $choice[0] ); ?></option>
			<?php endforeach; ?>
		</select>
		<noscript><button type="submit" class="wp-element-button"><?php esc_html_e( 'Aplică sortarea', 'zion-core' ); ?></button></noscript>
		<?php wc_query_string_form_fields( null, array( 'orderby', 'submit', $page_key, 'product-page', 'zion_category', 'zion_more' ) ); ?>
	</form>
	<?php
	return (string) ob_get_clean();
}

/**
 * Load AJAX controls for the native archive toolbar and Zion storefronts.
 */
function zion_core_enqueue_catalog_controls(): void {
	wp_enqueue_script_module(
		'zion-catalog',
		plugins_url( 'assets/catalog.js', dirname( __DIR__ ) . '/zion-core.php' ),
		array(
			'@wordpress/interactivity',
			array(
				'id'     => '@wordpress/interactivity-router',
				'import' => 'dynamic',
			),
		),
		'1.1.1-r4'
	);
}
add_filter(
	'render_block_woocommerce/catalog-sorting',
	static function ( string $html ): string {
		if ( $html && current_theme_supports( 'woocommerce' ) && wp_is_block_theme() ) {
			zion_core_enqueue_catalog_controls();
		}
		return $html;
	}
);

/**
 * Build and render one paginated native collection for a dedicated storefront.
 *
 * @param array $options Normalized shortcode arguments.
 * @param array $display Store presentation settings.
 * @return string
 */
function zion_core_render_storefront_collection( array $options, array $display ): string {
	$shop = $options['shop'];
	$term = get_term_by( 'slug', $shop, 'zion_shop' );
	if ( ! $term ) {
		return '';
	}
	$display = array_replace(
		array(
			'filters'         => true,
			'category_filter' => true,
			'price_filter'    => true,
			'stock_filter'    => true,
			'sale_filter'     => true,
			'brand_filter'    => true,
			'attributes'      => 'auto',
			'active_filters'  => true,
			'reset_filters'   => true,
			'hide_empty'      => true,
			'counts'          => true,
			'layout'          => 'grid',
			'desktop'         => $options['columns'],
			'tablet'          => 2,
			'mobile'          => 1,
			'page_size'       => $options['limit'],
			'load_more'       => true,
			'load_mode'       => 'auto',
			'width'           => 'content',
			'gap'             => 'regular',
			'filter_layout'   => 'sidebar',
			'sort'            => 'menu_order',
		),
		$display
	);

	$sort_options = zion_core_storefront_sorting();
	$sort         = zion_core_storefront_parameter( 'orderby' );
	if ( ! isset( $sort_options[ $sort ] ) ) {
		$sort = isset( $sort_options[ $display['sort'] ] ) ? $display['sort'] : 'menu_order';
	}
	$query_id   = '3d' === $shop ? 73 : 74;
	$page_key   = 'query-' . $query_id . '-page';
	$url        = get_permalink( get_queried_object_id() );
	$on_sale    = '1' === zion_core_storefront_parameter( 'on_sale' );
	$facets     = zion_core_storefront_facets( $display, $url, $page_key, $on_sale );
	$is_list    = 'list' === $display['layout'];
	$base_limit = max( 1, min( 48, (int) $display['page_size'] ) );
	$expanded   = $display['load_more'] && 'auto' === $display['load_mode'] ? zion_core_storefront_parameter( 'zion_more' ) : '';
	$limit      = ctype_digit( $expanded ) ? max( $base_limit, min( 96, (int) $expanded ) ) : $base_limit;
	$query      = array(
		'postType'                 => 'product',
		'perPage'                  => $limit,
		'orderBy'                  => $sort_options[ $sort ][1],
		'order'                    => $sort_options[ $sort ][2],
		'inherit'                  => false,
		'filterable'               => true,
		'isProductCollectionBlock' => true,
		'woocommerceOnSale'        => $on_sale,
		'zionShop'                 => $shop,
		'zionFeaturedFirst'        => 'featured' === $sort,
	);

	$card_blocks =
		zion_core_storefront_block(
			'woocommerce/product-image',
			array(
				'isDescendentOfQueryLoop' => true,
				'aspectRatio'             => '4/3',
			)
		) .
		zion_core_storefront_block(
			'post-title',
			array(
				'level'    => 2,
				'isLink'   => true,
				'fontSize' => 'card',
			)
		) .
		zion_core_storefront_block( 'woocommerce/product-price', array( 'isDescendentOfQueryLoop' => true ) ) .
		zion_core_storefront_block( 'woocommerce/product-button', array( 'isDescendentOfQueryLoop' => true ) );
	$cards       = zion_core_storefront_block(
		'woocommerce/product-template',
		array(),
		apply_filters( 'zion_core_product_card_blocks', $card_blocks, $shop )
	);
	$pagination  = zion_core_storefront_block(
		'query-pagination',
		array(
			'layout' => array(
				'type'           => 'flex',
				'justifyContent' => 'center',
			),
		),
		zion_core_storefront_block( 'query-pagination-previous', array( 'label' => __( 'Înapoi', 'zion-core' ) ) ) .
		zion_core_storefront_block( 'query-pagination-numbers' ) .
		zion_core_storefront_block( 'query-pagination-next', array( 'label' => __( 'Mai departe', 'zion-core' ) ) )
	);
	$empty       = zion_core_storefront_block(
		'woocommerce/product-collection-no-results',
		array(),
		zion_core_storefront_block( 'paragraph', array(), '<p>' . esc_html__( 'Nu există produse pentru selecția curentă.', 'zion-core' ) . '</p>' )
	);
	$classes     = array(
		'wp-block-woocommerce-product-collection',
		'zion-catalog-native',
		'zion-catalog-' . $shop,
		'zion-catalog-' . ( $is_list ? 'list' : 'grid' ),
		'zion-catalog-gap-' . sanitize_html_class( $display['gap'] ),
	);
	if ( 'wide' === $display['width'] ) {
		$classes[] = 'alignwide';
	}
	if ( $facets ) {
		$classes[] = 'zion-catalog-' . ( 'drawer' === $display['filter_layout'] ? 'drawer' : 'sidebar' );
	}
	$columns = max( 2, min( 5, (int) $display['desktop'] ) );
	$style   = sprintf( '--zion-desktop:%d;--zion-tablet:%d;--zion-mobile:%d', $columns, max( 1, min( 4, (int) $display['tablet'] ) ), max( 1, min( 2, (int) $display['mobile'] ) ) );
	$content = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" style="' . esc_attr( $style ) . '">' .
		'<div hidden data-wp-interactive="zion/catalog" data-wp-watch="callbacks.syncProgress"></div><div class="zion-catalog-shell">';
	if ( $facets ) {
		$heading_id = wp_unique_id( 'zion-catalog-filters-' );
		$content   .= '<aside class="zion-catalog-facets" aria-labelledby="' . esc_attr( $heading_id ) . '">' .
			'<h2 class="screen-reader-text" id="' . esc_attr( $heading_id ) . '">' . esc_html__( 'Filtre catalog', 'zion-core' ) . '</h2>' .
			$facets . '</aside>';
	}
	$content .= '<div class="zion-catalog-results"><div class="zion-catalog-toolbar">' .
		zion_core_storefront_sort_form( $url, $sort, $page_key ) . '<!-- zion-catalog-count --></div>' .
		$cards . '<!-- zion-catalog-more -->' . $pagination . $empty . '</div></div></div>';

	$results = null;
	$capture = static function ( array $posts, WP_Query $wp_query ) use ( &$results, $shop ): array {
		if ( $wp_query->get( 'zion_storefront_query' ) === $shop ) {
			$results = $wp_query;
		}
		return $posts;
	};
	add_filter( 'the_posts', $capture, 10, 2 );
	try {
		$html = do_blocks(
			zion_core_storefront_block(
				'woocommerce/product-collection',
				array(
					'queryId'       => $query_id,
					'query'         => $query,
					'displayLayout' => array(
						'type'    => $is_list ? 'list' : 'flex',
						'columns' => $columns,
					),
				),
				$content
			)
		);
	} finally {
		remove_filter( 'the_posts', $capture, 10 );
	}

	if ( $facets ) {
		// WooCommerce resets to the canonical URL when a facet changes. Preserve the independent controls.
		$selected_order = zion_core_storefront_parameter( 'orderby' );
		$carry          = isset( $sort_options[ $selected_order ] ) ? array( 'orderby' => $selected_order ) : array();
		if ( $on_sale ) {
			$carry['on_sale'] = '1';
		}
		wp_interactivity_config( 'woocommerce/product-filters', array( 'canonicalUrl' => add_query_arg( $carry, $url ) ) );
	}

	$total = $results ? (int) $results->found_posts : 0;
	$count = '';
	if ( $display['counts'] ) {
		/* translators: %s: total number of matching products. */
		$label = _n( '%s produs', '%s produse', $total, 'zion-core' );
		$count = '<p class="zion-catalog-count woocommerce-result-count" role="status">' .
			esc_html( sprintf( $label, number_format_i18n( $total ) ) ) . '</p>';
	}
	$more = '';
	if ( $display['load_more'] && 'auto' === $display['load_mode'] && $total > $limit && $limit < 96 && absint( zion_core_storefront_parameter( $page_key ) ) < 2 ) {
		$more_url = add_query_arg( 'zion_more', min( 96, $limit + $base_limit ), remove_query_arg( $page_key ) );
		$more     = '<div class="zion-catalog-sentinel" aria-hidden="true" data-zion-next="' . esc_url( $more_url ) . '"></div>';
	}
	zion_core_enqueue_catalog_controls();
	return str_replace( array( '<!-- zion-catalog-count -->', '<!-- zion-catalog-more -->' ), array( $count, $more ), $html );
}
