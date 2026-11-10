<?php
/**
 * Plan and reconcile explicit migration operations on a non-production clone.
 *
 * @package Zion_Theme
 */

/**
 * Hash a stable JSON document for approval and drift detection.
 *
 * @param array $value Document.
 * @return string
 */
function zion_theme_migration_hash( array $value ): string {
	return hash( 'sha256', (string) wp_json_encode( $value ) );
}

/**
 * Read the exact fields touched by a migration operation.
 *
 * @param string     $kind Operation type.
 * @param int|string $id Record identifier.
 * @return mixed
 * @throws RuntimeException For a missing or unsupported record.
 */
function zion_theme_migration_state( string $kind, int|string $id ): mixed {
	if ( 'option' === $kind && in_array( $id, array( 'show_on_front', 'page_on_front', 'page_for_posts' ), true ) ) {
		return 'show_on_front' === $id ? (string) get_option( $id ) : (int) get_option( $id );
	}
	if ( 'page' === $kind && 'page' === get_post_type( $id ) ) {
		$post = get_post( $id );
		return array(
			'content' => $post->post_content,
			'mode'    => get_post_meta( $id, '_elementor_edit_mode', false ),
			'slug'    => $post->post_name,
			'parent'  => (int) $post->post_parent,
			'status'  => $post->post_status,
		);
	}
	if ( 'shop' === $kind && 'product' === get_post_type( $id ) && taxonomy_exists( 'zion_shop' ) ) {
		$terms = wp_get_object_terms( $id, 'zion_shop', array( 'fields' => 'slugs' ) );
		if ( is_array( $terms ) ) {
			sort( $terms );
			return $terms;
		}
	}
	throw new RuntimeException( 'The operation references an unsupported or missing record.' );
}

/**
 * Validate manually rebuilt Gutenberg markup without executing it.
 *
 * @param array $blocks Parsed blocks.
 * @return bool
 */
function zion_theme_migration_native_blocks( array $blocks ): bool {
	foreach ( $blocks as $block ) {
		$name = $block['blockName'];
		if ( null === $name && '' === trim( $block['innerHTML'] ) ) {
			continue;
		}
		if ( ! is_string( $name ) || ! preg_match( '#^(?:core|woocommerce|zion)/#', $name ) ||
			in_array( $name, array( 'core/html', 'core/shortcode', 'core/freeform', 'core/block', 'core/pattern' ), true ) ||
			! WP_Block_Type_Registry::get_instance()->is_registered( $name ) ||
			! zion_theme_migration_native_blocks( $block['innerBlocks'] ) ) {
			return false;
		}
	}
	return true;
}

/**
 * Build the approved native replacement for a selected page.
 *
 * @param array $item Manifest page item.
 * @param array $state Current page snapshot.
 * @return string
 * @throws RuntimeException If the replacement is not native or changes a storefront route.
 */
function zion_theme_migration_page_content( array $item, array $state ): string {
	$kind = $item['kind'] ?? 'native';
	if ( 'homepage' === $kind ) {
		return '';
	}
	if ( in_array( $kind, array( '3d', 'software' ), true ) ) {
		if ( 'magazin-' . $kind !== $state['slug'] || 0 !== $state['parent'] ) {
			throw new RuntimeException( 'The storefront page must already use its canonical root slug.' );
		}
		return '<!-- wp:shortcode -->[zion_catalog shop="' . $kind . '"]<!-- /wp:shortcode -->';
	}
	$content = $item['blocks'] ?? '';
	if ( 'service' === $kind && function_exists( 'zion_theme_services' ) && isset( zion_theme_services()[ $state['slug'] ] ) ) {
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'zion-theme/service-' . $state['slug'] );
		$content = $pattern['content'] ?? '';
	}
	if ( ! in_array( $kind, array( 'service', 'native' ), true ) || ! is_string( $content ) || '' === trim( $content ) ||
		preg_match( '/elementor|etheme|xstore|<\s*(?:script|iframe|style|object|embed)\b|\[[a-z][a-z0-9_-]*(?:\s|\])/i', $content ) ||
		! zion_theme_migration_native_blocks( parse_blocks( $content ) ) ) {
		throw new RuntimeException( 'Replacement content must contain supported native Gutenberg blocks without legacy content or shortcodes.' );
	}
	return $content;
}

/**
 * Validate an explicit manifest against the live clone and create a plan.
 *
 * @param array $manifest Reviewed migration selections.
 * @return array
 * @throws RuntimeException For a stale or unsupported selection.
 */
