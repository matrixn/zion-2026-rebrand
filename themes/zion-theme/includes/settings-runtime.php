<?php
/**
 * Frontend integration for Control Center settings.
 *
 * @package Zion_Theme
 */

/**
 * Load and migrate the option in memory.
 *
 * @return array
 */
function zion_theme_settings_config(): array {
	static $previous = null;
	static $config   = array();
	$stored          = get_option( Zion_Theme_Settings::OPTION, Zion_Theme_Settings::empty_config() );
	if ( $stored !== $previous || ! $config ) {
		try {
			$config = Zion_Theme_Settings::normalize( $stored );
		} catch ( InvalidArgumentException $error ) {
			$config = Zion_Theme_Settings::empty_config();
		}
		$previous = $stored;
	}
	return $config;
}

/**
 * Determine the shop context for a page or product.
 *
 * @param int    $id Item ID.
 * @param string $type Item type.
 * @return string
 */
function zion_theme_item_shop( int $id, string $type ): string {
	if ( ! $id ) {
		return '';
	}
	if ( 'page' === $type ) {
		return match ( get_page_uri( $id ) ) {
			'magazin-3d' => '3d',
			'magazin-software' => 'software',
			default => '',
		};
	}
	if ( 'product' !== $type || ! taxonomy_exists( 'zion_shop' ) ) {
		return '';
	}
	$terms = get_the_terms( $id, 'zion_shop' );
	$shops = is_array( $terms ) ? array_intersect( wp_list_pluck( $terms, 'slug' ), Zion_Theme_Settings::SHOPS ) : array();
	return 1 === count( $shops ) ? reset( $shops ) : '';
}

/**
 * Resolve a setting for the current request.
 *
 * @param string      $key Setting identifier.
 * @param string|null $shop Explicit storefront for a Zion catalog shortcode.
 * @return bool|int|string
 */
function zion_theme_setting( string $key, ?string $shop = null ): bool|int|string {
	$id   = is_singular( Zion_Theme_Settings::ITEM_TYPES ) ? get_queried_object_id() : 0;
	$type = $id ? (string) get_post_type( $id ) : '';
	return Zion_Theme_Settings::resolve( zion_theme_settings_config(), $key, $shop ?? zion_theme_item_shop( $id, $type ), $type, $id )['value'];
}

/**
 * Compute only the classes that differ from the default catalog presentation.
 *
 * @param string|null $shop Explicit storefront or null to infer it.
 * @return array
 */
function zion_theme_catalog_classes( ?string $shop = null ): array {
	return array_filter(
		array(
			'compact' === zion_theme_setting( 'catalog_density', $shop ) ? 'zion-catalog-compact' : '',
			! zion_theme_setting( 'catalog_actions', $shop ) ? 'zion-catalog-no-actions' : '',
			! zion_theme_setting( 'catalog_counts', $shop ) ? 'zion-catalog-no-counts' : '',
		)
	);
}

/**
 * Apply settings for native theme collections and motion.
 *
 * @param array $classes Body classes.
 * @return array
 */
function zion_theme_control_body_classes( array $classes ): array {
	if ( 'reduced' === zion_theme_setting( 'motion' ) ) {
		$classes[] = 'zion-motion-reduced';
	}
	return array_merge( $classes, zion_theme_catalog_classes() );
}
add_filter( 'body_class', 'zion_theme_control_body_classes' );

/**
 * Apply a shortcode's shop context, even when it is placed on another page.
 *
 * @param string       $output Rendered shortcode.
 * @param string       $tag Shortcode name.
 * @param array|string $attributes Shortcode attributes.
 * @return string
 */
function zion_theme_control_shortcode( string $output, string $tag, array|string $attributes ): string {
	$shop = is_array( $attributes ) ? ( $attributes['shop'] ?? '' ) : '';
	if ( 'zion_catalog' !== $tag || '' === $output || ! in_array( $shop, Zion_Theme_Settings::SHOPS, true ) || is_admin() || wp_is_serving_rest_request() ) {
		return $output;
	}
	$classes = zion_theme_catalog_classes( $shop );
	if ( ! $classes ) {
		return $output;
	}
	return '<div class="zion-configured-catalog ' . esc_attr( implode( ' ', $classes ) ) . '">' . $output . '</div>';
}
add_filter( 'do_shortcode_tag', 'zion_theme_control_shortcode', 10, 3 );

