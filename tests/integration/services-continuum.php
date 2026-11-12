<?php
/**
 * Native service compositions gain decoration without persistence changes.
 *
 * @package Zion_Core
 */

$zion_core_service_settings = get_option( Zion_Theme_Settings::OPTION );
$zion_core_service_pages    = get_posts(
	array(
		'post_type'   => 'page',
		'numberposts' => -1,
	)
);
$zion_core_service_before   = array_map( fn( $page ) => get_post( $page->ID, ARRAY_A ), $zion_core_service_pages );
$zion_core_service_sources  = array( 'directory' => zion_theme_services_overview() );
foreach ( array_keys( zion_theme_services() ) as $zion_core_service_key ) {
	$zion_core_service_sources[ $zion_core_service_key ] = zion_theme_service_landing( $zion_core_service_key );
	zion_core_test_assert( zion_theme_continuum_service_key( zion_theme_service_paths()[ $zion_core_service_key ] ) === $zion_core_service_key, 'Canonical service URL resolves ' . $zion_core_service_key );
}
foreach ( $zion_core_service_sources as $zion_core_service_key => $zion_core_service_source ) {
	$zion_core_service_rendered = do_blocks( $zion_core_service_source );
	zion_core_test_assert(
		( 'directory' === $zion_core_service_key ? 10 : 3 ) === substr_count( $zion_core_service_rendered, 'data-zion-spatial="service-' ) &&
		str_contains( $zion_core_service_rendered, 'data-zion-service="' . $zion_core_service_key . '"' ),
		'Contextual service scenes render for ' . $zion_core_service_key
	);
	zion_core_test_assert( ! str_contains( $zion_core_service_source, '<canvas' ) && ! str_contains( $zion_core_service_source, 'core/html' ), 'Saved service blocks remain native with editorial fallback: ' . $zion_core_service_key );
}
zion_core_test_assert(
	array_map( fn( $page ) => get_post( $page->ID, ARRAY_A ), $zion_core_service_pages ) === $zion_core_service_before && get_option( Zion_Theme_Settings::OPTION ) === $zion_core_service_settings,
	'All native page records and theme settings are preserved by service rendering'
);
$zion_core_service_plain = '<div class="wp-block-group"><p>Editorial text.</p></div>';
zion_core_test_assert( zion_theme_services_continuum( $zion_core_service_plain, array( 'attrs' => array() ) ) === $zion_core_service_plain, 'Unrelated groups remain untouched' );
$zion_core_service_external = '<div><p class="zion-overview-icon">◇</p><a href="https://external.example/printare-3d/">Service</a></div>';
zion_core_test_assert( zion_theme_services_continuum( $zion_core_service_external, array( 'attrs' => array( 'className' => 'zion-overview-card' ) ) ) === $zion_core_service_external, 'External service-like links do not gain contextual previews' );
zion_core_test_assert( '' === zion_theme_continuum_service_key( 'unknown-service' ), 'Unknown service routes retain the original presentation' );
