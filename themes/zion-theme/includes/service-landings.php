<?php
/**
 * Editable block layouts for Zion service landing pages.
 *
 * @package Zion_Theme
 */

/**
 * Serialize a styled group or section.
 *
 * @param string $css_class Component classes.
 * @param string $content Nested blocks.
 * @param string $anchor Optional in-page target.
 * @return string
 */
function zion_theme_service_group( string $css_class, string $content, string $anchor = '' ): string {
	$attributes = array( 'className' => $css_class );
	if ( $anchor ) {
		$attributes['anchor'] = $anchor;
	}
	return get_comment_delimited_block_content(
		'core/group',
		$attributes,
		'<div class="wp-block-group ' . esc_attr( $css_class ) . '"' . ( $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '' ) . '>' . $content . '</div>'
	);
}

/**
 * Serialize a heading or paragraph for a service pattern.
 *
 * @param string $text Plain text.
 * @param int    $level Heading level or zero for a paragraph.
 * @param string $css_class Optional CSS class.
 * @return string
 */
function zion_theme_service_text( string $text, int $level = 0, string $css_class = '' ): string {
	$tag        = $level ? 'h' . $level : 'p';
	$attributes = $level && 2 !== $level ? array( 'level' => $level ) : array();
	if ( $css_class ) {
		$attributes['className'] = $css_class;
	}
	$classes = trim( ( $level ? 'wp-block-heading ' : '' ) . $css_class );
	return get_comment_delimited_block_content(
		$level ? 'core/heading' : 'core/paragraph',
		$attributes,
		'<' . $tag . ( $classes ? ' class="' . esc_attr( $classes ) . '"' : '' ) . '>' . esc_html( $text ) . '</' . $tag . '>'
	);
}

/**
 * Build a native button row.
 *
 * @param array<string,string> $links Map of labels to destinations.
 * @return string
 */
function zion_theme_service_buttons( array $links ): string {
	$buttons = '';
	foreach ( $links as $label => $url ) {
		$outline  = '' !== $buttons;
		$buttons .= get_comment_delimited_block_content(
			'core/button',
			$outline ? array( 'className' => 'is-style-outline' ) : array(),
			'<div class="wp-block-button' . ( $outline ? ' is-style-outline' : '' ) . '"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></div>'
		);
	}
	return get_comment_delimited_block_content( 'core/buttons', array(), '<div class="wp-block-buttons">' . $buttons . '</div>' );
}

/**
 * Build a numbered card grid.
 *
 * @param array<string,string> $items Card titles and descriptions.
 * @return string
 */
function zion_theme_service_cards( array $items ): string {
	$cards = '';
	$index = 0;
	foreach ( $items as $title => $text ) {
		$cards .= zion_theme_service_group(
			'zion-service-card',
			zion_theme_service_text( sprintf( '%02d', ++$index ), 0, 'zion-kicker' ) .
			zion_theme_service_text( $title, 3 ) .
			( '' !== $text ? zion_theme_service_text( $text ) : '' )
		);
	}
	return zion_theme_service_group( 'zion-service-grid', $cards );
}

/**
 * Describe the practical applications and editorial identity of each service.
 *
 * @return array
 */
function zion_theme_service_stories(): array {
	return array(
		'printare-3d'             => array( 'PRINT / FIT / REPEAT', 'Piesa de care ai nevoie, făcută la comandă.', 'Piesă de schimb', 'Înlocuim o componentă greu de găsit pornind de la model sau dimensiuni.', 'Prototip funcțional', 'Probezi forma și montajul înainte să comanzi o serie.', 'Serie mică', 'Realizăm loturi de suporturi, adaptoare sau carcase adaptate utilizării.' ),
		'proiectare-3d'           => array( 'SKETCH / CAD / PROTOTYPE', 'De la schiță la un model gata de probă.', 'Reconstrucție', 'Refacem geometria unei componente din fotografii și măsurători.', 'Carcasă dedicată', 'Adaptăm forma la piesele, prinderile și toleranțele reale.', 'Concept nou', 'Modelăm ideea pentru vizualizare și prototipare.' ),
		'software-personalizat'   => array( 'WORKFLOW / DATA / DELIVERY', 'Software care se potrivește cu munca echipei tale.', 'Portal pentru clienți', 'Grupezi solicitări, documente și statusuri într-un singur loc.', 'Gestiunea comenzilor', 'Urmărești comenzile de la preluare la livrare.', 'Operațiuni interne', 'Înlocuiești pașii manuali cu un flux ușor de urmărit.' ),
		'platforme-saas'          => array( 'VALIDATE / LAUNCH / GROW', 'Transformă o idee într-un serviciu online.', 'Prima versiune', 'Validezi utilitatea produsului cu un grup de clienți.', 'Portal cu abonament', 'Oferi conturi, acces și funcții potrivite fiecărui plan.', 'Platformă în creștere', 'Adaugi funcții pe baza utilizării reale.' ),
		'pluginuri-si-integrari'  => array( 'CONNECT / SYNC / EXTEND', 'Leagă sistemele pe care le folosești deja.', 'Extensie WooCommerce', 'Adaugi o funcție specifică magazinului tău.', 'Conector de date', 'Sincronizezi informații între magazin și aplicațiile interne.', 'Proces automatizat', 'Elimini transferurile manuale și urmărești erorile la sursă.' ),
		'aplicatii-windows-linux' => array( 'DESKTOP / DEVICE / CONTROL', 'Instrumente desktop pentru munca de zi cu zi.', 'Utilitar intern', 'Simplifici operațiunile repetitive care pornesc de pe un calculator.', 'Echipamente', 'Controlezi un flux care depinde de dispozitive locale.', 'Fișiere și date', 'Procesezi loturi de fișiere într-o interfață dedicată.' ),
		'integrari-ai'            => array( 'DATA / ASSIST / REVIEW', 'AI aplicat acolo unde scurtează munca.', 'Asistent intern', 'Găsești informații în documentele și procedurile echipei.', 'Procesare documente', 'Extragi și verifici informații din documente repetitive.', 'Automatizare asistată', 'Pregătești propuneri pe care echipa le validează înainte de utilizare.' ),
	);
}

