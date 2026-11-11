<?php
/**
 * Composable single-product layouts using WooCommerce data and forms.
 *
 * @package Zion_Theme
 */

/**
 * Register the configurable single product block.
 */
function zion_theme_register_product_page(): void {
	wp_register_script( 'zion-product-page-editor', get_theme_file_uri( 'assets/blocks/product-page-editor.js' ), array( 'wp-blocks', 'wp-block-editor', 'wp-element' ), wp_get_theme()->get( 'Version' ), true );
	register_block_type(
		'zion/product-page',
		array(
			'api_version'     => 3,
			'title'           => __( 'Pagină produs Zion', 'zion-theme' ),
			'category'        => 'widgets',
			'supports'        => array( 'multiple' => false ),
			'uses_context'    => array( 'postId', 'postType' ),
			'editor_script'   => 'zion-product-page-editor',
			'render_callback' => 'zion_theme_render_product_page',
		)
	);
}
add_action( 'init', 'zion_theme_register_product_page' );

/**
 * Resolve a page against the parent product, including during form submission.
 *
 * @param WC_Product $item Current product.
 * @return array
 */
function zion_theme_product_profile( WC_Product $item ): array {
	$id      = $item->get_id();
	$shop    = zion_theme_item_shop( $id, 'product' );
	$config  = zion_theme_settings_config();
	$resolve = static fn ( string $key ) => Zion_Theme_Settings::resolve( $config, $key, $shop, 'product', $id )['value'];
	$preset  = $resolve( 'product_preset' );
	if ( 'auto' === $preset ) {
		$preset = 'software' === $shop || ( '' === $shop && $item->is_virtual() ) ? 'software' : 'physical';
	}
	$common = array( 'title', 'gallery', 'lightbox', 'price', 'short_description', 'purchase', 'availability', 'attributes', 'variations', 'quantity' );
	$extra  = match ( $preset ) {
		'minimal' => array(),
		'software' => array( 'breadcrumb', 'description', 'specifications', 'compatibility', 'benefits', 'files', 'version', 'changelog', 'documentation', 'faq', 'reviews', 'upsells', 'cross_sells' ),
		'premium' => array( 'breadcrumb', 'zoom', 'description', 'specifications', 'compatibility', 'benefits', 'files', 'version', 'changelog', 'documentation', 'faq', 'reviews', 'upsells', 'cross_sells' ),
		default => array( 'breadcrumb', 'zoom', 'description', 'specifications', 'compatibility', 'benefits', 'files', 'documentation', 'faq', 'reviews', 'upsells', 'cross_sells' ),
	};
	$sections = array_keys( Zion_Theme_Settings::product_sections() );
	$summary  = array_keys( Zion_Theme_Settings::product_summary() );
	$defaults = array_merge( $common, $extra );
	$design   = $resolve( 'product_design' );
	if ( 'editorial' === $design ) {
		$defaults[] = 'sticky';
	}
	$show = array(
		'hero'    => true,
		'meta'    => $resolve( 'product_meta' ),
		'related' => $resolve( 'product_related' ),
	);
	foreach ( array_diff( array_merge( $sections, $summary, array( 'gallery', 'zoom', 'lightbox', 'variations', 'quantity', 'buy_now', 'sticky' ) ), array_keys( $show ) ) as $key ) {
		$value        = $resolve( 'product_show_' . $key );
		$show[ $key ] = 'preset' === $value ? in_array( $key, $defaults, true ) : 'show' === $value;
	}
	$style = $resolve( 'product_style' );
	if ( 'preset' === $style ) {
		$style = match ( $preset ) {
			'minimal' => 'plain',
			'premium' => 'accent',
			default => 'panel',
		};
	}
	return array(
		'design'   => $design,
		'shop'     => $shop,
		'preset'   => $preset,
		'style'    => $style,
		'side'     => $resolve( 'product_gallery_side' ),
		'sections' => array_unique( array_merge( explode( ',', $resolve( 'product_section_order' ) ), $sections ) ),
		'summary'  => array_unique( array_merge( explode( ',', $resolve( 'product_summary_order' ) ), $summary ) ),
		'accents'  => explode( ',', $resolve( 'product_accent_sections' ) ),
		'show'     => $show,
	);
}

