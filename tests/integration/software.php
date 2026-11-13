<?php
/**
 * Software composition keeps native leaf blocks and WooCommerce ownership.
 *
 * @package Zion_Core
 */

$zion_core_software_registry = zion_theme_software_pages();
zion_core_test_assert( 7 === count( $zion_core_software_registry ) && 'en' === end( $zion_core_software_registry )[4], 'Seven Software destinations retain distinct contexts and the English ReleaseStation page' );
$zion_core_software_source = file_get_contents( get_theme_file_path( 'templates/software.html' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Known local theme template.
zion_core_test_assert( zion_theme_migration_native_blocks( resolve_pattern_blocks( parse_blocks( $zion_core_software_source ) ) ), 'The canonical Software template resolves to native editable blocks and registered fragments' );
$zion_core_software_fixture = '<!-- wp:paragraph --><p>Preface &amp; context.</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Section A</h2><!-- /wp:heading --><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Card A</h3><!-- /wp:heading --><!-- wp:paragraph --><p><a href="/contact/">Native link</a></p><!-- /wp:paragraph --><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Card B</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Exact original text.</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Section B</h2><!-- /wp:heading --><!-- wp:code --><pre class="wp-block-code"><code>&lt;h1&gt;code &amp; data&lt;/h1&gt;</code></pre><!-- /wp:code -->';
$zion_core_software_outline = zion_theme_software_outline( $zion_core_software_fixture );
$zion_core_software_leaves  = function ( array $blocks ) use ( &$zion_core_software_leaves ): array {
	$result = array();
	foreach ( $blocks as $block ) {
		if ( 'core/group' === $block['blockName'] ) {
			$result = array_merge( $result, $zion_core_software_leaves( $block['innerBlocks'] ) );
		} elseif ( $block['blockName'] || trim( $block['innerHTML'] ) ) {
			$result[] = serialize_block( $block );
		}
	}
	return $result;
};
zion_core_test_assert( $zion_core_software_leaves( parse_blocks( $zion_core_software_outline['body'] ) ) === $zion_core_software_leaves( parse_blocks( $zion_core_software_fixture ) ), 'Section/card composition preserves every original native leaf block, link, escaped code and text exactly' );
zion_core_test_assert(
	array(
		'zion-software-section-2' => 'Section A',
		'zion-software-section-3' => 'Section B',
	) === $zion_core_software_outline['toc'],
	'The native table of contents shares exact section anchors with the rendered body'
);
zion_core_test_assert( zion_theme_software_outline( $zion_core_software_fixture ) === $zion_core_software_outline, 'Repeated Software outline reads produce stable section IDs and content' );
$zion_core_software_wrapped = zion_theme_service_group( 'zion-editorial-panel', $zion_core_software_fixture );
zion_core_test_assert( zion_theme_software_outline( $zion_core_software_wrapped ) === $zion_core_software_outline, 'The known migrated native wrapper is unwrapped without importing legacy layout code' );
$zion_core_software_plans     = '<!-- wp:heading --><h2 class="wp-block-heading">Plans</h2><!-- /wp:heading --><!-- wp:paragraph --><p><a href="/product/synthetic-plan-a/">Choose Plan A. Synthetic description. View product.</a></p><!-- /wp:paragraph --><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Plan A</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Synthetic description.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p><a href="/product/synthetic-plan-b/">Choose Plan B. Synthetic second description. View product.</a></p><!-- /wp:paragraph --><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Plan B</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Synthetic second description.</p><!-- /wp:paragraph -->';
$zion_core_software_plan_body = zion_theme_software_outline( $zion_core_software_plans )['body'];
zion_core_test_assert( 2 === substr_count( $zion_core_software_plan_body, '"zion-software-card zion-software-plan"' ) && $zion_core_software_leaves( parse_blocks( $zion_core_software_plan_body ) ) === $zion_core_software_leaves( parse_blocks( $zion_core_software_plans ) ), 'Leading plan links belong to their following heading while every original leaf and sequence remain identical' );
$zion_core_software_plan_html = zion_theme_software_plan( '<div><p><a href="/product/synthetic-plan-a/">Choose Plan A. Synthetic description. View product.</a></p><h3>Plan A</h3><p>Synthetic description.</p></div>', array( 'attrs' => array( 'className' => 'zion-software-card zion-software-plan' ) ) );
zion_core_test_assert( str_contains( $zion_core_software_plan_html, 'aria-label="Vezi produsul: Plan A"' ) && str_contains( $zion_core_software_plan_html, 'href="/product/synthetic-plan-a/"' ) && str_contains( $zion_core_software_plan_html, '>Vezi produsul ↗</a>' ) && str_contains( $zion_core_software_plan_html, '<p>Synthetic description.</p>' ), 'Plan display removes only the repeated link summary and retains the correct URL, unique accessible name, heading and description' );
$zion_core_software_hub   = '<!-- wp:paragraph --><p><a href="' . esc_url( home_url( '/zion-sms-confirmation-woocommerce/' ) ) . '">Original destination</a></p><!-- /wp:paragraph --><!-- wp:paragraph --><p><a href="https://example.test/zion-sms-confirmation-woocommerce/">External destination</a></p><!-- /wp:paragraph -->';
$zion_core_software_cards = zion_theme_software_hub( $zion_core_software_hub );
zion_core_test_assert( 1 === substr_count( $zion_core_software_cards, '"zion-software-link-card"' ) && str_contains( $zion_core_software_cards, 'Original destination' ) && str_contains( $zion_core_software_cards, 'https://example.test/zion-sms-confirmation-woocommerce/' ), 'Hub cards preserve original links and do not classify an external matching path as a Zion destination' );
$zion_core_software_queue = wp_scripts()->queue;
zion_core_test_assert( '<div>Unrelated.</div>' === zion_theme_software_motion( '<div>Unrelated.</div>', array( 'attrs' => array() ) ) && wp_scripts()->queue === $zion_core_software_queue, 'Unrelated groups do not load Software decorations or scripts' );
$zion_core_software_scene = zion_theme_software_motion( '<div>Native fallback.</div>', array( 'attrs' => array( 'className' => 'zion-software-hero-art' ) ) );
zion_core_test_assert( 1 === substr_count( $zion_core_software_scene, 'data-zion-spatial="software-hero"' ) && str_contains( $zion_core_software_scene, 'Native fallback.' ), 'Software adds one decorative scene and preserves the native no-JavaScript fallback' );
zion_core_test_assert( '<p><a href="void(0);">Unrelated.</a></p>' === zion_theme_software_cta( '<p><a href="void(0);">Unrelated.</a></p>', array( 'attrs' => array() ) ), 'Inquiry repair never changes unrelated markup outside the Software content block' );
zion_core_test_assert( null === zion_theme_software_fragment( 'reading_time' ), 'Software fragments delegate unrelated editorial controls unchanged' );

if ( get_page_by_path( 'pluginuri-woocommerce' ) ) {
	WP_CLI::error( 'Software integration requires the isolated tests fixture paths to be free.' );
}
$zion_core_software_alias = wp_insert_post(
	array(
		'post_type'   => 'page',
		'post_status' => 'draft',
		'post_name'   => 'pluginuri-woocommerce',
		'post_title'  => 'Synthetic Software alias',
	)
);
try {
	update_post_meta( $zion_core_software_alias, '_wp_page_template', 'elementor_theme' );
	zion_core_test_assert( 'hub' === zion_theme_software_context( $zion_core_software_alias )[0] && 'elementor_theme' === get_post_meta( $zion_core_software_alias, '_wp_page_template', true ), 'The known legacy template pointer resolves to native Software without rewriting saved metadata' );
	update_post_meta( $zion_core_software_alias, '_wp_page_template', 'service-landing' );
	zion_core_test_assert( array() === zion_theme_software_context( $zion_core_software_alias ), 'An explicitly selected alternative native template remains the author choice' );
	wp_update_post(
		array(
			'ID'        => $zion_core_software_alias,
			'post_name' => 'zion-software-unrelated-alias-fixture',
		)
	);
	update_post_meta( $zion_core_software_alias, '_wp_page_template', 'elementor_theme' );
	zion_core_test_assert( array() === zion_theme_software_context( $zion_core_software_alias ), 'Legacy pointer compatibility never captures unrelated page paths' );
} finally {
	wp_delete_post( $zion_core_software_alias, true );
}
