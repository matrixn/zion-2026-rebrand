<?php
/**
 * Rehearse a reversible migration on the isolated wp-env tests database.
 *
 * @package Zion_Theme
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI || 'local' !== wp_get_environment_type() || 8917 !== (int) wp_parse_url( home_url(), PHP_URL_PORT ) || ! in_array( wp_parse_url( home_url(), PHP_URL_HOST ), array( 'localhost', '127.0.0.1', '[::1]' ), true ) ) {
	return;
}

define( 'ZION_THEME_MIGRATION_LIBRARY', true );
require WP_CONTENT_DIR . '/zion-scripts/migration.php';

/**
 * Record a rehearsal check.
 *
 * @param bool   $condition Test result.
 * @param string $message Test label.
 * @throws RuntimeException When the check fails.
 */
function zion_theme_migration_check( bool $condition, string $message ): void {
	if ( ! $condition ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- This is a plain-text WP-CLI test failure.
		throw new RuntimeException( $message );
	}
	WP_CLI::log( 'PASS ' . $message );
}

/**
 * Verify a destructive operation is rejected before writing anything.
 *
 * @param array  $arguments Migration CLI arguments.
 * @param string $message Test label.
 */
function zion_theme_migration_rejects( array $arguments, string $message ): void {
	$rejected = false;
	try {
		zion_theme_migration_command( $arguments );
	} catch ( RuntimeException $error ) {
		$rejected = true;
	}
	zion_theme_migration_check( $rejected, $message );
}

/**
 * Exercise order storage and download permissions through WooCommerce CRUD.
 */
function zion_theme_migration_check_hpos(): void {
	$synchronizer = wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\DataSynchronizer::class );
	zion_theme_migration_check( $synchronizer->check_orders_table_exists() || $synchronizer->create_database_tables(), 'The isolated tests database has WooCommerce HPOS tables' );
	add_filter( 'pre_option_woocommerce_custom_orders_table_enabled', static fn (): string => 'yes' );
	add_filter( 'pre_option_wc_downloads_approved_directories_mode', static fn (): string => 'disabled' );
	add_filter( 'pre_wp_mail', '__return_true' );
	$customer = new WC_Customer();
	$product  = new WC_Product_Simple();
	$order    = null;
	$upload   = null;

	try {
		$upload = wp_upload_bits( 'zion-rehearsal-' . wp_generate_uuid4() . '.txt', null, 'Zion synthetic download' );
		zion_theme_migration_check( empty( $upload['error'] ), 'A synthetic digital file can be created in the test uploads directory' );
		$download = new WC_Product_Download();
		$download->set_id( wp_generate_uuid4() );
		$download->set_name( 'Zion rehearsal file' );
		$download->set_file( $upload['url'] );
		$product->set_name( 'Zion digital rehearsal' );
		$product->set_status( 'private' );
		$product->set_regular_price( '19.00' );
		$product->set_virtual( true );
		$product->set_downloadable( true );
		$product->set_downloads( array( $download ) );
		$product->save();
		zion_theme_migration_check( wc_get_product( $product->get_id() )->has_file( $download->get_id() ), 'The WooCommerce product keeps the synthetic file' );

		$customer->set_email( wp_generate_uuid4() . '@example.test' );
		$customer->set_password( wp_generate_password( 32 ) );
		$customer->save();
		$order = new WC_Order();
		$order->set_customer_id( $customer->get_id() );
		$order->set_billing_email( $customer->get_email() );
		$order->save();
		$order->add_product( $product );
		$order->set_status( 'completed' );
		$order->calculate_totals();
		$order->save();

		$stored = wc_get_order( $order->get_id() );
		zion_theme_migration_check( Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() && $stored instanceof WC_Order && $stored->has_status( 'completed' ), 'HPOS saves and reads a completed order via WooCommerce CRUD' );
		$downloads = array_filter( wc_get_customer_available_downloads( $customer->get_id() ), static fn ( array $row ): bool => $download->get_id() === $row['download_id'] && (int) $row['order_id'] === $order->get_id() );
		zion_theme_migration_check( 1 === count( $downloads ), 'Completed HPOS order grants the digital download to its customer' );
	} finally {
		if ( $order && $order->get_id() ) {
			WC_Data_Store::load( 'customer-download' )->delete_by_order_id( $order->get_id() );
			$order->delete( true );
		}
		if ( $product->get_id() ) {
			$product->delete( true );
		}
		if ( ! empty( $upload['file'] ) ) {
			wp_delete_file( $upload['file'] );
		}
		if ( $customer->get_id() ) {
			$customer->delete( true );
		}
	}
}

