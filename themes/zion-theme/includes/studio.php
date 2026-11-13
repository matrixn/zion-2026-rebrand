<?php
/**
 * Zion Studio edits native WordPress entities through their REST controllers.
 *
 * @package Zion_Theme
 */

/**
 * Read the active theme's source of visual defaults.
 *
 * @return array
 */
function zion_theme_design_defaults(): array {
	return wp_json_file_decode( get_template_directory() . '/theme.json', array( 'associative' => true ) );
}

/**
 * Read Global Styles without creating a database record on GET.
 *
 * @return array
 */
function zion_theme_design_document(): array {
	$post = WP_Theme_JSON_Resolver::get_user_data_from_wp_global_styles( wp_get_theme(), false );
	$data = isset( $post['post_content'] ) ? json_decode( $post['post_content'], true ) : array();
	return is_array( $data ) ? $data : array();
}

/**
 * Current design and its canonical native revision.
 *
 * @return array
 */
function zion_theme_design_state(): array {
	$theme    = zion_theme_design_defaults();
	$document = zion_theme_design_document();
	return array(
		'registry' => Zion_Theme_Design::registry( $theme ),
		'document' => $document,
		'revision' => hash( 'sha256', get_stylesheet() . wp_json_encode( $document ) ),
	);
}

/**
 * Store typed patches in the existing wp_global_styles entity.
 *
 * @param WP_REST_Request $request Authorized REST request.
 * @return array|WP_Error
 */
function zion_theme_design_save( WP_REST_Request $request ): array|WP_Error {
	$state = zion_theme_design_state();
	if ( ! is_string( $request['revision'] ) || ! hash_equals( $state['revision'], $request['revision'] ) ) {
		return new WP_Error( 'zion_conflict', 'Designul s-a schimbat în altă sesiune. Reîncarcă înainte să salvezi.', array( 'status' => 409 ) );
	}
	if ( ! is_array( $request['values'] ) || strlen( wp_json_encode( $request['values'] ) ) > 65536 ) {
		return new WP_Error( 'zion_invalid_design', 'Date de design invalide.', array( 'status' => 400 ) );
	}
	if ( ! $request['values'] ) {
		return $state;
	}
	try {
		$document = Zion_Theme_Design::patch( $state['document'], $request['values'], zion_theme_design_defaults() );
	} catch ( InvalidArgumentException $error ) {
		return new WP_Error( 'zion_invalid_design', $error->getMessage(), array( 'status' => 400 ) );
	}
	$id       = WP_Theme_JSON_Resolver::get_user_global_styles_post_id();
	$internal = new WP_REST_Request( 'POST', '/wp/v2/global-styles/' . $id );
	$internal->set_body_params( array_intersect_key( $document, array_flip( array( 'settings', 'styles' ) ) ) );
	$response = rest_do_request( $internal );
	if ( $response->is_error() ) {
		return $response->as_error();
	}
	WP_Theme_JSON_Resolver::clean_cached_data();
	return zion_theme_design_state();
}

/**
 * Map authoring views to the native endpoints, not another content store.
 *
 * @return array
 */
function zion_theme_studio_types(): array {
	return array(
		'pages'      => array( 'Pagini', 'pages', 'page' ),
		'posts'      => array( 'Articole', 'posts', 'post' ),
		'parts'      => array( 'Header, footer și componente', 'template-parts', 'wp_template_part' ),
		'templates'  => array( 'Șabloane', 'templates', 'wp_template' ),
		'navigation' => array( 'Meniuri', 'navigation', 'wp_navigation' ),
	);
}

/**
 * Resolve an entity with the native controller and its capability checks.
 *
 * @param string $kind Allowed authoring view.
 * @param string $id Native ID.
 * @return array|WP_Error
 */
function zion_theme_studio_entity( string $kind, string $id ): array|WP_Error {
	$type = zion_theme_studio_types()[ $kind ] ?? null;
	if ( ! $type || ! $id || strlen( $id ) > 200 ) {
		return new WP_Error( 'zion_invalid_entity', 'Element necunoscut.', array( 'status' => 400 ) );
	}
	$request = new WP_REST_Request( 'GET', '/wp/v2/' . $type[1] . '/' . $id );
	$request->set_param( 'context', 'edit' );
	$response = rest_do_request( $request );
	if ( $response->is_error() ) {
		return $response->as_error();
	}
	return $response->get_data();
}