/**
 * Render a registered native block with product context.
 *
 * @param string     $name Native block name.
 * @param WC_Product $item Current product.
 * @return string
 */
function zion_theme_product_native_block( string $name, WC_Product $item ): string {
	$parsed = parse_blocks( '<!-- wp:' . $name . ' /-->' )[0];
	$block  = new WP_Block(
		$parsed,
		array(
			'postId'   => $item->get_id(),
			'postType' => 'product',
		)
	);
	return $block->render();
}

/**
 * A direct purchase uses the native single product form.
 *
 * @param WC_Product $item Product being submitted.
 * @param array      $show Page visibility settings.
 * @return bool
 */
function zion_theme_can_direct_purchase( WC_Product $item, array $show ): bool {
	return $show['purchase'] && $item->is_in_stock() && $item->is_purchasable() &&
		( $item->is_type( 'simple' ) || ( $item->is_type( 'variable' ) && $show['variations'] ) );
}

/**
 * Redirect a successful Buy Now add through WooCommerce's own handler.
 *
 * @param string|false    $url Existing destination.
 * @param WC_Product|null $item Product WooCommerce added.
 * @return string|false
 */
function zion_theme_buy_now_redirect( $url, $item ) {
	// phpcs:ignore WordPress.Security.NonceVerification -- WooCommerce already accepted this public cart form; only the destination changes.
	$buy_now = isset( $_GET['zion_buy_now'], $_POST['add-to-cart'] ) && '1' === $_GET['zion_buy_now'];
	if ( $buy_now && $item instanceof WC_Product ) {
		$show = zion_theme_product_profile( $item )['show'];
		if ( $show['buy_now'] && zion_theme_can_direct_purchase( $item, $show ) ) {
			return wc_get_checkout_url();
		}
	}
	return $url;
}
add_filter( 'woocommerce_add_to_cart_redirect', 'zion_theme_buy_now_redirect', 20, 2 );

/**
 * Render the single native form and its optional secondary submit button.
 *
 * @param WC_Product $item Product.
 * @param array      $show Visibility settings.
 * @return string
 */
function zion_theme_product_purchase( WC_Product $item, array $show ): string {
	if ( $item->is_type( 'variable' ) && ! $show['variations'] ) {
		return '';
	}
	$buy_now      = static function () use ( $item ): void {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Reuse the WooCommerce form action for its alternate submitter.
		$action = apply_filters( 'woocommerce_add_to_cart_form_action', $item->get_permalink() );
		printf(
			'<button class="wp-element-button zion-buy-now" type="submit" name="add-to-cart" value="%1$d" formaction="%2$s"%3$s>%4$s</button>',
			(int) $item->get_id(),
			esc_url( add_query_arg( 'zion_buy_now', '1', $action ) ),
			$item->is_type( 'variable' ) ? ' disabled' : '',
			esc_html__( 'Cumpără acum', 'zion-theme' )
		);
	};
	$with_buy_now = $show['buy_now'] && zion_theme_can_direct_purchase( $item, $show );
	if ( $with_buy_now ) {
		add_action( 'woocommerce_after_add_to_cart_button', $buy_now );
	}
	try {
		$form = zion_theme_product_native_block( 'woocommerce/add-to-cart-form', $item );
	} finally {
		remove_action( 'woocommerce_after_add_to_cart_button', $buy_now );
	}
	$tags = new WP_HTML_Tag_Processor( $form );
	if ( ! $tags->next_tag( 'FORM' ) ) {
		return '';
	}
	$ajax = $item->is_type( array( 'simple', 'variable', 'grouped' ) );
	if ( $ajax ) {
		wp_enqueue_script( 'zion-product-purchase', get_theme_file_uri( 'assets/blocks/product-purchase.js' ), array(), wp_get_theme()->get( 'Version' ), true );
	}
	$classes = 'zion-purchase' . ( $show['quantity'] || $item->is_type( 'grouped' ) ? '' : ' zion-hide-quantity' );
	$note    = $item->is_type( 'variable' ) ? '<noscript><style>.zion-purchase .variations_button{display:none}</style><p>' .
		esc_html__( 'Activează JavaScript pentru a selecta și cumpăra o variație.', 'zion-theme' ) . '</p></noscript>' : '';
	return '<div id="zion-purchase" class="' . esc_attr( $classes ) . '">' . $form . $note .
		( $ajax ? '<div class="zion-purchase-notice" role="status" aria-live="polite"></div>' : '' ) . '</div>';
}

