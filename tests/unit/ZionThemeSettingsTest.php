<?php
/**
 * Control Center schema and precedence tests.
 *
 * @package Zion_Theme
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../themes/zion-theme/includes/class-zion-theme-settings.php';

/**
 * Exercise the registry without booting WordPress.
 */
final class ZionThemeSettingsTest extends TestCase {
	/**
	 * The five header designs share one validated, global, portable setting.
	 */
	public function test_header_designs_are_global_and_round_trip(): void {
		$config = Zion_Theme_Settings::empty_config();
		self::assertSame( 'atelier', Zion_Theme_Settings::resolve( $config, 'header_design' )['value'] );
		self::assertCount( 5, Zion_Theme_Settings::header_designs() );
		self::assertSame( array( 'global' ), Zion_Theme_Settings::registry()['header_design']['contexts'] );
		foreach ( array_keys( Zion_Theme_Settings::header_designs() ) as $design ) {
			$config['global']['header_design'] = $design;
			$config                            = Zion_Theme_Settings::normalize( $config );
			self::assertSame( $design, Zion_Theme_Settings::resolve( $config, 'header_design' )['value'] );
			// phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- Standalone unit test.
			self::assertSame( $config, Zion_Theme_Settings::import( json_encode( $config, JSON_THROW_ON_ERROR ) ) );
		}
		self::assertTrue( Zion_Theme_Settings::registry()['header_cart_badge']['default'] );
	}
	/**
	 * Account presentation shares the registry, never commercial settings.
	 */
	public function test_account_designs_are_global_and_round_trip(): void {
		$config = Zion_Theme_Settings::empty_config();
		self::assertSame( 'atelier', Zion_Theme_Settings::resolve( $config, 'account_design' )['value'] );
		foreach ( array( 'shop', 'product', 'page' ) as $context ) {
			self::assertArrayNotHasKey( 'account_design', Zion_Theme_Settings::fields( $context ) );
		}
		foreach ( array_keys( Zion_Theme_Settings::account_designs() ) as $design ) {
			$config['global']['account_design'] = $design;
			$config                             = Zion_Theme_Settings::normalize( $config );
			self::assertSame( $design, Zion_Theme_Settings::resolve( $config, 'account_design' )['value'] );
			// phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- Standalone unit tests.
			self::assertSame( $config, Zion_Theme_Settings::import( json_encode( $config, JSON_THROW_ON_ERROR ) ) );
		}
	}
	/**
	 * Checkout presentation shares the registry, never commercial settings.
	 */
	public function test_checkout_designs_are_global_and_round_trip(): void {
		$config = Zion_Theme_Settings::empty_config();
		self::assertSame( 'atelier', Zion_Theme_Settings::resolve( $config, 'checkout_design' )['value'] );
		foreach ( array( 'shop', 'product', 'page' ) as $context ) {
			self::assertArrayNotHasKey( 'checkout_design', Zion_Theme_Settings::fields( $context ) );
		}
		foreach ( array_keys( Zion_Theme_Settings::checkout_designs() ) as $design ) {
			$config['global']['checkout_design'] = $design;
			$config                              = Zion_Theme_Settings::normalize( $config );
			self::assertSame( $design, Zion_Theme_Settings::resolve( $config, 'checkout_design' )['value'] );
			// phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- Standalone unit tests.
			self::assertSame( $config, Zion_Theme_Settings::import( json_encode( $config, JSON_THROW_ON_ERROR ) ) );
		}
	}
	/**
	 * There is one native cart and a single global presentation choice.
	 */
	public function test_cart_designs_use_one_global_setting_and_round_trip(): void {
		$config = Zion_Theme_Settings::empty_config();
		self::assertSame( 'atelier', Zion_Theme_Settings::resolve( $config, 'cart_design' )['value'] );
		self::assertArrayNotHasKey( 'cart_design', Zion_Theme_Settings::fields( 'shop' ) );
		self::assertArrayNotHasKey( 'cart_design', Zion_Theme_Settings::fields( 'product' ) );
		foreach ( array_keys( Zion_Theme_Settings::cart_designs() ) as $design ) {
			$config['global']['cart_design'] = $design;
			$config                          = Zion_Theme_Settings::normalize( $config );
			self::assertSame( $design, Zion_Theme_Settings::resolve( $config, 'cart_design' )['value'] );
			// phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- Standalone unit tests.
			self::assertSame( $config, Zion_Theme_Settings::import( json_encode( $config, JSON_THROW_ON_ERROR ) ) );
		}
	}

