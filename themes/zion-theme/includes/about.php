<?php
/**
 * The Zion story, authored as editable native blocks.
 *
 * @package Zion_Theme
 */

/**
 * Build the About page without replacing subsequently edited page content.
 *
 * @return string
 */
function zion_theme_about_content(): string {
	$contact  = zion_theme_published_page_url( 'contact' );
	$contact  = $contact ? $contact : 'mailto:contact@zion3d.ro';
	$services = zion_theme_published_page_url( 'servicii' );
	$hero     = zion_theme_service_group(
		'zion-overview-band zion-overview-hero zion-about-hero',
		zion_theme_service_group(
			'zion-about-hero-grid',
			zion_theme_service_group(
				'zion-about-lead',
				zion_theme_service_text( 'DESPRE NOI / ZION 3D', 0, 'zion-overview-label' ) .
				get_comment_delimited_block_content( 'core/heading', array( 'level' => 1 ), '<h1 class="wp-block-heading">Oameni curioși.<br><em>Idei care prind formă.</em></h1>' ) .
				zion_theme_service_text( 'Ne place momentul în care o idee nu mai e doar o idee. O poți ține în mână. Sau o vezi rezolvând o problemă pe ecran.', 0, 'zion-about-intro' ) .
				zion_theme_service_buttons(
					array(
						'Cunoaște-ne ↓'     => '#povestea-zion',
						'Hai să discutăm ↗' => $contact,
					)
				) .
				zion_theme_service_text( 'RÂMNICU VÂLCEA · ROMÂNIA / IDEI CARE MERG MAI DEPARTE', 0, 'zion-about-coordinate' )
			) .
			zion_theme_service_group(
				'zion-about-note',
				zion_theme_service_text( 'DIN ATELIERUL NOSTRU DE IDEI', 0, 'zion-about-note-label' ) .
				zion_theme_service_text( 'Z', 0, 'zion-about-monogram' ) .
				zion_theme_service_text( '„Oare se poate?”', 0, 'zion-about-question' ) .
				zion_theme_service_text( 'De aici ne place să începem.', 0, 'zion-about-note-copy' ) .
				zion_theme_service_text( 'SCHIȚĂ / PROTOTIP / COD / SOLUȚIE', 0, 'zion-about-note-footer' )
			)
		)
	);
	$story    = zion_theme_service_group(
		'zion-overview-band zion-about-story',
		zion_theme_service_group(
			'zion-about-editorial',
			zion_theme_service_text( '01 / POVESTEA NOASTRĂ', 0, 'zion-overview-label' ) .
			zion_theme_service_group(
				'zion-about-prose',
				zion_theme_service_text( 'Mai întâi, curiozitatea.', 2 ) .
				zion_theme_service_text( 'Zion a pornit din pasiunea pentru tehnologie, design și soluții practice. Din dorința de a trece de la „ar fi interesant” la ceva ce poți vedea, proba și folosi.' ) .
				zion_theme_service_text( 'Astăzi, aceeași curiozitate leagă proiectarea și producția 3D de software, integrări și automatizări. Uneori răspunsul este o piesă. Alteori, o aplicație. Punctul de plecare rămâne omul care are nevoie de ea.' ) .
				zion_theme_service_text( 'Ne interesează ce vrei să rezolvi, ce ai încercat deja și cum ar arăta, pentru tine, un rezultat bun.', 0, 'zion-about-highlight' )
			)
		),
		'povestea-zion'
	);
	$worlds   = '';
	foreach (
		array(
			array( '01 / ÎN ATELIER', 'Din schiță, în mâinile tale.', 'Modelăm, alegem materialul și pregătim piesa pentru utilizarea ei reală. Ne plac detaliile care fac diferența la montaj, la atingere și în fiecare zi.', 'Printare 3D', zion_theme_service_url( 'printare-3d' ), 'zion-about-physical' ),
			array( '02 / ÎN COD', 'Din idee, în rutina ta.', 'Construim aplicații și conectăm sisteme pornind de la felul în care lucrezi. Tehnologia are rost atunci când face lucrurile mai clare și munca mai ușoară.', 'Software personalizat', zion_theme_service_url( 'software-personalizat' ), 'zion-about-digital' ),
		) as $world
	) {
		$worlds .= zion_theme_service_group(
			'zion-about-world ' . $world[5],
			zion_theme_service_text( $world[0], 0, 'zion-overview-label' ) .
			zion_theme_service_text( $world[1], 3 ) .
			zion_theme_service_text( $world[2] ) .
			( $world[4] ? zion_theme_service_buttons( array( $world[3] . ' ↗' => $world[4] ) ) : '' )
		);
	}
	$worlds = zion_theme_service_group(
		'zion-overview-band zion-about-worlds',
		zion_theme_service_text( '02 / CE NE LEAGĂ', 0, 'zion-overview-label' ) .
		zion_theme_service_text( 'Două lumi. Același fel de a gândi.', 2 ) .
		zion_theme_service_group( 'zion-about-world-grid', $worlds )
	);
	$values = '';
	foreach (
		array(
			'Vorbim omenește.'              => 'Începem prin a asculta. Punem întrebări și explicăm alegerile, ca să știi ce construim împreună și de ce.',
			'Încercăm. Verificăm. Ajustăm.' => 'O probă spune mai mult decât o presupunere. Lăsăm loc pentru testare și pentru schimbările care fac soluția mai bună.',
			'Construim pentru utilizare.'   => 'Aspectul contează. La fel și potrivirea, claritatea sau întreținerea. Ne gândim la ce se întâmplă după ce proiectul ajunge la tine.',
		) as $title => $copy
	) {
		$values .= zion_theme_service_group( 'zion-about-value', zion_theme_service_text( $title, 3 ) . zion_theme_service_text( $copy ) );
	}
	$values = zion_theme_service_group(
		'zion-overview-band zion-about-values',
		zion_theme_service_text( '03 / FELUL NOSTRU DE LUCRU', 0, 'zion-overview-label' ) .
		zion_theme_service_text( 'Lucrurile bune se construiesc cu grijă.', 2 ) .
		zion_theme_service_group( 'zion-about-value-grid', $values )
	);
	$note   = zion_theme_service_group(
		'zion-overview-band zion-about-invitation',
		zion_theme_service_group(
			'zion-about-editorial',
			zion_theme_service_text( 'DE LA OM LA OM', 0, 'zion-overview-label' ) .
			zion_theme_service_group(
				'zion-about-prose',
				zion_theme_service_text( 'Nu ai nevoie de un brief perfect.', 2 ) .
				zion_theme_service_text( 'Poate ai o schiță pe telefon, o piesă care nu se mai găsește sau un proces care îți consumă prea mult timp. Poate ai doar o întrebare. E un început suficient pentru o discuție.' ) .
				zion_theme_service_text( 'Ne aduci contextul. Împreună căutăm forma potrivită pentru idee.', 0, 'zion-about-highlight' ) .
				zion_theme_service_text( 'Ne bucurăm că ai ajuns aici. / Zion', 0, 'zion-about-signature' )
			)
		)
	);
	$end    = zion_theme_service_group(
		'zion-overview-band zion-overview-end zion-about-end',
		zion_theme_service_text( 'URMĂTORUL PROIECT POATE ÎNCEPE AICI', 0, 'zion-overview-label' ) .
		zion_theme_service_text( 'Spune-ne ce ai în minte.', 2 ) .
		zion_theme_service_text( 'O piesă. O aplicație. O idee care încă își caută forma.' ) .
		zion_theme_service_buttons(
			array_filter(
				array(
					'Hai să discutăm ↗'      => $contact,
					'Descoperă serviciile ↗' => $services,
				)
			)
		)
	);
	return zion_theme_service_group( 'zion-services-overview zion-about', $hero . $story . zion_theme_about_forge() . $worlds . $values . $note . $end );
}

