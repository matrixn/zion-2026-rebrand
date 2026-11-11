<?php
/**
 * Shared presentation for the native, editable Navigation block.
 *
 * @package Zion_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Match known destinations without changing their saved labels or URLs.
 *
 * @param string $label Visible native menu label.
 * @return array
 */
function zion_theme_menu_presentation( string $label ): array {
	$text  = strtolower( remove_accents( wp_strip_all_tags( $label ) ) );
	$items = array(
		'integrari ai'     => array( 'ai', 'Automatizări și fluxuri inteligente.' ),
		'printare'         => array( 'cube', 'Prototipuri și piese funcționale.' ),
		'proiectare'       => array( 'ruler', 'Modelare CAD pentru fabricație.' ),
		'windows'          => array( 'desktop', 'Instrumente desktop personalizate.' ),
		'linux'            => array( 'desktop', 'Instrumente desktop personalizate.' ),
		'personalizat'     => array( 'code', 'Aplicații construite pentru afacerea ta.' ),
		'saas'             => array( 'cloud', 'Produse digitale în cloud.' ),
		'platforme'        => array( 'cloud', 'Produse digitale în cloud.' ),
		'synology'         => array( 'server', 'Soluții pentru ecosistemul Synology.' ),
		'releasestation'   => array( 'branch', 'Deploy Git organizat pe Synology DSM.' ),
		'privacy'          => array( 'shield', 'Control și transparență pentru confidențialitate.' ),
		'sms'              => array( 'message', 'Confirmări și comunicare cu clienții.' ),
		'currency'         => array( 'coins', 'Prețuri și plăți în mai multe monede.' ),
		'food'             => array( 'food', 'Soluții digitale pentru restaurante.' ),
		'wordpress'        => array( 'plugin', 'Extensii pentru WordPress și WooCommerce.' ),
		'plugin'           => array( 'plugin', 'Extensii și conectare între sisteme.' ),
		'integrari'        => array( 'plugin', 'Extensii și conectare între sisteme.' ),
		'magazin 3d'       => array( 'cube', 'Piese, accesorii și modele pentru printare.' ),
		'magazin software' => array( 'bag', 'Aplicații, pluginuri și soluții digitale.' ),
		'acasa'            => array( 'home', '' ),
		'home'             => array( 'home', '' ),
		'servicii'         => array( 'tools', 'Soluții fizice și digitale pentru proiectul tău.' ),
		'software'         => array( 'code', 'Explorează ecosistemul digital Zion.' ),
		'blog'             => array( 'document', 'Idei și noutăți din atelier.' ),
		'jurnal'           => array( 'document', 'Idei și noutăți din atelier.' ),
		'intrebari'        => array( 'message', 'Răspunsuri pentru proiectul tău.' ),
		'resurse'          => array( 'document', 'Articole, ghiduri și răspunsuri.' ),
		'despre'           => array( 'users', 'Oamenii și ideile din spatele Zion.' ),
		'contact'          => array( 'mail', 'Hai să discutăm despre proiectul tău.' ),
	);
	foreach ( $items as $match => $item ) {
		if ( str_contains( $text, $match ) ) {
			return $item;
		}
	}
	return array( 'arrow', '' );
}

/**
 * Original lightweight outline icons, sharing one visual language.
 *
 * @param string $name Icon key.
 * @return string
 */
function zion_theme_menu_icon( string $name ): string {
	$paths = array(
		'home'     => '<path d="m3 10 9-7 9 7M5 9v12h5v-7h4v7h5V9"/>',
		'cube'     => '<path d="m12 2 9 5v10l-9 5-9-5V7l9-5Zm0 10 9-5M12 12 3 7m9 5v10"/>',
		'ruler'    => '<path d="m3 15 12-12 6 6L9 21l-6-6Zm5-5 3 3m1-7 3 3m-3 5 3 3"/>',
		'desktop'  => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8m-4-4v4"/>',
		'code'     => '<path d="m8 5-6 7 6 7m8-14 6 7-6 7m-3-16-2 20"/>',
		'cloud'    => '<path d="M6 19a5 5 0 0 1-1-10 7 7 0 0 1 13-2 6 6 0 0 1 0 12H6Z"/>',
		'server'   => '<rect x="3" y="2" width="18" height="8" rx="2"/><rect x="3" y="14" width="18" height="8" rx="2"/><path d="M7 6h1m-1 12h1m5-12h4m-4 12h4"/>',
		'branch'   => '<circle cx="6" cy="4" r="2"/><circle cx="18" cy="5" r="2"/><circle cx="6" cy="20" r="2"/><path d="M6 6v12m0-5c8 0 12-1 12-6"/>',
		'shield'   => '<path d="m12 2 9 4v6c0 5-9 10-9 10S3 17 3 12V6l9-4Zm-4 10 3 3 5-6"/>',
		'message'  => '<path d="M4 3h16a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H8l-6 4V5a2 2 0 0 1 2-2Z"/><path d="M7 8h10M7 13h7"/>',
		'coins'    => '<ellipse cx="9" cy="6" rx="7" ry="3"/><path d="M2 6v5c0 2 4 3 7 3m7-8v4m-14 1v5c0 2 4 3 7 3"/><ellipse cx="16" cy="14" rx="6" ry="3"/><path d="M10 14v5c0 4 12 4 12 0v-5"/>',
		'food'     => '<path d="M4 2v7m4-7v7M2 2v7a4 4 0 0 0 8 0V2M6 13v9m13 0V2c-5 4-5 10 0 11"/>',
		'plugin'   => '<path d="M8 2v5m8-5v5M5 7h14v5a7 7 0 0 1-14 0V7Zm7 12v3"/>',
		'bag'      => '<path d="M4 7h16l1 15H3L4 7Zm4 0V5a4 4 0 0 1 8 0v2"/>',
		'tools'    => '<path d="m4 3 17 17-3 3L1 6l3-3Zm15-1a5 5 0 0 0-6 6L3 18a2 2 0 0 0 3 3L16 11a5 5 0 0 0 6-6l-4 4-3-3 4-4Z"/>',
		'document' => '<path d="M4 2h11l5 5v15H4V2Zm11 0v6h5M8 12h8m-8 5h8"/>',
		'users'    => '<circle cx="9" cy="7" r="4"/><path d="M1 22v-3a8 8 0 0 1 16 0v3M17 3a4 4 0 0 1 0 8m4 11v-3a8 8 0 0 0-3-6"/>',
		'mail'     => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m3 5 9 8 9-8"/>',
		'ai'       => '<rect x="5" y="5" width="14" height="14" rx="3"/><path d="M9 1v4m6-4v4M9 19v4m6-4v4M1 9h4m-4 6h4m14-6h4m-4 6h4M9 9h6v6H9V9Z"/>',
		'search'   => '<circle cx="10" cy="10" r="7"/><path d="m15 15 7 7"/>',
		'arrow'    => '<path d="M5 19 19 5H7m12 0v12"/>',
	);
	$name  = isset( $paths[ $name ] ) ? $name : 'arrow';
	return '<svg class="zion-menu-icon zion-menu-icon--' . esc_attr( $name ) . '" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">' . $paths[ $name ] . '</svg>';
}

