<?php
/**
 * Native visual design schema.
 *
 * @package Zion_Theme
 */

/** Typed controls for the existing Global Styles document. */
final class Zion_Theme_Design {
	/**
	 * Derive preset controls from the actual theme.json.
	 *
	 * @param array $theme Theme defaults.
	 * @return array
	 */
	public static function registry( array $theme ): array {
		$fields = array();
		foreach ( array(
			'color'      => array( 'palette', 'color', 'Culori', 'color' ),
			'typography' => array( 'fontSizes', 'size', 'Tipografie', 'size' ),
			'spacing'    => array( 'spacingSizes', 'size', 'Spațiere', 'space' ),
		) as $group => $definition ) {
			foreach ( $theme['settings'][ $group ][ $definition[0] ] ?? array() as $preset ) {
				$fields[ $group . '_' . $preset['slug'] ] = array(
					'label'   => $preset['name'],
					'group'   => $definition[2],
					'type'    => $definition[3],
					'path'    => array( 'settings', $group, $definition[0] ),
					'slug'    => $preset['slug'],
					'prop'    => $definition[1],
					'preset'  => $preset,
					'default' => $preset[ $definition[1] ],
				);
			}
		}
		foreach ( $theme['settings']['typography']['fontFamilies'] ?? array() as $font ) {
			$fields[ 'font_' . $font['slug'] ] = array(
				'label'   => 'Font · ' . $font['name'],
				'group'   => 'Tipografie',
				'type'    => 'font',
				'path'    => array( 'settings', 'typography', 'fontFamilies' ),
				'slug'    => $font['slug'],
				'prop'    => 'fontFamily',
				'preset'  => $font,
				'default' => $font['fontFamily'],
				'choices' => array(
					$font['fontFamily']            => 'Implicit · ' . $font['name'],
					'Arial, Helvetica, sans-serif' => 'Arial',
					'Verdana, Geneva, sans-serif'  => 'Verdana',
					'Georgia, serif'               => 'Georgia',
					'"Trebuchet MS", sans-serif'   => 'Trebuchet',
					'"Courier New", monospace'     => 'Courier New',
				),
			);
		}
		foreach ( array(
			'content_width' => array( 'Lățime conținut', 'Containere', 'width', array( 'settings', 'layout', 'contentSize' ), $theme['settings']['layout']['contentSize'] ),
			'wide_width'    => array( 'Lățime extinsă', 'Containere', 'width', array( 'settings', 'layout', 'wideSize' ), $theme['settings']['layout']['wideSize'] ),
			'line_height'   => array( 'Înălțime rând text', 'Tipografie', 'line', array( 'styles', 'typography', 'lineHeight' ), $theme['styles']['typography']['lineHeight'] ),
			'button_radius' => array( 'Rotunjire butoane', 'Componente', 'radius', array( 'styles', 'elements', 'button', 'border', 'radius' ), $theme['styles']['elements']['button']['border']['radius'] ),
			'card_radius'   => array( 'Rotunjire carduri Zion', 'Componente', 'radius', array( 'settings', 'custom', 'zion', 'cardRadius' ), '' ),
		) as $key => $item ) {
			$fields[ $key ] = array(
				'label'   => $item[0],
				'group'   => $item[1],
				'type'    => $item[2],
				'path'    => $item[3],
				'default' => $item[4],
			);
		}
		foreach ( $fields as $key => &$field ) {
			$field['css'] = match ( true ) {
				isset( $field['slug'] ) => '--wp--preset--' . match ( $field['type'] ) {
					'color' => 'color',
					'font' => 'font-family',
					'space' => 'spacing',
					default => 'font-size',
				} . '--' . $field['slug'],
				'line_height' === $key => 'line-height',
				'card_radius' === $key => '--wp--custom--zion--card-radius',
				'button_radius' === $key => '--zion-button-radius',
				default => '--wp--style--global--' . ( 'wide_width' === $key ? 'wide' : 'content' ) . '-size',
			};
			$field['alias'] = match ( $key ) {
				'spacing_section' => '--zion-design-section-spacing',
				'spacing_grid' => '--zion-design-grid-spacing',
				'typography_display' => '--zion-design-display-size',
				'typography_section-title' => '--zion-design-heading-size',
				default => '',
			};
		}
		unset( $field );
		return $fields;
	}

