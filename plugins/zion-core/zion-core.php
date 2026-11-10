<?php
/**
 * Plugin Name: Zion Core
 * Description: Product classification and storefront tools for Zion.
 * Version: 0.1.0
 * Requires at least: 6.9
 * Requires PHP: 8.3
 * Text Domain: zion-core
 *
 * @package Zion_Core
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/includes/catalog.php';

add_action( 'init', 'zion_core_register_shops', 20 );
add_shortcode( 'zion_catalog', 'zion_core_catalog' );
