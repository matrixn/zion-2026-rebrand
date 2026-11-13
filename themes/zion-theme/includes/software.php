<?php
/**
 * Native Software page presentation. WooCommerce keeps all commerce ownership.
 *
 * @package Zion_Theme
 */

/**
 * Presentation contexts for the published Software menu destinations.
 *
 * @return array<string,array>
 */
function zion_theme_software_pages(): array {
	return array(
		'pluginuri-woocommerce'               => array( 'hub', 'ZION / DIGITAL SYSTEMS', 'Instrumente care conectează magazinul, datele și felul tău de lucru. Explorează ecosistemul Zion și alege de unde începi.', 'Software', 'ro' ),
		'magazin-software'                    => array( 'catalog', 'SOFTWARE / LICENȚE ȘI DESCĂRCĂRI', 'Găsește instrumentul potrivit pentru următorul pas. Funcționalitățile, compatibilitatea și condițiile de acces sunt descrise în fișa fiecărui produs.', 'Magazin Software', 'ro' ),
		'zion-sms-confirmation-woocommerce'   => array( 'sms', 'WORDPRESS / COMUNICARE ȘI CONTROL', 'Un fir clar între comandă, mesaj și decizia clientului. Descoperă fluxul de confirmare și opțiunile prezentate pentru magazinul tău.', 'SMS Confirmation', 'ro' ),
		'pluginuri-woocommerce/zion-privacy'  => array( 'privacy', 'WORDPRESS / PRIVACY INTELLIGENCE', 'De la semnale disparate la o imagine mai clară. Explorează scanarea, consimțământul și experiența de administrare prezentate mai jos.', 'Zion Privacy', 'ro' ),
		'zion-multi-currency-for-woocommerce' => array( 'currency', 'WOOCOMMERCE / PIEȚE CONECTATE', 'Monede diferite. O experiență coerentă. Descoperă cum sunt prezentate alegerea monedei, regulile comerciale și contextul comenzii.', 'Multi-Currency', 'ro' ),
		'zion-releasestation-git-deployment-automatizat-pentru-synology-dsm' => array( 'release', 'SYNOLOGY DSM / RELEASE ENGINEERING', 'Un traseu vizibil între cod și release. Explorează pipeline-ul, verificările și opțiunile de acces la Zion ReleaseStation.', 'ReleaseStation', 'ro' ),
		'zion-releasestation-automated-git-deployment-for-synology-dsm' => array( 'release', 'SYNOLOGY DSM / RELEASE ENGINEERING', 'A visible path from code to release. Explore the pipeline, verification steps and access options for Zion ReleaseStation.', 'ReleaseStation · English', 'en' ),
	);
}

/**
 * Resolve exact native page paths, respecting an explicit alternative template.
 *
 * @param int $id Optional page ID.
 * @return array
 */
function zion_theme_software_context( int $id = 0 ): array {
	if ( ! $id && ! is_page() ) {
		return array();
	}
	$id = $id ? $id : get_queried_object_id();
	if ( 'page' !== get_post_type( $id ) ) {
		return array();
	}
	$template = get_page_template_slug( $id );
	$known    = zion_theme_software_pages()[ get_page_uri( $id ) ] ?? array();
	if ( $template && ! in_array( $template, array( 'default', 'software' ), true ) && ! ( $known && 'elementor_theme' === $template ) ) {
		return array();
	}
	return $known ? $known : ( 'software' === $template ? array( 'hub', 'ZION / SOFTWARE', 'Explorează instrumentele și informațiile din această pagină.', 'Software', 'ro' ) : array() );
}

/**
 * One canonical, editable template for all seven native page paths.
 *
 * @param array $templates Native hierarchy.
 * @return array
 */
function zion_theme_software_template( array $templates ): array {
	if ( zion_theme_software_context() ) {
		array_unshift( $templates, 'software.php' );
	}
	return $templates;
}
add_filter( 'page_template_hierarchy', 'zion_theme_software_template' );