/**
 * A revision bound to all editable fields, including native scheduling data.
 *
 * @param array $entity Native REST entity.
 * @return string
 */
function zion_theme_studio_revision( array $entity ): string {
	$stable = array_intersect_key( $entity, array_flip( array( 'id', 'modified', 'date', 'status', 'template', 'featured_media' ) ) );
	foreach ( array( 'content', 'title', 'excerpt' ) as $key ) {
		$stable[ $key ] = $entity[ $key ]['raw'] ?? '';
	}
	return hash( 'sha256', wp_json_encode( $stable ) );
}

/**
 * Read and resolve pattern references for the editing canvas only.
 *
 * @param WP_REST_Request $request Authorized request.
 * @return array|WP_Error
 */
function zion_theme_studio_read( WP_REST_Request $request ): array|WP_Error {
	$entity = zion_theme_studio_entity( (string) $request['kind'], (string) $request['id'] );
	if ( is_wp_error( $entity ) ) {
		return $entity;
	}
	return array(
		'entity'   => $entity,
		'content'  => in_array( $request['kind'], array( 'parts', 'templates' ), true ) ? serialize_blocks( resolve_pattern_blocks( parse_blocks( $entity['content']['raw'] ?? '' ) ) ) : ( $entity['content']['raw'] ?? '' ),
		'revision' => zion_theme_studio_revision( $entity ),
	);
}

/**
 * Save to the same native entity after validation and conflict detection.
 *
 * @param WP_REST_Request $request Authorized request.
 * @return array|WP_Error
 */
function zion_theme_studio_save( WP_REST_Request $request ): array|WP_Error {
	$kind   = (string) $request['kind'];
	$id     = (string) $request['id'];
	$entity = zion_theme_studio_entity( $kind, $id );
	if ( is_wp_error( $entity ) ) {
		return $entity;
	}
	if ( ! is_string( $request['revision'] ) || ! hash_equals( zion_theme_studio_revision( $entity ), $request['revision'] ) ) {
		return new WP_Error( 'zion_conflict', 'Elementul s-a schimbat în altă sesiune. Reîncarcă înainte să salvezi.', array( 'status' => 409 ) );
	}
	$values  = $request['values'];
	$allowed = in_array( $kind, array( 'pages', 'posts' ), true ) ? array( 'content', 'title', 'date', 'status', 'featured_media', 'excerpt', 'template' ) : array( 'content', 'title' );
	if ( ! is_array( $values ) || array_diff( array_keys( $values ), $allowed ) || strlen( wp_json_encode( $values ) ) > 2097152 ) {
		return new WP_Error( 'zion_invalid_content', 'Câmpuri sau conținut nepermis.', array( 'status' => 400 ) );
	}
	$internal = new WP_REST_Request( 'POST', '/wp/v2/' . zion_theme_studio_types()[ $kind ][1] . '/' . $id );
	$internal->set_body_params( $values );
	$response = rest_do_request( $internal );
	if ( $response->is_error() ) {
		return $response->as_error();
	}
	return zion_theme_studio_read( $request );
}

/**
 * Authenticated native authoring bridge; native controllers still own writes.
 */
function zion_theme_register_studio_rest(): void {
	$permission = static fn() => current_user_can( 'edit_theme_options' );
	foreach ( array(
		'/design'   => array( 'zion_theme_design_state', 'zion_theme_design_save' ),
		'/document' => array( 'zion_theme_studio_read', 'zion_theme_studio_save' ),
	) as $route => $callbacks ) {
		$args = '/document' === $route ? array(
			'kind' => array(
				'type'     => 'string',
				'required' => true,
				'enum'     => array_keys( zion_theme_studio_types() ),
			),
			'id'   => array(
				'type'      => 'string',
				'required'  => true,
				'maxLength' => 200,
			),
		) : array();
		register_rest_route(
			'zion-theme/v1',
			$route,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => $callbacks[0],
					'permission_callback' => $permission,
					'args'                => $args,
				),
				array(
					'methods'             => 'POST',
					'callback'            => $callbacks[1],
					'permission_callback' => $permission,
					'args'                => $args,
				),
			)
		);
	}
}
add_action( 'rest_api_init', 'zion_theme_register_studio_rest' );

