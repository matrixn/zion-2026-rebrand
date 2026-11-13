<?php
/**
 * Native design tokens retain one source and preserve unrelated authoring data.
 *
 * @package Zion_Theme
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../themes/zion-theme/includes/class-zion-theme-design.php';

/** Tests the typed native Global Styles patch without booting WordPress. */
final class ZionThemeDesignTest extends TestCase {
	/** Read actual theme defaults. */
	private function theme(): array {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Standalone unit fixture is the actual local theme.json.
		return json_decode( file_get_contents( __DIR__ . '/../../themes/zion-theme/theme.json' ), true, 512, JSON_THROW_ON_ERROR );
	}

	/** Presets come from the real theme, not a separate palette. */
	public function test_registry_uses_theme_presets(): void {
		$theme    = $this->theme();
		$registry = Zion_Theme_Design::registry( $theme );
		foreach ( $theme['settings']['color']['palette'] as $color ) {
			self::assertSame( $color['color'], $registry[ 'color_' . $color['slug'] ]['default'] );
		}
		self::assertSame( '1320px', $registry['wide_width']['default'] );
	}

	/** A single color update preserves extra native presets and block styles. */
	public function test_native_patch_and_reset_are_scoped(): void {
		$original = array(
			'version'  => 3,
			'settings' => array(
				'color' => array(
					'palette' => array(
						array(
							'slug'  => 'custom-client',
							'name'  => 'Original',
							'color' => '#112233',
						),
					),
				),
			),
			'styles'   => array( 'blocks' => array( 'core/paragraph' => array( 'typography' => array( 'fontWeight' => '600' ) ) ) ),
		);
		$patch    = Zion_Theme_Design::patch(
			$original,
			array(
				'color_accent' => '#AABB33',
				'card_radius'  => '12px',
			),
			$this->theme()
		);
		self::assertSame( $original['styles'], $patch['styles'] );
		self::assertSame( $original['settings']['color']['palette'][0], $patch['settings']['color']['palette'][0] );
		self::assertSame( '#aabb33', $patch['settings']['color']['palette'][1]['color'] );
		self::assertSame( '12px', $patch['settings']['custom']['zion']['cardRadius'] );
		$reset = Zion_Theme_Design::patch(
			$patch,
			array(
				'color_accent' => null,
				'card_radius'  => null,
			),
			$this->theme()
		);
		self::assertSame( $original['settings']['color'], $reset['settings']['color'] );
		self::assertSame( $original['styles'], $reset['styles'] );
		self::assertNull( Zion_Theme_Design::read( $reset, Zion_Theme_Design::registry( $this->theme() )['card_radius'] ) );
	}

	/**
	 * Disallow arbitrary CSS, unexpected keys and malformed dimensions.
	 *
	 * @param array $values Invalid fields.
	 */
	#[DataProvider( 'invalid_values' )]
	public function test_invalid_values_are_rejected( array $values ): void {
		$this->expectException( InvalidArgumentException::class );
		Zion_Theme_Design::patch( array(), $values, $this->theme() );
	}

	/**
	 * Invalid import and API payloads.
	 *
	 * @return array
	 */
	public static function invalid_values(): array {
		return array(
			array( array( 'color_accent' => 'red;display:none' ) ),
			array( array( 'color_accent' => array( '#123456' ) ) ),
			array( array( 'font_body' => 'url(https://example.test/font)' ) ),
			array( array( 'unknown' => '12px' ) ),
			array( array( 'wide_width' => '12vw' ) ),
			array( array( 'card_radius' => '-1px' ) ),
			array( array( 'line_height' => '5' ) ),
			array( array( 'content_width' => '2000px' ) ),
		);
	}
}