/**
 * An editable narrative progressively enhanced by the spatial renderer.
 *
 * @return string
 */
function zion_theme_about_forge(): string {
	$phases = '';
	foreach (
		array(
			'Schiță'   => 'O întrebare, câteva linii, o direcție. Totul începe cu locul pe care îl lași unei idei.',
			'Straturi' => 'Desfacem ideea, încercăm și ajustăm. Fiecare pas adaugă ceva care o face mai clară.',
			'Obiect'   => 'Forma capătă volum. Dintr-o posibilitate apare un lucru pe care îl poți vedea, proba și folosi.',
			'Cod'      => 'Aceeași curiozitate continuă în lumea digitală. Conexiuni și instrumente care lucrează pentru tine.',
		) as $title => $copy
	) {
		$phases .= zion_theme_service_group( 'zion-forge-phase', zion_theme_service_text( $title, 3 ) . zion_theme_service_text( $copy ) );
	}
	return zion_theme_service_group(
		'zion-overview-band zion-about-forge',
		zion_theme_service_group(
			'zion-forge-stage',
			zion_theme_service_text( 'ZION / DIN IDEE ÎN LUMEA TA', 0, 'zion-overview-label' ) .
			zion_theme_service_text( 'Privește cum prinde formă.', 2 ) .
			zion_theme_service_group( 'zion-forge-viewport', '' ) .
			zion_theme_service_group( 'zion-forge-phases', $phases ) .
			zion_theme_service_text( 'De la prima linie la următoarea posibilitate.', 0, 'zion-forge-footnote' )
		)
	);
}

