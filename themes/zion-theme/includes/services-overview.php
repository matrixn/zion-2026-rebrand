<?php
/**
 * Gutenberg composition for the main services page.
 *
 * @package Zion_Theme
 */

/**
 * Render the main services page as editable blocks.
 *
 * @return string
 */
function zion_theme_services_overview(): string {
	$contact = home_url( '/contact/' );
	$heading = get_comment_delimited_block_content( 'core/heading', array( 'level' => 1 ), '<h1 class="wp-block-heading">Idei ambițioase.<br><em>Soluții reale.</em></h1>' );
	$hero    = zion_theme_service_group(
		'zion-overview-band zion-overview-hero',
		zion_theme_service_group(
			'zion-overview-split',
			zion_theme_service_group(
				'zion-overview-lead',
				zion_theme_service_text( 'Zion 3D / Proiectare și dezvoltare', 0, 'zion-overview-label' ) .
				$heading .
				zion_theme_service_text( 'De la piese 3D la aplicații și automatizări, construim soluții pe care le poți testa și folosi în activitatea ta.' ) .
				zion_theme_service_buttons(
					array(
						'Discutăm proiectul ↗'   => $contact,
						'Descoperă serviciile ↓' => '#exploreaza-serviciile',
					)
				) .
				zion_theme_service_text( '✦ Proiecte la comandă   ✦ 3D și digital   ✦ Colaborare directă', 0, 'zion-overview-proof' )
			) .
			zion_theme_service_group(
				'zion-overview-art',
				zion_theme_service_text( 'DESIGN / BUILD / EVOLVE', 0, 'zion-overview-art-label' ) .
				zion_theme_service_group( 'zion-overview-orbit', zion_theme_service_group( 'zion-overview-object', '' ) ) .
				zion_theme_service_text( '01–07 / SERVICII', 0, 'zion-overview-art-label zion-overview-art-label-bottom' )
			)
		) .
		zion_theme_service_group(
			'zion-overview-strip',
			zion_theme_service_text( 'PRINTARE 3D · PROIECTARE · SOFTWARE · AI' ) .
			zion_theme_service_text( 'RÂMNICU VÂLCEA / COLABORĂM ȘI LA DISTANȚĂ' )
		)
	);
	$intro   = zion_theme_service_group(
		'zion-overview-band zion-overview-split',
		zion_theme_service_group( 'zion-overview-heading', zion_theme_service_text( 'Ce facem diferit', 0, 'zion-overview-label' ) . zion_theme_service_text( 'Pornim de la problema pe care vrei s-o rezolvi.', 2 ) ) .
		zion_theme_service_group(
			'zion-overview-intro-copy',
			zion_theme_service_text( 'Fie că îți trebuie o piesă de schimb sau un instrument care să scurteze munca echipei, stabilim împreună ce rezultat merită construit mai întâi.' ) .
			zion_theme_service_group(
				'zion-overview-facts',
				zion_theme_service_group( 'zion-overview-fact', zion_theme_service_text( '3D + Digital', 3 ) . zion_theme_service_text( 'Fabricație, modelare și dezvoltare software.' ) ) .
				zion_theme_service_group( 'zion-overview-fact', zion_theme_service_text( '01 → ∞', 3 ) . zion_theme_service_text( 'De la primul prototip la un produs care evoluează.' ) )
			)
		)
	);

	$cards = '';
	$index = 0;
	foreach ( zion_theme_services() as $slug => $service ) {
		++$index;
		$variant = 3 === $index ? ' zion-overview-card-dark' : ( 7 === $index ? ' zion-overview-card-lime' : '' );
		$url     = zion_theme_service_url( $slug );
		$story   = zion_theme_service_stories()[ $slug ];
		$cards  .= zion_theme_service_group(
			'zion-overview-card' . $variant,
			zion_theme_service_group(
				'zion-overview-card-top',
				zion_theme_service_text( sprintf( '%02d / %s', $index, $service[0] ), 0, 'zion-overview-label' ) .
				zion_theme_service_text( array( '▱', '◇', '⌘', '☁', '⇄', '▣', '✦' )[ $index - 1 ], 0, 'zion-overview-icon' )
			) .
			zion_theme_service_group(
				'zion-overview-card-body',
				zion_theme_service_text( $story[1], 3 ) .
				zion_theme_service_group(
					'zion-overview-card-copy',
					zion_theme_service_text( $service[1] . ' ' . $story[3] ) .
					zion_theme_service_group( 'zion-overview-tags', implode( '', array_map( 'zion_theme_service_text', array_slice( $service, 2 ) ) ) )
				)
			) .
			zion_theme_service_buttons( array( 'Vezi ' . $service[0] . ' ↗' => $url ? $url : home_url( '/' . $slug . '/' ) ) )
		);
	}
	$services = zion_theme_service_group(
		'zion-overview-band zion-overview-services',
		zion_theme_service_text( 'Expertiza noastră', 0, 'zion-overview-label' ) .
		zion_theme_service_text( 'Șapte servicii pentru ideea ta', 2 ) .
		zion_theme_service_group( 'zion-overview-card-grid', $cards ),
		'exploreaza-serviciile'
	);
	$cases    = zion_theme_service_group(
		'zion-overview-band zion-overview-cases zion-overview-split',
		zion_theme_service_group( 'zion-overview-heading', zion_theme_service_text( 'Pentru cine lucrăm', 0, 'zion-overview-label' ) . zion_theme_service_text( 'Idei din industrii diferite', 2 ) . zion_theme_service_text( 'Ne poți spune ce vrei să obții chiar dacă soluția tehnică încă nu e clară.' ) ) .
		zion_theme_service_cards(
			array(
				'Producție și ateliere'            => 'Piese, prototipuri și utilitare de lucru.',
				'Comerț și magazine online'        => 'Extensii, integrări și automatizări.',
				'HoReCa și servicii'               => 'Rezervări, comenzi și operațiuni digitale.',
				'Antreprenori și echipe de produs' => 'Prototipuri și prime versiuni de produs.',
			)
		)
	);
	$process  = zion_theme_service_group(
		'zion-overview-band zion-overview-process',
		zion_theme_service_text( 'Cum lucrăm', 0, 'zion-overview-label' ) .
		zion_theme_service_text( 'Patru pași de la discuție la livrare', 2 ) .
		zion_theme_service_cards(
			array(
				'Ascultăm'    => 'Clarificăm obiectivul, contextul și utilizatorii.',
				'Propunem'    => 'Discutăm variantele și stabilim etapele.',
				'Construim'   => 'Dezvoltăm, testăm și îți arătăm progresul.',
				'Îmbunătățim' => 'Stabilim ce merită ajustat după livrare.',
			)
		)
	);
	$details  = '';
	foreach ( array(
		'Pot începe doar cu o idee?'           => 'Da. Spune-ne ce problemă vrei să rezolvi și pentru cine.',
		'Lucrați și la proiecte mici?'         => 'Da. Putem discuta inclusiv despre o piesă unicat sau o modificare punctuală.',
		'Cum se stabilește bugetul?'           => 'Estimarea depinde de cerințe, materiale și timpul necesar.',
		'Puteți extinde un software existent?' => 'Începem prin a verifica accesul și opțiunile de integrare.',
		'Putem colabora la distanță?'          => 'Da. Pentru obiectele fizice stabilim separat detaliile de livrare.',
	) as $question => $answer ) {
		$details .= get_comment_delimited_block_content( 'core/details', array(), '<details class="wp-block-details"><summary>' . esc_html( $question ) . '</summary>' . zion_theme_service_text( $answer ) . '</details>' );
	}
	$faq = zion_theme_service_group(
		'zion-overview-band zion-overview-faq zion-overview-split',
		zion_theme_service_group( 'zion-overview-heading', zion_theme_service_text( 'Întrebări frecvente', 0, 'zion-overview-label' ) . zion_theme_service_text( 'Ce e bine să știi înainte să începem', 2 ) ) .
		zion_theme_service_group( 'zion-overview-answers', $details )
	);
	$end = zion_theme_service_group(
		'zion-overview-band zion-overview-end',
		zion_theme_service_text( 'Următorul pas', 0, 'zion-overview-label' ) .
		zion_theme_service_text( 'Hai să dăm formă ideii tale.', 2 ) .
		zion_theme_service_text( 'Povestește-ne pe scurt ce ai nevoie și vedem de unde putem începe.' ) .
		zion_theme_service_buttons( array( 'Solicită o discuție ↗' => $contact ) )
	);
	return zion_theme_service_group( 'zion-services-overview', $hero . $intro . $services . $cases . $process . $faq . $end );
}
