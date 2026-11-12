<?php
/**
 * Capture synthetic CF7 fixture mail only; no external transport in E2E tests.
 *
 * @package Zion_Theme
 */

if ( ! defined( 'ABSPATH' ) || 'local' !== wp_get_environment_type() ) {
	return;
}
add_filter(
	'pre_wp_mail',
	static function ( $pre, array $mail ) {
		$form = class_exists( 'WPCF7_ContactForm' ) ? WPCF7_ContactForm::get_current() : null;
		if ( ! $form || 'zion-contact-demo' !== $form->name() || 'recipient@example.test' !== $mail['to'] ) {
			return $pre;
		}
		$uploads = wp_upload_dir();
		$path    = $uploads['basedir'] . '/zion-contact-test-mail.json';
		wp_mkdir_p( $uploads['basedir'] );
		$json = wp_json_encode( $mail );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Synthetic local-only capture, never installed on staging/production.
		if ( file_put_contents( $path, $json, LOCK_EX ) !== strlen( $json ) ) {
			return false;
		}
		return ! str_contains( $mail['message'], 'ZION-QA-FAIL' );
	},
	10,
	2
);
