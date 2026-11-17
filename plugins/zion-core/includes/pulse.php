<?php
/**
 * Zion Pulse extension: software-only interactions and native WordPress integration.
 *
 * @package Zion_Core
 */

/** Load presentation only when a Pulse component is used. */
function zion_core_pulse_assets(): void {
	if ( ! function_exists( 'get_theme_file_uri' ) || ! is_readable( get_theme_file_path( 'assets/dist/pulse.css' ) ) || ! is_readable( get_theme_file_path( 'assets/blocks/pulse.js' ) ) ) {
		return;
	}
	$version       = wp_get_theme()->get( 'Version' );
	$style_version = $version . '-' . substr( hash_file( 'sha256', get_theme_file_path( 'assets/dist/pulse.css' ) ), 0, 12 );
	$js_version    = $version . '-' . substr( hash_file( 'sha256', get_theme_file_path( 'assets/blocks/pulse.js' ) ), 0, 12 );
	wp_enqueue_style( 'zion-pulse', get_theme_file_uri( 'assets/dist/pulse.css' ), array(), $style_version );
	wp_enqueue_script(
		'zion-pulse',
		get_theme_file_uri( 'assets/blocks/pulse.js' ),
		array(),
		$js_version,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}

/** Preload conditional styles in the head, avoiding late layout changes. */
function zion_core_pulse_enqueue(): void {
	$post = get_queried_object();
	if ( $post instanceof WP_Post && ( has_shortcode( $post->post_content, 'zion_pulse' ) || is_page( array( 'servicii', 'printare-3d' ) ) ) ) {
		zion_core_pulse_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'zion_core_pulse_enqueue', 25 );

/**
 * A whitelist of decorative and interactive components, with no external commands.
 *
 * @param array|string $attributes Shortcode attributes.
 * @return string
 */
function zion_core_pulse_component( $attributes = array() ): string {
	$attributes = shortcode_atts( array( 'type' => 'demo' ), $attributes, 'zion_pulse' );
	$type       = $attributes['type'];
	if ( ! is_string( $type ) || ! in_array( $type, array( 'hero', 'demo', 'modules', 'directory' ), true ) ) {
		return '';
	}
	$file = get_theme_file_path( 'pulse/' . $type . '.php' );
	if ( ! file_exists( $file ) ) {
		return '';
	}
	zion_core_pulse_assets();
	ob_start();
	include $file;
	return (string) ob_get_clean();
}
add_shortcode( 'zion_pulse', 'zion_core_pulse_component' );

/**
 * Add one native page link without rewriting an existing menu or removing children.
 *
 * @param array $block Parsed native block.
 * @return array
 */
function zion_core_pulse_navigation( array $block ): array {
	if ( 'core/navigation-submenu' !== ( $block['blockName'] ?? '' ) || 'Servicii' !== wp_strip_all_tags( $block['attrs']['label'] ?? '' ) ) {
		return $block;
	}
	$page = get_page_by_path( 'zion-pulse' );
	if ( ! $page || 'publish' !== $page->post_status || post_password_required( $page ) ) {
		return $block;
	}
	$url = get_permalink( $page );
	if ( str_contains( serialize_blocks( $block['innerBlocks'] ?? array() ), $url ) ) {
		return $block;
	}
	$block['innerBlocks'][]  = array(
		'blockName'    => 'core/navigation-link',
		'attrs'        => array(
			'label' => 'Zion Pulse · Automatizări',
			'url'   => $url,
			'kind'  => 'post-type',
			'type'  => 'page',
			'id'    => $page->ID,
		),
		'innerBlocks'  => array(),
		'innerHTML'    => '',
		'innerContent' => array(),
	);
	$block['innerContent'][] = null;
	return $block;
}
add_filter( 'render_block_data', 'zion_core_pulse_navigation', 25 );

/**
 * A reversible, published-only invitation in the existing Services directory.
 *
 * @param string $html Rendered group.
 * @param array  $block Parsed block.
 * @return string
 */
function zion_core_pulse_directory( string $html, array $block ): string {
	if ( is_admin() || ! is_page( array( 'servicii', 'printare-3d' ) ) || ! in_array( 'zion-overview-services', explode( ' ', $block['attrs']['className'] ?? '' ), true ) ) {
		return $html;
	}
	$page = get_page_by_path( 'zion-pulse' );
	if ( ! $page || 'publish' !== $page->post_status || str_contains( $html, 'zion-pulse-directory' ) ) {
		return $html;
	}
	return preg_replace( '~</div>\s*$~', zion_core_pulse_component( array( 'type' => 'directory' ) ) . '</div>', $html, 1 ) ?? $html;
}
add_filter( 'render_block_core/group', 'zion_core_pulse_directory', 35, 2 );

/** Output Service metadata only for the actual Pulse document, never an archive. */
function zion_core_pulse_schema(): void {
	if ( ! is_page( 'zion-pulse' ) || post_password_required() ) {
		return;
	}
	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Service',
		'@id'         => get_permalink() . '#service',
		'name'        => 'Zion Pulse',
		'serviceType' => 'Automatizări inteligente și sisteme interactive',
		'description' => 'Proiectăm și dezvoltăm automatizări electronice, sisteme interactive și soluții IoT personalizate. Combinăm hardware, software și fabricație 3D.',
		'url'         => get_permalink(),
		'provider'    => array(
			'@type'     => 'Organization',
			'name'      => 'Zion 3D',
			'url'       => home_url( '/' ),
			'telephone' => '+40770675597',
		),
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE ) . '</script>';
}
add_action( 'wp_head', 'zion_core_pulse_schema', 35 );

/** A scoped native meta box reuses the SEO fields already understood by Zion Theme. */
function zion_core_pulse_meta_box(): void {
	$post = get_post();
	if ( $post && ( 'zion-pulse' === $post->post_name || has_shortcode( $post->post_content, 'zion_pulse' ) ) ) {
		add_meta_box( 'zion-pulse-seo', 'Zion Pulse · SEO', 'zion_core_pulse_meta_fields', 'page', 'normal' );
	}
}
add_action( 'add_meta_boxes_page', 'zion_core_pulse_meta_box' );

/**
 * Native, capability-protected editorial fields; no duplicate head provider.
 *
 * @param WP_Post $post Current page.
 */
function zion_core_pulse_meta_fields( WP_Post $post ): void {
	wp_nonce_field( 'zion_pulse_seo', 'zion_pulse_nonce' );
	foreach ( array(
		'_yoast_wpseo_title'    => 'Titlu SEO Zion Pulse',
		'_yoast_wpseo_metadesc' => 'Descriere SEO Zion Pulse',
		'_zion_pulse_focus'     => 'Expresie principală Zion Pulse',
	) as $key => $label ) {
		echo '<p><label for="zion-pulse-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label><br><input class="widefat" id="zion-pulse-' . esc_attr( $key ) . '" name="zion_pulse_seo[' . esc_attr( $key ) . ']" value="' . esc_attr( get_post_meta( $post->ID, $key, true ) ) . '"></p>';
	}
}

/**
 * Save only explicitly submitted, authorized Pulse SEO fields.
 *
 * @param int $post_id Current post ID.
 */
function zion_core_pulse_save_meta( int $post_id ): void {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ||
		! isset( $_POST['zion_pulse_nonce'] ) || ! is_string( $_POST['zion_pulse_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zion_pulse_nonce'] ) ), 'zion_pulse_seo' ) ) {
		return;
	}
	$values = isset( $_POST['zion_pulse_seo'] ) && is_array( $_POST['zion_pulse_seo'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['zion_pulse_seo'] ) ) : array();
	foreach ( array( '_yoast_wpseo_title', '_yoast_wpseo_metadesc', '_zion_pulse_focus' ) as $key ) {
		if ( isset( $values[ $key ] ) && is_string( $values[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( $values[ $key ] ) );
		}
	}
}
add_action( 'save_post_page', 'zion_core_pulse_save_meta' );