/**
 * Build one hero from the ordered summary and optional native gallery.
 *
 * @param WC_Product $item Product.
 * @param array      $profile Resolved presentation.
 * @return string
 */
function zion_theme_product_hero( WC_Product $item, array $profile ): string {
	$show    = $profile['show'];
	$gallery = '';
	if ( $show['gallery'] && ( $item->get_image_id() || $item->get_gallery_image_ids() ) ) {
		foreach ( array(
			'zoom'     => 'zoom',
			'lightbox' => 'photoswipe',
		) as $key => $feature ) {
			if ( ! $show[ $key ] ) {
				add_filter( 'woocommerce_single_product_' . $feature . '_enabled', '__return_false', 20 );
			}
		}
		$gallery = '<div class="zion-product-media' . ( $item->get_gallery_image_ids() ? ' zion-product-media--multiple' : '' ) . '">' . zion_theme_product_native_block( 'woocommerce/product-image-gallery', $item ) .
			'<noscript><style>.zion-product-media .woocommerce-product-gallery{opacity:1!important}</style></noscript></div>';
	}
	$summary = '';
	$intro   = '';
	foreach ( $profile['summary'] as $key ) {
		if ( ! $show[ $key ] ) {
			continue;
		}
		$content = match ( $key ) {
			'title' => '<h1 class="zion-section-title">' . esc_html( $item->get_name() ) . '</h1>',
			'price' => wp_kses_post( $item->get_price_html() ),
			'short_description' => wp_kses_post( wpautop( $item->get_short_description() ) ),
			'availability' => zion_theme_product_availability( $item ),
			'attributes' => zion_theme_product_attributes( $item ),
			'purchase' => zion_theme_product_purchase( $item, $show ),
			'meta' => zion_theme_product_meta( $item ),
			default => '',
		};
		if ( '' !== $content ) {
			$component = '<div class="zion-product-summary__' . esc_attr( $key ) . '">' . $content . '</div>';
			if ( in_array( $profile['design'], array( 'focus', 'editorial' ), true ) && in_array( $key, array( 'title', 'short_description' ), true ) ) {
				$intro .= $component;
			} else {
				$summary .= $component;
			}
		}
	}
	if ( ! $gallery && ! $summary && ! $intro ) {
		return '';
	}
	return '<div class="zion-product-layout' . ( $gallery && $summary ? ' zion-product-layout--split' : '' ) . '">' .
		( $intro ? '<div class="zion-product-intro">' . $intro . '</div>' : '' ) . $gallery .
		( $summary ? '<div class="zion-product-summary">' . $summary . '</div>' : '' ) . '</div>';
}

/**
 * Show at most three native public attributes near the purchase form.
 *
 * @param WC_Product $item Product.
 * @return string
 */
function zion_theme_product_attributes( WC_Product $item ): string {
	$rows  = '';
	$count = 0;
	foreach ( $item->get_attributes() as $attribute ) {
		if ( ! $attribute->get_visible() ) {
			continue;
		}
		$values = $attribute->is_taxonomy() ? wc_get_product_terms( $item->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) ) : $attribute->get_options();
		if ( is_wp_error( $values ) || ! $values ) {
			continue;
		}
		$rows .= '<div><dt>' . esc_html( wc_attribute_label( $attribute->get_name(), $item ) ) . '</dt><dd>' .
			esc_html( implode( ', ', array_slice( $values, 0, 3 ) ) . ( count( $values ) > 3 ? '…' : '' ) ) . '</dd></div>';
		++$count;
		if ( 3 === $count ) {
			break;
		}
	}
	return $rows ? '<dl class="zion-product-attributes">' . $rows . '</dl>' : '';
}

/**
 * Present native availability, even when WooCommerce omits a generic in-stock label.
 *
 * @param WC_Product $item Product.
 * @return string
 */