/**
 * Group existing native H2/H3 blocks for display, preserving every leaf block.
 *
 * @param string $content Saved native blocks.
 * @return array Body and navigation derived from the same structure.
 */
function zion_theme_software_outline( string $content ): array {
	static $cache = array();
	$key          = hash( 'sha256', $content );
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}
	$blocks = array_values( array_filter( parse_blocks( $content ), fn( $block ) => $block['blockName'] || trim( $block['innerHTML'] ) ) );
	if ( 1 === count( $blocks ) && 'core/group' === $blocks[0]['blockName'] && in_array( 'zion-editorial-panel', explode( ' ', $blocks[0]['attrs']['className'] ?? '' ), true ) ) {
		$blocks = $blocks[0]['innerBlocks'];
	}
	$sections = array();
	$current  = array();
	foreach ( $blocks as $block ) {
		if ( 'core/heading' === $block['blockName'] && ( $block['attrs']['level'] ?? 2 ) <= 2 && $current ) {
			$sections[] = $current;
			$current    = array();
		}
		$current[] = $block;
	}
	if ( $current ) {
		$sections[] = $current;
	}
	$body = '';
	$toc  = array();
	foreach ( $sections as $index => $section ) {
		$intro      = '';
		$cards      = array();
		$card       = '';
		$id         = 'zion-software-section-' . ( $index + 1 );
		$card_class = 'zion-software-card';
		$leading    = false;
		foreach ( $section as $position => $block ) {
			if ( 'core/heading' === $block['blockName'] && ( $block['attrs']['level'] ?? 2 ) <= 2 ) {
				$toc[ $id ] = html_entity_decode( wp_strip_all_tags( $block['innerHTML'] ), ENT_QUOTES, 'UTF-8' );
			}
			$next  = $section[ $position + 1 ] ?? array();
			$title = trim( wp_strip_all_tags( $next['innerHTML'] ?? '' ) );
			$plan  = 'core/paragraph' === $block['blockName'] && 'core/heading' === ( $next['blockName'] ?? '' ) && 3 === ( $next['attrs']['level'] ?? 2 ) && $title && str_contains( wp_strip_all_tags( $block['innerHTML'] ), $title ) && preg_match( '~^\s*<p\b[^>]*>\s*<a\b[^>]*>[^<]+</a>\s*</p>\s*$~s', $block['innerHTML'] );
			if ( $plan ) {
				if ( $card ) {
					$cards[] = zion_theme_service_group( $card_class, $card );
				}
				$card       = serialize_block( $block );
				$card_class = 'zion-software-card zion-software-plan';
				$leading    = true;
			} elseif ( 'core/heading' === $block['blockName'] && 3 === ( $block['attrs']['level'] ?? 2 ) ) {
				if ( $card && ! $leading ) {
					$cards[] = zion_theme_service_group( $card_class, $card );
				}
				$card       = ( $leading ? $card : '' ) . serialize_block( $block );
				$card_class = $leading ? $card_class : 'zion-software-card';
				$leading    = false;
			} elseif ( $card ) {
				$card .= serialize_block( $block );
			} else {
				$intro .= serialize_block( $block );
			}
		}
		if ( $card ) {
			$cards[] = zion_theme_service_group( $card_class, $card );
		}
		$body .= zion_theme_service_group( 'zion-software-section', $intro . ( $cards ? zion_theme_service_group( 'zion-software-cards', implode( '', $cards ) ) : '' ), $id );
	}
	$cache[ $key ] = array(
		'body' => $body,
		'toc'  => $toc,
	);
	return $cache[ $key ];
}

/**
 * Present a migrated plan's repeated summary link as its own clear action.
 * Saved leaf blocks and their sequence remain unchanged.
 *
 * @param string $html Native group output.
 * @param array  $block Parsed group.
 * @return string
 */
