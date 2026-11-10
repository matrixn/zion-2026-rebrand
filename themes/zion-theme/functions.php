<?php
/**
 * Zion Theme setup.
 *
 * @package Zion_Theme
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/includes/class-zion-theme-settings.php';
require_once __DIR__ . '/includes/settings-runtime.php';
if ( is_admin() ) {
	require_once __DIR__ . '/includes/control-center.php';
}

/**
 * Declare theme support and load component styles in the editor.
 */
function zion_theme_setup(): void {
	add_theme_support( 'woocommerce' );
	add_editor_style( 'assets/dist/zion.css' );
}
add_action( 'after_setup_theme', 'zion_theme_setup' );

/**
 * Load the built component stylesheet.
 */
function zion_theme_enqueue_styles(): void {
	$theme = wp_get_theme();
	wp_enqueue_style( 'zion-theme', get_theme_file_uri( 'assets/dist/zion.css' ), array(), $theme->get( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'zion_theme_enqueue_styles' );

/**
 * Get the published page selected as the posts index.
 *
 * @return string
 */
function zion_theme_journal_url(): string {
	$page_id = (int) get_option( 'page_for_posts' );
	return $page_id && 'publish' === get_post_status( $page_id ) ? (string) get_permalink( $page_id ) : '';
}

/**
 * Resolve a storefront page, with a homepage fallback on fresh installs.
 *
 * @param string $slug Storefront page slug.
 * @return string
 */
function zion_theme_shop_url( string $slug ): string {
	$page = get_page_by_path( $slug );
	if ( $page && 'publish' === $page->post_status ) {
		return (string) get_permalink( $page );
	}

	return home_url( '/#magazine' );
}