function zion_theme_product_availability( WC_Product $item ): string {
	if ( $item->is_type( 'external' ) || ( $item instanceof WC_Product_Grouped && ! $item->get_visible_children() ) ) {
		return '';
	}
	$stock = wc_get_stock_html( $item );
	if ( $stock ) {
		return wp_kses_post( $stock );
	}
	return $item->is_in_stock() && ! $item->is_type( 'variable' ) ? esc_html__( 'În stoc', 'zion-theme' ) : '';
}

/**
 * Render native SKU and category data as a single optional component.
 *
 * @param WC_Product $item Product.
 * @return string
 */
function zion_theme_product_meta( WC_Product $item ): string {
	$sku  = $item->get_sku();
	$cats = wc_get_product_category_list( $item->get_id(), ', ' );
	if ( ! $sku && ! $cats ) {
		return '';
	}
	return '<div class="wp-block-woocommerce-product-meta">' .
		( $sku ? '<p><span>SKU:</span> <span class="sku">' . esc_html( $sku ) . '</span></p>' : '' ) .
		( $cats ? '<p>' . wp_kses_post( $cats ) . '</p>' : '' ) . '</div>';
}

/**
 * Render native product recommendations using the shared Zion card.
 *
 * @param array      $ids Native product IDs.
 * @param WC_Product $item Current product.
 * @return string
 */
function zion_theme_product_recommendations( array $ids, WC_Product $item ): string {
	$ids = array_slice( array_values( array_diff( array_unique( $ids ), array( $item->get_id() ) ) ), 0, 5 );
	if ( ! $ids ) {
		return '';
	}
	$cards = '';
	foreach ( $ids as $id ) {
		$recommendation = wc_get_product( $id );
		if ( $recommendation && 'publish' === $recommendation->get_status() && $recommendation->is_visible() ) {
			$cards .= '<li>' . zion_theme_product_native_block( 'zion/product-card', $recommendation ) . '</li>';
		}
	}
	return $cards ? '<ul class="zion-product-recommendations">' . $cards . '</ul>' : '';
}

/**
 * Render a public editorial section without exposing downloadable file URLs.
 *
 * @param string     $key Editorial field.
 * @param WC_Product $item Product.
 * @return string
 */
