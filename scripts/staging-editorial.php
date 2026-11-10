<?php
/**
 * Rebuild menu destinations on a cloned staging site using native blocks.
 *
 * Usage: wp eval-file scripts/staging-editorial.php [apply|services [apply]]
 * Take a database snapshot before passing apply. Elementor metadata is retained.
 *
 * @package Zion_Theme
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || 'staging' !== wp_get_environment_type() || in_array( wp_parse_url( home_url(), PHP_URL_HOST ), array( 'zion3d.ro', 'www.zion3d.ro' ), true ) ) {
	exit( 1 );
}

/**
 * Serialize a native editable block.
 *
 * @param string $name Block type without namespace.
 * @param string $html Inner HTML.
 * @param array  $attributes Block attributes.
 * @return string
 */
function zion_theme_stage_block( string $name, string $html, array $attributes = array() ): string {
	return get_comment_delimited_block_content( 'core/' . $name, $attributes, $html );
}

/**
 * Rebuild inline copy with only safe, useful formatting.
 *
 * @param string $html Source copy.
 * @return string
 */
function zion_theme_stage_inline( string $html ): string {
	$site = untrailingslashit( home_url() );
	$html = preg_replace( '~https?://(?:www\.)?zion3d\.ro(?=[/?#"\x27]|$)~i', $site, $html );
	$html = preg_replace( '~\s(?:href)=(["\x27])#[^"\x27]*\1~i', '', $html );
	return trim(
		wp_kses(
			strip_shortcodes( $html ),
			array(
				'a'      => array( 'href' => true ),
				'strong' => array(),
				'b'      => array(),
				'em'     => array(),
				'i'      => array(),
				'code'   => array(),
				'br'     => array(),
			)
		)
	);
}

/**
 * Extract editorial content from markup without importing its design or behavior.
 *
 * @param string $html Source markup.
 * @return string
 */
