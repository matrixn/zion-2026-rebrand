<?php
/**
 * Apply an authoring preset through CF7 APIs, never during frontend requests.
 *
 * @package Zion_Theme
 */

/**
 * Extract the existing postal paragraph, including nested native groups.
 *
 * @param string $content Existing Contact block content.
 * @return string
 */
function zion_theme_contact_existing_address( string $content ): string {
	$walk = static function ( array $blocks ) use ( &$walk ): string {
		foreach ( $blocks as $block ) {
			if ( 'core/paragraph' === $block['blockName'] && str_contains( $block['innerHTML'], 'Independen' ) ) {
				return html_entity_decode( trim( wp_strip_all_tags( str_replace( array( '<br>', '<br/>', '<br />' ), ', ', $block['innerHTML'] ) ) ), ENT_QUOTES, 'UTF-8' );
			}
			$address = $walk( $block['innerBlocks'] ?? array() );
			if ( '' !== $address ) {
				return $address;
			}
		}
		return '';
	};
	return $walk( parse_blocks( $content ) );
}

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
