<?php
/**
 * Behavior for the native Zion site layout blocks.
 *
 * @package Zion_Theme
 */

/**
 * Add presentation classes before templates render.
 *
 * @param array $classes Body classes.
 * @return array
 */
function zion_theme_site_classes( array $classes ): array {
	foreach ( array( 'sticky', 'mega' ) as $flag ) {
		if ( zion_theme_setting( 'header_' . $flag ) ) {
			$classes[] = 'zion-header-' . $flag;
		}
	}
	if ( is_front_page() && zion_theme_setting( 'header_transparent' ) ) {
		$classes[] = 'zion-header-transparent';
	}
	if ( 'list' === zion_theme_setting( 'blog_layout' ) ) {
		$classes[] = 'zion-blog-list';
	}
	if ( zion_theme_is_standard_page() ) {
		$classes[] = 'zion-page-width-' . zion_theme_setting( 'page_width' );
		$classes[] = 'zion-page-header-' . zion_theme_setting( 'page_header' );
		if ( zion_theme_setting( 'page_sidebar' ) ) {
			$classes[] = 'zion-page-has-sidebar';
		}
	}
	return $classes;
}
add_filter( 'body_class', 'zion_theme_site_classes' );

/**
 * Keep the back-to-top target outside the sticky header.
 */
function zion_theme_page_top_target(): void {
	if ( zion_theme_setting( 'back_to_top' ) ) {
		echo '<span id="zion-page-top" tabindex="-1"></span>';
	}
}
add_action( 'wp_body_open', 'zion_theme_page_top_target' );

/**
 * Keep editorial page features away from WooCommerce utility pages.
 *
 * @return bool
 */
function zion_theme_is_standard_page(): bool {
	return is_page() && ! is_front_page() &&
		( ! function_exists( 'is_cart' ) || ( ! is_cart() && ! is_checkout() && ! is_account_page() ) );
}

/**
 * Skip disabled chrome before WooCommerce queues block assets.
 *
 * @param string|null $output A prior block override.
 * @param array       $block Parsed native block.
 * @return string|null
 */
function zion_theme_site_chrome( ?string $output, array $block ): ?string {
	if ( null !== $output || is_admin() || wp_is_serving_rest_request() ) {
		return $output;
	}
	$attrs = $block['attrs'] ?? array();
	$name  = $block['blockName'] ?? '';
	if ( empty( $attrs['className'] ) && 'core/template-part' !== $name ) {
		return $output;
	}
	$names = explode( ' ', $attrs['className'] ?? '' );
	if ( 'woocommerce/mini-cart' === $name && in_array( 'zion-header-cart', $names, true ) && ! zion_theme_setting( 'header_cart' ) ) {
		return zion_theme_header_cart_link( $attrs );
	}
	$map = array(
		'zion-header-search'    => 'header_search',
		'zion-header-account'   => 'header_account',
		'zion-header-cta'       => 'header_cta',
		'zion-footer-cta'       => 'footer_cta',
		'zion-footer-contact'   => 'footer_contact',
		'zion-footer-resources' => 'footer_resources',
		'zion-footer-social'    => 'footer_social',
		'zion-footer-legal'     => 'footer_legal',
		'zion-footer-copyright' => 'footer_copyright',
		'zion-footer-search'    => 'footer_search',
	);
	if ( array_intersect( array( 'zion-page-title', 'zion-page-image' ), $names ) && zion_theme_is_standard_page() ) {
		$map += array(
			'zion-page-title' => 'page_title',
			'zion-page-image' => 'page_image',
		);
	}
	foreach ( $names as $class ) {
		if ( isset( $map[ $class ] ) && ! zion_theme_setting( $map[ $class ] ) ) {
			return '';
		}
	}
	if ( 'core/template-part' === $name && 'page-sidebar' === ( $attrs['slug'] ?? '' ) && ( ! zion_theme_is_standard_page() || ! zion_theme_setting( 'page_sidebar' ) ) ) {
		return '';
	}
	if ( 'core/comments' === $name && in_array( 'zion-blog-comments', $names, true ) && is_singular( 'post' ) && ! zion_theme_setting( 'blog_comments' ) ) {
		return '';
	}
	if ( in_array( 'zion-header-account', $names, true ) && ( ! function_exists( 'wc_get_page_id' ) || 'publish' !== get_post_status( wc_get_page_id( 'myaccount' ) ) ) ) {
		return '';
	}
	return $output;
}
add_filter( 'pre_render_block', 'zion_theme_site_chrome', 10, 2 );

/**
 * Keep access to the native cart when the drawer is disabled, without its assets.
 *
 * @param array $attrs Editable mini-cart block attributes.
 * @return string
 */