function zion_theme_software_plan( string $html, array $block ): string {
	if ( is_admin() || wp_is_serving_rest_request() || ! in_array( 'zion-software-plan', explode( ' ', $block['attrs']['className'] ?? '' ), true ) ) {
		return $html;
	}
	$english = 'en' === ( zion_theme_software_context()[4] ?? 'ro' );
	$label   = $english ? 'View product' : 'Vezi produsul';
	preg_match( '~<h3\b[^>]*>(.*?)</h3>~s', $html, $title );
	$tag = new WP_HTML_Tag_Processor( $html );
	if ( $tag->next_tag( 'P' ) ) {
		$tag->add_class( 'zion-software-plan-action' );
	}
	if ( $tag->next_tag( 'A' ) ) {
		$tag->set_attribute( 'aria-label', $label . ': ' . wp_strip_all_tags( $title[1] ?? '' ) );
	}
	return preg_replace( '~(<p\b[^>]*>\s*<a\b[^>]*>)[^<]+(</a>\s*</p>)~s', '$1' . esc_html( $label . ' ↗' ) . '$2', $tag->get_updated_html(), 1 );
}
add_filter( 'render_block_core/group', 'zion_theme_software_plan', 32, 2 );

/**
 * Decorate the existing hub links as native cards, without replacing their URLs.
 *
 * @param string $content Saved native hub content.
 * @return string
 */
function zion_theme_software_hub( string $content ): string {
	$blocks = array_values( array_filter( parse_blocks( $content ), fn( $block ) => $block['blockName'] || trim( $block['innerHTML'] ) ) );
	if ( 1 === count( $blocks ) && 'core/group' === $blocks[0]['blockName'] && 'zion-editorial-panel' === ( $blocks[0]['attrs']['className'] ?? '' ) ) {
		$blocks = $blocks[0]['innerBlocks'];
	}
	$intro = '';
	$cards = '';
	foreach ( $blocks as $block ) {
		$context = array();
		if ( 'core/paragraph' === $block['blockName'] ) {
			$tag = new WP_HTML_Tag_Processor( $block['innerHTML'] );
			if ( $tag->next_tag( 'A' ) ) {
				$url  = (string) $tag->get_attribute( 'href' );
				$host = wp_parse_url( $url, PHP_URL_HOST );
				if ( ! $host || wp_parse_url( home_url(), PHP_URL_HOST ) === $host ) {
					foreach ( zion_theme_software_pages() as $path => $candidate ) {
						if ( wp_parse_url( $url, PHP_URL_PATH ) === wp_parse_url( home_url( '/' . $path . '/' ), PHP_URL_PATH ) ) {
							$context = $candidate;
							break;
						}
					}
				}
			}
		}
		if ( $context ) {
			$cards .= zion_theme_service_group( 'zion-software-link-card', zion_theme_service_text( $context[1], 0, 'zion-software-card-label' ) . serialize_block( $block ) . zion_theme_service_text( $context[2], 0, 'zion-software-card-intro' ) );
		} else {
			$intro .= serialize_block( $block );
		}
	}
	return $intro . ( $cards ? zion_theme_service_group( 'zion-software-hub-grid', $cards ) : '' );
}

/**
 * Apply presentation before native do_blocks, never in REST or stored content.
 *
 * @param string $content Current content.
 * @return string
 */
function zion_theme_software_content( string $content ): string {
	if ( is_admin() || wp_is_serving_rest_request() || ! is_page() || get_the_ID() !== get_queried_object_id() || post_password_required() || ! has_blocks( $content ) ) {
		return $content;
	}
	$context = zion_theme_software_context();
	if ( ! $context || 'catalog' === $context[0] ) {
		return $content;
	}
	return 'hub' === $context[0] ? zion_theme_software_hub( $content ) : zion_theme_software_outline( $content )['body'];
}
add_filter( 'the_content', 'zion_theme_software_content', 8 );

