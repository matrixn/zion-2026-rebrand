<?php
/**
 * Zion Control Center admin screen and authorized mutations.
 *
 * @package Zion_Theme
 */

/**
 * Register the single option with the Settings API.
 */
function zion_theme_register_control_settings(): void {
	register_setting(
		'zion_theme_control',
		Zion_Theme_Settings::OPTION,
		array(
			'type'              => 'array',
			'default'           => Zion_Theme_Settings::empty_config(),
			'sanitize_callback' => 'zion_theme_sanitize_control_option',
		)
	);
}
add_action( 'admin_init', 'zion_theme_register_control_settings' );

/**
 * Validate every Settings API write.
 *
 * @param mixed $input Option value.
 * @return mixed
 */
function zion_theme_sanitize_control_option( mixed $input ): mixed {
	try {
		return Zion_Theme_Settings::normalize( $input );
	} catch ( InvalidArgumentException $error ) {
		add_settings_error( Zion_Theme_Settings::OPTION, 'invalid', $error->getMessage() );
		return get_option( Zion_Theme_Settings::OPTION, Zion_Theme_Settings::empty_config() );
	}
}

/**
 * Register Appearance → Zion Theme and load assets only there.
 */
function zion_theme_register_control_page(): void {
	$page = add_theme_page( 'Zion Control Center', 'Zion Theme', 'edit_theme_options', 'zion-theme', 'zion_theme_render_control_page' );
	add_action(
		'load-' . $page,
		static function (): void {
			wp_enqueue_script( 'zion-theme-control', get_theme_file_uri( 'assets/admin/control-center.js' ), array(), wp_get_theme()->get( 'Version' ), true );
		}
	);
}
add_action( 'admin_menu', 'zion_theme_register_control_page' );

/**
 * Navigation sections for the control screen.
 *
 * @return array
 */
function zion_theme_control_sections(): array {
	return array(
		'general'     => 'General',
		'design'      => 'Identitate și design',
		'header'      => 'Header și navigație',
		'footer'      => 'Footer',
		'woo'         => 'WooCommerce global',
		'3d'          => 'Magazin 3D',
		'software'    => 'Magazin Software',
		'cards'       => 'Carduri produse',
		'product'     => 'Pagini individuale produse',
		'blog'        => 'Blog și articole',
		'pages'       => 'Pagini standard',
		'performance' => 'Performanță',
		'tools'       => 'Instrumente',
	);
}

/**
 * Build a link to a Control Center section.
 *
 * @param string $section Section key.
 * @param int    $id Optional override ID.
 * @return string
 */
function zion_theme_control_url( string $section = 'general', int $id = 0 ): string {
	$args = array(
		'page'    => 'zion-theme',
		'section' => $section,
	);
	if ( $id ) {
		$args['item'] = $id;
	}
	return add_query_arg( $args, admin_url( 'themes.php' ) );
}

/**
 * Return the type for a manageable override item.
 *
 * @param int $id Item ID.
 * @return string
 */
function zion_theme_control_item_type( int $id ): string {
	$type = $id > 0 ? get_post_type( $id ) : false;
	return in_array( $type, Zion_Theme_Settings::ITEM_TYPES, true ) && current_user_can( 'edit_post', $id ) ? $type : '';
}

/**
 * Add an override shortcut to eligible rows in the posts table.
 *
 * @param array   $actions Existing actions.
 * @param WP_Post $post Item.
 * @return array
 */
function zion_theme_control_row_actions( array $actions, WP_Post $post ): array {
	if ( current_user_can( 'edit_theme_options' ) && zion_theme_control_item_type( $post->ID ) ) {
		$actions['zion_theme'] = '<a href="' . esc_url( zion_theme_control_url( 'tools', $post->ID ) ) . '">Setări Zion</a>';
	}
	return $actions;
}
add_filter( 'page_row_actions', 'zion_theme_control_row_actions', 10, 2 );
add_filter( 'post_row_actions', 'zion_theme_control_row_actions', 10, 2 );

/**
 * Create an optimistic revision to detect forms opened before another save.
 *
 * @return string
 */
