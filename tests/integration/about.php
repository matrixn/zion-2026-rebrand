<?php
/**
 * Validate the editable About page on synthetic local data.
 *
 * @package Zion_Core
 */

$zion_core_about_content = zion_theme_about_content();
require_once WP_CONTENT_DIR . '/zion-scripts/migration-plan.php';
zion_core_test_assert( zion_theme_migration_native_blocks( parse_blocks( $zion_core_about_content ) ), 'About content uses registered native editable blocks only' );
zion_core_test_assert( 1 === substr_count( $zion_core_about_content, '<h1 ' ), 'About content contains one primary heading' );
zion_core_test_assert( ! str_contains( $zion_core_about_content, 'href=""' ) && ! str_contains( $zion_core_about_content, 'src=' ), 'About links are nonempty and no invented portrait is loaded' );
zion_core_test_assert( file_exists( get_theme_file_path( 'templates/page-despre-noi.html' ) ), 'About uses a native slug template with editable post content' );
$zion_core_about_before = str_replace( zion_theme_about_forge(), '', $zion_core_about_content );
$zion_core_about_before = str_replace( 'Mai întâi, curiozitatea.', 'Our authored heading.', $zion_core_about_before );
$zion_core_about_after  = zion_theme_about_add_forge( $zion_core_about_before );
zion_core_test_assert( str_contains( $zion_core_about_after, 'Our authored heading.' ) && 1 === substr_count( $zion_core_about_after, 'class="wp-block-group zion-overview-band zion-about-forge"' ), 'Motion upgrade retains editorial changes and inserts one native scene' );
zion_core_test_assert( zion_theme_about_add_forge( $zion_core_about_after ) === $zion_core_about_after, 'Motion upgrade is idempotent' );
zion_core_test_assert( zion_theme_migration_native_blocks( parse_blocks( $zion_core_about_after ) ), 'Motion insertion remains native and editable' );
zion_core_test_assert( 'Unrelated content.' === zion_theme_about_add_forge( 'Unrelated content.' ), 'Motion upgrade leaves unrelated content untouched' );
$zion_core_about_page = get_page_by_path( 'despre-noi' );
zion_core_test_assert( $zion_core_about_page && has_blocks( $zion_core_about_page->post_content ), 'Local seed creates an editable About page' );
$zion_core_about_original = $zion_core_about_page->post_content;
try {
	wp_update_post(
		array(
			'ID'           => $zion_core_about_page->ID,
			'post_content' => '<!-- wp:paragraph --><p>Editorial modification retained.</p><!-- /wp:paragraph -->',
		)
	);
	require WP_CONTENT_DIR . '/zion-scripts/seed-library.php';
	zion_core_test_assert( str_contains( get_post_field( 'post_content', $zion_core_about_page->ID ), 'Editorial modification retained.' ), 'Reseeding preserves authored About content' );
} finally {
	wp_update_post(
		wp_slash(
			array(
				'ID'           => $zion_core_about_page->ID,
				'post_content' => $zion_core_about_original,
			)
		)
	);
}
