<?php
/**
 * Shared product card for Zion's WooCommerce collections.
 *
 * @package Zion_Theme
 */

/**
 * Register the card block and its Site Editor representation.
 */
function zion_theme_register_product_card(): void {
	$version = wp_get_theme()->get( 'Version' );
	wp_register_script( 'zion-card-editor', get_theme_file_uri( 'assets/blocks/product-card-editor.js' ), array( 'wp-blocks', 'wp-block-editor', 'wp-element' ), $version, true );
	register_block_type(
		'zion/product-card',
		array(
			'api_version'     => 3,
			'title'           => __( 'Card produs Zion', 'zion-theme' ),
			'category'        => 'widgets',
			'supports'        => array( 'interactivity' => array( 'clientNavigation' => true ) ),
			'uses_context'    => array( 'postId', 'postType' ),
			'attributes'      => array(
				'shop'       => array( 'type' => 'string' ),
				'level'      => array(
					'type'    => 'integer',
					'default' => 2,
				),
				'deferImage' => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
			'editor_script'   => 'zion-card-editor',
			'render_callback' => 'zion_theme_render_product_card',
		)
	);
}
add_action( 'init', 'zion_theme_register_product_card' );

/**
 * Load the dialog behavior only for enabled Quick View actions.
 */
function zion_theme_enqueue_quick_view(): void {
	wp_enqueue_script(
		'zion-card',
		get_theme_file_uri( 'assets/blocks/product-card.js' ),
		array(),
		wp_get_theme()->get( 'Version' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}

/**
 * Give Zion Core the shared card while retaining its standalone WooCommerce fallback.
 *
 * @param string $template Native card markup.
 * @param string $shop Catalog context.
 * @return string
 */
function zion_theme_catalog_product_card( string $template, string $shop ): string {
	if ( ! in_array( $shop, Zion_Theme_Settings::SHOPS, true ) ) {
		return $template;
	}
	if ( zion_theme_card_profile( $shop )['show']['quick_view'] ) {
		// The native filter router can add cards after an initially empty result.
		zion_theme_enqueue_quick_view();
	}
	return '<!-- wp:zion/product-card ' . serialize_block_attributes( array( 'shop' => $shop ) ) . ' /-->';
}
add_filter( 'zion_core_product_card_blocks', 'zion_theme_catalog_product_card', 10, 2 );

/**
 * Resolve one configuration for the collection, independent of individual product data.
 *
 * @param string $shop Collection context.
 * @return array
 */
function zion_theme_card_profile( string $shop ): array {
	static $profiles = array();
	if ( isset( $profiles[ $shop ] ) ) {
		return $profiles[ $shop ];
	}
	$preset = zion_theme_setting_with_source( 'card_preset', $shop );
	if ( 'default' === $preset['source'] && 'compact' === zion_theme_setting( 'catalog_density', $shop ) ) {
		$preset['value'] = 'compact';
	}
	$settings = array( 'preset' => $preset['value'] );
	foreach ( array( 'ratio', 'fit', 'color', 'buttons', 'badge_place', 'title_lines', 'order' ) as $key ) {
		$settings[ $key ] = zion_theme_setting( 'card_' . $key, $shop );
	}
	$design = Zion_Theme_Settings::card_designs()[ $settings['preset'] ] ?? null;
	if ( $design ) {
		foreach ( array(
			'ratio' => $design[3],
			'fit'   => 'contain',
		) as $key => $value ) {
			if ( 'default' === zion_theme_setting_with_source( 'card_' . $key, $shop )['source'] ) {
				$settings[ $key ] = $value;
			}
		}
	}
	$common = array( 'image', 'title', 'price', 'sale_price', 'placeholder', 'wishlist', 'compare' );
	$shown  = match ( $settings['preset'] ) {
		'minimal' => array(),
		'compact' => array( 'badge_sale' ),
		'premium' => array( 'badge_sale', 'badge_featured', 'badge_stock', 'category', 'brand', 'description', 'rating', 'reviews', 'availability', 'details', 'zoom', 'secondary' ),
		'horizontal' => array( 'badge_sale', 'badge_stock', 'description', 'availability', 'details' ),
		'atelier', 'editorial', 'studio-compact', 'focus' => array( 'badge_sale', 'category', 'description', 'availability', 'details' ),
		'technical' => array( 'badge_sale', 'category', 'specs', 'description', 'availability', 'details' ),
		default => array( 'badge_sale', 'badge_stock', 'category', 'description', 'availability', 'details' ),
	};
	$shown             = array_merge( $common, $shown );
	$keys              = array_keys( Zion_Theme_Settings::card_elements() );
	$settings['order'] = array_unique( array_merge( explode( ',', $settings['order'] ), $keys ) );
	foreach ( array_merge( $keys, array( 'image', 'sale_price', 'placeholder', 'badge_sale', 'badge_featured', 'badge_stock', 'zoom', 'secondary' ) ) as $key ) {
		if ( 'cart' === $key ) {
			$settings['show'][ $key ] = zion_theme_setting( 'catalog_actions', $shop );
			continue;
		}
		$value                    = zion_theme_setting( 'card_show_' . $key, $shop );
		$settings['show'][ $key ] = 'preset' === $value ? in_array( $key, $shown, true ) : 'show' === $value;
	}
	$profiles[ $shop ] = $settings;
	return $settings;
}

/**
 * Render the image and overlaid product badges.
 *
 * @param WC_Product $product Native WooCommerce product.
 * @param array      $show Visibility controls.
 * @param string     $url Product URL.
 * @param bool       $defer Whether the image sits below the fold.
 * @param string     $stock Optional escaped stock markup.
 * @return string
 */
function zion_theme_card_image( WC_Product $product, array $show, string $url, bool $defer = false, string $stock = '' ): string {
	$image_id = $product->get_image_id();
	if ( ! $show['image'] || ( ! $image_id && ! $show['placeholder'] ) ) {
		return '';
	}
	// Use an uncropped size so the configured fit controls the full photograph.
	$size  = 'medium_large';
	$image = $product->get_image( $size, $defer ? array( 'loading' => 'lazy' ) : array(), $show['placeholder'] );
	if ( '' === $image ) {
		return '';
	}
	$secondary = '';
	if ( $show['secondary'] ) {
		$gallery = array_diff( $product->get_gallery_image_ids(), array( $image_id ) );
		if ( $gallery ) {
			$secondary = wp_get_attachment_image(
				reset( $gallery ),
				$size,
				false,
				array(
					'class'       => 'zion-product-card__secondary',
					'loading'     => 'lazy',
					'alt'         => '',
					'aria-hidden' => 'true',
				)
			);
		}
	}
	$badges     = array_filter(
		array(
			$show['badge_sale'] && $product->is_on_sale() ? __( 'Reducere', 'zion-theme' ) : '',
			$show['badge_featured'] && $product->is_featured() ? __( 'Recomandat', 'zion-theme' ) : '',
			$show['badge_stock'] && ! $product->is_in_stock() ? __( 'Stoc epuizat', 'zion-theme' ) : '',
		)
	);
	$badge_html = '';
	foreach ( $badges as $badge ) {
		$badge_html .= '<span>' . esc_html( $badge ) . '</span>';
	}
	return '<div class="zion-product-card__media"><a class="zion-product-card__image" href="' . esc_url( $url ) . '" aria-label="' . esc_attr( $product->get_name() ) . '">' .
		$image . $secondary . '</a>' . ( $badge_html ? '<div class="zion-product-card__badges">' . $badge_html . '</div>' : '' ) .
		( $stock ? '<div class="zion-card-stock-overlay">' . $stock . '</div>' : '' ) . '</div>';
}

/**
 * Read price presentation from WooCommerce, including regular variation ranges.
 *
 * @param WC_Product $product Native product.
 * @param bool       $sale Show promotional prices.
 * @return string
 */
function zion_theme_card_price( WC_Product $product, bool $sale ): string {
	if ( $sale || ! $product->is_on_sale() ) {
		return $product->get_price_html();
	}
	if ( $product instanceof WC_Product_Variable ) {
		$min = $product->get_variation_regular_price( 'min', true );
		$max = $product->get_variation_regular_price( 'max', true );
		return ( $min === $max ? wc_price( $min ) : wc_format_price_range( $min, $max ) ) . $product->get_price_suffix();
	}
	if ( $product instanceof WC_Product_Grouped ) {
		$prices = array();
		foreach ( $product->get_visible_children() as $child ) {
			if ( $child instanceof WC_Product_Variable ) {
				$prices[] = $child->get_variation_regular_price( 'min', true );
			} elseif ( '' !== $child->get_regular_price() ) {
				$prices[] = wc_get_price_to_display( $child, array( 'price' => $child->get_regular_price() ) );
			} elseif ( '' !== $child->get_price() ) {
				$prices[] = wc_get_price_to_display( $child );
			}
		}
		return $prices ? ( min( $prices ) === max( $prices ) ? wc_price( min( $prices ) ) : wc_format_price_range( min( $prices ), max( $prices ) ) ) . $product->get_price_suffix() : '';
	}
	return wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) ) . $product->get_price_suffix();
}

/**
 * Keep WooCommerce's native block behavior for each product type.
 *
 * @param WC_Product $product Native product.
 * @return string
 */
function zion_theme_card_cart( WC_Product $product ): string {
	$parsed = parse_blocks( '<!-- wp:woocommerce/product-button {"isDescendentOfQueryLoop":true} /-->' )[0];
	$button = new WP_Block(
		$parsed,
		array(
			'postId'   => $product->get_id(),
			'postType' => 'product',
		)
	);
	return $button->render();
}

/**
 * Connect optional extensions using their own registered shortcode output.
 *
 * @param string     $action Wishlist or Compare.
 * @param WC_Product $product Native product.
 * @return string
 */
function zion_theme_card_extension( string $action, WC_Product $product ): string {
	list( $tag, $attribute ) = 'wishlist' === $action
		? array( 'yith_wcwl_add_to_wishlist', 'product_id' )
		: array( 'yith_compare_button', 'product' );
	$html                    = shortcode_exists( $tag ) ? do_shortcode( sprintf( '[%s %s="%d"]', $tag, $attribute, $product->get_id() ) ) : '';
	return (string) apply_filters( 'zion_theme_card_' . $action . '_html', $html, $product );
}

/**
 * Build the quick view as an accessible native dialog with a product link fallback.
 *
 * @param WC_Product $product Native product.
 * @param string     $url Product URL.
 * @param array      $show Visibility settings.
 * @return string
 */
function zion_theme_card_quick_view( WC_Product $product, string $url, array $show ): string {
	zion_theme_enqueue_quick_view();
	$name = $product->get_name();
	return '<a class="wp-element-button" data-zion-quick-view href="' . esc_url( $url ) . '" aria-haspopup="dialog">' . esc_html__( 'Quick View', 'zion-theme' ) . '</a>' .
		'<dialog class="zion-product-card__dialog" aria-label="' . esc_attr( $name ) . '">' .
		'<form method="dialog"><button class="wp-element-button" type="submit" autofocus>' . esc_html__( 'Închide', 'zion-theme' ) . '</button></form>' .
		'<h2>' . esc_html( $name ) . '</h2>' .
		( $show['price'] ? '<p>' . wp_kses_post( zion_theme_card_price( $product, $show['sale_price'] ) ) . '</p>' : '' ) .
		wp_kses_post( wpautop( $product->get_short_description() ) ) .
		'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Vezi detalii', 'zion-theme' ) . '</a></dialog>';
}

