<?php
/**
 * Learning illustrations remain isolated from production data and protected copy.
 *
 * @package Zion_Core
 */

( static function (): void {
	wp_dequeue_script( 'zion-learning-lab' );
	do_blocks( '<!-- wp:paragraph --><p>Ordinary article.</p><!-- /wp:paragraph -->' );
	zion_core_test_assert( ! wp_script_is( 'zion-learning-lab', 'enqueued' ), 'Ordinary article blocks do not request the learning-lab script' );
	$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'zion-theme/learning-story' );
	zion_core_test_assert( $pattern && zion_theme_migration_native_blocks( parse_blocks( $pattern['content'] ) ), 'Learning story pattern uses native blocks and the existing editorial component' );
	$first  = do_blocks( '<!-- wp:zion/editorial {"part":"learning_lab"} /-->' );
	$second = do_blocks( '<!-- wp:zion/editorial {"part":"learning_lab"} /-->' );
	preg_match_all( '/\bid="([^"]+)"/', $first . $second, $ids );
	zion_core_test_assert( 6 === count( $ids[1] ) && count( $ids[1] ) === count( array_unique( $ids[1] ) ), 'Multiple learning illustrations have independent accessible panel identifiers' );
	zion_core_test_assert( wp_script_is( 'zion-learning-lab', 'enqueued' ) && ! str_contains( $first, '<script' ), 'Learning renderer enqueues its own script rather than storing executable article markup' );
	zion_core_test_assert( 3 === substr_count( $first, 'data-learning-step=' ) && str_contains( $first, 'aria-live="polite"' ) && str_contains( $first, 'data-learning-reset disabled' ), 'Learning controls expose progressive fallback, three stages and a polite status region' );
	zion_core_test_assert( ! str_contains( $first, '<h1' ) && str_contains( $first, 'Ilustrație interactivă schematică' ), 'Learning fragment preserves the article hierarchy and labels the visual as schematic' );
	zion_core_test_assert( str_contains( $pattern['content'], 'Înlocuiește acest text' ) && ! str_contains( $pattern['content'], '@' ), 'Reusable learning pattern contains editorial placeholders rather than partner or customer data' );
} )();
