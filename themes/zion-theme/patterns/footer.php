<?php
/**
 * Title: Zion Nexus footer
 * Slug: zion-theme/footer
 * Inserter: no
 *
 * @package Zion_Theme
 */

$zion_theme_contact = zion_theme_published_page_url( 'contact' );
$zion_theme_chat    = 'https://wa.me/40770675597';
$zion_theme_terms   = function_exists( 'wc_terms_and_conditions_page_id' ) ? wc_terms_and_conditions_page_id() : 0;
$zion_theme_groups  = array(
	array(
		'01 / PHYSICAL WORLD',
		'3D LAB',
		array(
			'Toate serviciile'          => zion_theme_published_page_url( 'servicii' ),
			'Printare 3D la comandă'    => zion_theme_service_url( 'printare-3d' ),
			'Proiectare și modelare 3D' => zion_theme_service_url( 'proiectare-3d' ),
			'Magazin 3D'                => zion_theme_published_page_url( 'magazin-3d' ),
		),
	),
	array(
		'02 / DIGITAL WORLD',
		'DIGITAL STUDIO',
		array(
			'Software personalizat'       => zion_theme_service_url( 'software-personalizat' ),
			'Platforme și produse SaaS'   => zion_theme_service_url( 'platforme-saas' ),
			'Pluginuri și integrări'      => zion_theme_service_url( 'pluginuri-si-integrari' ),
			'Aplicații Windows & Linux'   => zion_theme_service_url( 'aplicatii-windows-linux' ),
			'Integrări AI & Automatizări' => zion_theme_service_url( 'integrari-ai' ),
		),
	),
	array(
		'03 / OUR PRODUCTS',
		'ZION ECOSYSTEM',
		array(
			'WordPress & WooCommerce' => zion_theme_published_page_url( 'pluginuri-woocommerce' ),
			'Zion Privacy'            => zion_theme_published_page_url( 'pluginuri-woocommerce/zion-privacy' ),
			'Zion SMS Confirmation'   => zion_theme_published_page_url( 'zion-sms-confirmation-woocommerce' ),
			'Zion Multi-Currency'     => zion_theme_published_page_url( 'zion-multi-currency-for-woocommerce' ),
		),
	),
	array(
		'04 / STAY CONNECTED',
		'DISCOVER',
		array(
			'Acasă'               => home_url( '/' ),
			'Despre noi'          => zion_theme_published_page_url( 'despre-noi' ),
			'Blog'                => zion_theme_journal_url(),
			'Întrebări frecvente' => zion_theme_published_page_url( 'intrebari' ),
			'Contact'             => $zion_theme_contact,
			'Contul meu'          => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '',
		),
	),
);

$zion_theme_partners = array(
	'MakerWorld ↗'                 => 'https://makerworld.com/',
	'Printables ↗'                 => 'https://www.printables.com/',
	'Thingiverse ↗'                => 'https://www.thingiverse.com/',
	'Time 2 Decorate ↗'            => 'https://time2decorate.co.uk/',
	'Hausmeisterdienst Konstanz ↗' => 'https://hausmeisterdienst-konstanz.de/',
);
$zion_theme_privacy  = get_privacy_policy_url();
$zion_theme_legal    = array(
	'Confidențialitate'   => $zion_theme_privacy ? $zion_theme_privacy : zion_theme_published_page_url( 'politica-de-confidentialitate' ),
	'Termeni și condiții' => $zion_theme_terms && 'publish' === get_post_status( $zion_theme_terms ) ? get_permalink( $zion_theme_terms ) : zion_theme_published_page_url( 'termeni-si-conditii' ),
	'ANPC'                => 'https://anpc.ro/',
);

$zion_theme_nav = static function ( string $label, array $links, string $css_class = '', bool $vertical = false ): string {
	$items = '';
	foreach ( $links as $title => $url ) {
		$items .= zion_theme_nav_item( $title, $url );
	}
	if ( '' === $items ) {
		return '';
	}
	return get_comment_delimited_block_content(
		'core/navigation',
		array(
			'ariaLabel'   => $label,
			'overlayMenu' => 'never',
			'className'   => $css_class,
			'layout'      => array(
				'type'        => 'flex',
				'orientation' => $vertical ? 'vertical' : 'horizontal',
			),
		),
		$items
	);
};

