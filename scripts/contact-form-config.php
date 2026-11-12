<?php
/**
 * Apply an authoring preset through CF7 APIs, never during frontend requests.
 *
 * @package Zion_Theme
 */

/**
 * Build native CF7 properties while the deployment supplies site-specific mail.
 *
 * @param WPCF7_ContactForm $form Selected native form.
 * @param string            $recipient Existing recipient, supplied outside Git.
 * @param string            $sender Site-domain sender.
 * @return array
 * @throws InvalidArgumentException When site-specific mail addresses are invalid.
 */
function zion_theme_contact_form_properties( WPCF7_ContactForm $form, string $recipient, string $sender ): array {
	if ( ! is_email( $recipient ) || ! is_email( $sender ) ) {
		throw new InvalidArgumentException( 'Valid site-specific mail addresses are required.' );
	}
	$path              = dirname( __DIR__ ) . '/config/contact-form.php';
	$preset            = require is_file( $path ) ? $path : WP_CONTENT_DIR . '/zion-config/contact-form.php';
	$options           = array_map( fn( $service ) => '"' . str_replace( '"', '', $service[0] ) . '"', zion_theme_services() );
	$privacy           = get_privacy_policy_url();
	$privacy           = $privacy ? $privacy : home_url( '/politica-de-confidentialitate/' );
	$preset['form']    = str_replace( array( '{{services}}', '{{privacy}}' ), array( implode( ' ', $options ), esc_url( $privacy ) ), $preset['form'] );
	$props             = $form->get_properties();
	$props['form']     = $preset['form'];
	$props['mail']     = array_merge(
		$props['mail'] ?? array(),
		$preset['mail'],
		array(
			'recipient' => $recipient,
			'sender'    => 'Zion <' . $sender . '>',
		)
	);
	$props['messages'] = array_merge( $props['messages'] ?? array(), $preset['messages'] );
	return $props;
}