/**
 * Contextual fragments inside the canonical native template.
 *
 * @param string $part Fragment name.
 * @return string|null
 */
function zion_theme_software_fragment( string $part ): ?string {
	if ( ! str_starts_with( $part, 'software_' ) ) {
		return null;
	}
	$context = zion_theme_software_context();
	if ( ! $context ) {
		return '';
	}
	$english = 'en' === $context[4];
	$contact = home_url( '/contact/' );
	if ( 'software_intro' === $part ) {
		return '<p class="zion-software-eyebrow">' . esc_html( $context[1] ) . '</p><p class="zion-software-intro">' . esc_html( $context[2] ) . '</p>';
	}
	if ( 'software_actions' === $part ) {
		return '<div class="zion-software-actions"><a href="#software-content">' . esc_html( $english ? 'Explore the details' : ( 'catalog' === $context[0] ? 'Explorează catalogul' : 'Explorează detaliile' ) ) . ' ↘</a><a href="' . esc_url( $contact ) . '">' . esc_html( $english ? 'Talk to us' : 'Discută cu noi' ) . ' ↗</a></div>';
	}
	if ( 'software_breadcrumb' === $part ) {
		$hub = zion_theme_published_page_url( 'pluginuri-woocommerce' );
		return '<nav class="zion-software-breadcrumb" aria-label="' . esc_attr( $english ? 'Breadcrumb' : 'Fir de navigare' ) . '"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( $english ? 'Home' : 'Acasă' ) . '</a><span aria-hidden="true">/</span>' . ( $hub && 'hub' !== $context[0] ? '<a href="' . esc_url( $hub ) . '">Software</a><span aria-hidden="true">/</span>' : '' ) . '<span aria-current="page">' . esc_html( $context[3] ) . '</span></nav>';
	}
	if ( 'software_toc' === $part ) {
		if ( in_array( $context[0], array( 'catalog', 'hub' ), true ) || post_password_required() ) {
			return '';
		}
		$toc = zion_theme_software_outline( get_post_field( 'post_content', get_queried_object_id() ) )['toc'];
		if ( ! $toc ) {
			return '';
		}
		$links = '';
		foreach ( $toc as $id => $label ) {
			$links .= '<li><a href="#' . esc_attr( $id ) . '">' . esc_html( $label ) . '</a></li>';
		}
		return '<nav class="zion-software-toc" aria-label="' . esc_attr( $english ? 'On this page' : 'În această pagină' ) . '"><details><summary>' . esc_html( $english ? 'On this page' : 'În această pagină' ) . '</summary><ol>' . $links . '</ol></details></nav>';
	}
	if ( 'software_handoff' === $part ) {
		return '<div class="zion-software-handoff-copy"><p class="zion-software-eyebrow">' . esc_html( $english ? 'THE NEXT CONNECTION / YOUR PROJECT' : 'URMĂTOAREA CONEXIUNE / PROIECTUL TĂU' ) . '</p><h2>' . esc_html( $english ? 'Let’s connect the right pieces.' : 'Punem lucrurile potrivite în legătură.' ) . '</h2><p>' . esc_html( $english ? 'Tell us what you want to achieve. We can clarify the right tool, integration or next step together.' : 'Spune-ne ce vrei să obții. Clarificăm împreună instrumentul, integrarea sau următorul pas potrivit.' ) . '</p><a class="wp-element-button" href="' . esc_url( $contact ) . '">' . esc_html( $english ? 'Tell us about your project' : 'Povestește-ne proiectul' ) . ' ↗</a></div>';
	}
	return '';
}

/**
 * Add only hidden decorative scenes and context at frontend render time.
 *
 * @param string $html Native rendered group.
 * @param array  $block Parsed group.
 * @return string
 */
