<?php
/**
 * Authored notices remain editable and only load their own enhancement.
 *
 * @package Zion_Theme
 */

( static function (): void {
	$plain = '<aside class="other-announcement"><p>Synthetic unrelated message.</p></aside>';
	wp_dequeue_script( 'zion-announcements' );
	zion_core_test_assert( zion_theme_announcement( $plain ) === $plain && ! wp_script_is( 'zion-announcements', 'enqueued' ), 'Unrelated HTML does not load the announcement controller' );
	$html = '<aside class="zion-migration-announcement" style="display:flex"><p>Synthetic migration message.</p><a href="https://example.invalid/contact">Synthetic contact</a></aside>';
	$out  = render_block( parse_blocks( '<!-- wp:html -->' . $html . '<!-- /wp:html -->' )[0] );
	$tags = new WP_HTML_Tag_Processor( $out );
	$tags->next_tag( 'ASIDE' );
	$key    = $tags->get_attribute( 'data-zion-notice-key' );
	$second = new WP_HTML_Tag_Processor( zion_theme_announcement( $html ) );
	$second->next_tag( 'ASIDE' );
	zion_core_test_assert( wp_script_is( 'zion-announcements', 'enqueued' ) && str_starts_with( $key, 'zion-notice-' ), 'Native HTML rendering loads the controller for an explicit notice' );
	zion_core_test_assert( $second->get_attribute( 'data-zion-notice-key' ) === $key, 'The same authored announcement has a stable cross-page storage key' );
	$changed = new WP_HTML_Tag_Processor( zion_theme_announcement( str_replace( 'Synthetic migration message.', 'Updated synthetic message.', $html ) ) );
	$changed->next_tag( 'ASIDE' );
	zion_core_test_assert( $changed->get_attribute( 'data-zion-notice-key' ) !== $key, 'New announcement content starts a separate presentation preference' );
	$tags->remove_attribute( 'data-zion-notice-key' );
	zion_core_test_assert( preg_replace( '/^<aside\s+/', '<aside ', trim( $tags->get_updated_html() ) ) === $html, 'The authored copy, contact link and original fallback layout remain intact' );
} )();
