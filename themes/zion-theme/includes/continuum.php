<?php
/**
 * Shared Continuum renderer and progressive contextual illustrations.
 *
 * @package Zion_Theme
 */

/** Load one renderer for every rendered Continuum component. */
function zion_theme_enqueue_continuum(): void {
	wp_enqueue_script(
		'zion-continuum',
		get_theme_file_uri( 'assets/blocks/continuum.js' ),
		array(),
		wp_get_theme()->get( 'Version' ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}

/**
 * Frontend-only decoration, leaving saved blocks and commerce intact.
 *
 * @param string $html Rendered native block.
 * @param array  $block Parsed block.
 * @return string
 */
function zion_theme_home_continuum( string $html, array $block ): string {
	if ( is_admin() || wp_is_serving_rest_request() ) {
		return $html;
	}
	$classes = explode( ' ', $block['attrs']['className'] ?? '' );
	$type    = '';
	$before  = '';
	foreach (
		array(
			'zion-home-path-physical' => 'physical',
			'zion-home-path-digital'  => 'digital',
			'zion-home-services'      => 'services',
			'zion-home-process'       => 'process',
			'zion-home-faq'           => 'portal',
		) as $class => $scene
	) {
		if ( in_array( $class, $classes, true ) ) {
			$type = $scene;
			break;
		}
	}
	if ( 'core/html' === $block['blockName'] && str_contains( $html, 'class="zion-home-lab zion-blueprint"' ) ) {
		$type = 'hero';
	}
	if ( ! $type ) {
		return $html;
	}
	$canvas = '<canvas class="zion-spatial-canvas" data-zion-spatial="home-' . esc_attr( $type ) . '" aria-hidden="true"></canvas>';
	$panel  = '<div class="zion-home-motion zion-home-motion--' . esc_attr( $type ) . '">' . $canvas . '</div>';
	if ( in_array( $type, array( 'physical', 'digital' ), true ) ) {
		$html = preg_replace( '~</p>~', '$0' . $panel, $html, 1 );
	} elseif ( in_array( $type, array( 'services', 'process' ), true ) ) {
		$before = 'services' === $type ? 'zion-home-service-grid' : 'zion-service-grid';
		$html   = preg_replace( '~(?=<div\b[^>]*\bclass="[^"]*\b' . preg_quote( $before, '~' ) . '\b)~', $panel, $html, 1 );
	} elseif ( 'portal' === $type ) {
		$html = preg_replace( '~</h2>~', '$0' . $panel, $html, 1 );
	} else {
		// Keep the original SVG as the no-JavaScript and editor fallback.
		$html = preg_replace( '~\saria-hidden="true"~', '', $html, 1 );
		$html = preg_replace( '~<svg\b~', '<svg aria-hidden="true"', $html, 1 );
		$html = preg_replace( '~(</div>\s*)$~', $panel . '$1', $html, 1 );
	}
	if ( is_string( $html ) && str_contains( $html, $canvas ) ) {
		zion_theme_enqueue_continuum();
	}
	return $html ?? '';
}
add_filter( 'render_block_core/group', 'zion_theme_home_continuum', 26, 2 );
add_filter( 'render_block_core/html', 'zion_theme_home_continuum', 26, 2 );

/**
 * Resolve a presentation concept from a native service page or link.
 *
 * @param string $slug Page slug.
 * @return string
 */
function zion_theme_continuum_service_key( string $slug ): string {
	if ( isset( zion_theme_services()[ $slug ] ) ) {
		return $slug;
	}
	$key = array_search( $slug, zion_theme_service_paths(), true );
	return false === $key ? '' : $key;
}

/**
 * A decorative service scene; controls are added by the common renderer.
 *
 * @param string $type Scene role.
 * @param string $concept Service identifier.
 * @return string
 */
function zion_theme_service_motion_panel( string $type, string $concept = '' ): string {
	return '<div class="zion-service-motion zion-service-motion--' . esc_attr( $type ) . '"><canvas class="zion-spatial-canvas" data-zion-spatial="service-' . esc_attr( $type ) . '" data-zion-concept="' . esc_attr( $concept ) . '" aria-hidden="true"></canvas></div>';
}

/**
 * Enhance native service compositions without updating saved content.
 *
 * @param string $html Rendered native group.
 * @param array  $block Parsed block.
 * @return string
 */
function zion_theme_services_continuum( string $html, array $block ): string {
	if ( is_admin() || wp_is_serving_rest_request() ) {
		return $html;
	}
	$classes = explode( ' ', $block['attrs']['className'] ?? '' );
	$panel   = '';
	if ( in_array( 'zion-overview-card', $classes, true ) && preg_match( '~<a\b[^>]*href="([^"]+)"~', $html, $link ) ) {
		$url  = html_entity_decode( $link[1], ENT_QUOTES, 'UTF-8' );
		$host = wp_parse_url( $url, PHP_URL_HOST );
		$key  = ( ! $host || wp_parse_url( home_url(), PHP_URL_HOST ) === $host )
			? zion_theme_continuum_service_key( basename( (string) wp_parse_url( $url, PHP_URL_PATH ) ) ) : '';
		if ( $key ) {
			$panel = zion_theme_service_motion_panel( 'preview', $key );
			$html  = preg_replace( '~(?=<p\b[^>]*class="[^"]*\bzion-overview-icon\b)~', $panel, $html, 1 ) ?? $html;
		}
	} elseif ( in_array( 'zion-overview-process', $classes, true ) || in_array( 'zion-detail-process', $classes, true ) ) {
		$panel = zion_theme_service_motion_panel( 'process' );
		$html  = preg_replace( '~(?=<div\b[^>]*class="[^"]*\bzion-service-grid\b)~', $panel, $html, 1 ) ?? $html;
	} elseif ( in_array( 'zion-overview-end', $classes, true ) && ! in_array( 'zion-about-end', $classes, true ) ) {
		$panel = zion_theme_service_motion_panel( 'handoff' );
		$html  = preg_replace( '~</h2>~', '$0' . $panel, $html, 1 ) ?? $html;
	} elseif ( in_array( 'zion-services-overview', $classes, true ) && ! array_intersect( array( 'zion-homepage', 'zion-about' ), $classes ) ) {
		$key = 'directory';
		if ( in_array( 'zion-service-detail', $classes, true ) ) {
			$key = '';
			foreach ( array_keys( zion_theme_services() ) as $service ) {
				if ( in_array( 'zion-service--' . $service, $classes, true ) ) {
					$key = $service;
					break;
				}
			}
			$key = $key ? $key : zion_theme_continuum_service_key( (string) get_post_field( 'post_name', get_queried_object_id() ) );
		}
		if ( ! $key ) {
			return $html;
		}
		$panel  = zion_theme_service_motion_panel( 'hero', $key );
		$target = 'directory' === $key ? 'zion-overview-art' : 'zion-detail-screen';
		$html   = preg_replace( '~(<div\b[^>]*class="[^"]*\b' . $target . '\b[^"]*"[^>]*>)~', '$1' . $panel, $html, 1 ) ?? $html;
		$tag    = new WP_HTML_Tag_Processor( $html );
		if ( $tag->next_tag() ) {
			$tag->add_class( 'zion-service-continuum' );
			$tag->set_attribute( 'data-zion-service', $key );
			$html = $tag->get_updated_html();
		}
	}
	if ( $panel && str_contains( $html, $panel ) ) {
		zion_theme_enqueue_continuum();
	}
	return $html;
}
add_filter( 'render_block_core/group', 'zion_theme_services_continuum', 27, 2 );