	/**
	 * Supported page identities; the values override existing native presets.
	 *
	 * @return string[]
	 */
	public static function scopes(): array {
		return array( 'global', 'home', '3d', 'software' );
	}

	/**
	 * Explicit token values from one canonical Global Styles document.
	 *
	 * @param array  $document Native styles.
	 * @param array  $theme Theme defaults.
	 * @param string $scope Page identity.
	 * @return array
	 * @throws InvalidArgumentException Unknown scope.
	 */
	public static function overrides( array $document, array $theme, string $scope = 'global' ): array {
		if ( ! in_array( $scope, self::scopes(), true ) ) {
			throw new InvalidArgumentException( 'Destinație de design necunoscută.' );
		}
		$values = array();
		foreach ( self::registry( $theme ) as $key => $field ) {
			$value = 'global' === $scope ? self::read( $document, $field ) : ( $document['settings']['custom']['zion']['designContexts'][ $scope ][ $key ] ?? null );
			if ( null !== $value ) {
				if ( 'global' !== $scope ) {
					try {
						$value = self::validate( $value, $field );
					} catch ( InvalidArgumentException $error ) {
						continue;
					}
				}
				$values[ $key ] = $value;
			}
		}
		return $values;
	}

	/**
	 * Resolve page overrides, global values and theme.json defaults.
	 *
	 * @param array  $document Native styles.
	 * @param array  $theme Defaults.
	 * @param string $scope Page identity.
	 * @return array
	 */
	public static function effective( array $document, array $theme, string $scope = 'global' ): array {
		$defaults = array_map( static fn( $field ) => $field['default'], self::registry( $theme ) );
		return array_replace( $defaults, self::overrides( $document, $theme ), self::overrides( $document, $theme, $scope ) );
	}

	/**
	 * Validate a page patch atomically and preserve all other contexts.
	 *
	 * @param array  $document Canonical native document.
	 * @param array  $values Typed changes; null removes an override.
	 * @param array  $theme Defaults.
	 * @param string $scope Page identity.
	 * @return array
	 * @throws InvalidArgumentException Invalid scope, key, value or dimensions.
	 */
	public static function patch_scope( array $document, array $values, array $theme, string $scope = 'global' ): array {
		self::overrides( $document, $theme, $scope );
		if ( 'global' === $scope ) {
			$document = self::patch( $document, $values, $theme );
		} else {
			$registry = self::registry( $theme );
			$target   = $document['settings']['custom']['zion']['designContexts'][ $scope ] ?? array();
			if ( ! is_array( $target ) ) {
				throw new InvalidArgumentException( 'Configurație de destinație invalidă.' );
			}
			foreach ( $values as $key => $value ) {
				if ( ! isset( $registry[ $key ] ) ) {
					throw new InvalidArgumentException( 'Token de design necunoscut.' );
				}
				if ( null === $value ) {
					unset( $target[ $key ] );
				} else {
					$target[ $key ] = self::validate( $value, $registry[ $key ] );
				}
			}
			if ( $target ) {
				$document['settings']['custom']['zion']['designContexts'][ $scope ] = $target;
			} else {
				unset( $document['settings']['custom']['zion']['designContexts'][ $scope ] );
			}
			$document['version']                     = 3;
			$document['isGlobalStylesUserThemeJSON'] = true;
		}
		if ( array_key_exists( 'content_width', $values ) || array_key_exists( 'wide_width', $values ) ) {
			foreach ( 'global' === $scope ? self::scopes() : array( $scope ) as $check ) {
				$effective = self::effective( $document, $theme, $check );
				if ( (float) $effective['content_width'] > (float) $effective['wide_width'] ) {
					throw new InvalidArgumentException( 'Lățimea extinsă trebuie să fie cel puțin egală cu lățimea conținutului în fiecare destinație.' );
				}
			}
		}
		return $document;
	}

	/**
	 * Get a managed value without modifying the native document.
	 *
	 * @param array $document Native data.
	 * @param array $field Registry definition.
	 * @return mixed Null means inherit.
	 */
	public static function read( array $document, array $field ): mixed {
		$value = $document;
		foreach ( $field['path'] as $part ) {
			$value = $value[ $part ] ?? null;
		}
		if ( isset( $field['slug'] ) ) {
			foreach ( is_array( $value ) ? $value : array() as $preset ) {
				if ( ( $preset['slug'] ?? '' ) === $field['slug'] ) {
					return $preset[ $field['prop'] ] ?? null;
				}
			}
			return null;
		}
		return $value;
	}

