<?php
/**
 * SEO defaults for pages using the Zion templates.
 *
 * @package Zion_Theme
 */

/**
 * Let an installed SEO provider own meta descriptions.
 */
function zion_theme_meta_description(): void {
	if ( ! zion_theme_has_seo_provider() && apply_filters( 'zion_theme_meta_description_enabled', true ) ) {
		echo '<meta name="description" content="' . esc_attr( zion_theme_seo_description() ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'zion_theme_meta_description', 2 );

/**
 * A single provider owns head metadata; dormant legacy fields are only fallbacks.
 *
 * @return bool
 */
function zion_theme_has_seo_provider(): bool {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) ||
		defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );
}

/**
 * Plain, bounded head text, without expanding blocks or executing shortcodes.
 *
 * @param string $text Source text.
 * @param int    $length Maximum characters.
 * @return string
 */
function zion_theme_seo_text( string $text, int $length = 160 ): string {
	$text = preg_replace( '/\[[a-z][^\]]*\]/i', '', strip_shortcodes( $text ) );
	$text = html_entity_decode( wp_strip_all_tags( $text, true ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	return wp_html_excerpt( trim( preg_replace( '/\s+/u', ' ', $text ) ), $length, '…' );
}

/**
 * Honor native excerpts and existing editorial descriptions before deriving copy.
 *
 * @return string
 */
function zion_theme_seo_description(): string {
	$id   = is_home() ? (int) get_option( 'page_for_posts' ) : get_queried_object_id();
	$text = '';
	if ( is_singular() || is_front_page() || is_home() ) {
		if ( ! post_password_required( $id ) ) {
			foreach ( array( '_yoast_wpseo_metadesc', 'rank_math_description', '_seopress_titles_desc' ) as $key ) {
				$value = get_post_meta( $id, $key, true );
				if ( is_string( $value ) && $value && ! str_contains( $value, '%%' ) && ! str_contains( $value, '%seo_' ) ) {
					$text = $value;
					break;
				}
			}
			$post = get_post( $id );
			if ( ! $text && $post && ! is_front_page() ) {
				$text = $post->post_excerpt ? $post->post_excerpt : $post->post_content;
			}
		}
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$text = term_description();
		if ( ! zion_theme_seo_text( $text ) ) {
			/* translators: %s: taxonomy archive title. */
			$text = sprintf( __( 'Explorează %s: produse și resurse Zion 3D, cu informații, detalii și opțiuni de acces.', 'zion-theme' ), single_term_title( '', false ) );
		}
	}
	$text = zion_theme_seo_text( $text );
	if ( ! $text ) {
		$text = is_front_page()
			? __( 'Printare și proiectare 3D, software personalizat și integrări AI. De la idee la realitate, alături de Zion 3D.', 'zion-theme' )
			: zion_theme_seo_text( wp_get_document_title() . '. ' . __( 'Descoperă serviciile, produsele și resursele Zion 3D.', 'zion-theme' ) );
	}
	return $text;
}

/**
 * Current native canonical; filter/sort parameters do not create new documents.
 *
 * @return string
 */
function zion_theme_seo_url(): string {
	if ( is_singular() ) {
		return (string) wp_get_canonical_url();
	}
	$url   = (string) get_pagenum_link( max( 1, (int) get_query_var( 'paged' ) ), false );
	$parts = wp_parse_url( $url );
	parse_str( $parts['query'] ?? '', $query );
	$strip = array_filter( array_keys( $query ), static fn( $key ) => ! preg_match( '/^query-\d+-page$/', (string) $key ) );
	return remove_query_arg( $strip, $url );
}

/**
 * Reuse authored plain legacy titles without printing inactive provider templates.
 *
 * @param array $parts Native document title parts.
 * @return array
 */
function zion_theme_seo_title( array $parts ): array {
	if ( zion_theme_has_seo_provider() ) {
		return $parts;
	}
	if ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		$tax  = $term instanceof WP_Term ? get_taxonomy( $term->taxonomy ) : null;
		if ( $tax ) {
			$parts['title'] = $tax->labels->singular_name . ': ' . single_term_title( '', false );
		}
		return $parts;
	}
	if ( ! ( is_singular() || is_front_page() || is_home() ) ) {
		return $parts;
	}
	$id = is_home() ? (int) get_option( 'page_for_posts' ) : get_queried_object_id();
	if ( post_password_required( $id ) ) {
		return $parts;
	}
	foreach ( array( '_yoast_wpseo_title', 'rank_math_title', '_seopress_titles_title' ) as $key ) {
		$title = get_post_meta( $id, $key, true );
		if ( is_string( $title ) && $title && ! preg_match( '/%[a-z_%]+%/i', $title ) ) {
			$parts['title'] = zion_theme_seo_text( $title, 200 );
			unset( $parts['site'], $parts['tagline'] );
			break;
		}
	}
	$page = zion_theme_seo_collection_page();
	if ( $page > 1 ) {
		$parts['page'] = sprintf( 'Pagina %d', $page );
	}
	return $parts;
}
add_filter( 'document_title_parts', 'zion_theme_seo_title' );

/**
 * Read the same native collection page key as the active Zion storefront.
 *
 * @return int
 */
function zion_theme_seo_collection_page(): int {
	$shop = function_exists( 'zion_core_current_shop' ) ? zion_core_current_shop() : '';
	if ( ! $shop ) {
		return 1;
	}
	$key = '3d' === $shop ? 'query-73-page' : 'query-74-page';
	return max( 1, absint( zion_core_storefront_parameter( $key ) ) );
}

/**
 * Keep Product Collection pagination distinct and consolidate the old Services alias.
 *
 * @param string  $url Native canonical.
 * @param WP_Post $post Current post.
 * @return string
 */
function zion_theme_seo_canonical( string $url, WP_Post $post ): string {
	if ( zion_theme_has_seo_provider() ) {
		return $url;
	}
	if ( 'page' === $post->post_type && 'printare-3d' === get_page_uri( $post ) ) {
		$services = get_page_by_path( 'servicii' );
		if ( $services && 'publish' === $services->post_status && get_permalink( $services ) === get_post_meta( $post->ID, '_yoast_wpseo_canonical', true ) ) {
			return (string) get_permalink( $services );
		}
	}
	$page = zion_theme_seo_collection_page();
	if ( $page > 1 && get_queried_object_id() === $post->ID ) {
		$key = '3d' === zion_core_current_shop() ? 'query-73-page' : 'query-74-page';
		$url = add_query_arg( $key, $page, $url );
	}
	return $url;
}
add_filter( 'get_canonical_url', 'zion_theme_seo_canonical', 10, 2 );

/**
 * Native featured media first, followed by a representative Zion cover (never logo).
 *
 * @return array{url:string,width:int,height:int,alt:string,type:string}
 */
function zion_theme_social_image(): array {
	$id = is_home() ? (int) get_option( 'page_for_posts' ) : get_queried_object_id();
	if ( ! is_front_page() && ( is_singular() || is_home() ) && ! post_password_required( $id ) ) {
		$image_id = get_post_thumbnail_id( $id );
		if ( ! $image_id ) {
			$post = get_post( $id );
			if ( $post && preg_match( '/\bwp-image-(\d+)\b/', $post->post_content, $match ) ) {
				$image_id = (int) $match[1];
			}
		}
		$image = $image_id ? wp_get_attachment_image_src( $image_id, 'full' ) : false;
		if ( $image ) {
			$alt = zion_theme_seo_text( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ), 200 );
			return array(
				'url'    => $image[0],
				'width'  => (int) $image[1],
				'height' => (int) $image[2],
				'alt'    => $alt ? $alt : zion_theme_seo_text( (string) get_the_title( $id ), 200 ),
				'type'   => (string) get_post_mime_type( $image_id ),
			);
		}
	}
	return array(
		'url'    => get_theme_file_uri( 'assets/images/social/zion-home-share.png' ),
		'width'  => 1734,
		'height' => 907,
		'alt'    => __( 'Zion 3D — de la idee la realitate: proiectare CAD, printare 3D și soluții software.', 'zion-theme' ),
		'type'   => 'image/png',
	);
}

