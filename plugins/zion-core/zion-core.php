<?php
/**
 * Plugin Name: Zion Core
 * Description: Product classification and storefront tools for Zion.
 * Version: 1.1.19
 * Requires at least: 6.9
 * Requires PHP: 8.3
 * Text Domain: zion-core
 *
 * @package Zion_Core
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/includes/shops.php';
require_once __DIR__ . '/includes/catalog.php';
require_once __DIR__ . '/includes/product-collection.php';
require_once __DIR__ . '/includes/collections.php';
require_once __DIR__ . '/includes/classification.php';
require_once __DIR__ . '/includes/product-details.php';
if ( is_admin() ) {
	require_once __DIR__ . '/includes/shop-admin.php';
}

add_action( 'init', 'zion_core_register_shops', 20 );
add_action( 'init', 'zion_core_register_shop_journal' );
add_action( 'admin_init', 'zion_core_install_shops' );
register_activation_hook( __FILE__, 'zion_core_install_shops' );
add_shortcode( 'zion_catalog', 'zion_core_catalog' );

add_action(
	'before_woocommerce_init',
	static function (): void {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);
