<?php
/**
 * The reusable Zion services directory and editable page patterns.
 *
 * @package Zion_Theme
 */

/**
 * Define the seven service pages and their first-draft editorial content.
 *
 * @return array
 */
function zion_theme_services(): array {
	return array(
		'printare-3d'             => array( 'Printare 3D', 'Prototipuri funcționale și piese adaptate geometriei și utilizării lor.', 'Material și orientare de printare', 'Prototip de validare', 'Pregătire pentru producție' ),
		'proiectare-3d'           => array( 'Proiectare 3D', 'Modelare CAD pentru idei, piese, carcase și ansambluri tehnice.', 'Model parametric', 'Verificare dimensiuni și toleranțe', 'Fișiere pentru fabricație' ),
		'software-personalizat'   => array( 'Software personalizat', 'Aplicații construite în jurul fluxului tău de lucru.', 'Analiza procesului', 'Prototip funcțional', 'Testare și documentație' ),
		'platforme-saas'          => array( 'Platforme SaaS', 'Produse web cu roluri, fluxuri de lucru și administrare.', 'Definirea fluxurilor', 'Arhitectură și interfețe', 'Operare și mentenanță' ),
		'pluginuri-si-integrari'  => array( 'Pluginuri și integrări', 'Extensii și conexiuni între WordPress, WooCommerce și servicii externe.', 'Integrare prin API-uri', 'Automatizarea datelor', 'Verificarea compatibilității' ),
		'aplicatii-windows-linux' => array( 'Aplicații Windows/Linux', 'Unelte desktop și automatizări pentru operațiuni repetitive.', 'Interfață sau utilitar CLI', 'Integrare cu sistemul local', 'Pachete de instalare' ),
		'integrari-ai'            => array( 'Integrări AI', 'Fluxuri asistate pentru căutare, procesarea conținutului și automatizare.', 'Evaluarea surselor de date', 'Integrare cu aplicația', 'Validare umană și măsurare' ),
	);
}

/**
 * Paths retained when the existing Zion site is migrated.
 *
 * @return array<string,string>
 */
function zion_theme_service_paths(): array {
	return array(
		'printare-3d'             => 'printare-3d-la-comanda-zion-3d-print',
		'proiectare-3d'           => 'proiectare-si-modelare-3d',
		'software-personalizat'   => 'software-personalizat-pentru-afaceri',
		'platforme-saas'          => 'platforme-si-produse-saas',
		'pluginuri-si-integrari'  => 'pluginuri-si-integrari-software',
		'aplicatii-windows-linux' => 'aplicatii-windows-si-linux',
		'integrari-ai'            => 'integrari-ai-si-automatizari-inteligente',
	);
}

/**
 * Link to the existing service page before considering the short default slug.
 *
 * @param string $slug Service identifier.
 * @return string
 */
function zion_theme_service_url( string $slug ): string {
	$path = zion_theme_service_paths()[ $slug ] ?? $slug;
	$url  = zion_theme_published_page_url( $path );
	return $url ? $url : zion_theme_published_page_url( $slug );
}

/**
 * Register editable native Gutenberg patterns for the service pages.
 */
function zion_theme_register_services(): void {
	register_block_pattern_category( 'zion-library', array( 'label' => __( 'Zion · Componente', 'zion-theme' ) ) );
	register_block_pattern(
		'zion-theme/services-directory',
		array(
			'title'      => __( 'Zion · Pagina Servicii', 'zion-theme' ),
			'categories' => array( 'zion-library' ),
			'content'    => zion_theme_service_landing(),
		)
	);
	foreach ( zion_theme_services() as $slug => $service ) {
		register_block_pattern(
			'zion-theme/service-' . $slug,
			array(
				'title'      => $service[0],
				'categories' => array( 'zion-library' ),
				'content'    => zion_theme_service_landing( $slug ),
			)
		);
	}
}
add_action( 'init', 'zion_theme_register_services' );

/**
 * Publish missing service pages from their native editable block markup.
 *
 * @return array Counts for newly created pages and failures.
 */
function zion_theme_create_service_pages(): array {
	$result = array(
		'created' => 0,
		'failed'  => 0,
	);
	$pages  = array( 'servicii' => array( 'Servicii', 'Producție 3D, software și automatizare.' ) ) + zion_theme_services();
	foreach ( $pages as $slug => $service ) {
		if ( get_page_by_path( $slug ) || ( 'servicii' !== $slug && zion_theme_service_url( $slug ) ) ) {
			continue;
		}
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'servicii' === $slug ? 'zion-theme/services-directory' : 'zion-theme/service-' . $slug );
		$id      = wp_insert_post(
			wp_slash(
				array(
					'post_type'     => 'page',
					'post_status'   => 'publish',
					'post_name'     => $slug,
					'post_title'    => $service[0],
					'post_excerpt'  => $service[1],
					'post_content'  => $pattern['content'],
					'page_template' => 'service-landing',
				)
			),
			true
		);
		++$result[ is_wp_error( $id ) ? 'failed' : 'created' ];
	}
	return $result;
}

/**
 * Provision the pages through an authorized native admin form.
 */
function zion_theme_service_pages_action(): void {
	if ( ! current_user_can( 'edit_theme_options' ) || ! current_user_can( 'publish_pages' ) ) {
		wp_die( esc_html__( 'Nu ai permisiunea de a publica pagini.', 'zion-theme' ), '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'zion_create_service_pages' );
	$result = zion_theme_create_service_pages();
	wp_safe_redirect(
		add_query_arg(
			array(
				'service_created' => $result['created'],
				'service_failed'  => $result['failed'],
			),
			zion_theme_control_url( 'tools' )
		)
	);
	exit;
}
add_action( 'admin_post_zion_create_service_pages', 'zion_theme_service_pages_action' );

/**
 * Render the directory with real links only when service pages are public.
 *
 * @return string
 */
function zion_theme_services_grid(): string {
	$services = zion_theme_services();
	$cards    = '';
	$number   = 0;
	foreach ( $services as $slug => $service ) {
		$title  = esc_html( $service[0] );
		$url    = zion_theme_service_url( $slug );
		$cards .= '<article class="zion-card"><span class="zion-kicker" aria-hidden="true">' . esc_html( sprintf( '%02d', ++$number ) ) . '</span><h3>' .
			( $url ? '<a href="' . esc_url( $url ) . '">' . $title . '</a>' : $title ) .
			'</h3><p>' . esc_html( $service[1] ) . '</p></article>';
	}
	return '<div class="zion-services-grid">' . $cards . '</div>';
}
