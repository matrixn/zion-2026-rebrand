<?php
/**
 * A local-only Pulse page for automated browser validation.
 *
 * @package Zion_Core
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) ) {
	return;
}
$zion_core_pulse_page = get_page_by_path( 'zion-pulse' );
$zion_core_pulse_id   = wp_insert_post(
	array(
		'ID'           => $zion_core_pulse_page ? $zion_core_pulse_page->ID : 0,
		'post_title'   => 'Zion Pulse',
		'post_name'    => 'zion-pulse',
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_content' => file_get_contents( get_theme_file_path( 'assets/pulse/page.html' ) ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Versioned local fixture.
		'post_excerpt' => 'Dezvoltăm automatizări ESP32, Arduino, IoT, NFC/RFID și puzzle-uri interactive. Electronică, software și carcase 3D personalizate. Zion Pulse.',
	)
);
update_post_meta( $zion_core_pulse_id, '_yoast_wpseo_title', 'Automatizări ESP32 & Arduino | Sisteme Interactive | Zion Pulse' );
update_post_meta( $zion_core_pulse_id, '_yoast_wpseo_metadesc', 'Dezvoltăm automatizări ESP32, Arduino, IoT, NFC/RFID și puzzle-uri interactive. Electronică, software și carcase 3D personalizate. Zion Pulse.' );
WP_CLI::success( 'Local Pulse page prepared.' );
