<?php
/**
 * Title: Zion · Homepage / Creative engineering
 * Slug: zion-theme/home
 * Categories: zion-library
 *
 * @package Zion_Theme
 */

$zion_theme_contact = zion_theme_published_page_url( 'contact' );
$zion_theme_contact = $zion_theme_contact ? $zion_theme_contact : home_url( '/contact/' );
$zion_theme_hero    = zion_theme_service_group(
	'zion-overview-band zion-overview-hero zion-home-hero',
	zion_theme_service_group(
		'zion-home-hero-grid',
		zion_theme_service_group(
			'zion-home-hero-copy',
			zion_theme_service_text( 'ZION 3D / CREATIVE ENGINEERING', 0, 'zion-overview-label' ) .
			get_comment_delimited_block_content(
				'core/heading',
				array( 'level' => 1 ),
				'<h1 class="wp-block-heading">Imaginează.<br><em>Noi construim.</em></h1>'
			) .
			zion_theme_service_text( 'Piesa care lipsește. Aplicația care simplifică. Ideea care merită adusă în lumea reală.', 0, 'zion-home-lead' ) .
			zion_theme_service_buttons(
				array(
					'Descoperă ultimele produse ↓' => '#magazine',
					'Discutăm ideea ta ↗'          => $zion_theme_contact,
				)
			) .
			zion_theme_service_text( 'Printare 3D · Proiectare · Software · AI', 0, 'zion-home-capabilities' )
		) .
		get_comment_delimited_block_content(
			'core/html',
			array(),
			'<div class="zion-home-lab zion-blueprint" aria-hidden="true">
				<div class="zion-blueprint-label"><span>DESIGN / BUILD / EVOLVE</span><span>Z / 01</span></div>
				<svg viewBox="0 0 520 420" focusable="false">
					<g fill="none" stroke="currentColor">
						<path d="M35 338h450M72 362V54M448 362V54M53 80h414M53 322h414" opacity=".2" stroke-dasharray="3 7"/>
						<path d="M62 80h20M72 70v20M438 322h20M448 312v20" opacity=".7"/>
						<path d="m260 228 135 68-135 70-135-70z" opacity=".25"/>
						<path d="m260 199 135 68-135 70-135-70z" opacity=".45"/>
						<path d="m260 168 135 68-135 70-135-70z" opacity=".75" stroke-width="1.5"/>
						<path d="m260 75 104 53v98l-104 54-104-54v-98z" class="zion-home-solid" stroke-width="2"/>
						<path d="m156 128 104 54 104-54M260 182v98M260 75v43l-42 22" stroke-width="2"/>
						<path d="M156 169h-42l-36-40H35M364 199h40l35-39h46M260 43V22" opacity=".65"/>
						<circle cx="35" cy="129" r="4" fill="currentColor"/>
						<circle cx="485" cy="160" r="4" fill="currentColor"/>
					</g>
					<g fill="currentColor" font-family="monospace" font-size="11" letter-spacing="1">
						<text x="22" y="111">3D LAB</text><text x="394" y="142">&lt;/&gt; DIGITAL</text>
						<text x="192" y="395">IDEA → REALITY</text>
					</g>
				</svg>
				<div class="zion-blueprint-label"><span>FIZIC + DIGITAL</span><span>CONSTRUIT ÎN VÂLCEA, RO</span></div>
			</div>'
		)
	) .
	zion_theme_service_group(
		'zion-home-hero-note',
		zion_theme_service_text( '01 / DE LA SCHIȚĂ LA OBIECT' ) .
		zion_theme_service_text( '02 / DE LA PROBLEMĂ LA SOLUȚIE' ) .
		zion_theme_service_text( '03 / DE LA IDEE LA PRODUS' )
	)
);

$zion_theme_products = zion_theme_service_group(
	'zion-overview-band zion-home-products',
	zion_theme_service_group(
		'zion-home-section-head',
		zion_theme_service_group(
			'zion-home-heading',
			zion_theme_service_text( '01 / NOU ÎN MAGAZINUL 3D', 0, 'zion-overview-label' ) .
			zion_theme_service_text( 'Lucruri mici. Posibilități mari.', 2 ) .
			zion_theme_service_text( 'Cele mai noi piese, accesorii și modele din atelier. Alege un produs și descoperă detaliile care îl fac potrivit pentru tine.' )
		) .
		zion_theme_service_buttons( array( 'Tot magazinul 3D ↗' => zion_theme_shop_url( 'magazin-3d' ) ) )
	) .
	'<!-- wp:zion/new-arrivals {"shop":"3d","limit":6,"columns":3} /-->',
	'magazine'
);