	/**
	 * Apply only allowlisted values, preserving unrelated Global Styles data.
	 *
	 * @param array $document Native user data.
	 * @param array $values Explicit values; null resets just that token.
	 * @param array $theme Theme defaults.
	 * @return array
	 * @throws InvalidArgumentException Invalid key or value.
	 */
	public static function patch( array $document, array $values, array $theme ): array {
		$registry = self::registry( $theme );
		foreach ( $values as $key => $value ) {
			if ( ! isset( $registry[ $key ] ) ) {
				throw new InvalidArgumentException( 'Token de design necunoscut.' );
			}
			$field = $registry[ $key ];
			if ( null !== $value ) {
				$value = self::validate( $value, $field );
			}
			$target =& $document;
			$path   = $field['path'];
			$last   = array_pop( $path );
			foreach ( $path as $part ) {
				if ( ! isset( $target[ $part ] ) || ! is_array( $target[ $part ] ) ) {
					$target[ $part ] = array();
				}
				$target =& $target[ $part ];
			}
			if ( isset( $field['slug'] ) ) {
				$presets = $target[ $last ] ?? array();
				$old     = $field['preset'];
				foreach ( $presets as $preset ) {
					if ( ( $preset['slug'] ?? '' ) === $field['slug'] ) {
						$old = $preset;
					}
				}
				$presets = array_values( array_filter( $presets, fn( $preset ) => ( $preset['slug'] ?? '' ) !== $field['slug'] ) );
				if ( null !== $value ) {
					$old[ $field['prop'] ] = $value;
					$presets[]             = $old;
				}
				if ( $presets ) {
					$target[ $last ] = $presets;
				} else {
					unset( $target[ $last ] );
				}
			} elseif ( null === $value ) {
				unset( $target[ $last ] );
			} else {
				$target[ $last ] = $value;
			}
			unset( $target );
		}
		foreach ( array( 'content_width', 'wide_width' ) as $key ) {
			$widths[ $key ] = (float) ( self::read( $document, $registry[ $key ] ) ?? $registry[ $key ]['default'] );
		}
		if ( ( array_key_exists( 'content_width', $values ) || array_key_exists( 'wide_width', $values ) ) && $widths['content_width'] > $widths['wide_width'] ) {
			throw new InvalidArgumentException( 'Lățimea extinsă trebuie să fie cel puțin egală cu lățimea conținutului.' );
		}
		$document['version']                     = 3;
		$document['isGlobalStylesUserThemeJSON'] = true;
		return $document;
	}

	/**
	 * Validate CSS tokens before they reach the native REST controller.
	 *
	 * @param mixed $value Candidate.
	 * @param array $field Definition.
	 * @return string
	 * @throws InvalidArgumentException Invalid CSS.
	 */
	private static function validate( mixed $value, array $field ): string {
		if ( ! is_string( $value ) ) {
			throw new InvalidArgumentException( 'Valoarea de design trebuie să fie text.' );
		}
		if ( 'color' === $field['type'] && preg_match( '/^#[0-9a-f]{6}$/iD', $value ) ) {
			return strtolower( $value );
		}
		if ( 'font' === $field['type'] && isset( $field['choices'][ $value ] ) ) {
			return $value;
		}
		if ( 'line' === $field['type'] && is_numeric( $value ) && (float) $value >= 1 && (float) $value <= 2.5 ) {
			return (string) (float) $value;
		}
		if ( in_array( $field['type'], array( 'size', 'space', 'width', 'radius' ), true ) && preg_match( '/^(\d+(?:\.\d{1,3})?)(px|rem)$/D', $value, $parts ) ) {
			$pixels = (float) $parts[1] * ( 'rem' === $parts[2] ? 16 : 1 );
			$bounds = match ( $field['type'] ) {
				'width' => array( 240, 2400 ),
				'radius' => array( 0, 48 ),
				'size' => array( 8, 200 ),
				default => array( 0, 240 ),
			};
			if ( $pixels >= $bounds[0] && $pixels <= $bounds[1] && ( 'width' !== $field['type'] || 'px' === $parts[2] ) ) {
				return $value;
			}
		}
		throw new InvalidArgumentException( 'Valoare invalidă pentru tokenul de design.' );
	}
}
