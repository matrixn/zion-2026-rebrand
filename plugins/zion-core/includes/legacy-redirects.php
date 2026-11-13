<?php
/**
 * Exact retired public routes found during the launch audit.
 *
 * @package Zion_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve only known retired routes, never arbitrary missing URLs.
 *
 * @param string $path Native request path relative to the site's home.
 * @return string
 */
function zion_core_legacy_redirect_target( string $path ): string {
	$path = '/' . trim( $path, '/' ) . '/';
	if ( '/shop/' === $path && function_exists( 'wc_get_page_id' ) ) {
		$id = wc_get_page_id( 'shop' );
	} else {
		$paths = array(
			'/servicii-printare-3d/' => 'printare-3d-la-comanda-zion-3d-print',
			'/termeni-si-condiitii/' => 'termeni-si-conditii',
		);
		$page  = isset( $paths[ $path ] ) ? get_page_by_path( $paths[ $path ] ) : null;
		$id    = $page ? $page->ID : 0;
	}
	if ( empty( $id ) || $id < 1 || 'publish' !== get_post_status( $id ) ) {
		return '';
	}
	$url = (string) get_permalink( $id );
	return wp_parse_url( $url, PHP_URL_PATH ) !== wp_parse_url( home_url( $path ), PHP_URL_PATH ) ? $url : '';
}

/**
 * Permanent same-site redirects only for GET/HEAD 404s, before WordPress guessing.
 */
function zion_core_legacy_redirect(): void {
	global $wp;
	if ( ! is_404() || ! in_array( $_SERVER['REQUEST_METHOD'] ?? '', array( 'GET', 'HEAD' ), true ) ) {
		return;
	}
	$path   = '/' . trim( (string) ( $wp->request ?? '' ), '/' ) . '/';
	$target = zion_core_legacy_redirect_target( $path );
	if ( $target ) {
		wp_safe_redirect( $target, 301, 'Zion Core' );
		exit;
	}
}
add_action( 'template_redirect', 'zion_core_legacy_redirect', 1 );
