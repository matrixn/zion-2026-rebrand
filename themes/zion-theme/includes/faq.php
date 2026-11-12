<?php
/**
 * Native FAQ presentation; questions remain editable WordPress Details blocks.
 *
 * @package Zion_Theme
 */

/**
 * Decorate only explicitly marked FAQ components during frontend rendering.
 *
 * @param string $html Rendered native group.
 * @param array  $block Parsed block.
 * @return string
 */
function zion_theme_faq_motion( string $html, array $block ): string {
	if ( is_admin() || wp_is_serving_rest_request() ) {
		return $html;
	}
	$classes = explode( ' ', $block['attrs']['className'] ?? '' );
	if ( in_array( 'zion-faq', $classes, true ) ) {
		wp_enqueue_script(
			'zion-faq',
			get_theme_file_uri( 'assets/blocks/faq.js' ),
			array(),
			wp_get_theme()->get( 'Version' ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}
	foreach ( array( 'network', 'relay' ) as $type ) {
		if ( in_array( 'zion-faq-' . $type, $classes, true ) ) {
			zion_theme_enqueue_continuum();
			return preg_replace( '~(</div>\s*)$~', '<canvas class="zion-spatial-canvas" data-zion-spatial="faq-' . $type . '" aria-hidden="true"></canvas>$1', $html, 1 ) ?? $html;
		}
	}
	return $html;
}
add_filter( 'render_block_core/group', 'zion_theme_faq_motion', 30, 2 );