$zion_theme_paths = zion_theme_service_group(
	'zion-overview-band zion-home-paths',
	zion_theme_service_text( '02 / ALEGE DIRECȚIA', 0, 'zion-overview-label' ) .
	zion_theme_service_text( 'Din atelier. Din cod. Pentru tine.', 2 ) .
	zion_theme_service_group(
		'zion-home-path-grid',
		zion_theme_service_group(
			'zion-home-path zion-home-path-physical',
			zion_theme_service_text( '01 / PHYSICAL WORLD', 0, 'zion-overview-label' ) .
			zion_theme_service_text( 'Ce poți ține în mână.', 3 ) .
			zion_theme_service_text( 'O piesă de schimb, un accesoriu sau primul prototip. Descoperă obiecte gata de comandă sau povestește-ne ce ai nevoie să fabricăm.' ) .
			zion_theme_service_text( 'PIESE · MODELE · PROTOTIPURI', 0, 'zion-home-path-tags' ) .
			zion_theme_service_buttons( array( 'Explorează Magazin 3D ↗' => zion_theme_shop_url( 'magazin-3d' ) ) )
		) .
		zion_theme_service_group(
			'zion-home-path zion-home-path-digital',
			zion_theme_service_text( '02 / DIGITAL WORLD', 0, 'zion-overview-label' ) .
			zion_theme_service_text( 'Ce îți dă timpul înapoi.', 3 ) .
			zion_theme_service_text( 'Pluginuri, aplicații și integrări care leagă instrumentele pe care le folosești. Explorează produsele software sau construim unul pentru fluxul tău.' ) .
			zion_theme_service_text( 'APLICAȚII · PLUGINURI · AUTOMATIZĂRI', 0, 'zion-home-path-tags' ) .
			zion_theme_service_buttons( array( 'Explorează Magazin Software ↗' => zion_theme_shop_url( 'magazin-software' ) ) )
		)
	)
);

$zion_theme_service_tiles = '';
$zion_theme_service_index = 0;
foreach ( zion_theme_services() as $zion_theme_slug => $zion_theme_service ) {
	++$zion_theme_service_index;
	$zion_theme_url            = zion_theme_service_url( $zion_theme_slug );
	$zion_theme_service_tiles .= zion_theme_service_group(
		'zion-home-service' . ( 7 === $zion_theme_service_index ? ' zion-home-service-ai' : '' ),
		zion_theme_service_text( sprintf( '%02d /', $zion_theme_service_index ), 0, 'zion-overview-label' ) .
		get_comment_delimited_block_content(
			'core/heading',
			array( 'level' => 3 ),
			'<h3 class="wp-block-heading"><a href="' . esc_url( $zion_theme_url ? $zion_theme_url : home_url( '/servicii/' ) ) . '">' . esc_html( $zion_theme_service[0] ) . '<span aria-hidden="true"> ↗</span></a></h3>'
		) .
		zion_theme_service_text( $zion_theme_service[1] )
	);
}
$zion_theme_services = zion_theme_service_group(
	'zion-overview-band zion-home-services',
	zion_theme_service_group(
		'zion-home-section-head',
		zion_theme_service_group(
			'zion-home-heading',
			zion_theme_service_text( '03 / CONSTRUIT ÎN JURUL IDEII TALE', 0, 'zion-overview-label' ) .
			zion_theme_service_text( 'Șapte puncte de plecare.', 2 ) .
			zion_theme_service_text( 'Nu găsești soluția potrivită în magazin? Pornim de la ce vrei să obții și alegem împreună ce merită construit.' )
		) .
		zion_theme_service_buttons( array( 'Vezi toate serviciile ↗' => home_url( '/servicii/' ) ) )
	) .
	zion_theme_service_group( 'zion-home-service-grid', $zion_theme_service_tiles ),
	'servicii'
);

$zion_theme_cases = zion_theme_service_group(
	'zion-overview-band zion-home-cases',
	zion_theme_service_text( '04 / RECUNOȘTI PROVOCAREA?', 0, 'zion-overview-label' ) .
	zion_theme_service_text( 'De aici poate începe ceva bun.', 2 ) .
	zion_theme_service_cards(
		array(
			'O piesă care nu se mai găsește.'  => 'Un suport rupt sau o componentă ieșită din fabricație? Discutăm despre geometrie, material și cum trebuie să funcționeze.',
			'Prea multe operațiuni manuale.'   => 'Copiezi aceleași date între aplicații? Putem conecta sistemele și simplifica pașii care consumă timpul echipei.',
			'O idee care are nevoie de formă.' => 'Ai o schiță, un concept sau o problemă nouă? Începem cu un model ori o primă versiune pe care o poți testa.',
		)
	) .
	zion_theme_service_buttons( array( 'Spune-ne ce vrei să rezolvi ↗' => $zion_theme_contact ) )
);

