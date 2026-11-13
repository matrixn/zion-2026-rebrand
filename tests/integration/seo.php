<?php
/**
 * Native SEO metadata, privacy, canonical ownership and sitemap policy.
 *
 * @package Zion_Core
 */

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited, WordPress.Security.NonceVerification.Recommended -- Synthetic CLI query isolation restores all WordPress globals and GET state in finally.

( static function (): void {
	require_once WP_CONTENT_DIR . '/zion-scripts/launch-editorial-helpers.php';
	global $wp_query, $wp_the_query, $post;
	$zion_theme_before = array( $wp_query, $wp_the_query, $post, $_GET );
	$zion_theme_ids    = array();
	$zion_theme_shop   = get_option( 'woocommerce_shop_page_id' );
	$zion_theme_query  = static function ( array $args ): void {
		global $wp_query, $wp_the_query, $post;
		$wp_query     = new WP_Query( $args );
		$wp_the_query = $wp_query;
		$post         = $wp_query->post;
	};
	try {
		$zion_theme_page  = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Synthetic SEO document',
				'post_name'    => 'zion-seo-fixture',
				'post_excerpt' => 'An authored description with &amp; an entity.',
				'post_content' => '<p>Synthetic body.</p>',
			)
		);
		$zion_theme_ids[] = $zion_theme_page;
		$zion_theme_query( array( 'page_id' => $zion_theme_page ) );
		zion_core_test_assert( 'An authored description with & an entity.' === zion_theme_seo_description(), 'SEO uses the native authored excerpt and decodes entities once' );
		update_post_meta( $zion_theme_page, '_yoast_wpseo_metadesc', 'Preserved authored legacy description.' );
		zion_core_test_assert( 'Preserved authored legacy description.' === zion_theme_seo_description(), 'An inactive provider editorial description is preserved without loading that provider' );
		update_post_meta( $zion_theme_page, '_yoast_wpseo_title', 'Authored title | Zion' );
		zion_core_test_assert( str_contains( wp_get_document_title(), 'Authored title | Zion' ), 'An authored plain legacy title remains meaningful' );
		update_post_meta( $zion_theme_page, '_yoast_wpseo_title', '%%title%% %%sitename%%' );
		zion_core_test_assert( ! str_contains( wp_get_document_title(), '%%' ), 'Inactive provider template variables never leak into the document title' );
		zion_core_test_assert( 'Visible text' === zion_theme_seo_text( '<p>Visible text</p>[unregistered_private_form id="1"]' ), 'SEO never executes or publishes unknown shortcodes' );
		ob_start();
		zion_theme_social_meta();
		$zion_theme_meta = ob_get_clean();
		zion_core_test_assert( 1 === substr_count( $zion_theme_meta, 'property="og:image"' ) && str_contains( $zion_theme_meta, 'summary_large_image' ) && ! str_contains( $zion_theme_meta, 'rel="canonical"' ), 'Sharing emits one image and leaves the singular canonical to WordPress' );
		$zion_theme_attachment = get_post_thumbnail_id( (int) get_option( 'page_on_front' ) );
		if ( ! $zion_theme_attachment ) {
			$zion_theme_attachment = (int) get_theme_mod( 'custom_logo' );
		}
		set_post_thumbnail( $zion_theme_page, $zion_theme_attachment );
		zion_core_test_assert( 0 === strcmp( wp_get_attachment_image_src( $zion_theme_attachment, 'full' )[0], zion_theme_social_image()['url'] ), 'Ordinary pages share their native featured image' );
		$zion_theme_query( array( 'page_id' => (int) get_option( 'page_on_front' ) ) );
		zion_core_test_assert( str_contains( zion_theme_social_image()['url'], '/assets/images/social/zion-home-share.png' ), 'Homepage shares the representative generated cover instead of its logo thumbnail' );
		wp_update_post(
			array(
				'ID'            => $zion_theme_page,
				'post_password' => 'synthetic-local-only',
				'post_excerpt'  => 'PRIVATE SEO FIXTURE',
			)
		);
		delete_post_meta( $zion_theme_page, '_yoast_wpseo_metadesc' );
		$zion_theme_query( array( 'page_id' => $zion_theme_page ) );
		zion_core_test_assert( ! str_contains( zion_theme_seo_description(), 'PRIVATE SEO FIXTURE' ) && str_contains( zion_theme_social_image()['url'], '/assets/images/social/' ), 'Password-protected content and featured media do not leak into sharing metadata' );
		zion_core_test_assert( ! empty( zion_theme_seo_robots( array() )['noindex'] ), 'Password-protected pages remain noindex' );
		$zion_theme_query( array( 'page_id' => wc_get_page_id( 'cart' ) ) );
		// Woo caches its page conditional per request; this CLI switches several queries.
		add_filter( 'woocommerce_is_cart', '__return_true' );
		zion_core_test_assert( ! empty( zion_theme_seo_robots( array() )['noindex'] ), 'Cart stays noindex independently of the public-site setting' );
		remove_filter( 'woocommerce_is_cart', '__return_true' );
		$_GET = array(
			'orderby'         => 'price',
			'filter_material' => 'petg',
		);
		$zion_theme_query( array( 'page_id' => $zion_theme_page ) );
		zion_core_test_assert( ! empty( zion_theme_seo_robots( array( 'nofollow' => true ) )['nofollow'] ) && ! empty( zion_theme_seo_robots( array() )['noindex'] ), 'Filter variants are noindex and never remove a native global nofollow rule' );
		$_GET               = array();
		$zion_theme_sitemap = zion_theme_seo_sitemap_posts( array( 'post__not_in' => array( 987654 ) ), 'page' );
		zion_core_test_assert( in_array( wc_get_page_id( 'cart' ), $zion_theme_sitemap['post__not_in'], true ) && in_array( 987654, $zion_theme_sitemap['post__not_in'], true ), 'The native sitemap excludes utilities while preserving existing exclusions' );
		zion_core_test_assert(
			! isset(
				zion_theme_seo_sitemap_taxonomies(
					array(
						'post_tag' => true,
						'category' => true,
					)
				)['post_tag']
			) && false === zion_theme_seo_sitemap_provider(
				true,
				'users'
			),
			'Sitemap policy matches noindex tag and author navigation archives'
		);
		update_option( 'woocommerce_shop_page_id', $zion_theme_page );
		zion_core_test_assert( '' === zion_core_legacy_redirect_target( '/unknown-missing-page/' ) && get_permalink( $zion_theme_page ) === zion_core_legacy_redirect_target( '/shop/' ), 'Only exact retired routes redirect to real published native destinations' );
		update_option( 'woocommerce_shop_page_id', $zion_theme_shop );
		$zion_theme_query( array( 'page_id' => get_page_by_path( 'magazin-3d' )->ID ) );
		$_GET = array( 'query-73-page' => '2' );
		zion_core_test_assert( str_contains( zion_theme_seo_url(), 'query-73-page=2' ) && str_contains( wp_get_document_title(), 'Pagina 2' ), 'Native Product Collection pagination has a distinct canonical and title' );
		$zion_theme_short_service = get_page_by_path( 'printare-3d' );
		zion_core_test_assert( 0 === strcmp( get_permalink( $zion_theme_short_service ), wp_get_canonical_url( $zion_theme_short_service ) ), 'A fresh-install individual printing page never becomes a Services alias without an explicit editorial canonical' );
		$zion_theme_template_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Template preflight fixture',
				'post_name'    => 'zion-template-preflight-fixture',
				'post_content' => '<p>Synthetic preserved body.</p>',
			)
		);
		$zion_theme_ids[]       = $zion_theme_template_id;
		update_post_meta( $zion_theme_template_id, '_wp_page_template', 'elementor_canvas' );
		$zion_theme_template_before = wp_json_encode( array( get_post( $zion_theme_template_id, ARRAY_A ), get_post_meta( $zion_theme_template_id ) ) );
		$zion_theme_template_error  = zion_theme_launch_template_preflight( $zion_theme_template_id, array( 'post_title' => 'Edited fixture' ) );
		zion_core_test_assert( is_wp_error( $zion_theme_template_error ) && 'zion_launch_invalid_template' === $zion_theme_template_error->get_error_code() && 0 === strcmp( $zion_theme_template_before, wp_json_encode( array( get_post( $zion_theme_template_id, ARRAY_A ), get_post_meta( $zion_theme_template_id ) ) ) ), 'Legacy template preflight rejects the title update before any native partial write' );
		zion_core_test_assert( is_wp_error( zion_theme_launch_template_preflight( $zion_theme_template_id, array( 'page_template' => 'missing-template' ) ) ) && null === zion_theme_launch_template_preflight( $zion_theme_template_id, array( 'page_template' => 'default' ) ), 'Only an explicit registered or default native template can repair the legacy pointer' );
		$zion_theme_template_result = wp_update_post(
			array(
				'ID'            => $zion_theme_template_id,
				'post_title'    => 'Edited fixture',
				'page_template' => 'default',
			),
			true
		);
		zion_core_test_assert( ! is_wp_error( $zion_theme_template_result ) && 'default' === get_post_meta( $zion_theme_template_id, '_wp_page_template', true ) && '<p>Synthetic preserved body.</p>' === get_post_field( 'post_content', $zion_theme_template_id ) && 'zion-template-preflight-fixture' === get_post_field( 'post_name', $zion_theme_template_id ), 'A valid native replacement completes the WordPress update while preserving the body and URL' );
	} finally {
		update_option( 'woocommerce_shop_page_id', $zion_theme_shop );
		remove_filter( 'woocommerce_is_cart', '__return_true' );
		foreach ( $zion_theme_ids as $zion_theme_id ) {
			wp_delete_post( $zion_theme_id, true );
		}
		list( $wp_query, $wp_the_query, $post, $_GET ) = $zion_theme_before;
	}
} )();