/**
 * Link the native WooCommerce Brands taxonomy when available.
 *
 * @param int $id Product ID.
 * @return string
 */
function zion_theme_card_brand( int $id ): string {
	$links = taxonomy_exists( 'product_brand' ) ? get_the_term_list( $id, 'product_brand', '', ', ' ) : false;
	return is_string( $links ) ? wp_kses_post( $links ) : '';
}

/**
 * Render one enabled information field or action.
 *
 * @param string     $key Ordered element key.
 * @param WC_Product $product Native product.
 * @param string     $url Product URL.
 * @param array      $show Visibility settings.
 * @param int        $level Heading level.
 * @return string
 */
function zion_theme_card_element( string $key, WC_Product $product, string $url, array $show, int $level ): string {
	$id = $product->get_id();
	return match ( $key ) {
		'category' => wp_kses_post( wc_get_product_category_list( $id, ', ' ) ),
		'brand' => zion_theme_card_brand( $id ),
		'title' => sprintf( '<h%1$d><a href="%2$s">%3$s</a></h%1$d>', $level, esc_url( $url ), esc_html( $product->get_name() ) ),
		'sku' => $product->get_sku() ? esc_html( 'SKU: ' . $product->get_sku() ) : '',
		'price' => wp_kses_post( zion_theme_card_price( $product, $show['sale_price'] ) ),
		'rating' => wp_kses_post( wc_get_rating_html( $product->get_average_rating() ) ),
		/* translators: %s: review count. */
		'reviews' => esc_html( sprintf( _n( '%s recenzie', '%s recenzii', $product->get_review_count(), 'zion-theme' ), number_format_i18n( $product->get_review_count() ) ) ),
		'description' => esc_html( wp_trim_words( wp_strip_all_tags( strip_shortcodes( $product->get_short_description() ? $product->get_short_description() : $product->get_description() ) ), 24 ) ),
		'availability' => '<span class="zion-stock-pill zion-stock-' . ( ! $product->is_in_stock() ? 'out' : ( $product->is_on_backorder() ? 'backorder' : 'in' ) ) . '">' .
			( $product->is_in_stock() && ! $product->is_on_backorder() ? esc_html__( 'În stoc', 'zion-theme' ) : wp_kses_post( wc_get_stock_html( $product ) ) ) . '</span>',
		'specs' => zion_theme_card_specs( $product ),
		'cart' => zion_theme_card_cart( $product ),
		'quick_view' => zion_theme_card_quick_view( $product, $url, $show ),
		'wishlist', 'compare' => zion_theme_card_extension( $key, $product ),
		'details' => '<a class="wp-element-button" href="' . esc_url( $url ) . '">' . esc_html__( 'Vezi detalii', 'zion-theme' ) . '</a>',
		default => '',
	};
}

