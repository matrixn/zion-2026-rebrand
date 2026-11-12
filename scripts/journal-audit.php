<?php
/**
 * Verify a private staging editorial snapshot after a presentation-only release.
 *
 * Usage: wp eval-file scripts/journal-audit.php /private/editorial-before.json
 *
 * @package Zion_Theme
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || 'staging' !== wp_get_environment_type() || 'zion-stage.on-forge.com' !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
	exit( 1 );
}
$zion_theme_journal_path = realpath( $args[0] ?? '' );
if ( ! $zion_theme_journal_path || str_starts_with( $zion_theme_journal_path, trailingslashit( realpath( ABSPATH ) ) ) ) {
	WP_CLI::error( 'An explicit private snapshot outside public is required.' );
}
// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Read-only privileged CLI reads its explicit private snapshot.
$zion_theme_journal_before = json_decode( file_get_contents( $zion_theme_journal_path ), true );
if ( ! isset( $zion_theme_journal_before['records'], $zion_theme_journal_before['comments'], $zion_theme_journal_before['terms'], $zion_theme_journal_before['settings'], $zion_theme_journal_before['reading'] ) ) {
	WP_CLI::error( 'Invalid editorial snapshot.' );
}
foreach ( $zion_theme_journal_before['records'] as $zion_theme_journal_id => $zion_theme_journal_saved ) {
	$zion_theme_journal_post = get_post( $zion_theme_journal_id );
	if ( ! $zion_theme_journal_post ) {
		WP_CLI::error( 'Editorial record missing: ' . $zion_theme_journal_id );
	}
	$zion_theme_journal_current = array(
		'post'  => get_post( $zion_theme_journal_id, ARRAY_A ),
		'meta'  => get_post_meta( $zion_theme_journal_id ),
		'terms' => wp_get_object_terms(
			$zion_theme_journal_id,
			get_object_taxonomies( $zion_theme_journal_post->post_type ),
			array(
				'fields'  => 'all',
				'orderby' => 'term_id',
			)
		),
	);
	if ( json_decode( wp_json_encode( $zion_theme_journal_current ), true ) !== $zion_theme_journal_saved ) {
		WP_CLI::error( 'Editorial content, metadata or terms changed: ' . $zion_theme_journal_id );
	}
}
$zion_theme_journal_comments = array();
foreach ( get_comments(
	array(
		'status'  => 'all',
		'number'  => 0,
		'orderby' => 'comment_ID',
		'order'   => 'ASC',
	)
) as $zion_theme_journal_comment ) {
	$zion_theme_journal_comments[ $zion_theme_journal_comment->comment_ID ] = array(
		'comment' => get_object_vars( $zion_theme_journal_comment ),
		'meta'    => get_comment_meta( $zion_theme_journal_comment->comment_ID ),
	);
}
$zion_theme_journal_checks = array(
	'comments' => $zion_theme_journal_comments,
	'terms'    => get_terms(
		array(
			'taxonomy'   => array( 'category', 'post_tag' ),
			'hide_empty' => false,
			'orderby'    => 'term_id',
		)
	),
	'settings' => get_option( 'zion_theme_settings' ),
	'reading'  => array( get_option( 'show_on_front' ), get_option( 'page_on_front' ), get_option( 'page_for_posts' ), get_option( 'posts_per_page' ), get_option( 'permalink_structure' ) ),
);
foreach ( $zion_theme_journal_checks as $zion_theme_journal_key => $zion_theme_journal_value ) {
	if ( json_decode( wp_json_encode( $zion_theme_journal_value ), true ) !== $zion_theme_journal_before[ $zion_theme_journal_key ] ) {
		WP_CLI::error( 'Editorial state changed: ' . $zion_theme_journal_key );
	}
}
WP_CLI::line(
	wp_json_encode(
		array(
			'mode'                                 => 'read-only',
			'records_preserved'                    => count( $zion_theme_journal_before['records'] ),
			'comments_preserved'                   => count( $zion_theme_journal_comments ),
			'theme'                                => wp_get_theme()->get( 'Version' ),
			'settings_terms_and_reading_preserved' => true,
		)
	)
);
WP_CLI::success( 'Editorial snapshot preserved; no content, URL, taxonomy, SEO or comment migration required.' );