/**
 * Server-rendered sharing works without JavaScript and without an SEO plugin.
 */
function zion_theme_social_meta(): void {
	if ( zion_theme_has_seo_provider() || is_feed() || is_embed() ) {
		return;
	}
	$url   = zion_theme_seo_url();
	$image = zion_theme_social_image();
	$tags  = array(
		'og:type'         => is_singular( 'post' ) ? 'article' : 'website',
		'og:title'        => wp_get_document_title(),
		'og:description'  => zion_theme_seo_description(),
		'og:url'          => $url,
		'og:site_name'    => get_bloginfo( 'name' ),
		'og:locale'       => zion_theme_seo_language() === 'en' ? 'en_US' : 'ro_RO',
		'og:image'        => $image['url'],
		'og:image:width'  => $image['width'],
		'og:image:height' => $image['height'],
		'og:image:alt'    => $image['alt'],
		'og:image:type'   => $image['type'],
	);
	foreach ( $tags as $property => $content ) {
		echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( (string) $content ) . '">' . "\n";
	}
	foreach ( array(
		'twitter:card'        => 'summary_large_image',
		'twitter:title'       => $tags['og:title'],
		'twitter:description' => $tags['og:description'],
		'twitter:image'       => $image['url'],
		'twitter:image:alt'   => $image['alt'],
	) as $name => $content ) {
		echo '<meta name="' . esc_attr( $name ) . '" content="' . esc_attr( $content ) . '">' . "\n";
	}
	// WordPress already emits the singular canonical. Archives need their native paged URL.
	if ( ! is_singular() && ! is_404() && ! is_search() ) {
		echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'zion_theme_social_meta', 3 );

/**
 * Keep utility/filter pages out of search while respecting global noindex.
 *
 * @param array $robots Native robot directives.
 * @return array
 */
function zion_theme_seo_robots( array $robots ): array {
	if ( zion_theme_has_seo_provider() ) {
		return $robots;
	}
	$utility = is_search() || is_404() || is_tag() || is_author() || is_date() || ( is_singular() && post_password_required() ) ||
		( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) );
	// Filter variants stay navigable but do not compete with the canonical catalog.
	$query_keys = array_keys( wp_unslash( $_GET ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only robot directives; no state change.
	foreach ( $query_keys as $key ) {
		if ( preg_match( '/^(?:filter_|query_type_|min_price$|max_price$|rating_filter$|orderby$|on_sale$|stock_status$|filtering$|categories$|attributes$|brands$|zion_more$)/', (string) $key ) ) {
			$utility = true;
		}
	}
	if ( $utility ) {
		$robots['noindex'] = true;
		unset( $robots['index'] );
	}
	return $robots;
}
add_filter( 'wp_robots', 'zion_theme_seo_robots' );

/**
 * Native sitemap excludes utility pages and the duplicate legacy Services alias.
 *
 * @param array  $args Native post query.
 * @param string $post_type Native post type.
 * @return array
 */
function zion_theme_seo_sitemap_posts( array $args, string $post_type ): array {
	if ( zion_theme_has_seo_provider() || 'page' !== $post_type ) {
		return $args;
	}
	$exclude = array();
	foreach ( array( 'cart', 'checkout', 'myaccount' ) as $page ) {
		if ( function_exists( 'wc_get_page_id' ) && wc_get_page_id( $page ) > 0 ) {
			$exclude[] = wc_get_page_id( $page );
		}
	}
	$alias = get_page_by_path( 'printare-3d' );
	$main  = get_page_by_path( 'servicii' );
	if ( $alias && $main && 'publish' === $main->post_status && get_permalink( $main ) === get_post_meta( $alias->ID, '_yoast_wpseo_canonical', true ) ) {
		$exclude[] = $alias->ID;
	}
	$args['post__not_in'] = array_unique( array_merge( $args['post__not_in'] ?? array(), $exclude ) );
	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'zion_theme_seo_sitemap_posts', 10, 2 );

/**
 * Tags and author listings are navigation aids rather than launch search documents.
 *
 * @param array $taxonomies Native sitemap taxonomies.
 * @return array
 */
function zion_theme_seo_sitemap_taxonomies( array $taxonomies ): array {
	if ( ! zion_theme_has_seo_provider() ) {
		unset( $taxonomies['post_tag'] );
	}
	return $taxonomies;
}
add_filter( 'wp_sitemaps_taxonomies', 'zion_theme_seo_sitemap_taxonomies' );

/**
 * Match the intentional noindex author archive policy in the native sitemap.
 *
 * @param WP_Sitemaps_Provider|false $provider Native provider.
 * @param string                     $name Provider name.
 * @return WP_Sitemaps_Provider|false
 */
function zion_theme_seo_sitemap_provider( $provider, string $name ) {
	return ! zion_theme_has_seo_provider() && 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'zion_theme_seo_sitemap_provider', 10, 2 );

/**
 * Exact language for the English Software landing, otherwise WordPress locale.
 *
 * @return string
 */
function zion_theme_seo_language(): string {
	$context = zion_theme_software_context();
	return $context && 'en' === $context[4] ? 'en' : substr( get_locale(), 0, 2 );
}

/**
 * The English landing must not claim the Romanian site's document language.
 *
 * @param string $attributes Native HTML attributes.
 * @return string
 */
function zion_theme_seo_language_attributes( string $attributes ): string {
	return 'en' === zion_theme_seo_language() && zion_theme_software_context()
		? preg_replace( '/\blang="[^"]*"/', 'lang="en-US"', $attributes ) : $attributes;
}
add_filter( 'language_attributes', 'zion_theme_seo_language_attributes' );

/**
 * Reciprocal alternates only when both real published language versions exist.
 */
function zion_theme_seo_alternates(): void {
	if ( zion_theme_has_seo_provider() || ! is_page() ) {
		return;
	}
	$paths = array(
		'ro' => 'zion-releasestation-git-deployment-automatizat-pentru-synology-dsm',
		'en' => 'zion-releasestation-automated-git-deployment-for-synology-dsm',
	);
	if ( ! in_array( get_page_uri( get_queried_object_id() ), $paths, true ) ) {
		return;
	}
	$pages = array_map( 'get_page_by_path', $paths );
	foreach ( $pages as $page ) {
		if ( ! $page || 'publish' !== $page->post_status ) {
			return;
		}
	}
	foreach ( $pages as $language => $page ) {
		echo '<link rel="alternate" hreflang="' . esc_attr( $language ) . '" href="' . esc_url( get_permalink( $page ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'zion_theme_seo_alternates', 4 );

/**
 * Restore WooCommerce Product structured data when a Zion block owns the page.
 *
 * @param WC_Product $item Current product.
 */
function zion_theme_native_product_schema( WC_Product $item ): void {
	if ( 'publish' !== $item->get_status() || post_password_required( $item->get_id() ) ||
		! apply_filters( 'zion_theme_native_product_schema_enabled', true, $item ) ) {
		return;
	}
	$schema = WC()->structured_data;
	if ( $schema && ! $schema->get_structured_data( array( 'product' ) ) ) {
		$schema->generate_product_data( $item );
	}
}
