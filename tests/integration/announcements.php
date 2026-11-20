<?php
/**
 * Public news selection, catalogue boundaries and native header rendering.
 *
 * @package Zion_Theme
 */

( static function (): void {
	$assert = static function ( bool $condition, string $message ): void {
		if ( ! $condition ) {
			throw new RuntimeException( esc_html( $message ) );
		}
		zion_core_test_assert( true, $message );
	};
	$ids    = array();
	$before = get_option( 'woocommerce_hide_out_of_stock_items' );
	$create = static function ( string $type, string $title, int $offset = 0 ) use ( &$ids ): int {
		$id    = wp_insert_post(
			array(
				'post_type'   => $type,
				'post_title'  => $title,
				'post_name'   => sanitize_title( $title ),
				'post_status' => 'draft',
				'post_date'   => '2090-01-' . sprintf( '%02d', $offset + 1 ) . ' 12:00:00',
			)
		);
		$ids[] = $id;
		wp_publish_post( $id );
		return $id;
	};
	try {
		$old     = $create( 'post', 'Synthetic older article' );
		$article = $create( 'post', 'Synthetic &amp; newest article', 1 );
		$private = $create( 'post', 'Synthetic private article', 2 );
		wp_update_post(
			array(
				'ID'          => $private,
				'post_status' => 'private',
			)
		);
		$draft = $create( 'post', 'Synthetic draft article', 3 );
		wp_update_post(
			array(
				'ID'          => $draft,
				'post_status' => 'draft',
			)
		);
		$password = $create( 'post', 'Synthetic protected article', 4 );
		wp_update_post(
			array(
				'ID'            => $password,
				'post_password' => 'synthetic-only',
			)
		);
		$products = array();
		foreach ( array( 'visible', 'variable', 'software', 'hidden', 'search', 'stock', 'draft', 'unclassified', 'both' ) as $offset => $name ) {
			$id                = $create( 'product', 'Synthetic ' . $name, $offset );
			$products[ $name ] = $id;
			$product           = 'variable' === $name ? new WC_Product_Variable( $id ) : new WC_Product_Simple( $id );
			$product->set_regular_price( '10' );
			$product->set_catalog_visibility( in_array( $name, array( 'hidden', 'search' ), true ) ? $name : 'visible' );
			$product->set_stock_status( 'stock' === $name ? 'outofstock' : 'instock' );
			$product->save();
			if ( 'variable' === $name ) {
				$variation = new WC_Product_Variation();
				$variation->set_parent_id( $id );
				$variation->set_regular_price( '10' );
				$variation->set_stock_status( 'instock' );
				$ids[] = $variation->save();
				WC_Product_Variable::sync( $id );
			}
			wp_publish_post( $id );
			if ( 'unclassified' !== $name ) {
				wp_set_object_terms( $id, 'software' === $name ? array( 'software' ) : ( 'both' === $name ? array( '3d', 'software' ) : array( '3d' ) ), 'zion_shop' );
			}
		}
		wp_update_post(
			array(
				'ID'          => $products['draft'],
				'post_status' => 'draft',
			)
		);
		update_option( 'woocommerce_hide_out_of_stock_items', 'yes' );
		$items = zion_core_header_updates();
		$assert( array_column( $items, 'id' ) === array( $article, $old, $products['both'], $products['variable'] ), 'Header feed selects exactly two newest public articles and two visible classified 3D products, including variable and dual-shop parents' );
		$assert( 'Synthetic & newest article' === $items[0]['title'] && 'article' === $items[0]['type'] && 'product' === $items[2]['type'], 'Feed titles are plain decoded text and preserve their context' );
		update_option( 'woocommerce_hide_out_of_stock_items', 'no' );
		$assert( array_column( zion_core_header_updates(), 'id' ) === array( $article, $old, $products['both'], $products['stock'] ), 'Native WooCommerce out-of-stock visibility setting controls news products' );
		$html = zion_theme_announcements();
		$assert( 4 === substr_count( $html, '<li>' ) && str_contains( $html, esc_url( get_permalink( $article ) ) ) && str_contains( $html, 'Synthetic &amp; newest article' ), 'Header HTML escapes all titles and retains four native links without JavaScript' );
		$assert( wp_script_is( 'zion-announcements', 'enqueued' ) && str_contains( $html, 'zion-updates__close' ), 'Native feed enqueues its controller and close control' );
		$block = render_block( parse_blocks( '<!-- wp:zion/editorial {"part":"announcements"} /-->' )[0] );
		$assert( $block === $html, 'Site Editor header component shares the same news renderer' );
		$legacy = '<aside class="zion-migration-announcement"><p>Synthetic retired payment notice.</p></aside>';
		$assert( zion_theme_announcement( $legacy ) === $html && ! str_contains( zion_theme_announcement( $legacy ), 'retired payment' ), 'Saved legacy payment notices are replaced with current news' );
		$plain = '<aside class="other-announcement">Synthetic unrelated announcement.</aside>';
		$assert( zion_theme_announcement( $plain ) === $plain, 'Unrelated authored HTML remains unchanged' );
		wp_update_post(
			array(
				'ID'          => $article,
				'post_status' => 'draft',
			)
		);
		$assert( ! in_array( $article, array_column( zion_core_header_updates(), 'id' ), true ), 'Unpublishing an article immediately removes it from the feed without stale application caches' );
	} finally {
		foreach ( $ids as $id ) {
			wp_delete_post( $id, true );
		}
		update_option( 'woocommerce_hide_out_of_stock_items', $before );
	}
} )();
