<?php
/**
 * Customer location defaults using WooCommerce's existing geolocation pipeline.
 *
 * @package Zion_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Romania is the fallback before WooCommerce applies a valid IP location.
 * Saved customer/session addresses are owned by WooCommerce and remain intact.
 *
 * @param string $location Existing default country/state.
 * @return string
 */
function zion_core_customer_location_fallback( string $location ): string {
	return in_array( get_option( 'woocommerce_default_customer_address' ), array( 'geolocation', 'geolocation_ajax' ), true ) ? 'RO' : $location;
}
add_filter( 'woocommerce_customer_default_location', 'zion_core_customer_location_fallback' );

/**
 * Keep native localized sorting with Romania first, only when already allowed.
 *
 * @param array $countries Native country code/name pairs.
 * @return array
 */
function zion_core_order_customer_countries( array $countries ): array {
	wc_asort_by_locale( $countries );
	return isset( $countries['RO'] ) ? array( 'RO' => $countries['RO'] ) + $countries : $countries;
}
add_filter( 'woocommerce_countries', 'zion_core_order_customer_countries' );
add_filter( 'woocommerce_sort_countries', '__return_false' );
add_filter( 'woocommerce_countries_allowed_countries', 'zion_core_order_customer_countries' );
add_filter( 'woocommerce_countries_shipping_countries', 'zion_core_order_customer_countries' );

/**
 * Blocks alphabetize countryData separately from WC_Countries. Reorder that
 * existing settings object before the native address components initialize.
 */
function zion_core_order_block_country_data(): void {
	if ( ! function_exists( 'is_checkout' ) || ( ! is_checkout() && ! is_cart() ) ) {
		return;
	}
	wp_enqueue_script(
		'zion-core-country-order',
		plugins_url( 'assets/customer-location.js', dirname( __DIR__ ) . '/zion-core.php' ),
		array( 'wc-settings' ),
		get_file_data( dirname( __DIR__ ) . '/zion-core.php', array( 'Version' => 'Version' ) )['Version'],
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
	$scripts = wp_scripts();
	if ( isset( $scripts->registered['wc-cart-checkout-base'] ) ) {
		$scripts->registered['wc-cart-checkout-base']->deps[] = 'zion-core-country-order';
	}
}
add_action( 'wp_enqueue_scripts', 'zion_core_order_block_country_data', 30 );