/**
 * Derive Zion button variables from the existing native style, only if explicit.
 */
function zion_theme_native_component_tokens(): void {
	$radius = zion_theme_design_document()['styles']['elements']['button']['border']['radius'] ?? null;
	if ( is_string( $radius ) && preg_match( '/^\d+(?:\.\d{1,3})?(?:px|rem)$/D', $radius ) ) {
		wp_add_inline_style( 'zion-theme', ':root{--zion-button-radius:' . $radius . ';}body .wp-site-blocks :is(.wp-element-button,.wc-block-components-button),.wp-site-blocks .zion-card-design .zion-card-actions :is(.wp-element-button,.wc-block-components-product-button__button){border-radius:var(--zion-button-radius);}' );
	}
}
add_action( 'wp_enqueue_scripts', 'zion_theme_native_component_tokens', 20 );

/**
 * Load WordPress authoring packages only in the relevant Zion admin sections.
 */
function zion_theme_enqueue_studio(): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Selects a read-only admin view.
	$section = isset( $_GET['section'] ) && is_string( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : '';
	if ( ! in_array( $section, array( 'design', 'studio' ), true ) ) {
		return;
	}
	wp_enqueue_media();
	$dependencies = array( 'wp-element', 'wp-components', 'wp-api-fetch' );
	$config       = array(
		'home'      => home_url( '/' ),
		'url'       => add_query_arg(
			array(
				'page'    => 'zion-theme',
				'section' => 'studio',
			),
			admin_url( 'themes.php' )
		),
		'timezone'  => wp_timezone_string(),
		'identity'  => current_user_can( 'manage_options' ),
		'types'     => zion_theme_studio_types(),
		'templates' => array(
			'pages' => wp_get_theme()->get_page_templates( null, 'page' ),
			'posts' => wp_get_theme()->get_page_templates( null, 'post' ),
		),
		'previews'  => array_map(
			static fn( $page ) => array(
				'id'    => $page->ID,
				'title' => $page->post_title,
				'url'   => get_permalink( $page ),
			),
			get_pages(
				array(
					'post_status' => 'publish',
					'number'      => 100,
				)
			)
		),
	);
	if ( 'studio' === $section ) {
		get_current_screen()->is_block_editor( true );
		$dependencies = array_merge( $dependencies, array( 'wp-blocks', 'wp-block-editor', 'wp-block-library', 'wp-format-library', 'wp-core-data', 'wp-data', 'wp-editor', 'wp-hooks', 'wp-media-utils' ) );
		wp_enqueue_style( 'wp-edit-blocks' );
		wp_enqueue_style( 'wp-block-editor' );
		wp_add_inline_script( 'wp-blocks', 'wp.blocks.unstable__bootstrapServerSideBlockDefinitions(' . wp_json_encode( get_block_editor_server_block_settings(), JSON_HEX_TAG | JSON_UNESCAPED_SLASHES ) . ');' );
		do_action( 'enqueue_block_editor_assets' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Invoke the native authoring lifecycle for WordPress and plugin blocks.
		wp_enqueue_registered_block_scripts_and_styles();
		$config['editor'] = get_block_editor_settings(
			array(
				'hasFixedToolbar' => true,
				'mediaUpload'     => null,
			),
			new WP_Block_Editor_Context( array( 'name' => 'zion-theme/studio' ) )
		);
	}
	wp_enqueue_style( 'wp-components' );
	wp_enqueue_script( 'zion-theme-studio', get_theme_file_uri( 'assets/admin/studio.js' ), $dependencies, wp_get_theme()->get( 'Version' ), true );
	wp_add_inline_script( 'zion-theme-studio', 'window.zionStudio=' . wp_json_encode( $config, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES ) . ';', 'before' );
}