$zion_theme_process = zion_theme_service_group(
	'zion-overview-band zion-home-process',
	zion_theme_service_group(
		'zion-home-section-head',
		zion_theme_service_group(
			'zion-home-heading',
			zion_theme_service_text( '05 / CUM LUCRĂM', 0, 'zion-overview-label' ) .
			zion_theme_service_text( 'De la „dacă?” la „uite-l”.', 2 )
		) .
		zion_theme_service_text( 'O conversație. O direcție clară. Ceva real de testat.', 0, 'zion-home-process-intro' )
	) .
	zion_theme_service_cards(
		array(
			'Ne spui ideea.'       => 'O schiță, o fotografie sau câteva rânduri sunt suficiente ca să începem discuția.',
			'Stabilim direcția.'   => 'Clarificăm funcționalitatea, materialele sau tehnologiile, etapele și bugetul estimativ.',
			'Construim și testăm.' => 'Dezvoltăm, verificăm și ajustăm rezultatul pentru felul în care îl vei folosi.',
		)
	)
);

$zion_theme_journal = zion_theme_service_group(
	'zion-overview-band zion-home-journal',
	zion_theme_service_text( '06 / DIN CULISE', 0, 'zion-overview-label' ) .
	zion_theme_service_text( 'Idei de luat mai departe.', 2 ) .
	zion_theme_service_text( 'Povești, ghiduri și perspective din lumea 3D și a produselor digitale.' ) .
	'<!-- wp:query {"queryId":91,"query":{"perPage":3,"postType":"post","order":"desc","orderBy":"date","inherit":false},"className":"zion-home-recent"} -->
	<div class="wp-block-query zion-home-recent">
	<!-- wp:post-template -->
	<!-- wp:group {"tagName":"article","className":"zion-home-post"} -->
	<article class="wp-block-group zion-home-post">
	<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->
	<!-- wp:post-date {"className":"zion-kicker"} /-->
	<!-- wp:post-title {"level":3,"isLink":true} /-->
	<!-- wp:post-excerpt {"excerptLength":18} /-->
	</article>
	<!-- /wp:group -->
	<!-- /wp:post-template -->
	<!-- wp:query-no-results --><!-- wp:paragraph --><p>Următoarele articole vor apărea aici.</p><!-- /wp:paragraph --><!-- /wp:query-no-results -->
	</div><!-- /wp:query -->'
);

$zion_theme_questions = '';
foreach ( array(
	'Am doar o idee. Este suficient?' => 'Da. Spune-ne ce vrei să obții, unde apare problema și cum ai vrea să folosești rezultatul. Clarificăm împreună pașii următori.',
	'Pot comanda o singură piesă?'    => 'Putem discuta inclusiv despre unicat, piese de schimb și prototipuri. Fezabilitatea, materialul și costul se stabilesc după ce vedem cerințele.',
	'Putem lucra și de la distanță?'  => 'Da. Putem discuta și dezvolta proiectele digitale de la distanță. Pentru piesele fizice, stabilim împreună detaliile de măsurare și livrare.',
) as $zion_theme_question => $zion_theme_answer ) {
	$zion_theme_questions .= get_comment_delimited_block_content(
		'core/details',
		array(),
		'<details class="wp-block-details"><summary>' . esc_html( $zion_theme_question ) . '</summary>' . zion_theme_service_text( $zion_theme_answer ) . '</details>'
	);
}
$zion_theme_faq = zion_theme_service_group(
	'zion-overview-band zion-home-faq zion-overview-split',
	zion_theme_service_group(
		'zion-home-heading',
		zion_theme_service_text( '07 / ÎNAINTE SĂ ÎNCEPEM', 0, 'zion-overview-label' ) .
		zion_theme_service_text( 'Curiozitatea e un început bun.', 2 ) .
		zion_theme_service_text( 'N-ai nevoie de toate răspunsurile. De aceea discutăm mai întâi.' )
	) .
	zion_theme_service_group( 'zion-home-questions', $zion_theme_questions )
);

echo zion_theme_service_group( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- All content is serialized as native blocks with escaped text and URLs.
	'alignwide zion-services-overview zion-homepage',
	$zion_theme_hero . $zion_theme_products . $zion_theme_paths . $zion_theme_services . $zion_theme_cases . $zion_theme_process . $zion_theme_journal . $zion_theme_faq
);
