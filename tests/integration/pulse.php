<?php
/**
 * Pulse is additive: native menus, protected metadata, scoped assets, local widgets.
 *
 * @package Zion_Core
 */

( static function (): void {
	$content = file_get_contents( get_theme_file_path( 'assets/pulse/page.html' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local fixture.
	zion_core_test_assert( 1 === substr_count( $content, '<h1 ' ) && 7 === substr_count( $content, '<details ' ) && 6 === substr_count( $content, 'class="wp-block-group zion-pulse-card"' ), 'Pulse composition has one H1, six capabilities and seven native FAQs' );
	zion_core_test_assert( '' === zion_core_pulse_component( array( 'type' => '../../wp-config' ) ), 'Pulse rejects unknown component paths' );
	zion_core_test_assert( array( 'pulse', 'Electronică, senzori și automatizări.' ) === zion_theme_menu_presentation( 'Zion Pulse · Automatizări' ), 'Pulse menu receives its own outline icon and uniform description' );
	$preview = zion_core_pulse_component( array( 'type' => 'preview' ) );
	zion_core_test_assert( str_contains( $preview, 'zion-pulse-preview' ) && 1 === substr_count( $preview, '<svg ' ) && str_contains( $preview, 'data-pulse-motion' ), 'Homepage preview reuses one accessible Pulse circuit renderer' );
	$card          = WP_Block_Patterns_Registry::get_instance()->get_registered( 'zion-theme/home-pulse' );
	$rendered_card = do_blocks( $card['content'] );
	zion_core_test_assert( str_contains( $rendered_card, 'data-pulse-motion-scene' ) && ! str_contains( $rendered_card, '[zion_pulse' ), 'Native homepage pattern renders the Pulse shortcode outside post content' );
	zion_core_test_assert( ! preg_match( '~<p>\s*</p>~', $rendered_card ), 'Pulse shortcode does not introduce empty paragraph grid cells' );
	zion_core_test_assert( '[other_demo]' === zion_core_pulse_shortcode_block( '[other_demo]' ), 'Unrelated shortcode blocks remain unchanged' );
	$html = zion_core_pulse_component( array( 'type' => 'demo' ) );
	zion_core_test_assert( 5 === substr_count( $html, 'data-pulse-key=' ) && str_contains( $html, 'aria-live="polite"' ) && str_contains( $html, 'data-pulse-solve' ), 'Puzzle exposes five labeled inputs and accessible local controls' );
	zion_core_test_assert( wp_style_is( 'zion-pulse', 'enqueued' ) && wp_script_is( 'zion-pulse', 'enqueued' ), 'Pulse widget requests its conditional presentation' );
	zion_core_test_assert( str_ends_with( wp_scripts()->registered['zion-pulse']->ver, substr( hash_file( 'sha256', get_theme_file_path( 'assets/blocks/pulse.js' ) ), 0, 12 ) ), 'Pulse script version changes with its content to avoid stale CDN assets' );
	$page = get_page_by_path( 'zion-pulse' );
	$id   = $page ? $page->ID : wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_title'   => 'Zion Pulse',
			'post_name'    => 'zion-pulse',
			'post_status'  => 'publish',
			'post_content' => $content,
		)
	);
	$menu = array(
		'blockName'    => 'core/navigation-submenu',
		'attrs'        => array( 'label' => 'Servicii' ),
		'innerBlocks'  => array(),
		'innerContent' => array(),
		'innerHTML'    => '',
	);
	$with = zion_core_pulse_navigation( $menu );
	zion_core_test_assert( 1 === count( $with['innerBlocks'] ) && (int) $with['innerBlocks'][0]['attrs']['id'] === (int) $id && zion_core_pulse_navigation( $with ) === $with, 'Published Pulse adds exactly one native page link and is idempotent' );
	$other                   = $menu;
	$other['attrs']['label'] = 'Software';
	zion_core_test_assert( zion_core_pulse_navigation( $other ) === $other, 'Unrelated navigation is unchanged' );
	wp_set_current_user( 0 );
	$_POST['zion_pulse_seo'] = array( '_yoast_wpseo_title' => 'Unauthorized override' );
	$before                  = get_post_meta( $id, '_yoast_wpseo_title', true );
	zion_core_pulse_save_meta( (int) $id );
	zion_core_test_assert( get_post_meta( $id, '_yoast_wpseo_title', true ) === $before, 'Anonymous metadata writes without a nonce are rejected' );
	unset( $_POST['zion_pulse_seo'] );
	if ( ! $page ) {
		wp_delete_post( $id, true );
	}
} )();