function zion_theme_software_motion( string $html, array $block ): string {
	if ( is_admin() || wp_is_serving_rest_request() ) {
		return $html;
	}
	$classes = explode( ' ', $block['attrs']['className'] ?? '' );
	if ( in_array( 'zion-software-rail', $classes, true ) && 'catalog' === ( zion_theme_software_context()[0] ?? '' ) ) {
		return '';
	}
	if ( in_array( 'zion-software', $classes, true ) ) {
		$context = zion_theme_software_context();
		$tag     = new WP_HTML_Tag_Processor( $html );
		if ( $tag->next_tag() ) {
			$tag->set_attribute( 'data-zion-software', $context[0] ?? 'hub' );
			$tag->set_attribute( 'lang', $context[4] ?? 'ro' );
			$html = $tag->get_updated_html();
		}
	}
	foreach ( array(
		'hero-art' => 'hero',
		'map'      => 'map',
	) as $class => $scene ) {
		if ( in_array( 'zion-software-' . $class, $classes, true ) ) {
			zion_theme_enqueue_continuum();
			return preg_replace( '~(</div>\s*)$~', '<canvas class="zion-spatial-canvas" data-zion-spatial="software-' . $scene . '" aria-hidden="true"></canvas>$1', $html, 1 ) ?? $html;
		}
	}
	return $html;
}
add_filter( 'render_block_core/group', 'zion_theme_software_motion', 31, 2 );

/**
 * Keep optional covers away from the hub/catalog and preserve password behavior.
 *
 * @param string $html Native image output.
 * @param array  $block Parsed block.
 * @return string
 */
function zion_theme_software_cover( string $html, array $block ): string {
	if ( ! in_array( 'zion-software-cover', explode( ' ', $block['attrs']['className'] ?? '' ), true ) ) {
		return $html;
	}
	$context = zion_theme_software_context();
	return ! $context || in_array( $context[0], array( 'hub', 'catalog' ), true ) || post_password_required() ? '' : $html;
}
add_filter( 'render_block_core/post-featured-image', 'zion_theme_software_cover', 26, 2 );

/**
 * Replace the three known invalid Multi-Currency CTA destinations at display time.
 * No product, price or license is invented and saved page content remains intact.
 *
 * @param string $html Native content output.
 * @param array  $block Parsed content block.
 * @return string
 */
function zion_theme_software_cta( string $html, array $block ): string {
	if ( ! in_array( 'zion-software-content', explode( ' ', $block['attrs']['className'] ?? '' ), true ) ) {
		return $html;
	}
	$context = zion_theme_software_context();
	if ( ! $context || 'currency' !== $context[0] || is_admin() || wp_is_serving_rest_request() ) {
		return $html;
	}
	$tag     = new WP_HTML_Tag_Processor( $html );
	$changed = false;
	while ( $tag->next_tag( 'A' ) ) {
		if ( 'void(0);' === $tag->get_attribute( 'href' ) ) {
			$tag->set_attribute( 'href', add_query_arg( 'software', 'multi-currency', home_url( '/contact/' ) ) );
			$tag->set_attribute( 'aria-label', 'Solicită detalii despre Zion Multi-Currency' );
			$tag->add_class( 'zion-software-inquiry' );
			$changed = true;
		}
	}
	return $changed ? str_replace( array( 'Vezi produsul', 'Cumpără pluginul' ), 'Solicită detalii', $tag->get_updated_html() ) : $html;
}
add_filter( 'render_block_core/post-content', 'zion_theme_software_cta', 26, 2 );

/**
 * Make overflowing native code examples reachable by keyboard.
 *
 * @param string $html Native code output.
 * @return string
 */
function zion_theme_software_code( string $html ): string {
	if ( is_admin() || wp_is_serving_rest_request() || ! zion_theme_software_context() ) {
		return $html;
	}
	$tag = new WP_HTML_Tag_Processor( $html );
	if ( $tag->next_tag( 'PRE' ) ) {
		$tag->set_attribute( 'tabindex', '0' );
	}
	return $tag->get_updated_html();
}
add_filter( 'render_block_core/code', 'zion_theme_software_code' );
