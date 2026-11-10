<?php
/**
 * Catalog option tests.
 *
 * @package Zion_Core
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../plugins/zion-core/includes/catalog.php';

/**
 * Validate shortcode options without loading WordPress.
 */
final class CatalogOptionsTest extends TestCase {
	/**
	 * Verify the normalization contract.
	 *
	 * @param array|string $input Shortcode input.
	 * @param array        $expected Expected options.
	 */
	#[DataProvider( 'cases' )]
	public function test_normalizes_catalog_options( array|string $input, array $expected ): void {
		self::assertSame( $expected, zion_core_catalog_options( $input ) );
	}

	/**
	 * Provide representative shortcode inputs.
	 *
	 * @return array<string,array>
	 */
	public static function cases(): array {
		return array(
			'empty'   => array(
				'',
				array(
					'shop'    => '',
					'limit'   => 12,
					'columns' => 3,
				),
			),
			'3d'      => array(
				array( 'shop' => '3d' ),
				array(
					'shop'    => '3d',
					'limit'   => 12,
					'columns' => 3,
				),
			),
			'bounds'  => array(
				array(
					'shop'    => 'software',
					'limit'   => '100',
					'columns' => '0',
				),
				array(
					'shop'    => 'software',
					'limit'   => 48,
					'columns' => 1,
				),
			),
			'invalid' => array(
				array(
					'shop'    => 'all',
					'limit'   => 'many',
					'columns' => array(),
				),
				array(
					'shop'    => '',
					'limit'   => 12,
					'columns' => 3,
				),
			),
		);
	}
}
