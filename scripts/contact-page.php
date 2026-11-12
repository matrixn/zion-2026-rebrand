<?php
/**
 * Preview/apply the Contact composition and repair an empty selected CF7 form.
 *
 * Usage: wp eval-file scripts/contact-page.php FORM_ID [apply BACKUP_JSON]
 * Backup must include page/form records and metadata, outside the public root.
 *
 * @package Zion_Theme
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || 'staging' !== wp_get_environment_type() || in_array( wp_parse_url( home_url(), PHP_URL_HOST ), array( 'zion3d.ro', 'www.zion3d.ro' ), true ) ) {
	exit( 1 );
}
require_once __DIR__ . '/contact-form-config.php';
$zion_theme_contact_id    = absint( $args[0] ?? 0 );
$zion_theme_contact_page  = get_page_by_path( 'contact' );
$zion_theme_contact_form  = class_exists( 'WPCF7_ContactForm' ) ? WPCF7_ContactForm::get_instance( $zion_theme_contact_id ) : null;
$zion_theme_contact_apply = 'apply' === ( $args[1] ?? '' );
if ( ! $zion_theme_contact_page || ! $zion_theme_contact_form || ! wp_is_block_theme() ) {
	WP_CLI::error( 'An existing Contact page, selected CF7 form and block theme are required.' );
}
$zion_theme_contact_before = $zion_theme_contact_form->get_properties();
$zion_theme_contact_mail   = $zion_theme_contact_before['mail'] ?? array();
$zion_theme_contact_repair = '' === trim( $zion_theme_contact_before['form'] ?? '' ) || empty( $zion_theme_contact_mail['sender'] ) || empty( $zion_theme_contact_mail['subject'] ) || empty( $zion_theme_contact_mail['body'] );
if ( $zion_theme_contact_repair ) {
	$zion_theme_contact_props = zion_theme_contact_form_properties(
		$zion_theme_contact_form,
		(string) ( $zion_theme_contact_mail['recipient'] ?? '' ),
		'wordpress@' . wp_parse_url( home_url(), PHP_URL_HOST )
	);
	if ( '' !== trim( $zion_theme_contact_before['form'] ?? '' ) ) {
		$zion_theme_contact_props['form'] = $zion_theme_contact_before['form'];
	}
	$zion_theme_contact_form->set_properties( $zion_theme_contact_props );
	$zion_theme_contact_form->set_locale( 'ro_RO' );
}
$zion_theme_contact_validator = new WPCF7_ConfigValidator( $zion_theme_contact_form );
$zion_theme_contact_validator->validate();
if ( ! $zion_theme_contact_validator->is_valid() ) {
	WP_CLI::error( 'The proposed CF7 configuration still contains errors: ' . $zion_theme_contact_validator->count_errors() );
}
// Preserve the existing public postal paragraph rather than inventing an address.
$zion_theme_contact_address = zion_theme_contact_existing_address( $zion_theme_contact_page->post_content );
if ( str_contains( $zion_theme_contact_page->post_content, 'zion-contact-channel-grid' ) ) {
	// A repeated apply preserves already-authored Contact content.
	$zion_theme_contact_content = $zion_theme_contact_page->post_content;
} else {
	$zion_theme_contact_content = zion_theme_contact_content( $zion_theme_contact_id, $zion_theme_contact_address );
}
$zion_theme_contact_changed = $zion_theme_contact_repair || $zion_theme_contact_page->post_content !== $zion_theme_contact_content;
WP_CLI::line(
	wp_json_encode(
		array(
			'mode'                 => $zion_theme_contact_apply ? 'apply' : 'preview',
			'page_id'              => $zion_theme_contact_page->ID,
			'form_id'              => $zion_theme_contact_id,
			'repair_form'          => $zion_theme_contact_repair,
			'configuration_errors' => $zion_theme_contact_validator->count_errors(),
			'recipient_unchanged'  => ( $zion_theme_contact_form->prop( 'mail' )['recipient'] ?? '' ) === ( $zion_theme_contact_mail['recipient'] ?? '' ),
			'before_sha256'        => hash( 'sha256', $zion_theme_contact_page->post_content ),
			'after_sha256'         => hash( 'sha256', $zion_theme_contact_content ),
			'changed'              => $zion_theme_contact_changed,
		)
	)
);
if ( ! $zion_theme_contact_apply || ! $zion_theme_contact_changed ) {
	return;
}
$zion_theme_contact_backup_path = realpath( $args[2] ?? '' );
if ( ! $zion_theme_contact_backup_path || str_starts_with( $zion_theme_contact_backup_path, trailingslashit( realpath( ABSPATH ) ) ) ) {
	WP_CLI::error( 'A private backup outside the public root is required before apply.' );
}
// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Privileged CLI reads its explicit private backup.
$zion_theme_contact_backup = json_decode( file_get_contents( $zion_theme_contact_backup_path ), true );
foreach ( array( $zion_theme_contact_page->ID, $zion_theme_contact_id ) as $zion_theme_contact_record_id ) {
	$zion_theme_contact_saved = $zion_theme_contact_backup['records'][ $zion_theme_contact_record_id ] ?? null;
	if ( ! $zion_theme_contact_saved || get_post( $zion_theme_contact_record_id, ARRAY_A ) !== $zion_theme_contact_saved['post'] || get_post_meta( $zion_theme_contact_record_id ) !== $zion_theme_contact_saved['meta'] ) {
		WP_CLI::error( 'The backup does not match the exact current page/form snapshot.' );
	}
}
wp_save_post_revision( $zion_theme_contact_page->ID );
if ( $zion_theme_contact_repair ) {
	$zion_theme_contact_form->save();
	$zion_theme_contact_validator->save();
}
$zion_theme_contact_result = wp_update_post(
	array(
		'ID'           => $zion_theme_contact_page->ID,
		'post_content' => wp_slash( $zion_theme_contact_content ),
	),
	true
);
if ( is_wp_error( $zion_theme_contact_result ) ) {
	WP_CLI::error( $zion_theme_contact_result->get_error_message() );
}
$zion_theme_contact_template = get_post_meta( $zion_theme_contact_page->ID, '_wp_page_template', true );
if ( $zion_theme_contact_template && 'default' !== $zion_theme_contact_template ) {
	update_post_meta( $zion_theme_contact_page->ID, '_wp_page_template', 'default' );
}
WP_CLI::success( 'Contact composition applied with native CF7; permalink and unrelated records retained.' );