$zion_theme_columns = '';
$zion_theme_extra   = array(
	'ZION ECOSYSTEM' => array(
		'CLOUD & PLATFORM' => array(
			'ZionFood'           => 'https://food.zion3d.ro/',
			'Zion Storage Cloud' => 'https://storage.zion3d.ro/',
		),
		'SYNOLOGY DSM'     => array(
			'Zion ReleaseStation' => zion_theme_published_page_url( 'zion-releasestation-git-deployment-automatizat-pentru-synology-dsm' ),
		),
	),
	'DISCOVER'       => array(
		'DIRECT CONTACT' => array(
			'+40 770 675 597' => 'tel:+40770675597',
			'WhatsApp'        => $zion_theme_chat,
		),
	),
);
foreach ( $zion_theme_groups as $zion_theme_group ) {
	$zion_theme_subgroups = '';
	foreach ( $zion_theme_extra[ $zion_theme_group[1] ] ?? array() as $zion_theme_label => $zion_theme_links ) {
		$zion_theme_links = array_filter( $zion_theme_links );
		if ( $zion_theme_links ) {
			$zion_theme_subgroups .= zion_theme_service_group(
				'DIRECT CONTACT' === $zion_theme_label ? 'zion-nexus-subgroup zion-footer-contact' : 'zion-nexus-subgroup',
				zion_theme_service_text( $zion_theme_label, 0, 'zion-kicker' ) .
				$zion_theme_nav( $zion_theme_label, $zion_theme_links, '', true )
			);
		}
	}
	$zion_theme_columns .= zion_theme_service_group(
		'zion-nexus-column',
		zion_theme_service_text( $zion_theme_group[0], 0, 'zion-kicker' ) .
		zion_theme_service_text( $zion_theme_group[1], 3 ) .
		$zion_theme_nav( $zion_theme_group[1], $zion_theme_group[2], '', true ) .
		$zion_theme_subgroups
	);
}
?>
<!-- wp:group {"align":"full","className":"zion-footer zion-nexus"} -->
<div class="wp-block-group alignfull zion-footer zion-nexus">
<!-- wp:group {"className":"zion-shell zion-nexus-shell"} -->
<div class="wp-block-group zion-shell zion-nexus-shell">
<!-- wp:group {"className":"zion-nexus-status"} -->
<div class="wp-block-group zion-nexus-status">
<!-- wp:paragraph --><p>ZION 3D / READY FOR NEW IDEAS</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>DESIGN · ENGINEERING · SOFTWARE · AI</p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"zion-nexus-hero"} -->
<div class="wp-block-group zion-nexus-hero">
<!-- wp:group {"className":"zion-nexus-intro"} -->
<div class="wp-block-group zion-nexus-intro">
<!-- wp:paragraph {"className":"zion-kicker"} --><p class="zion-kicker">FROM CONCEPT TO REALITY / EST. 2021</p><!-- /wp:paragraph -->
<!-- wp:heading {"className":"zion-nexus-title"} --><h2 class="wp-block-heading zion-nexus-title">IDEILE NU<br><em>STAU PE LOC.</em></h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>De la piese proiectate și printate 3D la platforme digitale, aplicații la comandă și automatizări. <strong>Tu vii cu provocarea. Noi construim soluția.</strong></p><!-- /wp:paragraph -->
<?php
echo zion_theme_service_group( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native blocks serialize escaped links.
	'zion-footer-cta',
	zion_theme_service_buttons(
		array(
			'PORNEȘTE UN PROIECT ↗' => $zion_theme_contact ? $zion_theme_contact : home_url( '/contact/' ),
			'HAI SĂ VORBIM ↗'       => $zion_theme_chat,
		)
	)
);
?>
</div>
<!-- /wp:group -->
<!-- wp:html -->
<div class="zion-nexus-blueprint" aria-hidden="true"><span>01 / DESIGN</span><span>02 / BUILD</span><span>03 / DEPLOY</span></div>
<!-- /wp:html -->
</div>
<!-- /wp:group -->
<!-- wp:paragraph {"className":"zion-nexus-steps"} --><p class="zion-nexus-steps">01 IMAGINEAZĂ ✳ 02 PROIECTĂM ✳ 03 CONSTRUIM ✳ 04 EVOLUĂM</p><!-- /wp:paragraph -->
<!-- wp:group {"className":"zion-footer-resources zion-nexus-resources"} -->
<div class="wp-block-group zion-footer-resources zion-nexus-resources">
<!-- wp:paragraph {"className":"zion-kicker"} --><p class="zion-kicker">EXPLORE THE ZION UNIVERSE</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2 class="wp-block-heading">Un univers de soluții.</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Explorează serviciile, produsele și resursele din ecosistemul Zion 3D.</p><!-- /wp:paragraph -->
<?php echo zion_theme_service_group( 'zion-nexus-directory', $zion_theme_columns ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native blocks serialize escaped content. ?>
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"zion-nexus-signature"} -->
<div class="wp-block-group zion-nexus-signature">
<!-- wp:group {"className":"zion-nexus-network"} -->
<div class="wp-block-group zion-nexus-network">
<?php echo $zion_theme_nav( 'Parteneri și comunități', $zion_theme_partners, 'zion-footer-social zion-nexus-partners' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native blocks serialize escaped links. ?>
<!-- wp:paragraph {"className":"zion-kicker"} --><p class="zion-kicker">◉ RÂMNICU VÂLCEA · ROMÂNIA</p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:paragraph {"className":"zion-nexus-wordmark"} --><p class="zion-nexus-wordmark">ZION <strong>3D</strong></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"zion-footer-meta"} -->
<div class="wp-block-group zion-footer-meta">
<!-- wp:paragraph {"className":"zion-footer-copyright"} -->
<p class="zion-footer-copyright">© <?php echo esc_html( wp_date( 'Y' ) ); ?> Zion 3D Print Vâlcea. Designed to create.</p>
<!-- /wp:paragraph -->
<?php echo $zion_theme_nav( 'Linkuri legale', $zion_theme_legal, 'zion-footer-legal' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native blocks serialize escaped links. ?>
<!-- wp:zion/editorial {"part":"back_top"} /-->
</div>
<!-- /wp:group -->
<!-- wp:search {"label":"Caută pe site","placeholder":"Ex. prototip","buttonText":"Caută","className":"zion-search zion-footer-search"} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