/**
 * Insert the experience without replacing authored About sections.
 *
 * @param string $content Existing native page content.
 * @return string
 */
function zion_theme_about_add_forge( string $content ): string {
	$blocks = parse_blocks( $content );
	foreach ( $blocks as &$block ) {
		$classes = explode( ' ', $block['attrs']['className'] ?? '' );
		if ( 'core/group' !== $block['blockName'] || ! in_array( 'zion-about', $classes, true ) ) {
			continue;
		}
		$index = 1;
		foreach ( $block['innerBlocks'] as $position => $child ) {
			$child_classes = explode( ' ', $child['attrs']['className'] ?? '' );
			if ( in_array( 'zion-about-forge', $child_classes, true ) ) {
				return $content;
			}
			if ( in_array( 'zion-about-story', $child_classes, true ) ) {
				$index = $position + 1;
			}
		}
		$forge = parse_blocks( zion_theme_about_forge() )[0];
		array_splice( $block['innerBlocks'], $index, 0, array( $forge ) );
		// Match the insertion to native child placeholders, preserving wrapper HTML.
		$seen = 0;
		foreach ( $block['innerContent'] as $offset => $chunk ) {
			if ( null === $chunk && $seen++ === $index ) {
				array_splice( $block['innerContent'], $offset, 0, array( null ) );
				return serialize_blocks( $blocks );
			}
		}
		array_splice( $block['innerContent'], -1, 0, array( null ) );
		return serialize_blocks( $blocks );
	}
	return $content;
}

/**
 * Keep canvases out of saved blocks and load the renderer only when present.
 *
 * @param string $html Native group HTML.
 * @param array  $block Parsed block.
 * @return string
 */
function zion_theme_about_spatial_markup( string $html, array $block ): string {
	if ( is_admin() || wp_is_serving_rest_request() ) {
		return $html;
	}
	$classes = explode( ' ', $block['attrs']['className'] ?? '' );
	$seed    = in_array( 'zion-about-note', $classes, true );
	$forge   = in_array( 'zion-forge-viewport', $classes, true );
	if ( ! $seed && ! $forge ) {
		return $html;
	}
	zion_theme_enqueue_continuum();
	$canvas = '<canvas class="zion-spatial-canvas" data-zion-spatial="' . ( $seed ? 'seed' : 'forge' ) . '" aria-hidden="true"></canvas>';
	return preg_replace( '~(</div>\s*)$~', $canvas . '$1', $html, 1 ) ?? $html;
}
add_filter( 'render_block_core/group', 'zion_theme_about_spatial_markup', 25, 2 );
