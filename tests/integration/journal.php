<?php
/**
 * Journal templates retain native queries, article data and protected content.
 *
 * @package Zion_Core
 */

$zion_core_journal_raw      = '<h1 class="example" title="a > b">Titlu</h1><p>Text</p><script>const x="<h1>cod</h1>";</script><style>.x::before{content:"<h1>"}</style><textarea><h1>text brut</h1></textarea><!-- <h1>comentariu</h1> --><pre>&lt;h1&gt;cod&lt;/h1&gt;</pre><svg viewBox="0 0 20 20"><path d="M0 0"/></svg>';
$zion_core_journal_expected = str_replace( '<h1 class="example" title="a > b">Titlu</h1>', '<h2 class="example" title="a > b">Titlu</h2>', $zion_core_journal_raw );
zion_core_test_assert( zion_theme_journal_heading_levels( $zion_core_journal_raw ) === $zion_core_journal_expected, 'Article hierarchy changes real body H1 tags without rewriting attributes, raw text, SVG or escaped code' );
zion_core_test_assert( zion_theme_journal_heading_levels( $zion_core_journal_expected ) === $zion_core_journal_expected, 'Display-only heading normalization is idempotent' );
foreach ( array( 'home', 'archive', 'single', 'search-posts' ) as $zion_core_journal_template ) {
	$zion_core_journal_source = file_get_contents( get_theme_file_path( 'templates/' . $zion_core_journal_template . '.html' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Integration reads the known local theme template.
	zion_core_test_assert( zion_theme_migration_native_blocks( resolve_pattern_blocks( parse_blocks( $zion_core_journal_source ) ) ), $zion_core_journal_template . ' resolves to native editable blocks and registered Zion fragments' );
}
$zion_core_journal_pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'zion-theme/post-card' );
zion_core_test_assert( $zion_core_journal_pattern && ! str_contains( $zion_core_journal_pattern['content'], '<canvas' ), 'The shared editorial card stores native blocks without decorative runtime markup' );
$zion_core_journal_post        = get_page_by_path( 'jurnal-prototip-zion', OBJECT, 'post' );
$zion_core_journal_before      = get_post( $zion_core_journal_post->ID, ARRAY_A );
$zion_core_journal_meta        = get_post_meta( $zion_core_journal_post->ID );
$zion_core_journal_config      = get_option( Zion_Theme_Settings::OPTION );
$zion_core_journal_loop_before = array_intersect_key( $GLOBALS, array_flip( array( 'post', 'id', 'authordata', 'currentday', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages' ) ) );
$zion_core_journal_card        = zion_theme_journal_cards( array( $zion_core_journal_post ) );
zion_core_test_assert( str_contains( $zion_core_journal_card, 'zion-post-card-copy' ) && str_contains( $zion_core_journal_card, esc_html( $zion_core_journal_post->post_title ) ) && str_contains( $zion_core_journal_card, 'zion-post-card-link' ), 'Related collections use the same native card and real article permalink' );
zion_core_test_assert( get_post( $zion_core_journal_post->ID, ARRAY_A ) === $zion_core_journal_before && get_post_meta( $zion_core_journal_post->ID ) === $zion_core_journal_meta && get_option( Zion_Theme_Settings::OPTION ) === $zion_core_journal_config, 'Card rendering leaves article content, metadata, dates and settings unchanged' );
zion_core_test_assert( array_intersect_key( $GLOBALS, array_flip( array( 'post', 'id', 'authordata', 'currentday', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages' ) ) ) === $zion_core_journal_loop_before, 'Related cards restore the surrounding WordPress title, author and comment-loop context exactly' );
$zion_core_journal_unrelated = '<div><p>Unrelated native content.</p></div>';
zion_core_test_assert( zion_theme_journal_motion( $zion_core_journal_unrelated, array( 'attrs' => array() ) ) === $zion_core_journal_unrelated, 'Unrelated blocks receive no journal script or illustration' );
$zion_core_journal_scene = zion_theme_journal_motion( '<div class="zion-editorial-motion"><p>Native fallback.</p></div>', array( 'attrs' => array( 'className' => 'zion-editorial-motion' ) ) );
zion_core_test_assert( 1 === substr_count( $zion_core_journal_scene, 'data-zion-spatial="journal-pages"' ) && str_contains( $zion_core_journal_scene, 'Native fallback.' ), 'Journal decoration adds one hidden canvas while retaining its native no-JavaScript fallback' );
$zion_core_journal_protected = wp_insert_post(
	array(
		'post_type'     => 'post',
		'post_status'   => 'publish',
		'post_title'    => 'Protected editorial fixture',
		'post_password' => 'local-test-only',
		'post_content'  => 'SYNTHETIC SECRET',
	)
);
try {
	$zion_core_journal_block = new WP_Block(
		array(
			'blockName'    => 'zion/editorial',
			'attrs'        => array(),
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		),
		array( 'postId' => $zion_core_journal_protected )
	);
	zion_core_test_assert( '' === zion_theme_journal_fragment( 'card_link', $zion_core_journal_block ), 'Custom fragments do not bypass native password protection' );
	$zion_core_journal_block = new WP_Block(
		array(
			'blockName'    => 'core/post-featured-image',
			'attrs'        => array(),
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		),
		array( 'postId' => $zion_core_journal_protected )
	);
	zion_core_test_assert( '' === zion_theme_journal_image( '<img src="protected-image">', array( 'attrs' => array( 'className' => 'zion-blog-image' ) ), $zion_core_journal_block ), 'Protected articles do not expose their cover through the theme image renderer' );
} finally {
	wp_delete_post( $zion_core_journal_protected, true );
}