function zion_theme_header_cart_link( array $attrs ): string {
	if ( ! function_exists( 'wc_get_cart_url' ) || ! function_exists( 'wc_get_page_id' ) || 'publish' !== get_post_status( wc_get_page_id( 'cart' ) ) ) {
		return '';
	}
	$icons = '\\Automattic\\WooCommerce\\Blocks\\Utils\\MiniCartUtils';
	$icon  = is_callable( array( $icons, 'get_svg_icon' ) ) ? $icons::get_svg_icon( $attrs['miniCartIcon'] ?? '' ) : 'Coș';
	return '<a class="zion-header-cart zion-header-cart-link" href="' . esc_url( wc_get_cart_url() ) . '" aria-label="Coș"><span aria-hidden="true">' . $icon . '</span><span class="screen-reader-text">Coș</span></a>';
}

/**
 * Pass the count preference to the native mini-cart block.
 *
 * @param array $block Parsed block.
 * @return array
 */
function zion_theme_native_cart_badge( array $block ): array {
	if ( 'woocommerce/mini-cart' === $block['blockName'] && 'zion-header-cart' === ( $block['attrs']['className'] ?? '' ) && ! is_admin() && ! wp_is_serving_rest_request() && ! zion_theme_setting( 'header_cart_badge' ) ) {
		$block['attrs']['productCountVisibility'] = 'never';
	}
	return $block;
}
add_filter( 'render_block_data', 'zion_theme_native_cart_badge' );

/**
 * Resolve editorial overrides using the actual post in the WordPress loop.
 *
 * @param string $key Setting ID.
 * @param int    $id Post ID.
 * @return bool|int|string
 */
function zion_theme_editorial_setting( string $key, int $id ): bool|int|string {
	return Zion_Theme_Settings::resolve( zion_theme_settings_config(), $key, '', 'post', $id )['value'];
}

/**
 * Apply visibility to marked native article blocks.
 *
 * @param string   $output Rendered block.
 * @param array    $block Parsed block.
 * @param WP_Block $instance Block context.
 * @return string
 */
function zion_theme_article_blocks( string $output, array $block, WP_Block $instance ): string {
	$id = $instance->context['postId'] ?? get_the_ID();
	if ( is_admin() || wp_is_serving_rest_request() || 'post' !== get_post_type( $id ) ) {
		return $output;
	}
	$map = array(
		'zion-blog-image'      => 'blog_image',
		'zion-blog-categories' => 'blog_categories',
		'zion-blog-date'       => 'blog_date',
		'zion-blog-author'     => 'blog_author',
		'zion-blog-excerpt'    => 'blog_excerpt',
		'zion-blog-comments'   => 'blog_comments',
	);
	foreach ( explode( ' ', $block['attrs']['className'] ?? '' ) as $class ) {
		if ( isset( $map[ $class ] ) && ! zion_theme_editorial_setting( $map[ $class ], $id ) ) {
			return '';
		}
	}
	return $output;
}
foreach ( array( 'post-featured-image', 'post-terms', 'post-date', 'post-author-name', 'post-excerpt', 'comments' ) as $zion_theme_block ) {
	add_filter( 'render_block_core/' . $zion_theme_block, 'zion_theme_article_blocks', 20, 3 );
}

/**
 * Adjust only the main WordPress editorial query, preserving native pagination.
 *
 * @param WP_Query $query Current query.
 */
function zion_theme_blog_per_page( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || $query->is_feed() || ! ( $query->is_home() || $query->is_category() || $query->is_tag() || $query->is_author() || $query->is_date() ) ) {
		return;
	}
	$setting = Zion_Theme_Settings::resolve( zion_theme_settings_config(), 'blog_per_page' );
	if ( 'default' !== $setting['source'] ) {
		$query->set( 'posts_per_page', $setting['value'] );
	}
}
add_action( 'pre_get_posts', 'zion_theme_blog_per_page' );

/**
 * Estimate reading time independently of the publication date.
 *
 * @param int $id Article ID.
 * @return string
 */
function zion_theme_article_reading_time( int $id ): string {
	if ( ! zion_theme_editorial_setting( 'reading_time', $id ) ) {
		return '';
	}
	$content = (string) get_post_field( 'post_content', $id );
	$words   = preg_match_all( '/[\p{L}\p{N}]+/u', wp_strip_all_tags( strip_shortcodes( $content ) ) );
	if ( ! $words ) {
		return '';
	}
	$minutes = (int) ceil( $words / zion_theme_editorial_setting( 'reading_speed', $id ) );
	/* translators: %d: estimated minutes to read the article. */
	return '<p class="zion-reading-time">' . esc_html( sprintf( __( '%d min de citit', 'zion-theme' ), $minutes ) ) . '</p>';
}

/**
 * List up to three posts sharing categories with the current public article.
 *
 * @param int $id Article ID.
 * @return string
 */