/**
 * Decorate labels while retaining every native link, toggle and directive.
 *
 * @param string $html Native navigation HTML.
 * @param array  $block Parsed native block.
 * @return string
 */
function zion_theme_menu_design( string $html, array $block ): string {
	if ( is_admin() || wp_is_serving_rest_request() || ! in_array( 'zion-navigation', explode( ' ', $block['attrs']['className'] ?? '' ), true ) ) {
		return $html;
	}
	wp_enqueue_script( 'zion-navigation', get_theme_file_uri( 'assets/blocks/navigation.js' ), array(), wp_get_theme()->get( 'Version' ), true );
	if ( str_contains( $html, 'zion-nav-copy' ) ) {
		return $html;
	}
	$rendered = preg_replace_callback(
		'~(<span\b[^>]*class="[^"]*\bwp-block-navigation-item__label\b[^"]*"[^>]*>)(.*?)(</span>)~s',
		static function ( array $parts ): string {
			list( $icon, $description ) = zion_theme_menu_presentation( html_entity_decode( wp_strip_all_tags( $parts[2] ), ENT_QUOTES, 'UTF-8' ) );
			return $parts[1] . zion_theme_menu_icon( $icon ) . '<span class="zion-nav-copy"><span class="zion-menu-text">' . $parts[2] . '</span>' . ( $description ? '<span class="zion-menu-description" aria-hidden="true">' . esc_html( $description ) . '</span>' : '' ) . '</span>' . $parts[3];
		},
		$html
	) ?? $html;
	return $rendered . '<noscript><style>@media(max-width:1100px){.zion-header>nav.zion-navigation{grid-column:1/-1;grid-row:2;justify-self:stretch;width:100%}.zion-header>.zion-header-tools{grid-row:1;grid-column:-2/-1}.zion-header nav.zion-navigation .wp-block-navigation__responsive-container:not(.is-menu-open){display:block;position:static}.zion-header nav.zion-navigation .wp-block-navigation__responsive-container-open{display:none}.zion-header nav.zion-navigation .wp-block-navigation__responsive-container .has-child>.wp-block-navigation__submenu-container{display:flex;position:static;visibility:visible;opacity:1;width:100%;height:auto;background:var(--zion-menu-panel)}.zion-header nav.zion-navigation .wp-block-navigation__responsive-container .wp-block-navigation__submenu-icon{display:none}.zion-header nav.zion-navigation .wp-block-navigation__container{align-items:stretch;width:100%}.zion-header nav.zion-navigation .wp-block-navigation-item{flex-wrap:wrap;width:100%}.zion-header nav.zion-navigation .wp-block-navigation-item__content{color:var(--wp--preset--color--text)}.zion-header nav.zion-navigation .wp-block-navigation__responsive-container .wp-block-navigation__submenu-container .wp-block-navigation-item__content{color:var(--zion-menu-ink)}}</style></noscript>';
}
add_filter( 'render_block_core/navigation', 'zion_theme_menu_design', 20, 2 );

/**
 * A visible search icon without replacing the native Details interaction.
 *
 * @param string $html Native Details markup.
 * @param array  $block Parsed block.
 * @return string
 */
function zion_theme_menu_search_icon( string $html, array $block ): string {
	if ( ! is_admin() && ! wp_is_serving_rest_request() && in_array( 'zion-header-search', explode( ' ', $block['attrs']['className'] ?? '' ), true ) ) {
		return preg_replace( '~(<summary\b[^>]*>)~', '$1' . zion_theme_menu_icon( 'search' ), $html, 1 ) ?? $html;
	}
	return $html;
}
add_filter( 'render_block_core/details', 'zion_theme_menu_search_icon', 10, 2 );