/**
 * Skip optional blocks on public product pages before they query or render.
 *
 * @param string|null $output A prior override.
 * @param array       $block Parsed block.
 * @return string|null
 */
function zion_theme_control_product_blocks( ?string $output, array $block ): ?string {
	if ( null !== $output || is_admin() || wp_is_serving_rest_request() || ! is_singular( 'product' ) ) {
		return $output;
	}

	$name = $block['blockName'] ?? '';
	if ( 'woocommerce/product-meta' === $name && ! zion_theme_setting( 'product_meta' ) ) {
		return '';
	}
	if ( 'woocommerce/product-collection' === $name && 'woocommerce/product-collection/related' === ( $block['attrs']['collection'] ?? '' ) && ! zion_theme_setting( 'product_related' ) ) {
		return '';
	}
	return $output;
}
add_filter( 'pre_render_block', 'zion_theme_control_product_blocks', 10, 2 );

/**
 * Add contextual presentation to marked native theme blocks.
 *
 * @param string $output Rendered block.
 * @param array  $block Parsed block.
 * @return string
 */
function zion_theme_control_render( string $output, array $block ): string {
	if ( is_admin() || wp_is_serving_rest_request() || '' === $output ) {
		return $output;
	}

	$classes = explode( ' ', $block['attrs']['className'] ?? '' );
	if ( in_array( 'zion-navigation', $classes, true ) && zion_theme_setting( 'highlight_shop' ) ) {
		$id   = is_singular() ? get_queried_object_id() : 0;
		$shop = zion_theme_item_shop( $id, (string) get_post_type( $id ) );
		if ( $shop ) {
			$url   = zion_theme_shop_url( 'magazin-' . $shop );
			$links = new WP_HTML_Tag_Processor( $output );
			while ( $links->next_tag( 'A' ) ) {
				if ( $links->get_attribute( 'href' ) === $url ) {
					$links->add_class( 'zion-current-shop' );
				}
			}
			return $links->get_updated_html();
		}
	}
	if ( in_array( 'zion-footer-links', $classes, true ) && zion_theme_setting( 'back_to_top' ) ) {
		$closing_tag = strrpos( $output, '</' );
		if ( false !== $closing_tag ) {
			$link = '<p class="zion-back-to-top"><a href="#zion-top">' . esc_html__( 'Înapoi sus', 'zion-theme' ) . ' ↑</a></p>';
			return substr_replace( $output, $link, $closing_tag, 0 );
		}
	}
	if ( in_array( 'zion-page-title', $classes, true ) && is_page() && zion_theme_setting( 'page_home_link' ) ) {
		return '<p class="zion-page-home"><a href="' . esc_url( home_url( '/' ) ) . '">← ' . esc_html__( 'Acasă', 'zion-theme' ) . '</a></p>' . $output;
	}
	if ( in_array( 'zion-article-date', $classes, true ) && is_singular( 'post' ) && zion_theme_setting( 'reading_time' ) ) {
		$content = (string) get_post_field( 'post_content', get_queried_object_id() );
		$words   = preg_match_all( '/[\p{L}\p{N}]+/u', wp_strip_all_tags( strip_shortcodes( $content ) ) );
		$minutes = max( 1, (int) ceil( $words / zion_theme_setting( 'reading_speed' ) ) );
		/* translators: %d: estimated minutes to read the article. */
		return $output . '<p class="zion-reading-time">' . esc_html( sprintf( __( '%d min de citit', 'zion-theme' ), $minutes ) ) . '</p>';
	}
	return $output;
}
add_filter( 'render_block_core/navigation', 'zion_theme_control_render', 10, 2 );
add_filter( 'render_block_core/group', 'zion_theme_control_render', 10, 2 );
add_filter( 'render_block_core/post-title', 'zion_theme_control_render', 10, 2 );
add_filter( 'render_block_core/post-date', 'zion_theme_control_render', 10, 2 );

/**
 * Remove optional WordPress emoji compatibility assets on public requests.
 */
function zion_theme_control_emoji(): void {
	if ( ! zion_theme_setting( 'disable_emoji_assets' ) ) {
		return;
	}
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
}
add_action( 'wp', 'zion_theme_control_emoji' );