/**
 * Show up to two existing, public WooCommerce attributes.
 *
 * @param WC_Product $product Native product.
 * @return string
 */
function zion_theme_card_specs( WC_Product $product ): string {
	$specs = array();
	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! $attribute->get_visible() ) {
			continue;
		}
		$values = $attribute->is_taxonomy() ? wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) ) : $attribute->get_options();
		if ( is_wp_error( $values ) || ! $values ) {
			continue;
		}
		$specs[] = '<span>' . esc_html( wc_attribute_label( $attribute->get_name() ) . ': ' . implode( ', ', $values ) ) . '</span>';
		if ( 2 === count( $specs ) ) {
			break;
		}
	}
	return implode( '', $specs );
}

/**
 * Render the card inside an existing product query.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content Saved block content.
 * @param WP_Block $block Block context.
 * @return string
 */
function zion_theme_render_product_card( array $attributes, string $content, WP_Block $block ): string {
	$id = $block->context['postId'] ?? 0;
	if ( ! $id || ! function_exists( 'wc_get_product' ) ) {
		return '';
	}
	$product = wc_get_product( $id );
	if ( ! $product ) {
		return '';
	}
	$shop = $attributes['shop'] ?? '';
	if ( ! in_array( $shop, Zion_Theme_Settings::SHOPS, true ) ) {
		$context_id = is_singular( Zion_Theme_Settings::ITEM_TYPES ) ? get_queried_object_id() : 0;
		$shop       = $context_id ? zion_theme_item_shop( $context_id, (string) get_post_type( $context_id ) ) : '';
	}
	$profile = zion_theme_card_profile( $shop );
	$show    = $profile['show'];
	$level   = 3 === ( $attributes['level'] ?? 2 ) ? 3 : 2;
	$url     = $product->get_permalink();
	$design  = isset( Zion_Theme_Settings::card_designs()[ $profile['preset'] ] );
	$stock   = $design && $show['availability'] && in_array( $profile['preset'], array( 'editorial', 'technical' ), true )
		? zion_theme_card_element( 'availability', $product, $url, $show, $level ) : '';
	$image   = zion_theme_card_image( $product, $show, $url, ! empty( $attributes['deferImage'] ), $stock );
	$overlay = $stock && $image;
	$items   = '';
	$groups  = array();
	foreach ( $profile['order'] as $key ) {
		if ( $show[ $key ] && ! ( $overlay && 'availability' === $key ) ) {
			$item = zion_theme_card_element( $key, $product, $url, $show, $level );
			if ( '' !== $item ) {
				$group = $design ? match ( $key ) {
					'cart', 'quick_view', 'wishlist', 'compare', 'details' => 'actions',
					'price', 'availability' => 'commerce',
					default => $key,
				} : $key;
				$last = array_key_last( $groups );
				if ( null === $last || $groups[ $last ]['key'] !== $group ) {
					$groups[] = array(
						'key'  => $group,
						'html' => '',
					);
					$last     = array_key_last( $groups );
				}
				$groups[ $last ]['html'] .= '<div class="zion-product-card__' . esc_attr( $key ) . '">' . $item . '</div>';
			}
		}
	}
	foreach ( $groups as $group ) {
		$items .= $design && in_array( $group['key'], array( 'actions', 'commerce' ), true )
			? '<div class="zion-card-' . esc_attr( $group['key'] ) . '">' . $group['html'] . '</div>'
			: $group['html'];
	}
	$classes = array( 'zion-product-card', 'zion-product-card--' . $profile['preset'], 'zion-card-color-' . $profile['color'], 'zion-card-buttons-' . $profile['buttons'], 'zion-card-badges-' . $profile['badge_place'] );
	if ( $design ) {
		$classes[] = 'zion-card-design';
	}
	if ( $show['zoom'] ) {
		$classes[] = 'zion-card-zoom';
	}
	if ( $image ) {
		$classes[] = 'zion-card-has-image';
	}
	$style = sprintf( '--zion-card-ratio:%s;--zion-card-fit:%s;--zion-card-lines:%d', $profile['ratio'], $profile['fit'], $profile['title_lines'] );
	return '<article class="' . esc_attr( implode( ' ', $classes ) ) . '" style="' . esc_attr( $style ) . '">' .
		$image . '<div class="zion-product-card__body">' . $items . '</div></article>';
}
