<?php
/**
 * Native Contact composition and Contact Form 7 presentation integration.
 *
 * @package Zion_Theme
 */

/**
 * Build an editable CF7 selector using the plugin's own shortcode and identity.
 *
 * @param int $form_id Native CF7 post ID, explicitly selected by the author.
 * @return string
 */
function zion_theme_contact_form_block( int $form_id = 0 ): string {
	$form  = class_exists( 'WPCF7_ContactForm' ) && $form_id ? WPCF7_ContactForm::get_instance( $form_id ) : null;
	$attrs = $form ? array(
		'id'    => $form->id(),
		'hash'  => $form->hash(),
		'title' => str_replace( array( '"', '[', ']' ), '', $form->title() ),
	) : array();
	return get_comment_delimited_block_content(
		'contact-form-7/contact-form-selector',
		$attrs,
		'<div class="wp-block-contact-form-7-contact-form-selector">' . ( $form ? htmlspecialchars( $form->shortcode(), ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8' ) : '[contact-form-7]' ) . '</div>'
	);
}

/**
 * Native blocks; the plugin owns validation, submission and email delivery.
 *
 * @param int    $form_id Explicit native form selection.
 * @param string $address Existing public address, supplied during migration.
 * @return string
 */
function zion_theme_contact_content( int $form_id = 0, string $address = '' ): string {
	$hero     = zion_theme_service_group(
		'zion-overview-band zion-overview-hero zion-contact-hero',
		zion_theme_service_group(
			'zion-contact-hero-grid',
			zion_theme_service_group(
				'zion-contact-lead',
				zion_theme_service_text( 'CONTACT / ZION 3D', 0, 'zion-overview-label' ) .
				get_comment_delimited_block_content( 'core/heading', array( 'level' => 1 ), '<h1 class="wp-block-heading">O idee bună începe<br><em>cu o conversație.</em></h1>' ) .
				zion_theme_service_text( 'O piesă care îți lipsește. Un software care simplifică munca. O idee care încă își caută forma. Spune-ne de unde pornim.', 0, 'zion-contact-intro' ) .
				zion_theme_service_buttons(
					array(
						'Povestește-ne proiectul ↗' => '#scrie-ne',
						'Sună-ne ↗'                 => 'tel:+40770675597',
					)
				) .
				zion_theme_service_text( 'CLARIFICĂM CONTEXTUL. GĂSIM PRIMUL PAS.', 0, 'zion-contact-coordinate' )
			) .
			zion_theme_service_group(
				'zion-contact-signal',
				zion_theme_service_text( 'TU / IDEEA TA', 0, 'zion-contact-signal-label' ) .
				zion_theme_service_text( '↔', 0, 'zion-contact-signal-fallback' ) .
				zion_theme_service_text( 'ZION / URMĂTORUL PAS', 0, 'zion-contact-signal-caption' )
			)
		)
	);
	$channels = '';
	foreach (
		array(
			array( '01 / ÎN SCRIS', 'Un mesaj. O direcție.', 'contact@zion3d.ro ↗', 'mailto:contact@zion3d.ro', 'Poți include o schiță, un link sau câteva idei.' ),
			array( '02 / DIRECT', 'Mai simplu, la telefon.', '+40 770 675 597 ↗', 'tel:+40770675597', 'Ne găsești și pe WhatsApp.', 'https://wa.me/40770675597' ),
			array( '03 / AICI ȘI LA DISTANȚĂ', 'Râmnicu Vâlcea, România.', '', '', $address ? $address : 'Lucrăm local și colaborăm online, în funcție de proiect.' ),
		) as $channel
	) {
		$channels .= zion_theme_service_group(
			'zion-contact-channel',
			zion_theme_service_text( $channel[0], 0, 'zion-overview-label' ) .
			zion_theme_service_text( $channel[1], 3 ) .
			( $channel[2] ? get_comment_delimited_block_content( 'core/paragraph', array( 'className' => 'zion-contact-channel-link' ), '<p class="zion-contact-channel-link"><a href="' . esc_url( $channel[3] ) . '">' . esc_html( $channel[2] ) . '</a></p>' ) : '' ) .
			zion_theme_service_text( $channel[4] ) .
			( isset( $channel[5] ) ? zion_theme_service_buttons( array( 'WhatsApp ↗' => $channel[5] ) ) : '' )
		);
	}
	$channels = zion_theme_service_group( 'zion-overview-band zion-contact-channels', zion_theme_service_group( 'zion-contact-channel-grid', $channels ) );
	$brief    = '';
	foreach (
		array(
			'Ce vrei să obții?'        => 'Spune-ne problema sau rezultatul pe care îl ai în minte. Nu ai nevoie de o soluție tehnică deja aleasă.',
			'Ce ai la îndemână?'       => 'O schiță, dimensiuni, o fotografie sau un exemplu de aplicație ne ajută să înțelegem contextul. Poți include un link în mesaj.',
			'Ce contează pentru tine?' => 'Utilizarea, cantitatea, compatibilitatea sau o dată orientativă sunt detalii bune de la care să pornim.',
		) as $title => $copy
	) {
		$brief .= zion_theme_service_group( 'zion-contact-prompt', zion_theme_service_text( $title, 3 ) . zion_theme_service_text( $copy ) );
	}
	$form    = zion_theme_service_group(
		'zion-overview-band zion-contact-write',
		zion_theme_service_text( 'PRIMUL SEMNAL / FĂRĂ UN BRIEF PERFECT', 0, 'zion-overview-label' ) .
		zion_theme_service_text( 'Spune-ne ce ai în minte.', 2 ) .
		zion_theme_service_group(
			'zion-contact-write-grid',
			zion_theme_service_group( 'zion-contact-brief', $brief ) .
			zion_theme_service_group(
				'zion-contact-panel',
				zion_theme_service_text( 'DE AICI ÎNCEPE DIALOGUL', 0, 'zion-overview-label' ) .
				zion_theme_service_text( 'Ideea ta are loc aici.', 3 ) .
				zion_theme_service_text( 'Câmpurile marcate cu * sunt obligatorii. Folosește câte detalii îți sunt utile.', 0, 'zion-contact-form-help' ) .
				zion_theme_service_group(
					'zion-contact-console',
					zion_theme_service_text( 'CONTEXT / CONEXIUNE / CONVERSAȚIE', 0, 'zion-contact-progress' )
				) .
				zion_theme_contact_form_block( $form_id )
			)
		),
		'scrie-ne'
	);
	$process = zion_theme_service_group(
		'zion-overview-band zion-contact-process',
		zion_theme_service_text( 'DUPĂ PRIMUL MESAJ', 0, 'zion-overview-label' ) .
		zion_theme_service_text( 'O conversație, apoi un pas concret.', 2 ) .
		zion_theme_service_group( 'zion-contact-route', zion_theme_service_text( '01 / NE SCRII → 02 / CLARIFICĂM → 03 / ALEGEM DIRECȚIA', 0, 'zion-contact-route-fallback' ) ) .
		zion_theme_service_cards(
			array(
				'Ne scrii.'        => 'Pornim de la ce ai nevoie, chiar dacă ideea nu este încă complet conturată.',
				'Clarificăm.'      => 'Discutăm utilizarea, constrângerile și informațiile care mai sunt necesare.',
				'Alegem direcția.' => 'Stabilim împreună dacă și cum putem transforma ideea într-un proiect.',
			)
		)
	);
	return zion_theme_service_group( 'zion-services-overview zion-contact', $hero . $channels . $form . $process );
}

/**
 * Add only decorative markup during frontend rendering.
 *
 * @param string $html Native rendered group.
 * @param array  $block Parsed block.
 * @return string
 */
function zion_theme_contact_motion( string $html, array $block ): string {
	if ( is_admin() || wp_is_serving_rest_request() ) {
		return $html;
	}
	$classes = explode( ' ', $block['attrs']['className'] ?? '' );
	if ( in_array( 'zion-contact', $classes, true ) ) {
		$tag = new WP_HTML_Tag_Processor( $html );
		if ( $tag->next_tag() ) {
			$tag->set_attribute( 'data-zion-contact-services', wp_json_encode( array_map( fn( $service ) => $service[0], zion_theme_services() ) ) );
			$html = $tag->get_updated_html();
		}
	}
	foreach ( array( 'signal', 'console', 'route' ) as $type ) {
		if ( in_array( 'zion-contact-' . $type, $classes, true ) ) {
			zion_theme_enqueue_continuum();
			$canvas = '<canvas class="zion-spatial-canvas" data-zion-spatial="contact-' . esc_attr( $type ) . '" aria-hidden="true"></canvas>';
			return preg_replace( '~(</div>\s*)$~', $canvas . '$1', $html, 1 ) ?? $html;
		}
	}
	return $html;
}
add_filter( 'render_block_core/group', 'zion_theme_contact_motion', 28, 2 );

/**
 * Keep missing-form handling useful without exposing a raw shortcode.
 *
 * @param string $html Native selector output.
 * @param array  $block Parsed selector.
 * @return string
 */
function zion_theme_contact_form_fallback( string $html, array $block ): string {
	if ( is_admin() || wp_is_serving_rest_request() ) {
		return $html;
	}
	$form = null;
	if ( shortcode_exists( 'contact-form-7' ) ) {
		$form = ! empty( $block['attrs']['hash'] )
			? wpcf7_get_contact_form_by_hash( $block['attrs']['hash'] )
			: wpcf7_contact_form( $block['attrs']['id'] ?? 0 );
	}
	if ( ! $form ) {
		return '<p class="zion-contact-form-fallback">Ne poți scrie direct la <a href="mailto:contact@zion3d.ro">contact@zion3d.ro</a> sau ne poți suna la <a href="tel:+40770675597">+40 770 675 597</a>.</p>';
	}
	return $html;
}
add_filter( 'render_block_contact-form-7/contact-form-selector', 'zion_theme_contact_form_fallback', 10, 2 );

/**
 * Load native CF7 assets only after a form was actually rendered.
 * Block themes render their template before wp_head, including template parts.
 *
 * @param bool $load Plugin's asset preference.
 * @return bool
 */
function zion_theme_contact_load_assets( bool $load ): bool {
	return $load && ! empty( $GLOBALS['zion_theme_contact_rendered_form'] );
}
add_filter( 'wpcf7_load_js', 'zion_theme_contact_load_assets' );
add_filter( 'wpcf7_load_css', 'zion_theme_contact_load_assets' );

/**
 * Native CF7 enqueuing for any form actually rendered outside post content.
 *
 * @param string $output Shortcode result.
 * @param string $tag Shortcode name.
 * @return string
 */
function zion_theme_contact_rendered_assets( string $output, string $tag ): string {
	if ( 'contact-form-7' === $tag && ! is_admin() && ! wp_is_serving_rest_request() && str_contains( $output, 'class="wpcf7' ) ) {
		$GLOBALS['zion_theme_contact_rendered_form'] = true;
		zion_theme_contact_enqueue_rendered();
	}
	return $output;
}
add_filter( 'do_shortcode_tag', 'zion_theme_contact_rendered_assets', 10, 2 );

/** Native block themes may render their template before wp_head registers assets. */
function zion_theme_contact_enqueue_rendered(): void {
	if ( empty( $GLOBALS['zion_theme_contact_rendered_form'] ) || ! wp_script_is( 'contact-form-7', 'registered' ) ) {
		return;
	}
	if ( ! wpcf7_script_is() ) {
		wpcf7_enqueue_scripts();
	}
	if ( ! wpcf7_style_is() ) {
		wpcf7_enqueue_styles();
	}
}
add_action( 'wp_enqueue_scripts', 'zion_theme_contact_enqueue_rendered', 11 );
