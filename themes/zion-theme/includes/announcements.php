<?php
/**
 * Native header news with progressive, accessible presentation.
 *
 * @package Zion_Theme
 */

/**
 * Render linked public updates, readable without JavaScript.
 *
 * @return string
 */
function zion_theme_announcements(): string {
	$items = function_exists( 'zion_core_header_updates' ) ? zion_core_header_updates() : array();
	if ( ! $items ) {
		return '';
	}
	wp_enqueue_script( 'zion-announcements', get_theme_file_uri( 'assets/blocks/announcements.js' ), array(), wp_get_theme()->get( 'Version' ), true );
	$html = '<aside class="zion-updates" aria-label="' . esc_attr__( 'Noutăți Zion', 'zion-theme' ) . '"><div class="zion-updates__panel"><div class="zion-updates__inner"><span class="zion-updates__heading">' . esc_html__( 'Noutăți', 'zion-theme' ) . '</span><ul class="zion-updates__items">';
	foreach ( $items as $item ) {
		$html .= '<li><a href="' . esc_url( $item['url'] ) . '"><span class="zion-updates__category">' . esc_html( $item['label'] ) . '</span><span class="zion-updates__title">' . esc_html( $item['title'] ) . '</span></a></li>';
	}
	return $html . '</ul><div class="zion-updates__controls" hidden><span class="zion-updates__count" aria-hidden="true"></span><button type="button" class="zion-updates__pause" aria-label="' . esc_attr__( 'Oprește animația', 'zion-theme' ) . '" aria-pressed="false">Ⅱ</button><button type="button" class="zion-updates__next" aria-label="' . esc_attr__( 'Următoarea noutate', 'zion-theme' ) . '">→</button><button type="button" class="zion-updates__close" aria-label="' . esc_attr__( 'Închide bara de noutăți', 'zion-theme' ) . '">×</button></div></div></div></aside>';
}

/**
 * Retire old payment notices in saved Header HTML blocks during migration.
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
	return zion_theme_announcements();
}
add_filter( 'render_block_core/html', 'zion_theme_announcement' );
