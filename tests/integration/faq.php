<?php
/**
 * FAQ templates retain native disclosures, content, links and authoring data.
 *
 * @package Zion_Core
 */

foreach ( array( 'page-intrebari', 'faq' ) as $zion_core_faq_template ) {
	$zion_core_faq_source = file_get_contents( get_theme_file_path( 'templates/' . $zion_core_faq_template . '.html' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Known local theme template.
	zion_core_test_assert( zion_theme_migration_native_blocks( resolve_pattern_blocks( parse_blocks( $zion_core_faq_source ) ) ), $zion_core_faq_template . ' resolves to native editable blocks' );
}
$zion_core_faq_page     = get_page_by_path( 'intrebari' );
$zion_core_faq_before   = get_post( $zion_core_faq_page->ID, ARRAY_A );
$zion_core_faq_meta     = get_post_meta( $zion_core_faq_page->ID );
$zion_core_faq_settings = get_option( Zion_Theme_Settings::OPTION );
$zion_core_faq_html     = do_blocks( $zion_core_faq_page->post_content );
zion_core_test_assert( 10 === substr_count( $zion_core_faq_html, '<details ' ) && 10 === substr_count( $zion_core_faq_html, '<summary>' ), 'Ten synthetic native Details remain available without JavaScript' );
$zion_core_faq_pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'zion-theme/faq-layout' );
zion_core_test_assert( $zion_core_faq_pattern && ! str_contains( $zion_core_faq_pattern['content'], '<canvas' ) && ! str_contains( $zion_core_faq_pattern['content'], 'core/html' ), 'FAQ layout stores native editable blocks without runtime canvas or a legacy HTML import' );
$zion_core_faq_scene = zion_theme_faq_motion( '<div class="zion-faq-network"><p>Question fallback.</p></div>', array( 'attrs' => array( 'className' => 'zion-faq-network' ) ) );
zion_core_test_assert( 1 === substr_count( $zion_core_faq_scene, 'data-zion-spatial="faq-network"' ) && str_contains( $zion_core_faq_scene, 'Question fallback.' ) && str_contains( $zion_core_faq_scene, 'aria-hidden="true"' ), 'FAQ decoration retains its fallback and adds one decorative scene' );
$zion_core_faq_queue = wp_scripts()->queue;
zion_core_test_assert( '<div>Unrelated.</div>' === zion_theme_faq_motion( '<div>Unrelated.</div>', array( 'attrs' => array() ) ) && wp_scripts()->queue === $zion_core_faq_queue, 'Unrelated components do not enqueue FAQ assets' );
zion_core_test_assert( get_post( $zion_core_faq_page->ID, ARRAY_A ) === $zion_core_faq_before && get_post_meta( $zion_core_faq_page->ID ) === $zion_core_faq_meta && get_option( Zion_Theme_Settings::OPTION ) === $zion_core_faq_settings, 'Rendering FAQ leaves question content, URL, metadata and settings intact' );
