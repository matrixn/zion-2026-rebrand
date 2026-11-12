<?php
/**
 * Isolated synthetic CF7 fixture for the wp-env instances.
 *
 * @package Zion_Theme
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || 'local' !== wp_get_environment_type() || ! class_exists( 'WPCF7_ContactForm' ) ) {
	return;
}
require_once __DIR__ . '/contact-form-config.php';
$zion_theme_contact_fixture_post = get_page_by_path( 'zion-contact-demo', OBJECT, 'wpcf7_contact_form' );
if ( ! $zion_theme_contact_fixture_post ) {
	$zion_theme_contact_fixture_form = WPCF7_ContactForm::get_template( array( 'locale' => 'ro_RO' ) );
	$zion_theme_contact_fixture_form->set_title( 'Zion Contact Demo' );
	$zion_theme_contact_fixture_form->set_properties( zion_theme_contact_form_properties( $zion_theme_contact_fixture_form, 'recipient@example.test', 'wordpress@example.test' ) );
	$zion_theme_contact_fixture_form->save();
	$zion_theme_contact_fixture_id = $zion_theme_contact_fixture_form->id();
} else {
	$zion_theme_contact_fixture_id    = $zion_theme_contact_fixture_post->ID;
	$zion_theme_contact_fixture_form  = WPCF7_ContactForm::get_instance( $zion_theme_contact_fixture_id );
	$zion_theme_contact_fixture_props = zion_theme_contact_form_properties( $zion_theme_contact_fixture_form, 'recipient@example.test', 'wordpress@example.test' );
	if ( $zion_theme_contact_fixture_props !== $zion_theme_contact_fixture_form->get_properties() ) {
		$zion_theme_contact_fixture_form->set_properties( $zion_theme_contact_fixture_props );
		$zion_theme_contact_fixture_form->save();
	}
}
if ( ! get_page_by_path( 'contact' ) ) {
	$zion_theme_contact_fixture_page = wp_insert_post(
		wp_slash(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => 'contact',
				'post_title'   => 'Contact',
				'post_content' => zion_theme_contact_content( $zion_theme_contact_fixture_id ),
			)
		),
		true
	);
	if ( is_wp_error( $zion_theme_contact_fixture_page ) ) {
		WP_CLI::error( $zion_theme_contact_fixture_page->get_error_message() );
	}
}
$zion_theme_contact_mu_directory = WP_CONTENT_DIR . '/mu-plugins';
wp_mkdir_p( $zion_theme_contact_mu_directory );
// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Local-only fixture installs the guarded test transport.
file_put_contents( $zion_theme_contact_mu_directory . '/zion-contact-test-mail.php', '<?php require WP_CONTENT_DIR . "/zion-scripts/local-contact-mail.php";' );
