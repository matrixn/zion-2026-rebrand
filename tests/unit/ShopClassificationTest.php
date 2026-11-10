<?php
/**
 * Classification operations independent of the WordPress database.
 *
 * @package Zion_Core
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../plugins/zion-core/includes/classification.php';

/**
 * Test exact term relationship changes.
 */
final class ShopClassificationTest extends TestCase {
	/**
	 * Verify a single term transition.
	 *
	 * @param array  $before Current term IDs.
	 * @param array  $selected Requested term IDs.
	 * @param string $operation Operation key.
	 * @param array  $expected Resulting term IDs.
	 */
	#[DataProvider( 'operations' )]
	public function test_plans_canonical_relationships( array $before, array $selected, string $operation, array $expected ): void {
		self::assertSame( $expected, zion_core_target_shop_terms( $before, $selected, array( 11, 12 ), $operation ) );
	}

	/**
	 * Provide add, remove, replace and legacy relationship cases.
	 *
	 * @return array
	 */
	public static function operations(): array {
		return array(
			'add-second'   => array( array( 11 ), array( 12 ), 'add', array( 11, 12 ) ),
			'add-existing' => array( array( 11 ), array( 11 ), 'add', array( 11 ) ),
			'remove-one'   => array( array( 11, 12 ), array( 11 ), 'remove', array( 12 ) ),
			'replace'      => array( array( 11 ), array( 12 ), 'replace', array( 12 ) ),
			'unclassify'   => array( array( 12, 11, 15 ), array(), 'replace', array( 15 ) ),
			'legacy'       => array( array( 15, 11 ), array( 12 ), 'replace', array( 12, 15 ) ),
		);
	}
}
