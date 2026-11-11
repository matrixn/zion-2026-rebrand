<?php
/**
 * Shared Continuum renderer and progressive homepage illustrations.
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
