<?php
/**
 * Native authoring permissions, persistence and stale-write protection.
 *
 * @package Zion_Core
 */

( static function (): void {
	$zion_studio_assert  = static function ( bool $valid, string $message ): void {
		if ( ! $valid ) {
			throw new RuntimeException( esc_html( $message ) );
		}
		zion_core_test_assert( true, $message );
	};
	$zion_studio_user    = get_current_user_id();
	$zion_studio_before  = WP_Theme_JSON_Resolver::get_user_data_from_wp_global_styles( wp_get_theme(), false );
	$zion_studio_config  = get_option( Zion_Theme_Settings::OPTION );
	$zion_studio_page    = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'draft',
			'post_title'   => 'Synthetic Studio',
			'post_content' => '<!-- wp:paragraph --><p>Synthetic Studio body.</p><!-- /wp:paragraph -->',
		)
	);
	$zion_studio_request = static function ( string $method, string $route, array $params = array() ) {
		$request = new WP_REST_Request( $method, '/zion-theme/v1/' . $route );
		if ( 'GET' === $method ) {
			$request->set_query_params( $params );
		} else {
			$request->set_body_params( $params );
		}
		return rest_do_request( $request );
	};
	try {
		wp_set_current_user( 0 );
		$zion_studio_assert( 401 === $zion_studio_request( 'GET', 'design' )->get_status(), 'Anonymous visitors cannot read native Studio authoring data' );
		wp_set_current_user( 1 );
		$state = $zion_studio_request( 'GET', 'design' )->get_data();
		$zion_studio_assert( isset( $state['registry']['color_accent'] ) && WP_Theme_JSON_Resolver::get_user_data_from_wp_global_styles( wp_get_theme(), false ) === $zion_studio_before, 'Reading design controls creates no parallel option or native style record' );
		$bad = $zion_studio_request(
			'POST',
			'design',
			array(
				'revision' => $state['revision'],
				'values'   => array( 'color_accent' => 'url(evil)' ),
			)
		);
		$zion_studio_assert( 400 === $bad->get_status() && zion_theme_design_document() === $state['document'], 'Invalid design imports are rejected before any native data changes' );
		$saved = $zion_studio_request(
			'POST',
			'design',
			array(
				'revision' => $state['revision'],
				'values'   => array(
					'color_accent' => '#aabb33',
					'card_radius'  => '16px',
				),
			)
		);
		$zion_studio_assert( 200 === $saved->get_status() && '#aabb33' === Zion_Theme_Design::read( zion_theme_design_document(), $state['registry']['color_accent'] ), 'Design persists in the canonical wp_global_styles record through the native REST controller' );
		$zion_studio_assert(
			409 === $zion_studio_request(
				'POST',
				'design',
				array(
					'revision' => $state['revision'],
					'values'   => array( 'color_accent' => '#ffffff' ),
				)
			)->get_status(),
			'A stale design form cannot overwrite a newer native save'
		);
		$zion_studio_assert( get_option( Zion_Theme_Settings::OPTION ) === $zion_studio_config, 'Native styles are never duplicated in zion_theme_settings' );
		$home = $zion_studio_request( 'GET', 'design', array( 'scope' => 'home' ) )->get_data();
		$zion_studio_assert( '#aabb33' === $home['inherited']['color_accent'] && array() === $home['overrides'], 'Homepage inherits global styles without copying defaults' );
		foreach ( array(
			'home'     => '#ffcc33',
			'3d'       => '#33ccff',
			'software' => '#cc88ff',
		) as $scope => $accent ) {
			$current = $zion_studio_request( 'GET', 'design', array( 'scope' => $scope ) )->get_data();
			$scoped  = $zion_studio_request(
				'POST',
				'design',
				array(
					'scope'    => $scope,
					'revision' => $current['revision'],
					'values'   => array(
						'color_accent'    => $accent,
						'font_body'       => 'Georgia, serif',
						'spacing_regular' => '32px',
					),
				)
			);
			$zion_studio_assert( 200 === $scoped->get_status() && $scoped->get_data()['overrides']['color_accent'] === $accent, $scope . ' persists validated native design overrides independently' );
		}
		$current = $zion_studio_request( 'GET', 'design', array( 'scope' => 'home' ) )->get_data();
		$zion_studio_assert( '#aabb33' === $current['inherited']['color_accent'] && '#33ccff' === $current['contexts']['3d']['color_accent'], 'Scoped writes preserve global presets and sibling contexts' );
		$invalid = $zion_studio_request(
			'POST',
			'design',
			array(
				'scope'    => 'home',
				'revision' => $current['revision'],
				'values'   => array( 'color_accent' => 'red;display:none' ),
			)
		);
		$zion_studio_assert( 400 === $invalid->get_status() && zion_theme_design_document() === $current['document'], 'Invalid context CSS is rejected before any write' );
		$zion_studio_assert( 400 === $zion_studio_request( 'GET', 'design', array( 'scope' => 'product' ) )->get_status(), 'Unknown design scopes never fall back to Global' );
		$zion_studio_assert(
			409 === $zion_studio_request(
				'POST',
				'design',
				array(
					'scope'    => 'software',
					'revision' => $home['revision'],
					'values'   => array( 'color_accent' => '#ffffff' ),
				)
			)->get_status(),
			'A concurrent save in another scope invalidates stale forms'
		);
		$reset = $zion_studio_request(
			'POST',
			'design',
			array(
				'scope'    => 'home',
				'revision' => $current['revision'],
				'values'   => array( 'color_accent' => null ),
			)
		)->get_data();
		$zion_studio_assert( ! isset( $reset['overrides']['color_accent'] ) && '#cc88ff' === $reset['contexts']['software']['color_accent'], 'Resetting one page token restores inheritance and preserves siblings' );
		$zion_studio_assert( get_option( Zion_Theme_Settings::OPTION ) === $zion_studio_config, 'Page design profiles do not duplicate the native palette in Zion settings' );
		$params = array(
			'kind' => 'pages',
			'id'   => (string) $zion_studio_page,
		);
		$read   = $zion_studio_request( 'GET', 'document', $params )->get_data();
		$write  = $zion_studio_request(
			'POST',
			'document',
			$params + array(
				'revision' => $read['revision'],
				'values'   => array(
					'title'   => 'Synthetic Studio edited',
					'content' => '<!-- wp:paragraph --><p>Edited native body.</p><!-- /wp:paragraph -->',
				),
			)
		);
		$zion_studio_assert( 200 === $write->get_status() && 'Synthetic Studio edited' === get_post_field( 'post_title', $zion_studio_page ) && str_contains( get_post_field( 'post_content', $zion_studio_page ), 'Edited native body.' ), 'Studio edits the original WordPress page, not a second content store' );
		$zion_studio_assert(
			409 === $zion_studio_request(
				'POST',
				'document',
				$params + array(
					'revision' => $read['revision'],
					'values'   => array( 'title' => 'Stale' ),
				)
			)->get_status(),
			'Concurrent page changes are detected before native persistence'
		);
		$current = $write->get_data();
		$zion_studio_assert(
			400 === $zion_studio_request(
				'POST',
				'document',
				$params + array(
					'revision' => $current['revision'],
					'values'   => array( 'meta' => array( 'unexpected' => 'value' ) ),
				)
			)->get_status(),
			'Studio cannot write arbitrary metadata through its content bridge'
		);
		$dynamic_a = array(
			'id'      => 1,
			'content' => array(
				'raw'      => 'Original',
				'rendered' => 'Dynamic one',
			),
		);
		$dynamic_b = array(
			'id'      => 1,
			'content' => array(
				'raw'      => 'Original',
				'rendered' => 'Dynamic two',
			),
		);
		$zion_studio_assert( zion_theme_studio_revision( $dynamic_a ) === zion_theme_studio_revision( $dynamic_b ), 'Dynamic frontend rendering does not produce false authoring conflicts' );
	} finally {
		wp_set_current_user( 1 );
		$zion_studio_after = WP_Theme_JSON_Resolver::get_user_data_from_wp_global_styles( wp_get_theme(), false );
		if ( $zion_studio_before ) {
			wp_update_post(
				wp_slash(
					array(
						'ID'           => $zion_studio_before['ID'],
						'post_content' => $zion_studio_before['post_content'],
					)
				)
			);
		} elseif ( $zion_studio_after ) {
			wp_delete_post( $zion_studio_after['ID'], true );
		}
		WP_Theme_JSON_Resolver::clean_cached_data();
		wp_delete_post( $zion_studio_page, true );
		wp_set_current_user( $zion_studio_user );
	}
} )();
