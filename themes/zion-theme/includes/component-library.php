<?php
/**
 * Gutenberg blocks and shortcode entry points for the Zion component library.
 *
 * @package Zion_Theme
 */

/**
 * Map both entry points to the same render modes.
 *
 * @return array
 */
function zion_theme_library_blocks(): array {
	return array(
		'product-grid'      => array( 'Product Grid', 'products', 'zion_products' ),
		'featured-products' => array( 'Featured Products', 'featured', 'zion_featured_products' ),
		'products-by-shop'  => array( 'Products by Shop', 'shop', 'zion_products_by_shop' ),
		'new-arrivals'      => array( 'New Arrivals', 'new', 'zion_new_products' ),
		'on-sale'           => array( 'On Sale', 'sale', 'zion_on_sale' ),
		'related-products'  => array( 'Related Products', 'related', 'zion_related_products' ),
		'services-grid'     => array( 'Services Grid', 'services', 'zion_services' ),
		'cta'               => array( 'CTA', 'cta', 'zion_cta' ),
		'testimonials'      => array( 'Testimonials', 'testimonials', 'zion_testimonials' ),
	);
}

/**
 * Register editor controls and conditionally used styles.
 */
function zion_theme_register_library(): void {
	$version = wp_get_theme()->get( 'Version' );
	wp_register_style( 'zion-collections', get_theme_file_uri( 'assets/dist/collections.css' ), array(), $version );
	wp_register_style( 'zion-library-content', get_theme_file_uri( 'assets/dist/library-content.css' ), array(), $version );
	wp_register_script( 'zion-library-editor', get_theme_file_uri( 'assets/blocks/library-editor.js' ), array( 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element' ), $version, true );
	$editor = array();
	foreach ( zion_theme_library_blocks() as $slug => list( $title, $mode, $shortcode ) ) {
		$attributes = match ( $mode ) {
			'services' => array(),
			'cta' => array(
				'title' => array( 'type' => 'string' ),
				'text'  => array( 'type' => 'string' ),
				'label' => array( 'type' => 'string' ),
				'url'   => array( 'type' => 'string' ),
			),
			'testimonials' => array(
				'quote'  => array( 'type' => 'string' ),
				'author' => array( 'type' => 'string' ),
				'limit'  => array( 'type' => 'integer' ),
			),
			default => array(
				'shop'      => array( 'type' => 'string' ),
				'limit'     => array( 'type' => 'integer' ),
				'columns'   => array( 'type' => 'integer' ),
				'paginate'  => array( 'type' => 'boolean' ),
				'productId' => array( 'type' => 'integer' ),
			),
		};
		$editor[ $slug ] = array(
			'title'      => 'Zion ' . $title,
			'mode'       => $mode,
			'attributes' => (object) $attributes,
		);
		register_block_type(
			'zion/' . $slug,
			array(
				'api_version'     => 3,
				'title'           => 'Zion ' . $title,
				'category'        => 'widgets',
				'uses_context'    => array( 'postId' ),
				'attributes'      => $attributes,
				'editor_script'   => 'zion-library-editor',
				'render_callback' => static fn ( array $args, string $content, WP_Block $block ): string => zion_theme_render_library( $mode, $args, (int) ( $block->context['postId'] ?? get_the_ID() ) ),
			)
		);
		add_shortcode( $shortcode, static fn ( array|string $args ): string => zion_theme_render_library( $mode, is_array( $args ) ? $args : array(), (int) get_the_ID() ) );
	}
	wp_add_inline_script( 'zion-library-editor', 'window.zionLibraryBlocks = ' . wp_json_encode( $editor ) . ';', 'before' );
}
add_action( 'init', 'zion_theme_register_library' );

/**
 * Render a library component, regardless of whether it came from a block or shortcode.
 *
 * @param string $mode Registered mode.
 * @param array  $args Component attributes.
 * @param int    $id Context post ID.
 * @return string
 */
function zion_theme_render_library( string $mode, array $args, int $id ): string {
	switch ( $mode ) {
		case 'services':
			$html = zion_theme_services_grid();
			break;
		case 'cta':
			$html = zion_theme_library_cta( $args );
			break;
		case 'testimonials':
			$html = zion_theme_library_testimonials( $args );
			break;
		default:
			return zion_theme_library_products( $mode, $args, $id );
	}
	if ( $html ) {
		wp_enqueue_style( 'zion-library-content' );
	}
	return $html;
}

/**
 * Render WooCommerce's Product Collection and its native pagination.
 *
 * @param string $mode Collection mode.
 * @param array  $args Block or shortcode attributes.
 * @param int    $id Context post ID.
 * @return string
 */
function zion_theme_library_products( string $mode, array $args, int $id ): string {
	if ( ! function_exists( 'zion_core_collection_options' ) || ! zion_core_has_storefront_blocks() ) {
		return '';
	}
	if ( 'shop' === $mode && ! isset( $args['shop'] ) ) {
		$args['shop'] = '3d';
	}
	$options = zion_core_collection_options( $args );
	if ( ! $options || ( 'shop' === $mode && ! $options['shop'] ) ) {
		return '';
	}
	$reference = $args['productId'] ?? $args['product_id'] ?? null;
	if ( 'related' === $mode && ( is_int( $reference ) || ( is_string( $reference ) && ctype_digit( $reference ) ) ) ) {
		$id = max( 0, (int) $reference );
	}
	$query = zion_core_collection_query( $mode, $options, $id );
	if ( ! $query ) {
		return '';
	}
	static $query_id = 100;
	$card            = array(
		'level'      => 3,
		'deferImage' => true,
	);
	if ( $options['shop'] ) {
		$card['shop'] = $options['shop'];
	}
	$template   = zion_core_storefront_block( 'woocommerce/product-template', array(), zion_core_storefront_block( 'zion/product-card', $card ) );
	$pagination = $query['zionPaginate'] ? zion_core_storefront_block(
		'query-pagination',
		array(),
		zion_core_storefront_block( 'query-pagination-previous' ) . zion_core_storefront_block( 'query-pagination-numbers' ) . zion_core_storefront_block( 'query-pagination-next' )
	) : '';
	$classes    = 'wp-block-woocommerce-product-collection zion-library-collection zion-library-collection--' . sanitize_html_class( $mode ) . ' zion-library-columns-' . $options['columns'];
	$html       = do_blocks(
		zion_core_storefront_block(
			'woocommerce/product-collection',
			array(
				'queryId'         => ++$query_id,
				'query'           => $query,
				'forcePageReload' => true,
				'displayLayout'   => array(
					'type'    => 'flex',
					'columns' => $options['columns'],
				),
			),
			'<div class="' . esc_attr( $classes ) . '">' . $template . $pagination . '</div>'
		)
	);
	if ( ! str_contains( $html, 'class="zion-product-card ' ) ) {
		return '<p class="zion-library-empty">' . esc_html__( 'Nu sunt produse disponibile în această colecție.', 'zion-theme' ) . '</p>';
	}
	wp_enqueue_style( 'zion-collections' );
	return $html;
}

/**
 * Read a bounded text attribute.
 *
 * @param mixed  $value Submitted value.
 * @param int    $length Character limit.
 * @param string $fallback Fallback text.
 * @return string
 */
function zion_theme_library_text( mixed $value, int $length, string $fallback = '' ): string {
	return is_string( $value ) && '' !== trim( $value ) ? wp_html_excerpt( sanitize_text_field( $value ), $length, '' ) : $fallback;
}

/**
 * Render the shared CTA.
 *
 * @param array $args Block or shortcode attributes.
 * @return string
 */
function zion_theme_library_cta( array $args ): string {
	$title = zion_theme_library_text( $args['title'] ?? null, 90, __( 'Ai un proiect în minte?', 'zion-theme' ) );
	$text  = zion_theme_library_text( $args['text'] ?? null, 250, __( 'Alege direcția potrivită pentru următoarea idee.', 'zion-theme' ) );
	$label = zion_theme_library_text( $args['label'] ?? null, 65, __( 'Explorează serviciile', 'zion-theme' ) );
	$url   = isset( $args['url'] ) && is_string( $args['url'] ) ? sanitize_url( $args['url'], array( 'http', 'https' ) ) : '';
	if ( '' === $url ) {
		$url = home_url( '/#servicii' );
	}
	return '<div class="zion-library-cta"><div><h2 class="zion-section-title">' . esc_html( $title ) . '</h2><p>' . esc_html( $text ) . '</p></div><a class="wp-element-button" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></div>';
}

/**
 * Use explicit editorial quotes or approved reviews of visible products.
 *
 * @param array $args Block or shortcode attributes.
 * @return string
 */
function zion_theme_library_testimonials( array $args ): string {
	$quote  = zion_theme_library_text( $args['quote'] ?? null, 380 );
	$author = zion_theme_library_text( $args['author'] ?? null, 90 );
	$items  = array();
	if ( $quote ) {
		$items[] = array( $quote, $author );
	} elseif ( function_exists( 'wc_reviews_enabled' ) && wc_reviews_enabled() ) {
		$limit   = function_exists( 'zion_core_collection_integer' ) ? zion_core_collection_integer( $args['limit'] ?? null, 3, 6 ) : 3;
		$reviews = get_comments(
			array(
				'status'      => 'approve',
				'type'        => 'review',
				'post_type'   => 'product',
				'post_status' => 'publish',
				'number'      => $limit * 2,
			)
		);
		foreach ( $reviews as $review ) {
			$product = wc_get_product( $review->comment_post_ID );
			if ( $product && $product->is_visible() ) {
				$items[] = array( zion_theme_library_text( $review->comment_content, 380 ), zion_theme_library_text( $review->comment_author, 90 ) );
				if ( count( $items ) >= $limit ) {
					break;
				}
			}
		}
	}
	$html = '';
	foreach ( $items as list( $text, $name ) ) {
		if ( $text ) {
			$html .= '<figure class="zion-library-quote"><blockquote><p>' . esc_html( $text ) . '</p></blockquote>' .
				( $name ? '<figcaption>' . esc_html( $name ) . '</figcaption>' : '' ) . '</figure>';
		}
	}
	return $html ? '<div class="zion-library-testimonials">' . $html . '</div>' : '';
}