/**
 * Assemble the overview or a detail page from editable Gutenberg blocks.
 *
 * @param string $slug Service key. Empty selects the directory.
 * @return string
 */
function zion_theme_service_landing( string $slug = '' ): string {
	if ( '' === $slug ) {
		return zion_theme_services_overview();
	}
	$services = zion_theme_services();
	$service  = $services[ $slug ];
	$story    = zion_theme_service_stories()[ $slug ];
	$detail   = zion_theme_service_detail_copy()[ $slug ];
	$code     = sprintf( '%02d', 1 + array_search( $slug, array_keys( $services ), true ) );
	$query    = array(
		'pluginuri-si-integrari'  => 'pluginuri-integrari',
		'aplicatii-windows-linux' => 'aplicatii-desktop',
	);
	$contact  = add_query_arg( 'serviciu', $query[ $slug ] ?? $slug, home_url( '/contact/' ) );
	$heading  = get_comment_delimited_block_content(
		'core/heading',
		array( 'level' => 1 ),
		'<h1 class="wp-block-heading">' . esc_html( $detail['hero'][0] ) . '<br><em>' . esc_html( $detail['hero'][1] ) . '</em></h1>'
	);
	$intro    = zion_theme_service_group(
		'zion-overview-band zion-overview-hero zion-overview-split zion-detail-hero',
		zion_theme_service_group(
			'zion-overview-lead',
			zion_theme_service_text( $code . ' / ' . $service[0], 0, 'zion-overview-label' ) .
			$heading .
			zion_theme_service_text( $detail['lead'] ) .
			zion_theme_service_buttons(
				array(
					'Discută proiectul ↗'   => $contact,
					'Descoperă serviciul ↓' => '#beneficii',
				)
			)
		) .
		zion_theme_service_group(
			'zion-detail-scene',
			zion_theme_service_group(
				'zion-detail-screen',
				zion_theme_service_text( $detail['hero'][2], 0, 'zion-detail-symbol' ) .
				zion_theme_service_text( $detail['hero'][3], 0, 'zion-detail-caption' )
			)
		)
	);
	$benefits = zion_theme_service_group(
		'zion-overview-band zion-detail-benefits',
		zion_theme_service_text( 'Beneficiile tale', 0, 'zion-overview-label' ) .
		zion_theme_service_text( $detail['headings'][0], 2 ) .
		zion_theme_service_text( $service[1], 0, 'zion-detail-intro' ) .
		zion_theme_service_cards( $detail['benefits'] ),
		'beneficii'
	);
	$examples = zion_theme_service_group(
		'zion-overview-band zion-detail-examples',
		zion_theme_service_text( 'Din idee în practică', 0, 'zion-overview-label' ) .
		zion_theme_service_text( $detail['headings'][1], 2 ) .
		zion_theme_service_cards(
			array(
				$story[2] => $story[3],
				$story[4] => $story[5],
				$story[6] => $story[7],
			)
		),
		'aplicatii-practice'
	);
	$process  = zion_theme_service_group(
		'zion-overview-band zion-overview-process zion-detail-process',
		zion_theme_service_text( 'Proces de colaborare', 0, 'zion-overview-label' ) .
		zion_theme_service_text( $detail['headings'][2], 2 ) .
		zion_theme_service_cards( $detail['steps'] )
	);
	$end      = zion_theme_service_group(
		'zion-overview-band zion-overview-end zion-detail-end',
		zion_theme_service_text( 'Hai să începem', 0, 'zion-overview-label' ) .
		zion_theme_service_text( $detail['headings'][3], 2 ) .
		zion_theme_service_text( 'Spune-ne ce ai nevoie și vedem ce putem construi împreună.' ) .
		zion_theme_service_buttons(
			array(
				'Solicită o discuție ↗' => $contact,
				'Scrie pe WhatsApp ↗'   => 'https://wa.me/40770675597',
				'Toate serviciile'      => home_url( '/servicii/' ),
			)
		)
	);
	return zion_theme_service_group( 'zion-services-overview zion-service-detail', $intro . $benefits . $examples . $process . $end );
}