function zion_theme_product_editorial( string $key, WC_Product $item ): string {
	$value = function_exists( 'zion_core_product_detail' ) ? zion_core_product_detail( $item->get_id(), $key ) : '';
	if ( 'documentation' === $key ) {
		$url = esc_url( $value, array( 'http', 'https' ) );
		return $url ? '<p><a href="' . $url . '">' . esc_html__( 'Deschide documentația', 'zion-theme' ) . '</a></p>' : '';
	}
	if ( 'files' === $key && '' === $value ) {
		$names = array_filter( array_map( static fn ( WC_Product_Download $file ): string => $file->get_name(), $item->get_downloads() ) );
		$value = implode( "\n", $names );
	}
	if ( '' === $value ) {
		return '';
	}
	if ( in_array( $key, array( 'benefits', 'files', 'faq' ), true ) ) {
		$list = '';
		foreach ( preg_split( '/\R/', $value ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			if ( 'faq' === $key ) {
				$parts = array_map( 'trim', explode( '|', $line, 2 ) );
				if ( ! empty( $parts[0] ) && ! empty( $parts[1] ) ) {
					$list .= '<details><summary>' . esc_html( $parts[0] ) . '</summary><p>' . esc_html( $parts[1] ) . '</p></details>';
				}
			} else {
				$list .= '<li>' . esc_html( $line ) . '</li>';
			}
		}
		return 'faq' === $key || ! $list ? $list : '<ul>' . $list . '</ul>';
	}
	return wpautop( esc_html( $value ) );
}

/**
 * Render a single section from its native or Zion data source.
 *
 * @param string     $key Section key.
 * @param WC_Product $item Product.
 * @param array      $profile Resolved presentation.
 * @return string
 */
function zion_theme_product_section( string $key, WC_Product $item, array $profile ): string {
	switch ( $key ) {
		case 'breadcrumb':
			return zion_theme_product_native_block( 'woocommerce/breadcrumbs', $item );
		case 'hero':
			return zion_theme_product_hero( $item, $profile );
		case 'description':
			return trim( $item->get_description() ) ? zion_theme_product_native_block( 'core/post-content', $item ) : '';
		case 'specifications':
			ob_start();
			wc_display_product_attributes( $item );
			return (string) ob_get_clean();
		case 'reviews':
			if ( ! wc_reviews_enabled() || ( ! comments_open( $item->get_id() ) && ! $item->get_review_count() ) ) {
				return '';
			}
			ob_start();
			comments_template();
			return (string) ob_get_clean();
		case 'related':
			return zion_theme_product_recommendations( wc_get_related_products( $item->get_id(), 5, $item->get_upsell_ids() ), $item );
		case 'upsells':
			return zion_theme_product_recommendations( $item->get_upsell_ids(), $item );
		case 'cross_sells':
			return zion_theme_product_recommendations( $item->get_cross_sell_ids(), $item );
		default:
			return zion_theme_product_editorial( $key, $item );
	}
}

/**
 * Render the configurable product template.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content Saved block content.
 * @param WP_Block $block Product context.
 * @return string
 */
function zion_theme_render_product_page( array $attributes, string $content, WP_Block $block ): string {
	$id   = $block->context['postId'] ?? get_queried_object_id();
	$item = function_exists( 'wc_get_product' ) && is_singular( 'product' ) ? wc_get_product( $id ) : false;
	if ( ! $item ) {
		return '';
	}
	zion_theme_native_product_schema( $item );
	$profile   = zion_theme_product_profile( $item );
	$show      = $profile['show'];
	$classes   = array( 'zion-product-page', 'alignwide', 'zion-product-page--' . $profile['preset'], 'zion-product-style--' . $profile['style'], 'zion-gallery--' . $profile['side'] );
	$classes[] = 'zion-product-design--' . $profile['design'];
	$classes[] = 'zion-product-shop--' . ( $profile['shop'] ? $profile['shop'] : 'global' );
	foreach ( array( 'availability', 'price' ) as $key ) {
		if ( ! $show[ $key ] ) {
			$classes[] = 'zion-hide-' . $key;
		}
	}
	$labels   = Zion_Theme_Settings::product_sections();
	$sections = '';
	foreach ( $profile['sections'] as $key ) {
		if ( ! $show[ $key ] ) {
			continue;
		}
		$markup = zion_theme_product_section( $key, $item, $profile );
		if ( '' === trim( $markup ) ) {
			continue;
		}
		$heading   = 'related' === $key ? __( 'Related products', 'woocommerce' ) : $labels[ $key ];
		$panel     = in_array( $key, array( 'breadcrumb', 'hero' ), true ) ? '' : ' zion-product-panel';
		$accent    = in_array( $key, $profile['accents'], true ) ? ' zion-product-section--accent' : '';
		$sections .= '<section class="zion-product-section' . $panel . $accent . '" data-zion-section="' . esc_attr( $key ) . '">' .
			( $panel && 'reviews' !== $key ? '<h2>' . esc_html( $heading ) . '</h2>' : '' ) . $markup . '</section>';
	}
	$direct = zion_theme_can_direct_purchase( $item, $show ) && str_contains( $sections, 'id="zion-purchase"' );
	$sticky = '';
	if ( $direct && $show['sticky'] ) {
		$classes[] = 'zion-product-page--sticky';
		$sticky    = '<div class="zion-purchase-bar"><span>' . esc_html( $item->get_name() ) . '</span>' .
			'<a class="wp-element-button" data-zion-sticky data-ready="' . esc_attr__( 'Adaugă în coș', 'zion-theme' ) . '" data-unready="' .
			esc_attr__( 'Alege opțiunile', 'zion-theme' ) . '" href="#zion-purchase">' . esc_html__( 'Mergi la cumpărare', 'zion-theme' ) . '</a></div>';
	}
	if ( $direct && ( $show['sticky'] || $show['buy_now'] ) ) {
		wp_enqueue_script( 'zion-product-purchase', get_theme_file_uri( 'assets/blocks/product-purchase.js' ), array(), wp_get_theme()->get( 'Version' ), true );
	}
	return '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $sections . $sticky . '</div>';
}