function zion_theme_stage_editorial( string $html ): string {
	$html = preg_replace( '~<(script|style|head|svg|iframe|form|noscript)\b[^>]*>.*?</\1\s*>~is', '', $html );
	$doc  = new DOMDocument();
	if ( ! $doc->loadHTML( '<meta charset="utf-8">' . $html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING ) ) {
		return '';
	}
	$path   = new DOMXPath( $doc );
	$nodes  = $path->query( '//*[self::h1 or self::h2 or self::h3 or self::h4 or self::p or self::li or self::pre or self::a][not(ancestor::h1 or ancestor::h2 or ancestor::h3 or ancestor::h4 or ancestor::p or ancestor::li or ancestor::pre)]' );
	$output = '';
	foreach ( $nodes as $node ) {
		$tag  = $node->nodeName; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Native DOM API.
		$copy = '';
		foreach ( $node->childNodes as $child ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Native DOM API.
			$copy .= $doc->saveHTML( $child );
		}
		$copy = zion_theme_stage_inline( $copy );
		if ( '' === trim( wp_strip_all_tags( $copy ) ) ) {
			continue;
		}
		if ( preg_match( '/^h[1-4]$/', $tag ) ) {
			$level   = in_array( $tag, array( 'h1', 'h2' ), true ) ? 2 : 3;
			$output .= zion_theme_stage_block( 'heading', '<h' . $level . ' class="wp-block-heading">' . $copy . '</h' . $level . '>', array( 'level' => $level ) );
		} elseif ( 'pre' === $tag ) {
			$output .= zion_theme_stage_block( 'code', '<pre class="wp-block-code"><code>' . esc_html( $node->textContent ) . '</code></pre>' ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Native DOM API.
		} elseif ( 'a' === $tag ) {
			$link = zion_theme_stage_inline( $doc->saveHTML( $node ) );
			if ( str_contains( $link, 'href=' ) ) {
				$output .= zion_theme_stage_block( 'paragraph', '<p>' . $link . '</p>' );
			}
		} else {
			$output .= zion_theme_stage_block( 'paragraph', '<p>' . $copy . '</p>' );
		}
	}
	return $output;
}

/**
 * Convert selected editorial widget fields without rendering Elementor.
 *
 * @param array $items Widget tree.
 * @return string
 */
function zion_theme_stage_widgets( array $items ): string {
	$blocks = '';
	foreach ( $items as $item ) {
		$settings = $item['settings'] ?? array();
		switch ( $item['widgetType'] ?? '' ) {
			case 'html':
				$blocks .= zion_theme_stage_editorial( (string) ( $settings['html'] ?? '' ) );
				break;
			case 'text-editor':
				$blocks .= zion_theme_stage_editorial( (string) ( $settings['editor'] ?? '' ) );
				break;
			case 'heading':
			case 'etheme_icon_box':
				$blocks .= zion_theme_stage_editorial( '<h2>' . ( $settings['title'] ?? '' ) . '</h2><p>' . ( $settings['description'] ?? '' ) . '</p>' );
				break;
			case 'toggle':
				foreach ( $settings['tabs'] ?? array() as $tab ) {
					$blocks .= zion_theme_stage_block( 'details', '<details class="wp-block-details"><summary>' . esc_html( $tab['tab_title'] ?? '' ) . '</summary>' . zion_theme_stage_editorial( $tab['tab_content'] ?? '' ) . '</details>' );
				}
				break;
		}
		$blocks .= zion_theme_stage_widgets( $item['elements'] ?? array() );
	}
	return $blocks;
}

$zion_theme_stage_paths    = array_merge(
	array_values( zion_theme_service_paths() ),
	array(
		'printare-3d',
		'despre-noi',
		'contact',
		'intrebari',
		'zion-sms-confirmation-woocommerce',
		'zion-multi-currency-for-woocommerce',
		'pluginuri-woocommerce/zion-privacy',
		'zion-releasestation-git-deployment-automatizat-pentru-synology-dsm',
		'zion-releasestation-automated-git-deployment-for-synology-dsm',
		'servicii',
		'pluginuri-woocommerce',
		'magazin-3d',
		'magazin-software',
	)
);
$zion_theme_stage_services = array();
foreach ( zion_theme_service_paths() as $zion_theme_stage_key => $zion_theme_stage_canonical ) {
	$zion_theme_stage_route                               = ! get_page_by_path( $zion_theme_stage_canonical ) && get_page_by_path( $zion_theme_stage_key )
		? $zion_theme_stage_key
		: $zion_theme_stage_canonical;
	$zion_theme_stage_services[ $zion_theme_stage_route ] = $zion_theme_stage_key;
}
$zion_theme_stage_services['servicii'] = '';
if ( ! isset( $zion_theme_stage_services['printare-3d'] ) ) {
	$zion_theme_stage_services['printare-3d'] = '';
}
$zion_theme_stage_only_services = 'services' === ( $args[0] ?? '' );
if ( $zion_theme_stage_only_services ) {
	$zion_theme_stage_paths = array_keys( $zion_theme_stage_services );
}
$zion_theme_stage_apply = 'apply' === ( $args[ $zion_theme_stage_only_services ? 1 : 0 ] ?? '' );
foreach ( $zion_theme_stage_paths as $zion_theme_stage_path ) {
	$zion_theme_stage_page    = get_page_by_path( $zion_theme_stage_path );
	$zion_theme_stage_title   = $zion_theme_stage_page ? $zion_theme_stage_page->post_title : 'Servicii';
	$zion_theme_stage_content = '';
	$zion_theme_stage_landing = isset( $zion_theme_stage_services[ $zion_theme_stage_path ] );
	if ( $zion_theme_stage_landing ) {
		$zion_theme_stage_content = zion_theme_service_landing( $zion_theme_stage_services[ $zion_theme_stage_path ] );
	} elseif ( 'pluginuri-woocommerce' === $zion_theme_stage_path ) {
		$zion_theme_stage_content = zion_theme_stage_block( 'paragraph', '<p>Explorează pluginurile și aplicațiile Zion sau deschide magazinul pentru licențe și descărcări.</p>' );
		foreach ( array( 'magazin-software', 'zion-sms-confirmation-woocommerce', 'pluginuri-woocommerce/zion-privacy', 'zion-multi-currency-for-woocommerce', 'zion-releasestation-git-deployment-automatizat-pentru-synology-dsm', 'zion-releasestation-automated-git-deployment-for-synology-dsm' ) as $zion_theme_stage_child_path ) {
			$zion_theme_stage_child = get_page_by_path( $zion_theme_stage_child_path );
			if ( $zion_theme_stage_child && 'publish' === $zion_theme_stage_child->post_status ) {
				$zion_theme_stage_content .= zion_theme_stage_block( 'paragraph', '<p><a href="' . esc_url( get_permalink( $zion_theme_stage_child ) ) . '">' . esc_html( $zion_theme_stage_child->post_title ) . '</a></p>' );
			}
		}
	} elseif ( str_starts_with( $zion_theme_stage_path, 'magazin-' ) ) {
		$zion_theme_stage_shop        = 'magazin-3d' === $zion_theme_stage_path ? '3d' : 'software';
		$zion_theme_stage_description = '3d' === $zion_theme_stage_shop
			? 'Alege piese de schimb, accesorii și modele pentru printare 3D. Deschide fișa unui produs pentru materiale, dimensiuni și opțiunile disponibile.'
			: 'Explorează pluginuri și aplicații Zion. Consultă fișa produsului pentru compatibilitate, detalii de licențiere și descărcare.';
		$zion_theme_stage_content     = zion_theme_stage_block( 'paragraph', '<p class="zion-store-intro">' . esc_html( $zion_theme_stage_description ) . '</p>', array( 'className' => 'zion-store-intro' ) ) .
			'<!-- wp:shortcode -->[zion_catalog shop="' . $zion_theme_stage_shop . '"]<!-- /wp:shortcode -->';
	} elseif ( $zion_theme_stage_page ) {
		$zion_theme_stage_meta    = json_decode( (string) get_post_meta( $zion_theme_stage_page->ID, '_elementor_data', true ), true );
		$zion_theme_stage_content = zion_theme_stage_widgets( is_array( $zion_theme_stage_meta ) ? $zion_theme_stage_meta : array() );
		if ( 'contact' === $zion_theme_stage_path ) {
			$zion_theme_stage_content .= zion_theme_stage_block( 'paragraph', '<p><a href="mailto:contact@zion3d.ro">Scrie la contact@zion3d.ro</a> sau sună la <a href="tel:+40770675597">+40 770 67 55 97</a>.</p>' );
		}
	}
	if ( ! $zion_theme_stage_content || ( ! $zion_theme_stage_page && 'servicii' !== $zion_theme_stage_path ) ) {
		WP_CLI::warning( 'Necesită verificare: ' . $zion_theme_stage_path );
		continue;
	}
	if ( ! $zion_theme_stage_landing && ! str_starts_with( $zion_theme_stage_path, 'magazin-' ) ) {
		$zion_theme_stage_content = zion_theme_stage_block( 'group', '<div class="wp-block-group zion-editorial-panel">' . $zion_theme_stage_content . '</div>', array( 'className' => 'zion-editorial-panel' ) );
	}
	WP_CLI::log( sprintf( '%s %s: %d bytes', $zion_theme_stage_apply ? 'Apply' : 'Preview', $zion_theme_stage_path, strlen( $zion_theme_stage_content ) ) );
	if ( $zion_theme_stage_apply ) {
		$zion_theme_stage_result = wp_insert_post(
			wp_slash(
				array(
					'ID'           => $zion_theme_stage_page ? $zion_theme_stage_page->ID : 0,
					'post_type'    => 'page',
					'post_name'    => basename( $zion_theme_stage_path ),
					'post_parent'  => $zion_theme_stage_page ? $zion_theme_stage_page->post_parent : 0,
					'post_title'   => $zion_theme_stage_title,
					'post_status'  => $zion_theme_stage_landing && $zion_theme_stage_page ? $zion_theme_stage_page->post_status : 'publish',
					'post_content' => $zion_theme_stage_content,
				)
			),
			true
		);
		if ( is_wp_error( $zion_theme_stage_result ) ) {
			WP_CLI::error( $zion_theme_stage_result->get_error_message() );
		}
		if ( $zion_theme_stage_landing ) {
			update_post_meta( $zion_theme_stage_result, '_wp_page_template', 'service-landing' );
		}
		delete_post_meta( $zion_theme_stage_result, '_elementor_edit_mode' );
		foreach ( array( 'elementor_canvas', 'elementor_header_footer' ) as $zion_theme_stage_legacy_template ) {
			delete_post_meta( $zion_theme_stage_result, '_wp_page_template', $zion_theme_stage_legacy_template );
		}
	}
}