	/**
	 * Layout choices remain independent between stores and from content presets.
	 */
	public function test_single_product_design_precedence_and_choices(): void {
		$config = Zion_Theme_Settings::empty_config();
		self::assertSame( 'atelier', Zion_Theme_Settings::resolve( $config, 'product_design' )['value'] );
		$config['global'] = array(
			'product_design' => 'immersive',
			'product_preset' => 'software',
		);
		$config['shops']  = array(
			'3d'       => array( 'product_design' => 'technical' ),
			'software' => array( 'product_design' => 'editorial' ),
		);
		$config['items']  = array( 'product' => array( 42 => array( 'product_design' => 'focus' ) ) );
		$config           = Zion_Theme_Settings::normalize( $config );
		self::assertSame( 'technical', Zion_Theme_Settings::resolve( $config, 'product_design', '3d' )['value'] );
		self::assertSame( 'editorial', Zion_Theme_Settings::resolve( $config, 'product_design', 'software' )['value'] );
		self::assertSame( 'focus', Zion_Theme_Settings::resolve( $config, 'product_design', '3d', 'product', 42 )['value'] );
		self::assertSame( 'immersive', Zion_Theme_Settings::resolve( $config, 'product_design' )['value'] );
		self::assertSame( 'software', Zion_Theme_Settings::resolve( $config, 'product_preset', '3d' )['value'] );
		foreach ( array_keys( Zion_Theme_Settings::product_designs() ) as $design ) {
			self::assertSame( array( 'product_design' => $design ), Zion_Theme_Settings::normalize_scope( array( 'product_design' => $design ), Zion_Theme_Settings::fields( 'product' ), true ) );
		}
		$this->expectException( InvalidArgumentException::class );
		Zion_Theme_Settings::normalize_scope( array( 'product_design' => 'unknown' ), Zion_Theme_Settings::fields( 'product' ), true );
	}

	/**
	 * New designs use the existing option and preserve explicit page precedence.
	 */
	public function test_visual_designs_share_the_existing_card_setting(): void {
		$config = Zion_Theme_Settings::normalize(
			array(
				'version' => 2,
				'global'  => array( 'card_preset' => 'atelier' ),
				'shops'   => array(
					'3d'       => array( 'card_preset' => 'technical' ),
					'software' => array( 'card_preset' => 'editorial' ),
				),
				'items'   => array( 'page' => array( 42 => array( 'card_preset' => 'focus' ) ) ),
			)
		);
		self::assertSame( 'focus', Zion_Theme_Settings::resolve( $config, 'card_preset', '3d', 'page', 42 )['value'] );
		self::assertSame( 'technical', Zion_Theme_Settings::resolve( $config, 'card_preset', '3d' )['value'] );
		self::assertSame( 'editorial', Zion_Theme_Settings::resolve( $config, 'card_preset', 'software' )['value'] );
		foreach ( array_keys( Zion_Theme_Settings::card_designs() ) as $preset ) {
			self::assertSame( array( 'card_preset' => $preset ), Zion_Theme_Settings::normalize_scope( array( 'card_preset' => $preset ), Zion_Theme_Settings::fields( 'page' ), true ) );
			$config['global']['card_preset'] = $preset;
			self::assertSame( Zion_Theme_Settings::card_designs()[ $preset ][3], Zion_Theme_Settings::resolve( $config, 'card_ratio' )['value'] );
			self::assertSame( 'contain', Zion_Theme_Settings::resolve( $config, 'card_fit' )['value'] );
		}
		$config['global']['card_preset'] = 'classic';
		self::assertSame( 'cover', Zion_Theme_Settings::resolve( $config, 'card_fit' )['value'] );
		self::assertSame( array( 'card_preset' => 'classic' ), Zion_Theme_Settings::normalize_scope( array( 'card_preset' => 'classic' ), Zion_Theme_Settings::fields( 'global' ), true ) );
	}

	/**
	 * Defaults are derived and never copied into storage.
	 */
	public function test_resolves_defaults_without_persisting_them(): void {
		$config = Zion_Theme_Settings::empty_config();
		self::assertSame( array(), $config['global'] );
		foreach ( Zion_Theme_Settings::registry() as $key => $definition ) {
			self::assertSame(
				array(
					'value'  => $definition['default'],
					'source' => 'default',
				),
				Zion_Theme_Settings::resolve( $config, $key, 'software', 'page', 14 )
			);
			self::assertSame( array( $key => $definition['default'] ), Zion_Theme_Settings::normalize_scope( array( $key => $definition['default'] ), Zion_Theme_Settings::fields( 'global' ) ) );
		}
	}

	/**
	 * Explicit false is distinct from inheritance.
	 */
	public function test_resolves_item_shop_global_then_default(): void {
		$config = Zion_Theme_Settings::normalize(
			array(
				'version' => 2,
				'global'  => array( 'catalog_counts' => false ),
				'shops'   => array( 'software' => array( 'catalog_counts' => true ) ),
				'items'   => array( 'page' => array( 14 => array( 'catalog_counts' => false ) ) ),
			)
		);
		self::assertSame(
			array(
				'value'  => false,
				'source' => 'item',
			),
			Zion_Theme_Settings::resolve( $config, 'catalog_counts', 'software', 'page', 14 )
		);
		self::assertSame(
			array(
				'value'  => true,
				'source' => 'shop',
			),
			Zion_Theme_Settings::resolve( $config, 'catalog_counts', 'software', 'page', 15 )
		);
		self::assertSame(
			array(
				'value'  => false,
				'source' => 'global',
			),
			Zion_Theme_Settings::resolve( $config, 'catalog_counts', '3d' )
		);
		self::assertSame(
			array(
				'value'  => true,
				'source' => 'default',
			),
			Zion_Theme_Settings::resolve( $config, 'catalog_actions', '3d' )
		);
	}