$zion_theme_admins = get_users(
	array(
		'role'   => 'administrator',
		'number' => 1,
		'fields' => 'ID',
	)
);
wp_set_current_user( (int) $zion_theme_admins[0] );
$zion_theme_site      = home_url( '/' );
$zion_theme_original  = (int) get_option( 'page_on_front' );
$zion_theme_product   = wc_get_product( wc_get_product_id_by_sku( 'ZION-DEMO-3D-01' ) );
$zion_theme_terms     = zion_theme_migration_state( 'shop', $zion_theme_product->get_id() );
$zion_theme_price     = $zion_theme_product->get_price();
$zion_theme_service   = get_page_by_path( 'printare-3d' );
$zion_theme_directory = sys_get_temp_dir() . '/zion-rehearsal-' . wp_generate_uuid4();
$zion_theme_manifest  = $zion_theme_directory . '/manifest.json';
$zion_theme_backup    = $zion_theme_directory . '/backup.sql';
$zion_theme_journal   = $zion_theme_directory . '/rollback.json';
$zion_theme_page      = 0;

// phpcs:disable WordPress.WP.AlternativeFunctions -- Private ephemeral files used only by the isolated CLI rehearsal.
mkdir( $zion_theme_directory, 0700 );
try {
	$zion_theme_legacy = '<p>[et_button] Continut sintetic Elementor.</p>';
	$zion_theme_page   = wp_insert_post(
		wp_slash(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Zion legacy rehearsal',
				'post_name'    => 'zion-legacy-rehearsal',
				'post_content' => $zion_theme_legacy,
			)
		),
		true
	);
	zion_theme_migration_check( is_int( $zion_theme_page ) && $zion_theme_page > 0, 'Synthetic legacy page created' );
	update_post_meta( $zion_theme_page, '_elementor_edit_mode', 'builder' );
	update_post_meta( $zion_theme_page, '_elementor_data', '[{"widgetType":"etheme-button","settings":{"shortcode":"[et_nested]"}}]' );
	update_post_meta( $zion_theme_page, '_yoast_wpseo_metadesc', 'Synthetic SEO description.' );
	add_filter( 'sidebars_widgets', static fn ( array $sidebars ): array => array_merge( $sidebars, array( 'zion-rehearsal' => array( 'etheme-posts-1' ) ) ) );
	$zion_theme_audit = zion_theme_migration_command( array( 'audit', $zion_theme_site ) );
	$zion_theme_rows  = array_column( $zion_theme_audit['content'], null, 'id' );
	$zion_theme_row   = $zion_theme_rows[ $zion_theme_page ];
	zion_theme_migration_check( $zion_theme_row['dependencies']['elementor_builder'] && in_array( 'etheme-button', $zion_theme_row['dependencies']['xstore_candidates'], true ) && in_array( 'et_button', $zion_theme_row['dependencies']['shortcodes'], true ), 'Audit identifies Elementor, XStore and shortcode indicators' );
	zion_theme_migration_check( in_array( 'et_nested', $zion_theme_row['dependencies']['shortcodes'], true ) && in_array( 'etheme-posts-1', $zion_theme_audit['xstore_sidebar_candidates'], true ), 'Audit identifies shortcodes inside Elementor data and XStore sidebar widget instances' );
	zion_theme_migration_check( 'Synthetic SEO description.' === $zion_theme_row['seo']['_yoast_wpseo_metadesc'] && str_ends_with( $zion_theme_row['url'], '/zion-legacy-rehearsal/' ), 'Audit includes SEO and the existing route' );
	zion_theme_migration_check( \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() === $zion_theme_audit['commerce']['hpos'] && ! empty( $zion_theme_audit['product_types']['simple'] ), 'Audit sees WooCommerce product types and the current HPOS state' );
	$zion_theme_plan = array(
		'version' => 1,
		'site'    => $zion_theme_site,
		'pages'   => array(
			array(
				'id'             => $zion_theme_page,
				'content_sha256' => $zion_theme_row['content_sha256'],
				'kind'           => 'homepage',
			),
			array(
				'id'             => $zion_theme_service->ID,
				'content_sha256' => hash( 'sha256', $zion_theme_service->post_content ),
				'kind'           => 'service',
			),
		),
		'shops'   => array(
			array(
				'id'   => $zion_theme_product->get_id(),
				'from' => $zion_theme_terms,
				'to'   => array( '3d', 'software' ),
			),
		),
		'reading' => array(
			'page_on_front' => array(
				'from' => $zion_theme_original,
				'to'   => $zion_theme_page,
			),
		),
	);
	file_put_contents( $zion_theme_manifest, wp_json_encode( $zion_theme_plan ) );
	$zion_theme_dry = zion_theme_migration_command( array( 'dry-run', $zion_theme_site, $zion_theme_manifest ) );
	zion_theme_migration_check( count( $zion_theme_dry['changes'] ) >= 3 && get_post_field( 'post_content', $zion_theme_page ) === $zion_theme_legacy && ! file_exists( $zion_theme_journal ), 'Dry-run reports changes without writing content or a journal' );
	zion_theme_migration_rejects( array( 'audit', 'https://zion3d.ro/' ), 'Production URL is refused without a network request' );
	zion_theme_migration_rejects( array( 'apply', $zion_theme_site, $zion_theme_manifest, str_repeat( '0', 64 ) ), 'Apply rejects an unapproved plan' );
	zion_theme_migration_rejects( array( 'apply', $zion_theme_site, $zion_theme_manifest, $zion_theme_dry['plan_sha256'], $zion_theme_backup ), 'Apply rejects a missing backup' );
	WP_CLI::runcommand( 'db export ' . escapeshellarg( $zion_theme_backup ), array( 'return' => true ) );
	$zion_theme_apply = zion_theme_migration_command( array( 'apply', $zion_theme_site, $zion_theme_manifest, $zion_theme_dry['plan_sha256'], $zion_theme_backup, hash_file( 'sha256', $zion_theme_backup ), $zion_theme_journal ) );
	zion_theme_migration_check( count( $zion_theme_dry['changes'] ) === $zion_theme_apply['verified'] && file_exists( $zion_theme_journal ) && '' === get_post_field( 'post_content', $zion_theme_page ) && (int) get_option( 'page_on_front' ) === $zion_theme_page, 'Apply uses a real SQL backup, journals first and installs the native homepage' );
	zion_theme_migration_check( '' === get_post_meta( $zion_theme_page, '_elementor_edit_mode', true ) && '[{"widgetType":"etheme-button","settings":{"shortcode":"[et_nested]"}}]' === get_post_meta( $zion_theme_page, '_elementor_data', true ) && 'Synthetic SEO description.' === get_post_meta( $zion_theme_page, '_yoast_wpseo_metadesc', true ), 'Apply keeps SEO and legacy data for rollback' );
	zion_theme_migration_check( array( '3d', 'software' ) === zion_theme_migration_state( 'shop', $zion_theme_product->get_id() ) && wc_get_product( $zion_theme_product->get_id() )->get_price() === $zion_theme_price, 'Classification leaves the native price unchanged' );
	$zion_theme_after = zion_theme_migration_command( array( 'audit', $zion_theme_site ) );
	$zion_theme_back  = zion_theme_migration_command( array( 'rollback', $zion_theme_site, $zion_theme_journal ) );
	zion_theme_migration_check( $zion_theme_back['verified'] === $zion_theme_apply['verified'] && get_post_field( 'post_content', $zion_theme_page ) === $zion_theme_legacy && 'builder' === get_post_meta( $zion_theme_page, '_elementor_edit_mode', true ) && (int) get_option( 'page_on_front' ) === $zion_theme_original && zion_theme_migration_state( 'shop', $zion_theme_product->get_id() ) === $zion_theme_terms, 'Rollback restores page content, edit mode, reading settings and product terms' );
	zion_theme_migration_command( array( 'rollback', $zion_theme_site, $zion_theme_journal ) );
	WP_CLI::line( 'ZION_AUDIT_BEFORE ' . wp_json_encode( $zion_theme_audit, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
	WP_CLI::line( 'ZION_AUDIT_AFTER ' . wp_json_encode( $zion_theme_after, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
	WP_CLI::line( 'ZION_DRY_RUN ' . wp_json_encode( $zion_theme_dry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
	WP_CLI::line(
		'ZION_REHEARSAL ' . wp_json_encode(
			array(
				'apply'       => $zion_theme_apply,
				'rollback'    => $zion_theme_back,
				'environment' => 'local-demo',
			)
		)
	);
	zion_theme_migration_check_hpos();
} finally {
	update_option( 'page_on_front', $zion_theme_original );
	wp_set_object_terms( $zion_theme_product->get_id(), $zion_theme_terms, 'zion_shop' );
	if ( $zion_theme_page && ! is_wp_error( $zion_theme_page ) ) {
		wp_delete_post( $zion_theme_page, true );
	}
	foreach ( array( $zion_theme_manifest, $zion_theme_backup, $zion_theme_journal ) as $zion_theme_file ) {
		if ( is_file( $zion_theme_file ) ) {
			unlink( $zion_theme_file );
		}
	}
	rmdir( $zion_theme_directory );
}
// phpcs:enable WordPress.WP.AlternativeFunctions