function zion_theme_control_revision(): string {
	return hash( 'sha256', wp_json_encode( get_option( Zion_Theme_Settings::OPTION, Zion_Theme_Settings::empty_config() ) ) );
}

/**
 * Serialize the same document for export and for the import size check.
 *
 * @param array $config Settings.
 * @return string
 */
function zion_theme_control_json( array $config ): string {
	return (string) wp_json_encode( $config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
}

/**
 * Route authorized forms. Validate the entire candidate before saving.
 */
function zion_theme_handle_control_post(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( 'Nu ai permisiunea să modifici setările Zion.', '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'zion_theme_control' );
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each operation and field is allowlisted below. JSON is parsed before persistence.
	$request   = wp_unslash( $_POST );
	$operation = $request['operation'] ?? '';
	$section   = $request['section'] ?? '';
	$raw_id    = $request['item'] ?? '0';
	$id        = is_string( $raw_id ) && ctype_digit( $raw_id ) && (string) (int) $raw_id === $raw_id ? (int) $raw_id : -1;
	$type      = zion_theme_control_item_type( $id );

	if ( ! is_string( $section ) || ! isset( zion_theme_control_sections()[ $section ] ) || $id < 0 || ( $id && ( 'tools' !== $section || ! $type ) ) ) {
		wp_die( 'Secțiune sau element nepermis.', '', array( 'response' => 403 ) );
	}

	try {
		$stored = get_option( Zion_Theme_Settings::OPTION, Zion_Theme_Settings::empty_config() );
		if ( 'export' === $operation ) {
			$json = zion_theme_control_json( Zion_Theme_Settings::normalize( $stored ) );
			nocache_headers();
			header( 'Content-Type: application/json; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename="zion-theme-settings.json"' );
			echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attachment is serialized JSON with an explicit content type.
			exit;
		}

		$revision = $request['revision'] ?? '';
		if ( ! is_string( $revision ) || ! hash_equals( zion_theme_control_revision(), $revision ) ) {
			wp_die(
				'Setările s-au schimbat. Reîncarcă panoul înainte să salvezi.',
				'',
				array(
					'response'  => 409,
					'back_link' => true,
				)
			);
		}

		$config = zion_theme_control_candidate( $stored, $section, $id, $type, $request );
		if ( $stored !== $config && ! update_option( Zion_Theme_Settings::OPTION, $config ) && get_option( Zion_Theme_Settings::OPTION ) !== $config ) {
			wp_die(
				'Setările nu au putut fi scrise.',
				'',
				array(
					'response'  => 500,
					'back_link' => true,
				)
			);
		}
	} catch ( InvalidArgumentException $error ) {
		wp_die(
			esc_html( $error->getMessage() ),
			'',
			array(
				'response'  => 400,
				'back_link' => true,
			)
		);
	}

	wp_safe_redirect( add_query_arg( 'updated', $operation, zion_theme_control_url( $section, $id ) ) );
	exit;
}
add_action( 'admin_post_zion_theme_control', 'zion_theme_handle_control_post' );

/**
 * Build a complete, valid configuration without persisting any data.
 *
 * @param mixed  $stored Stored settings.
 * @param string $section Section key.
 * @param int    $id Item ID.
 * @param string $type Item type.
 * @param array  $request Verified request.
 * @return array
 * @throws InvalidArgumentException If the requested mutation is invalid.
 */
function zion_theme_control_candidate( mixed $stored, string $section, int $id, string $type, array $request ): array {
	switch ( $request['operation'] ?? '' ) {
		case 'reset_all':
			if ( '1' !== ( $request['confirm_reset'] ?? '' ) ) {
				throw new InvalidArgumentException( 'Confirmă resetarea totală.' );
			}
			return Zion_Theme_Settings::empty_config();
		case 'import':
			$json = $request['settings_json'] ?? null;
			if ( ! is_string( $json ) ) {
				throw new InvalidArgumentException( 'Furnizează o configurație JSON.' );
			}
			$config = Zion_Theme_Settings::import( $json );
			foreach ( $config['items'] as $item_type => $items ) {
				foreach ( array_keys( $items ) as $item_id ) {
					$actual_type = zion_theme_control_item_type( $item_id );
					if ( $item_type !== $actual_type ) {
						throw new InvalidArgumentException( 'Un ID din import lipsește, are alt tip sau nu poate fi editat.' );
					}
				}
			}
			break;
		case 'save':
		case 'reset_section':
			$config = zion_theme_merge_control_section( Zion_Theme_Settings::normalize( $stored ), $section, $id, $type, $request );
			$config = Zion_Theme_Settings::normalize( $config );
			break;
		default:
			throw new InvalidArgumentException( 'Operație necunoscută.' );
	}

	if ( strlen( zion_theme_control_json( $config ) ) > Zion_Theme_Settings::MAX_IMPORT_BYTES ) {
		throw new InvalidArgumentException( 'Configurația rezultată depășește limita de 256 KiB.' );
	}
	return $config;
}

/**
 * Replace only values belonging to the submitted section or override.
 *
 * @param array  $config Settings.
 * @param string $section Section key.
 * @param int    $id Item ID.
 * @param string $type Item type.
 * @param array  $request Verified request.
 * @return array
 * @throws InvalidArgumentException On invalid input.
 */
function zion_theme_merge_control_section( array $config, string $section, int $id, string $type, array $request ): array {
	$shop    = in_array( $section, Zion_Theme_Settings::SHOPS, true ) ? $section : '';
	$context = $id ? $type : ( $shop ? 'shop' : 'global' );
	$fields  = Zion_Theme_Settings::fields( $context, 'global' === $context ? $section : '' );
	$values  = $request['values'] ?? array();
	if ( ! $fields || ! is_array( $values ) ) {
		throw new InvalidArgumentException( 'Formularul nu conține o secțiune configurabilă.' );
	}
	$reset   = 'reset_section' === $request['operation'];
	$changed = $reset ? $fields : $values;
	$values  = $reset ? array() : Zion_Theme_Settings::normalize_scope( $values, $fields, true );
	if ( $id ) {
		$stored_scope = &$config['items'][ $type ][ $id ];
	} elseif ( $shop ) {
		$stored_scope = &$config['shops'][ $shop ];
	} else {
		$stored_scope = &$config['global'];
	}
	$stored_scope = array_replace( array_diff_key( $stored_scope ?? array(), $changed ), $values );
	return $config;
}

/**
 * Common form fields for a Control Center request.
 *
 * @param string $section Section key.
 * @param int    $id Optional item ID.
 */
function zion_theme_control_form_fields( string $section, int $id = 0 ): void {
	wp_nonce_field( 'zion_theme_control' );
	?>
	<input type="hidden" name="action" value="zion_theme_control">
	<input type="hidden" name="section" value="<?php echo esc_attr( $section ); ?>">
	<input type="hidden" name="item" value="<?php echo esc_attr( (string) $id ); ?>">
	<input type="hidden" name="revision" value="<?php echo esc_attr( zion_theme_control_revision() ); ?>">
	<?php
}

/**
 * Render the navigation and active section.
 */
function zion_theme_render_control_page(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( 'Nu ai acces la Zion Control Center.', '', array( 'response' => 403 ) );
	}
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- These query arguments select a read-only view.
	$section = isset( $_GET['section'] ) && is_string( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : 'general';
	$id      = 'tools' === $section && isset( $_GET['item'] ) && is_scalar( $_GET['item'] ) ? absint( $_GET['item'] ) : 0;
	$updated = isset( $_GET['updated'] ) && is_string( $_GET['updated'] ) ? sanitize_key( wp_unslash( $_GET['updated'] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	$sections = zion_theme_control_sections();
	$section  = isset( $sections[ $section ] ) ? $section : 'general';
	$type     = zion_theme_control_item_type( $id );
	$shop     = in_array( $section, Zion_Theme_Settings::SHOPS, true ) ? $section : zion_theme_item_shop( $id, $type );
	$context  = $type ? $type : ( $shop ? 'shop' : 'global' );
	$fields   = Zion_Theme_Settings::fields( $context, 'global' === $context ? $section : '' );
	$config   = zion_theme_settings_config();
	?>
	<div class="wrap">
		<h1>Zion Control Center</h1>
		<nav class="nav-tab-wrapper" aria-label="Secțiuni Zion Control Center">
			<?php foreach ( $sections as $key => $label ) : ?>
				<a class="nav-tab<?php echo $section === $key ? ' nav-tab-active' : ''; ?>" href="<?php echo esc_url( zion_theme_control_url( $key ) ); ?>" <?php echo $section === $key ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>
		<h2><?php echo esc_html( $sections[ $section ] ); ?></h2>
		<?php zion_theme_control_notices( $config, $section, $updated ); ?>

		<?php zion_theme_control_editor_links( $section ); ?>
		<?php if ( 'general' === $section ) : ?>
			<p>Alege o secțiune pentru a configura tema. Valorile explicite au prioritate în ordinea produs, pagină sau articol → vitrină → global → implicit.</p>
			<p>Versiunea temei: <strong><?php echo esc_html( wp_get_theme()->get( 'Version' ) ); ?></strong>. Schema setărilor: <strong><?php echo esc_html( (string) Zion_Theme_Settings::VERSION ); ?></strong>.</p>
		<?php endif; ?>
		<?php if ( 'woo' === $section && class_exists( 'WooCommerce' ) ) : ?>
			<p>Prețurile, stocurile, moneda și checkoutul se gestionează în <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings' ) ); ?>">setările WooCommerce</a>.</p>
		<?php endif; ?>
		<?php if ( 'tools' === $section ) : ?>
			<?php zion_theme_render_control_tools( $id, $type ); ?>
		<?php endif; ?>
		<?php if ( $fields && ( ! $id || $type ) ) : ?>
			<?php zion_theme_render_control_form( $section, $id, $type, $shop, $fields, $config ); ?>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Link directly to the native editor for the blocks managed there.
 *
 * @param string $section Control Center section.
 */
function zion_theme_control_editor_links( string $section ): void {
	$views = match ( $section ) {
		'header' => array( array( 'wp_template_part', 'header', 'Editează Header, logo, meniu și CTA' ) ),
		'footer' => array( array( 'wp_template_part', 'footer', 'Editează Footer, contact și linkuri sociale' ) ),
		'blog' => array( array( 'wp_template', 'home', 'Editează Jurnal' ), array( 'wp_template', 'single', 'Editează șablonul de articol' ) ),
		'pages' => array( array( 'wp_template', 'page', 'Editează șablonul de pagină' ), array( 'wp_template_part', 'page-sidebar', 'Editează bara laterală' ) ),
		default => array(),
	};
	if ( in_array( $section, array( 'general', 'design' ), true ) ) {
		echo '<p><a href="' . esc_url( admin_url( 'site-editor.php' ) ) . '">Deschide Site Editor</a></p>';
	}
	foreach ( $views as $view ) {
		$url = add_query_arg(
			array(
				'p'      => '/' . $view[0] . '/' . rawurlencode( get_stylesheet() . '//' . $view[1] ),
				'canvas' => 'edit',
			),
			admin_url( 'site-editor.php' )
		);
		echo '<p><a href="' . esc_url( $url ) . '">' . esc_html( $view[2] ) . '</a></p>';
	}
}

/**
 * Display operation feedback and incompatibilities that affect the active view.
 *
 * @param array  $config Current settings.
 * @param string $section Section key.
 * @param string $updated Last operation.
 */
function zion_theme_control_notices( array $config, string $section, string $updated ): void {
	$messages = array(
		'save'          => 'Setările au fost salvate.',
		'reset_section' => 'Secțiunea a revenit la valorile moștenite.',
		'reset_all'     => 'Toate setările au revenit la valorile implicite.',
		'import'        => 'Configurația a fost importată.',
	);
	if ( isset( $messages[ $updated ] ) ) {
		echo '<div class="notice notice-success is-dismissible" role="status"><p>' . esc_html( $messages[ $updated ] ) . '</p></div>';
	}
	try {
		Zion_Theme_Settings::normalize( get_option( Zion_Theme_Settings::OPTION, Zion_Theme_Settings::empty_config() ) );
	} catch ( InvalidArgumentException $error ) {
		echo '<div class="notice notice-error"><p>' . esc_html( $error->getMessage() ) . ' Importă o configurație compatibilă sau confirmă resetarea totală în Instrumente.</p></div>';
	}
	if ( in_array( $section, array( 'woo', '3d', 'software', 'cards', 'product' ), true ) && ! class_exists( 'WooCommerce' ) ) {
		echo '<div class="notice notice-warning"><p>WooCommerce este inactiv. Controalele de magazin au efect după activare.</p></div>';
	} elseif ( in_array( $section, Zion_Theme_Settings::SHOPS, true ) && ! taxonomy_exists( 'zion_shop' ) ) {
		echo '<div class="notice notice-warning"><p>Zion Core este inactiv. Vitrinele și clasificarea produselor necesită Zion Core.</p></div>';
	}
	if ( 'blog' === $section && isset( $config['global']['reading_speed'] ) && ! Zion_Theme_Settings::resolve( $config, 'reading_time' )['value'] ) {
		echo '<div class="notice notice-warning"><p>Viteza configurată este păstrată și se aplică după activarea timpului de citire.</p></div>';
	}
}

/**
 * Render item lookup and configuration transfer tools.
 *
 * @param int    $id Optional override ID.
 * @param string $type Authorized item type.
 */
function zion_theme_render_control_tools( int $id, string $type ): void {
	?>
	<h3>Setări pentru un produs, o pagină sau un articol</h3>
	<form action="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>" method="get">
		<input type="hidden" name="page" value="zion-theme">
		<input type="hidden" name="section" value="tools">
		<label for="zion-item">ID produs, pagină sau articol</label>
		<input id="zion-item" name="item" type="number" min="1" required value="<?php echo $id ? esc_attr( (string) $id ) : ''; ?>">
		<button type="submit" class="button">Deschide setările</button>
	</form>
	<p class="description">Poți folosi și acțiunea „Setări Zion” din lista de pagini, articole sau produse.</p>
	<?php if ( $id && ! $type ) : ?>
		<div class="notice notice-warning"><p>Elementul nu există, nu este o pagină sau un produs sau nu poate fi editat cu acest cont.</p></div>
	<?php endif; ?>
	<?php if ( $type ) : ?>
		<h3><?php echo esc_html( get_the_title( $id ) . ' (#' . $id . ')' ); ?></h3>
		<p>Modifici doar setările explicite pentru acest element. „Moștenește” păstrează contextul vitrinei sau valoarea globală.</p>
		<p><a href="<?php echo esc_url( zion_theme_control_url( 'tools' ) ); ?>">Înapoi la import, export și resetare totală</a></p>
		<?php return; ?>
	<?php endif; ?>

	<hr>
	<h3>Export și import</h3>
	<p>Exportul include valorile explicite. Importul înlocuiește întreaga configurație după validare. Importă override-urile pe aceeași instalare, deoarece folosesc ID-urile locale.</p>
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<?php zion_theme_control_form_fields( 'tools' ); ?>
		<p><button class="button" name="operation" value="export">Descarcă JSON</button></p>
		<p><label for="zion-json">Configurație JSON</label></p>
		<textarea id="zion-json" class="large-text code" name="settings_json" rows="8" maxlength="<?php echo esc_attr( (string) Zion_Theme_Settings::MAX_IMPORT_BYTES ); ?>" spellcheck="false"></textarea>
		<p><button class="button button-primary" name="operation" value="import">Validează și importă</button></p>
	</form>
	<hr>
	<h3>Resetare totală</h3>
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<?php zion_theme_control_form_fields( 'tools' ); ?>
		<p><label><input type="checkbox" name="confirm_reset" value="1" required> Confirm că vreau să elimin toate valorile explicite, inclusiv cele per produs și pagină.</label></p>
		<p><button class="button" name="operation" value="reset_all">Resetează toate setările</button></p>
	</form>
	<?php
}

/**
 * Render a section form with its explicit, inherited and default values.
 *
 * @param string $section Section key.
 * @param int    $id Optional item ID.
 * @param string $type Item type.
 * @param string $shop Storefront context.
 * @param array  $fields Registry fields for the scope.
 * @param array  $config Current settings.
 */
function zion_theme_render_control_form( string $section, int $id, string $type, string $shop, array $fields, array $config ): void {
	$values           = $id ? ( $config['items'][ $type ][ $id ] ?? array() ) : ( $shop ? ( $config['shops'][ $shop ] ?? array() ) : $config['global'] );
	$sources          = array(
		'default' => 'implicit',
		'global'  => 'global',
		'shop'    => 'vitrină',
		'item'    => 'element',
	);
	$inherited_config = $config;
	if ( $id ) {
		unset( $inherited_config['items'][ $type ][ $id ] );
	} elseif ( $shop ) {
		unset( $inherited_config['shops'][ $shop ] );
	} else {
		$inherited_config['global'] = array();
	}
	?>
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" data-zion-section>
		<?php zion_theme_control_form_fields( $section, $id ); ?>
		<table class="form-table" role="presentation">
			<tbody>
			<?php foreach ( $fields as $key => $field ) : ?>
				<?php
				$effective       = Zion_Theme_Settings::resolve( $config, $key, $shop, $type, $id );
				$inherited       = Zion_Theme_Settings::resolve( $inherited_config, $key, $shop, $type, $id )['value'];
				$free_input      = in_array( $field['type'], array( 'int', 'slugs', 'ordered_keys' ), true );
				$default_label   = $free_input ? $field['default'] : $field['allowed'][ is_bool( $field['default'] ) ? (int) $field['default'] : $field['default'] ];
				$effective_label = $free_input ? $effective['value'] : $field['allowed'][ is_bool( $effective['value'] ) ? (int) $effective['value'] : $effective['value'] ];
				$value           = isset( $values[ $key ] ) ? ( is_bool( $values[ $key ] ) ? (string) (int) $values[ $key ] : (string) $values[ $key ] ) : '';
				$control_id      = 'zion-field-' . $key;
				?>
				<tr data-zion-requires="<?php echo esc_attr( $field['requires'] ?? '' ); ?>">
					<th scope="row"><label for="<?php echo esc_attr( $control_id ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
					<td>
					<?php if ( 'int' === $field['type'] ) : ?>
						<input id="<?php echo esc_attr( $control_id ); ?>" name="values[<?php echo esc_attr( $key ); ?>]" type="number" min="<?php echo esc_attr( (string) $field['allowed']['min'] ); ?>" max="<?php echo esc_attr( (string) $field['allowed']['max'] ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="Moștenește" aria-describedby="<?php echo esc_attr( $control_id . '-help' ); ?>">
				<?php elseif ( $free_input ) : ?>
						<input id="<?php echo esc_attr( $control_id ); ?>" name="values[<?php echo esc_attr( $key ); ?>]" type="text" class="regular-text" maxlength="180" value="<?php echo esc_attr( $value ); ?>" placeholder="Moștenește" aria-describedby="<?php echo esc_attr( $control_id . '-help' ); ?>">
					<?php else : ?>
						<select id="<?php echo esc_attr( $control_id ); ?>" name="values[<?php echo esc_attr( $key ); ?>]" data-zion-inherited="<?php echo esc_attr( (string) ( is_bool( $inherited ) ? (int) $inherited : $inherited ) ); ?>" aria-describedby="<?php echo esc_attr( $control_id . '-help' ); ?>">
							<option value="" <?php selected( $value, '' ); ?>>Moștenește</option>
							<?php foreach ( $field['allowed'] as $choice => $label ) : ?>
								<option value="<?php echo esc_attr( (string) $choice ); ?>" <?php selected( $value, (string) $choice ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
						<p class="description" id="<?php echo esc_attr( $control_id . '-help' ); ?>">
							<?php echo esc_html( $field['description'] . ' Implicit: ' . $default_label . '. Activ: ' . $effective_label . ' (' . $sources[ $effective['source'] ] . ').' ); ?>
						</p>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p class="submit">
			<button type="submit" class="button button-primary" name="operation" value="save">Salvează setările</button>
			<button type="submit" class="button" name="operation" value="reset_section" formnovalidate>Resetează secțiunea</button>
		</p>
	</form>
	<?php
}