	/**
	 * Form transport becomes canonical PHP types.
	 */
	public function test_sanitizes_valid_form_values(): void {
		self::assertSame(
			array(
				'reading_speed' => 260,
				'reading_time'  => false,
			),
			Zion_Theme_Settings::normalize_scope(
				array(
					'motion'        => '',
					'reading_time'  => '0',
					'reading_speed' => '260',
				),
				Zion_Theme_Settings::fields( 'global' ),
				true
			)
		);
	}

	/**
	 * Preserve a partial explicit card order in the submitted sequence.
	 */
	public function test_normalizes_card_order_and_preserves_the_sequence(): void {
		self::assertSame(
			array( 'card_order' => 'price,title,quick_view' ),
			Zion_Theme_Settings::normalize_scope( array( 'card_order' => 'price, title, quick_view' ), Zion_Theme_Settings::fields( 'shop' ), true )
		);
	}

	/**
	 * Reject malformed data instead of coercing it.
	 *
	 * @param array  $input Input values.
	 * @param string $scope Scope key.
	 * @param bool   $form Form input mode.
	 */
	#[DataProvider( 'invalid_values' )]
	public function test_rejects_invalid_values( array $input, string $scope, bool $form ): void {
		$this->expectException( InvalidArgumentException::class );
		Zion_Theme_Settings::normalize_scope( $input, Zion_Theme_Settings::fields( $scope ), $form );
	}

	/**
	 * Invalid field and context examples.
	 *
	 * @return array
	 */
	public static function invalid_values(): array {
		return array(
			array( array( 'cart_design' => 'unknown' ), 'global', true ),
			array( array( 'account_design' => 'unknown' ), 'global', true ),
			array( array( 'account_design' => 'focus' ), 'shop', true ),
			array( array( 'account_design' => 'focus' ), 'product', true ),
			array( array( 'checkout_design' => 'unknown' ), 'global', true ),
			array( array( 'checkout_design' => 'focus' ), 'shop', true ),
			array( array( 'checkout_design' => 'focus' ), 'page', true ),
			array( array( 'cart_design' => 'focus' ), 'shop', true ),
			array( array( 'catalog_density' => '<script>' ), 'shop', true ),
			array( array( 'product_meta' => '1' ), 'page', true ),
			array( array( 'reading_speed' => '500' ), 'global', true ),
			array( array( 'reading_speed' => '120.0' ), 'global', true ),
			array( array( 'reading_time' => 'false' ), 'global', true ),
			array( array( 'reading_time' => '1' ), 'global', false ),
			array( array( 'reading_time' => array() ), 'global', true ),
			array( array( 'card_order' => 'price,title,price' ), 'shop', true ),
			array( array( 'card_order' => 'price,unknown' ), 'shop', false ),
			array( array( 'card_ratio' => '2/3' ), 'shop', true ),
		);
	}

	/**
	 * Migrate a legacy flat export and read it again in the current format.
	 */
	public function test_migrates_version_one_and_round_trips_current_json(): void {
		$config = Zion_Theme_Settings::import( '{"version":1,"settings":{"catalog_density":"compact","catalog_actions":false}}' );
		self::assertSame( 2, $config['version'] );
		self::assertSame(
			array(
				'catalog_actions' => false,
				'catalog_density' => 'compact',
			),
			$config['global']
		);
		// phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- Standalone PHPUnit without WordPress.
		self::assertSame( $config, Zion_Theme_Settings::import( json_encode( $config, JSON_THROW_ON_ERROR ) ) );
	}

	/**
	 * Reject corrupted and unsupported exports.
	 *
	 * @param string $json Raw JSON.
	 */
	#[DataProvider( 'invalid_documents' )]
	public function test_rejects_incompatible_imports( string $json ): void {
		$this->expectException( InvalidArgumentException::class );
		Zion_Theme_Settings::import( $json );
	}

	/**
	 * Documents that must never replace settings.
	 *
	 * @return array
	 */
	public static function invalid_documents(): array {
		return array(
			array( '{broken' ),
			array( '{"version":3,"global":{},"shops":{},"items":{}}' ),
			array( '{"version":2,"global":{},"shops":{},"items":{"page":{"0":{"page_home_link":true}}}}' ),
			array( '{"version":2,"global":{},"shops":{"other":{}},"items":{}}' ),
			array( '{"version":2,"global":{"reading_time":"1"},"shops":{},"items":{}}' ),
			array( '{"version":2,"global":{},"shops":{},"items":{},"unknown":true}' ),
			array( str_repeat( ' ', 262145 ) ),
		);
	}
}
