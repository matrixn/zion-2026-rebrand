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
 * Register editable native Gutenberg patterns for the service pages.
 */
function zion_theme_register_services(): void {
	register_block_pattern_category( 'zion-library', array( 'label' => __( 'Zion · Componente', 'zion-theme' ) ) );
	foreach ( zion_theme_services() as $slug => $service ) {
		$content  = '<!-- wp:paragraph {"className":"zion-lead"} --><p class="zion-lead">' . esc_html( $service[1] ) . '</p><!-- /wp:paragraph -->';
		$content .= '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Ce poate include proiectul', 'zion-theme' ) . '</h2><!-- /wp:heading -->';
		$content .= '<!-- wp:list --><ul class="wp-block-list">';
		foreach ( array_slice( $service, 2 ) as $item ) {
			$content .= '<!-- wp:list-item --><li>' . esc_html( $item ) . '</li><!-- /wp:list-item -->';
		}
		$content .= '</ul><!-- /wp:list --><!-- wp:zion/cta /-->';
		register_block_pattern(
			'zion-theme/service-' . $slug,
			array(
				'title'      => $service[0],
				'categories' => array( 'zion-library' ),
				'content'    => $content,
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
	foreach ( zion_theme_services() as $slug => $service ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'zion-theme/service-' . $slug );
		$id      = wp_insert_post(
			wp_slash(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_name'    => $slug,
					'post_title'   => $service[0],
					'post_excerpt' => $service[1],
					'post_content' => $pattern['content'],
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
	$pages    = get_posts(
		array(
			'post_type'     => 'page',
			'post_status'   => 'publish',
			'post_name__in' => array_keys( $services ),
			'numberposts'   => count( $services ),
			'has_password'  => false,
		)
	);
	$urls     = array();
	foreach ( $pages as $page ) {
		$urls[ $page->post_name ] = get_permalink( $page );
	}
	$cards  = '';
	$number = 0;
	foreach ( $services as $slug => $service ) {
		$title  = esc_html( $service[0] );
		$cards .= '<article class="zion-card"><span class="zion-kicker" aria-hidden="true">' . esc_html( sprintf( '%02d', ++$number ) ) . '</span><h3>' .
			( isset( $urls[ $slug ] ) ? '<a href="' . esc_url( $urls[ $slug ] ) . '">' . $title . '</a>' : $title ) .
			'</h3><p>' . esc_html( $service[1] ) . '</p></article>';
	}
	return '<div class="zion-services-grid">' . $cards . '</div>';
}
