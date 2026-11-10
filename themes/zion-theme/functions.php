<?php
/**
 * Zion Theme setup.
 *
 * @package Zion_Theme
 */

defined( 'ABSPATH' ) || exit;

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
