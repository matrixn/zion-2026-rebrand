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
	return zion_theme_service_group( 'zion-services-overview zion-about', $hero . $story . $worlds . $values . $note . $end );
}
