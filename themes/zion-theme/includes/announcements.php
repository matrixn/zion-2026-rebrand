<?php
/**
 * Progressive presentation for authored migration announcements.
 *
 * @package Zion_Theme
 */

/**
 * Load the mobile disclosure only when its native HTML block is rendered.
 *
 * @param string $html Rendered HTML block.
 * @return string
 */
function zion_theme_announcement( string $html ): string {
	if ( is_admin() || wp_is_serving_rest_request() || ! str_contains( $html, 'zion-migration-announcement' ) ) {
		return $html;
	}
	$tags = new WP_HTML_Tag_Processor( $html );
	if ( ! $tags->next_tag(
		array(
			'tag_name'   => 'ASIDE',
			'class_name' => 'zion-migration-announcement',
		)
	) ) {
		return $html;
	}
	$tags->set_attribute( 'data-zion-notice-key', 'zion-notice-' . substr( hash( 'sha256', $html ), 0, 16 ) );
	wp_enqueue_script( 'zion-announcements', get_theme_file_uri( 'assets/blocks/announcements.js' ), array(), wp_get_theme()->get( 'Version' ), true );
	return $tags->get_updated_html();
}
add_filter( 'render_block_core/html', 'zion_theme_announcement' );
