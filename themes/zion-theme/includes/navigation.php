<?php
/**
 * Native block markup for the default navigation.
 *
 * @package Zion_Theme
 */

/**
 * Resolve a published page at its existing path.
 *
 * @param string $path Page path.
 * @return string
 */
function zion_theme_published_page_url( string $path ): string {
	$page = get_page_by_path( $path );
	return $page && 'publish' === $page->post_status ? (string) get_permalink( $page ) : '';
}

/**
 * Build a navigation item or submenu from resolved links.
 *
 * @param string $label Menu label.
 * @param string $url Menu URL.
 * @param array  $children Child block markup.
 * @return string
 */
function zion_theme_nav_item( string $label, string $url, array $children = array() ): string {
	$children = array_values( array_filter( $children ) );
	if ( ! $url && ! $children ) {
		return '';
	}
	$attrs = array(
		'label' => $label,
		'kind'  => 'custom',
	);
	if ( $url ) {
		$attrs['url'] = esc_url_raw( $url );
	}
	$json = serialize_block_attributes( $attrs );
	return $children
		? '<!-- wp:navigation-submenu ' . $json . ' -->' . implode( '', $children ) . '<!-- /wp:navigation-submenu -->'
		: '<!-- wp:navigation-link ' . $json . ' /-->';
}

/**
 * Reproduce the public information architecture using local destinations.
 *
 * @return string
 */
function zion_theme_primary_navigation(): string {
	$services = array();
	$labels   = array(
		'printare-3d'             => 'Printare 3D',
		'proiectare-3d'           => 'Proiectare și modelare 3D',
		'software-personalizat'   => 'Software personalizat pentru afaceri',
		'platforme-saas'          => 'Platforme și produse SaaS',
		'pluginuri-si-integrari'  => 'Pluginuri și integrări software',
		'aplicatii-windows-linux' => 'Aplicații Windows și Linux',
		'integrari-ai'            => 'Integrări AI și automatizări inteligente',
	);
	foreach ( $labels as $slug => $label ) {
		$services[] = zion_theme_nav_item( $label, zion_theme_service_url( $slug ) );
	}
	$extensions = zion_theme_published_page_url( 'pluginuri-woocommerce' );
	$software   = array(
		zion_theme_nav_item( 'Magazin Software', zion_theme_published_page_url( 'magazin-software' ) ),
		zion_theme_nav_item(
			'WordPress & WooCommerce',
			$extensions,
			array(
				zion_theme_nav_item( 'Zion SMS Confirmation', zion_theme_published_page_url( 'zion-sms-confirmation-woocommerce' ) ),
				zion_theme_nav_item( 'Zion Privacy', zion_theme_published_page_url( 'pluginuri-woocommerce/zion-privacy' ) ),
				zion_theme_nav_item( 'Zion Multi-Currency', zion_theme_published_page_url( 'zion-multi-currency-for-woocommerce' ) ),
			)
		),
		zion_theme_nav_item(
			'Synology DSM',
			'',
			array(
				zion_theme_nav_item( 'Zion ReleaseStation', zion_theme_published_page_url( 'zion-releasestation-git-deployment-automatizat-pentru-synology-dsm' ) ),
				zion_theme_nav_item( 'Zion ReleaseStation · English', zion_theme_published_page_url( 'zion-releasestation-automated-git-deployment-for-synology-dsm' ) ),
			)
		),
	);

	return zion_theme_nav_item( 'Acasă', home_url( '/' ) ) .
		zion_theme_nav_item( 'Magazin 3D', zion_theme_shop_url( 'magazin-3d' ) ) .
		zion_theme_nav_item( 'Servicii', zion_theme_published_page_url( 'servicii' ), $services ) .
		zion_theme_nav_item( 'Software', $extensions, $software ) .
		zion_theme_nav_item( 'Zion Food', 'https://food.zion3d.ro' ) .
		zion_theme_nav_item(
			'Resurse',
			zion_theme_journal_url(),
			array(
				zion_theme_nav_item( 'Blog', zion_theme_journal_url() ),
				zion_theme_nav_item( 'Întrebări', zion_theme_published_page_url( 'intrebari' ) ),
			)
		) .
		zion_theme_nav_item( 'Despre noi', zion_theme_published_page_url( 'despre-noi' ) ) .
		zion_theme_nav_item( 'Contact', zion_theme_published_page_url( 'contact' ) );
}
