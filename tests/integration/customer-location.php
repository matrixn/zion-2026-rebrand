<?php
/**
 * Native WooCommerce geolocation, restrictions and customer precedence.
 *
 * @package Zion_Core
 */

$zion_core_location_keys   = array( 'woocommerce_default_customer_address', 'woocommerce_default_country', 'woocommerce_allowed_countries', 'woocommerce_specific_allowed_countries', 'woocommerce_ship_to_countries', 'woocommerce_specific_ship_to_countries' );
$zion_core_location_before = array();
foreach ( $zion_core_location_keys as $zion_core_location_key ) {
	$zion_core_location_before[ $zion_core_location_key ] = get_option( $zion_core_location_key );
}
$zion_core_location_detected = 'DE';
$zion_core_location_stub     = static function () use ( &$zion_core_location_detected ): string {
	return $zion_core_location_detected;
};
add_filter( 'woocommerce_geolocate_ip', $zion_core_location_stub, 1 );
try {
	update_option( 'woocommerce_allowed_countries', 'all' );
	update_option( 'woocommerce_default_country', 'US:CA' );
	foreach ( array( 'geolocation', 'geolocation_ajax' ) as $zion_core_location_mode ) {
		update_option( 'woocommerce_default_customer_address', $zion_core_location_mode );
		$zion_core_location_detected = 'DE';
		zion_core_test_assert( 'DE' === wc_get_customer_default_location()['country'], $zion_core_location_mode . ' uses the native detected country before the Romanian fallback' );
		$zion_core_location_detected = '';
		zion_core_test_assert(
			array(
				'country' => 'RO',
				'state'   => '',
			) === wc_get_customer_default_location(),
			$zion_core_location_mode . ' falls back to Romania without copying the shop county'
		);
		$zion_core_location_detected = 'ZZ';
		zion_core_test_assert( 'RO' === wc_get_customer_default_location()['country'], $zion_core_location_mode . ' rejects an invalid detected country' );
	}
	update_option( 'woocommerce_default_customer_address', 'base' );
	zion_core_test_assert(
		array(
			'country' => 'US',
			'state'   => 'CA',
		) === wc_get_customer_default_location(),
		'An explicit WooCommerce base-location choice is respected'
	);
	update_option( 'woocommerce_default_customer_address', '' );
	zion_core_test_assert( '' === wc_get_customer_default_location()['country'], 'An explicit WooCommerce no-location choice is respected' );
	update_option( 'woocommerce_default_customer_address', 'geolocation' );
	update_option( 'woocommerce_allowed_countries', 'specific' );
	update_option( 'woocommerce_specific_allowed_countries', array( 'FR', 'RO', 'DE' ) );
	zion_core_test_assert( 'RO' === array_key_first( WC()->countries->get_countries() ) && 'RO' === array_key_first( WC()->countries->get_allowed_countries() ), 'Romania is first in the native full and allowed country lists' );
	update_option( 'woocommerce_ship_to_countries', 'specific' );
	update_option( 'woocommerce_specific_ship_to_countries', array( 'FR', 'RO' ) );
	zion_core_test_assert( array( 'RO', 'FR' ) === array_keys( WC()->countries->get_shipping_countries() ), 'Shipping countries retain native restrictions with Romania first' );
	update_option( 'woocommerce_specific_allowed_countries', array( 'DE' ) );
	$zion_core_location_detected = '';
	zion_core_test_assert( '' === wc_get_customer_default_location()['country'] && ! isset( WC()->countries->get_allowed_countries()['RO'] ), 'The fallback never re-enables a country excluded by the merchant' );
	update_option( 'woocommerce_specific_ship_to_countries', array( 'FR' ) );
	zion_core_test_assert( array( 'FR' ) === array_keys( WC()->countries->get_shipping_countries() ), 'Ordering never inserts Romania into an excluded shipping list' );
	$zion_core_location_customer = new WC_Customer();
	$zion_core_location_customer->set_billing_country( 'DE' );
	$zion_core_location_customer->set_shipping_country( 'FR' );
	wc_get_customer_default_location();
	zion_core_test_assert( 'DE' === $zion_core_location_customer->get_billing_country() && 'FR' === $zion_core_location_customer->get_shipping_country(), 'Default location hooks do not replace explicitly entered customer addresses' );
} finally {
	remove_filter( 'woocommerce_geolocate_ip', $zion_core_location_stub, 1 );
	foreach ( $zion_core_location_before as $zion_core_location_key => $zion_core_location_value ) {
		if ( false === $zion_core_location_value ) {
			delete_option( $zion_core_location_key );
		} else {
			update_option( $zion_core_location_key, $zion_core_location_value );
		}
	}
}
