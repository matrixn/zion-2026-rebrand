<?php
/**
 * Native contact composition, CF7 boundaries and rendering-only decoration.
 *
 * @package Zion_Core
 */

$zion_core_contact_page     = get_page_by_path( 'contact' );
$zion_core_contact_fixture  = get_page_by_path( 'zion-contact-demo', OBJECT, 'wpcf7_contact_form' );
$zion_core_contact_form     = WPCF7_ContactForm::get_instance( $zion_core_contact_fixture->ID );
$zion_core_contact_before   = get_post( $zion_core_contact_page->ID, ARRAY_A );
$zion_core_contact_metadata = get_post_meta( $zion_core_contact_fixture->ID );
$zion_core_contact_settings = get_option( Zion_Theme_Settings::OPTION );
$zion_core_contact_content  = zion_theme_contact_content( $zion_core_contact_fixture->ID );
$zion_core_contact_html     = do_shortcode( do_blocks( $zion_core_contact_content ) );
zion_core_test_assert( zion_theme_migration_native_blocks( parse_blocks( $zion_core_contact_content ) ), 'Contact uses native editable blocks and the registered CF7 selector' );
zion_core_test_assert( 3 === substr_count( $zion_core_contact_html, 'data-zion-spatial="contact-' ) && ! str_contains( $zion_core_contact_html, 'service-hero' ), 'Contact renders its three scenes without the Services decorator' );
zion_core_test_assert( 1 === substr_count( $zion_core_contact_html, '<h1 ' ) && str_contains( $zion_core_contact_html, 'class="wpcf7' ) && str_contains( $zion_core_contact_html, 'name="your-message"' ), 'A single H1 and the real native CF7 form are rendered' );
zion_core_test_assert( ! str_contains( $zion_core_contact_content, '<canvas' ) && ! str_contains( $zion_core_contact_content, 'core/html' ) && ! str_contains( $zion_core_contact_content, 'core/shortcode' ), 'Contact stores neither Canvas markup nor a parallel form implementation' );
zion_core_test_assert( ! str_contains( $zion_core_contact_html, '&lt;a href' ) && str_contains( $zion_core_contact_html, 'href="mailto:contact@zion3d.ro"' ), 'Public Contact links remain real native paragraph links' );
zion_core_test_assert(
	get_post( $zion_core_contact_page->ID, ARRAY_A ) === $zion_core_contact_before && get_post_meta( $zion_core_contact_fixture->ID ) === $zion_core_contact_metadata && get_option( Zion_Theme_Settings::OPTION ) === $zion_core_contact_settings,
	'Rendering leaves page records, native CF7 properties and theme settings unchanged'
);
$zion_core_contact_required = array_filter( $zion_core_contact_form->scan_form_tags(), fn( $tag ) => $tag->is_required() );
zion_core_test_assert( 4 === count( $zion_core_contact_required ), 'CF7 itself owns the four required fields and their native validation rules' );
$zion_core_contact_block = parse_blocks( zion_theme_contact_form_block( $zion_core_contact_fixture->ID ) )[0];
zion_core_test_assert( str_contains( zion_theme_contact_form_fallback( '<native-form>', $zion_core_contact_block ), '<native-form>' ), 'A valid native hash resolves to the selected form instead of the fallback' );
zion_core_test_assert( str_contains( zion_theme_contact_form_fallback( '<missing-form>', array( 'attrs' => array( 'id' => PHP_INT_MAX ) ) ), 'mailto:' ), 'A deleted form provides useful direct contact instead of a raw shortcode' );
$zion_core_contact_shortcode = $GLOBALS['shortcode_tags']['contact-form-7'];
try {
	remove_shortcode( 'contact-form-7' );
	zion_core_test_assert( str_contains( do_blocks( $zion_core_contact_content ), 'zion-contact-form-fallback' ), 'Contact remains useful when CF7 is not active' );
} finally {
	add_shortcode( 'contact-form-7', $zion_core_contact_shortcode );
}
zion_core_test_assert( ! zion_theme_migration_native_blocks( parse_blocks( '<!-- wp:shortcode -->[arbitrary]<!-- /wp:shortcode -->' ) ), 'Allowing the registered CF7 selector does not enable arbitrary shortcode migration' );
do_action( 'wp_enqueue_scripts' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Integration exercises the real WordPress lifecycle.
zion_core_test_assert( wp_script_is( 'contact-form-7' ) && wp_style_is( 'contact-form-7' ), 'Forms rendered before wp_head enqueue native CF7 assets after registration' );
unset( $GLOBALS['zion_theme_contact_rendered_form'] );
zion_core_test_assert( false === zion_theme_contact_load_assets( true ), 'Contexts without rendered forms do not preload CF7 assets' );
try {
	wp_update_post(
		array(
			'ID'           => $zion_core_contact_page->ID,
			'post_content' => '<!-- wp:paragraph --><p>Authored Contact content.</p><!-- /wp:paragraph -->',
		)
	);
	require WP_CONTENT_DIR . '/zion-scripts/seed-contact.php';
	zion_core_test_assert( str_contains( get_post_field( 'post_content', $zion_core_contact_page->ID ), 'Authored Contact content.' ), 'Reseeding never overwrites an authored Contact page' );
} finally {
	wp_update_post(
		array(
			'ID'           => $zion_core_contact_page->ID,
			'post_content' => wp_slash( $zion_core_contact_before['post_content'] ),
		)
	);
}