function zion_theme_related_posts( int $id ): string {
	$categories = wp_get_post_categories( $id );
	if ( ! $categories || ! zion_theme_editorial_setting( 'blog_related', $id ) ) {
		return '';
	}
	$posts = get_posts(
		array(
			'category__in'        => $categories,
			'post__not_in'        => array( $id ),
			'numberposts'         => 3,
			'has_password'        => false,
			'ignore_sticky_posts' => true,
		)
	);
	$links = '';
	foreach ( $posts as $post ) {
		$links .= '<li><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a></li>';
	}
	return $links ? '<section class="zion-article-related"><h2>' . esc_html__( 'Articole asociate', 'zion-theme' ) . '</h2><ul>' . $links . '</ul></section>' : '';
}

/**
 * Build a breadcrumb from public parent pages.
 *
 * @param int $id Current page ID.
 * @return string
 */
function zion_theme_page_breadcrumb( int $id ): string {
	$links = '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Acasă', 'zion-theme' ) . '</a></li>';
	foreach ( array_reverse( get_post_ancestors( $id ) ) as $parent_id ) {
		if ( 'publish' === get_post_status( $parent_id ) ) {
			$links .= '<li><a href="' . esc_url( get_permalink( $parent_id ) ) . '">' . esc_html( get_the_title( $parent_id ) ) . '</a></li>';
		}
	}
	return '<nav class="zion-page-breadcrumb" aria-label="' . esc_attr__( 'Fir de navigare', 'zion-theme' ) . '"><ol>' .
		$links . '<li aria-current="page">' . esc_html( get_the_title( $id ) ) . '</li></ol></nav>';
}

/**
 * Render lightweight editorial fragments inside the Site Editor templates.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content Saved markup.
 * @param WP_Block $block Current block context.
 * @return string
 */
function zion_theme_editorial( array $attributes, string $content, WP_Block $block ): string {
	$part = $attributes['part'] ?? '';
	if ( 'back_top' === $part ) {
		return zion_theme_setting( 'back_to_top' ) ? '<p class="zion-back-to-top"><a href="#zion-page-top">' . esc_html__( 'Înapoi sus', 'zion-theme' ) . ' ↑</a></p>' : '';
	}
	if ( 'breadcrumb' === $part ) {
		return zion_theme_is_standard_page() && zion_theme_setting( 'page_breadcrumb' ) ? zion_theme_page_breadcrumb( get_queried_object_id() ) : '';
	}
	$id = $block->context['postId'] ?? get_the_ID();
	if ( ! $id || 'post' !== get_post_type( $id ) || post_password_required( $id ) ) {
		return '';
	}
	if ( 'reading_time' === $part ) {
		return zion_theme_article_reading_time( $id );
	}
	if ( ! is_singular( 'post' ) || get_queried_object_id() !== $id ) {
		return '';
	}
	if ( 'related' === $part ) {
		return zion_theme_related_posts( $id );
	}
	if ( 'share' === $part && zion_theme_editorial_setting( 'blog_share', $id ) ) {
		$url = get_permalink( $id );
		wp_enqueue_script( 'zion-article-share', get_theme_file_uri( 'assets/blocks/article-share.js' ), array(), wp_get_theme()->get( 'Version' ), true );
		return '<div class="zion-article-share"><a href="' .
			esc_url( 'mailto:?subject=' . rawurlencode( get_the_title( $id ) ) . '&body=' . rawurlencode( $url ) ) . '">' . esc_html__( 'Distribuie prin e-mail', 'zion-theme' ) .
			'</a><button type="button" class="wp-element-button" hidden data-zion-copy="' . esc_attr( $url ) . '" data-copied="' . esc_attr__( 'Link copiat.', 'zion-theme' ) . '">' .
			esc_html__( 'Copiază linkul', 'zion-theme' ) . '</button><span aria-live="polite"></span></div>';
	}
	return '';
}

/**
 * Register the small server-rendered fragments used by the templates.
 */
function zion_theme_register_editorial_block(): void {
	wp_register_script( 'zion-editorial-editor', get_theme_file_uri( 'assets/blocks/editorial-editor.js' ), array( 'wp-blocks', 'wp-block-editor', 'wp-element' ), wp_get_theme()->get( 'Version' ), true );
	register_block_type(
		'zion/editorial',
		array(
			'api_version'     => 3,
			'title'           => __( 'Componentă editorială Zion', 'zion-theme' ),
			'category'        => 'widgets',
			'uses_context'    => array( 'postId' ),
			'attributes'      => array( 'part' => array( 'type' => 'string' ) ),
			'supports'        => array( 'inserter' => false ),
			'editor_script'   => 'zion-editorial-editor',
			'render_callback' => 'zion_theme_editorial',
		)
	);
}
add_action( 'init', 'zion_theme_register_editorial_block' );
