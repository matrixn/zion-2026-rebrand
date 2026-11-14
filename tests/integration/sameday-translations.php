<?php
/**
 * Sameday checkout labels use the provider's gettext contract, without shipping credentials.
 *
 * @package Zion_Theme
 */

// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited -- Synthetic locale/screen isolation is restored in finally.

( static function (): void {
	global $current_screen;
	$before = $current_screen ?? null;
	$locale = 'ro_RO';
	$filter = static function () use ( &$locale ): string {
		return $locale;
	};
	add_filter( 'determine_locale', $filter );
	try {
		$current_screen = null;
		$strings        = array(
			'Show Locations Map'                           => 'Alege un punct de ridicare pe hartă',
			'Ship to'                                      => 'Livrare la',
			'Please choose your EasyBox Locker !'          => 'Alege un locker easybox pentru livrare.',
			'Select easyBox'                               => 'Alege un locker easybox',
			'Please wait for easyBox list to be populated' => 'Se încarcă lista punctelor easybox…',
		);
		foreach ( array( 'ro_RO', 'en_US' ) as $locale ) {
			foreach ( $strings as $source => $expected ) {
				// phpcs:ignore WordPress.WP.I18n -- Exercise the provider's native gettext contract with a synthetic message matrix.
				zion_core_test_assert( translate( $source, 'samedaycourier-shipping' ) === $expected, 'Sameday native PHP/Blocks fallback: ' . $locale . ' ' . $source );
			}
		}
		zion_core_test_assert( 'Traducere proprie' === zion_theme_translate_sameday( 'Traducere proprie', 'Ship to', 'samedaycourier-shipping' ) && 'Ship to' === zion_theme_translate_sameday( 'Ship to', 'Ship to', 'other-plugin' ), 'Installed Sameday translations and unrelated domains remain untouched' );
		zion_core_test_assert( 'Unknown label' === zion_theme_translate_sameday( 'Unknown label', 'Unknown label', 'samedaycourier-shipping' ), 'Unlisted Sameday messages remain unchanged' );
		$locale = 'de_DE';
		zion_core_test_assert( 'Ship to' === zion_theme_translate_sameday( 'Ship to', 'Ship to', 'samedaycourier-shipping' ), 'Sameday fallback leaves other storefront locales alone' );
		$locale = 'ro_RO';
		require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
		require_once ABSPATH . 'wp-admin/includes/screen.php';
		set_current_screen( 'dashboard' );
		zion_core_test_assert( 'Ship to' === zion_theme_translate_sameday( 'Ship to', 'Ship to', 'samedaycourier-shipping' ), 'Sameday administration labels retain their installed language' );
	} finally {
		remove_filter( 'determine_locale', $filter );
		$current_screen = $before;
	}
} )();