function zion_theme_migration_plan( array $manifest ): array {
	if ( 1 !== ( $manifest['version'] ?? null ) || home_url( '/' ) !== ( $manifest['site'] ?? null ) ||
		array_diff( array_keys( $manifest ), array( 'version', 'site', 'pages', 'shops', 'reading' ) ) ) {
		throw new RuntimeException( 'Manifest version, site or keys are invalid.' );
	}
	foreach ( array( 'pages', 'shops', 'reading' ) as $key ) {
		if ( ! is_array( $manifest[ $key ] ?? array() ) ) {
			throw new RuntimeException( 'Invalid manifest collection.' );
		}
	}
	$operations = array();
	$seen       = array();
	$reading    = $manifest['reading'] ?? array();
	$front      = $reading['page_on_front']['to'] ?? (int) get_option( 'page_on_front' );
	$protected  = function_exists( 'wc_get_page_id' ) ? array_map( 'wc_get_page_id', array( 'shop', 'cart', 'checkout', 'myaccount', 'terms' ) ) : array();
	foreach ( $manifest['pages'] ?? array() as $item ) {
		$id = $item['id'] ?? 0;
		if ( ! is_int( $id ) || $id < 1 || isset( $seen[ $id ] ) || in_array( $id, $protected, true ) ||
			( 'homepage' === ( $item['kind'] ?? '' ) && $front !== $id ) ) {
			throw new RuntimeException( 'Duplicate, protected or invalid page selected.' );
		}
		$seen[ $id ] = true;
		$before      = zion_theme_migration_state( 'page', $id );
		if ( hash( 'sha256', $before['content'] ) !== ( $item['content_sha256'] ?? '' ) ) {
			throw new RuntimeException( 'Page content changed since the audit.' );
		}
		$after            = $before;
		$after['content'] = zion_theme_migration_page_content( $item, $before );
		$after['mode']    = array();
		$operations[]     = array(
			'kind'   => 'page',
			'id'     => $id,
			'before' => $before,
			'after'  => $after,
		);
	}
	foreach ( $reading as $option => $change ) {
		$before = zion_theme_migration_state( 'option', $option );
		$after  = $change['to'] ?? null;
		if ( ( $change['from'] ?? null ) !== $before ||
			( 'show_on_front' === $option && ! in_array( $after, array( 'page', 'posts' ), true ) ) ||
			( 'show_on_front' !== $option && ( ! is_int( $after ) || $after < 0 || ( $after && ( 'page' !== get_post_type( $after ) || 'publish' !== get_post_status( $after ) || in_array( $after, $protected, true ) ) ) ) ) ) {
			throw new RuntimeException( 'Reading settings are stale or reference an invalid page.' );
		}
		$operations[] = array(
			'kind'   => 'option',
			'id'     => $option,
			'before' => $before,
			'after'  => $after,
		);
	}
	if ( 'page' === ( $reading['show_on_front']['to'] ?? get_option( 'show_on_front' ) ) &&
		( ! $front || ( $reading['page_for_posts']['to'] ?? (int) get_option( 'page_for_posts' ) ) === $front ) ) {
		throw new RuntimeException( 'Static homepage and posts page must be distinct.' );
	}
	$seen = array();
	foreach ( $manifest['shops'] ?? array() as $item ) {
		$id = $item['id'] ?? 0;
		if ( ! is_int( $id ) || $id < 1 || isset( $seen[ $id ] ) ) {
			throw new RuntimeException( 'Invalid or duplicate product ID.' );
		}
		$seen[ $id ] = true;
		$before      = zion_theme_migration_state( 'shop', $id );
		$from        = $item['from'] ?? null;
		$after       = $item['to'] ?? null;
		if ( ! is_array( $from ) || ! is_array( $after ) || array_filter( array_merge( $from, $after ), static fn ( $term ): bool => ! is_string( $term ) ) || array_diff( $after, array( '3d', 'software' ) ) || count( $after ) !== count( array_unique( $after ) ) ) {
			throw new RuntimeException( 'Invalid storefront selections.' );
		}
		sort( $from );
		sort( $after );
		if ( $before !== $from ) {
			throw new RuntimeException( 'Product storefront assignments changed since the audit.' );
		}
		$operations[] = array(
			'kind'   => 'shop',
			'id'     => $id,
			'before' => $before,
			'after'  => $after,
		);
	}
	return array_values( array_filter( $operations, static fn ( array $row ): bool => $row['before'] !== $row['after'] ) );
}

/**
 * Write one approved record through native WordPress APIs and verify it.
 *
 * @param array  $row Planned operation.
 * @param string $side Target state.
 * @throws RuntimeException If a write is rejected or changed during processing.
 */
function zion_theme_migration_write( array $row, string $side ): void {
	$id    = $row['id'];
	$state = $row[ $side ];
	switch ( $row['kind'] ) {
		case 'page':
			$result = wp_update_post(
				wp_slash(
					array(
						'ID'           => $id,
						'post_content' => $state['content'],
					)
				),
				true
			);
			if ( ! is_wp_error( $result ) ) {
				delete_post_meta( $id, '_elementor_edit_mode' );
				foreach ( $state['mode'] as $value ) {
					add_post_meta( $id, '_elementor_edit_mode', wp_slash( $value ) );
				}
			}
			break;
		case 'shop':
			$result = wp_set_object_terms( $id, $state, 'zion_shop' );
			break;
		default:
			$result = update_option( $id, $state );
	}
	if ( is_wp_error( $result ) || zion_theme_migration_state( $row['kind'], $id ) !== $state ) {
		throw new RuntimeException( 'An operation could not be verified. Use the saved rollback journal before retrying.' );
	}
}
