<?php
/**
 * Homepage decoration must not write editorial or commercial data.
 *
 * @package Zion_Core
 */

$zion_core_home_id       = (int) get_option( 'page_on_front' );
$zion_core_home_before   = get_post( $zion_core_home_id, ARRAY_A );
$zion_core_home_settings = get_option( Zion_Theme_Settings::OPTION );
ob_start();
require get_theme_file_path( 'patterns/home.php' );
$zion_core_home_blocks = ob_get_clean();
$zion_core_home_html   = do_blocks( $zion_core_home_blocks );
zion_core_test_assert( 6 === substr_count( $zion_core_home_html, 'data-zion-spatial="home-' ), 'Homepage renders six contextual Continuum illustrations' );
zion_core_test_assert( ! str_contains( $zion_core_home_blocks, '<canvas' ), 'Saved homepage pattern retains native editable content and SVG fallback' );
zion_core_test_assert( str_contains( $zion_core_home_html, '<svg aria-hidden="true"' ) && str_contains( $zion_core_home_html, 'zion-home-products' ), 'Homepage keeps the decorative SVG fallback and native product section' );
zion_core_test_assert( get_post( $zion_core_home_id, ARRAY_A ) === $zion_core_home_before && get_option( Zion_Theme_Settings::OPTION ) === $zion_core_home_settings, 'Rendering homepage motion leaves saved content and theme settings unchanged' );
$zion_core_home_plain = '<div class="wp-block-group"><p>Unrelated editorial copy.</p></div>';
zion_core_test_assert( zion_theme_home_continuum( $zion_core_home_plain, array( 'blockName' => 'core/group' ) ) === $zion_core_home_plain, 'Unrelated groups do not gain illustration markup' );
