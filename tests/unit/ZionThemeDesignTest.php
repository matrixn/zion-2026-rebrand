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

	/** Page identities inherit globals without copying values into each profile. */
	public function test_context_inheritance_and_independent_reset(): void {
		$theme  = $this->theme();
		$global = Zion_Theme_Design::patch_scope(
			array(),
			array(
				'color_accent'    => '#bbcc33',
				'font_body'       => 'Georgia, serif',
				'spacing_regular' => '24px',
			),
			$theme
		);
		$home   = Zion_Theme_Design::patch_scope(
			$global,
			array(
				'color_accent'    => '#ffcc33',
				'spacing_section' => '48px',
			),
			$theme,
			'home'
		);
		$both   = Zion_Theme_Design::patch_scope( $home, array( 'color_accent' => '#33bbff' ), $theme, '3d' );
		$all    = Zion_Theme_Design::patch_scope( $both, array( 'font_body' => 'Arial, Helvetica, sans-serif' ), $theme, 'software' );
		self::assertSame( '#bbcc33', Zion_Theme_Design::effective( $all, $theme )['color_accent'] );
		self::assertSame( '#ffcc33', Zion_Theme_Design::effective( $all, $theme, 'home' )['color_accent'] );
		self::assertSame( '#33bbff', Zion_Theme_Design::effective( $all, $theme, '3d' )['color_accent'] );
		self::assertSame( '#bbcc33', Zion_Theme_Design::effective( $all, $theme, 'software' )['color_accent'] );
		self::assertSame( 'Georgia, serif', Zion_Theme_Design::effective( $all, $theme, 'home' )['font_body'] );
		self::assertSame( '24px', Zion_Theme_Design::effective( $all, $theme, '3d' )['spacing_regular'] );
		self::assertSame( array( 'font_body' => 'Arial, Helvetica, sans-serif' ), Zion_Theme_Design::overrides( $all, $theme, 'software' ) );
		$reset = Zion_Theme_Design::patch_scope(
			$all,
			array(
				'color_accent'    => null,
				'spacing_section' => null,
			),
			$theme,
			'home'
		);
		self::assertSame( array(), Zion_Theme_Design::overrides( $reset, $theme, 'home' ) );
		self::assertSame( Zion_Theme_Design::effective( $all, $theme ), Zion_Theme_Design::effective( $reset, $theme, 'home' ) );
		self::assertSame( $all['settings']['custom']['zion']['designContexts']['3d'], $reset['settings']['custom']['zion']['designContexts']['3d'] );
		self::assertSame( $global['settings']['color'], $reset['settings']['color'] );
	}

	/** Global edits and resets preserve explicit page choices. */
	public function test_global_reset_preserves_context_overrides(): void {
		$theme    = $this->theme();
		$document = Zion_Theme_Design::patch_scope( array(), array( 'color_accent' => '#44bbff' ), $theme, 'software' );
		$document = Zion_Theme_Design::patch_scope( $document, array( 'color_accent' => '#ffe033' ), $theme );
		self::assertSame( '#ffe033', Zion_Theme_Design::effective( $document, $theme, '3d' )['color_accent'] );
		$reset = Zion_Theme_Design::patch_scope( $document, array( 'color_accent' => null ), $theme );
		self::assertSame( '#d9fb4f', Zion_Theme_Design::effective( $reset, $theme, 'home' )['color_accent'] );
		self::assertSame( '#44bbff', Zion_Theme_Design::effective( $reset, $theme, 'software' )['color_accent'] );
	}

	/** Width validation uses inherited values and guards affected profiles. */
	public function test_context_dimensions_resolve_inheritance(): void {
		$theme    = $this->theme();
		$document = Zion_Theme_Design::patch_scope( array(), array( 'wide_width' => '1800px' ), $theme );
		$document = Zion_Theme_Design::patch_scope( $document, array( 'content_width' => '1600px' ), $theme, 'home' );
		self::assertSame( '1800px', Zion_Theme_Design::effective( $document, $theme, 'home' )['wide_width'] );
		$this->expectException( InvalidArgumentException::class );
		Zion_Theme_Design::patch_scope( $document, array( 'wide_width' => '1200px' ), $theme );
	}

	/** Invalid page patches cannot create CSS or change other profiles. */
	public function test_context_patch_rejects_invalid_css_atomically(): void {
		$theme    = $this->theme();
		$document = Zion_Theme_Design::patch_scope( array(), array( 'color_accent' => '#33bbff' ), $theme, '3d' );
		$before   = $document;
		try {
			Zion_Theme_Design::patch_scope(
				$document,
				array(
					'color_accent'    => '#ffee33',
					'spacing_regular' => 'url(unsafe)',
				),
				$theme,
				'home'
			);
			self::fail( 'Invalid CSS must fail.' );
		} catch ( InvalidArgumentException $error ) {
			self::assertSame( $before, $document );
		}
		$document['settings']['custom']['zion']['designContexts']['home']['color_accent'] = 'red;display:none';
		self::assertSame( '#d9fb4f', Zion_Theme_Design::effective( $document, $theme, 'home' )['color_accent'] );
	}

	/** There is no implicit fallback for an unknown authoring target. */
	public function test_unknown_context_is_rejected(): void {
		$this->expectException( InvalidArgumentException::class );
		Zion_Theme_Design::patch_scope( array(), array( 'color_accent' => '#ffffff' ), $this->theme(), 'product' );
	}
}
