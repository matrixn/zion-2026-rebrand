<?php
/**
 * Settings registry, schema and precedence. Independent of WordPress I/O.
 *
 * @package Zion_Theme
 */

/**
 * Canonical representation of Control Center settings.
 */
final class Zion_Theme_Settings {
	public const OPTION           = 'zion_theme_settings';
	public const VERSION          = 2;
	public const MAX_IMPORT_BYTES = 262144;
	public const SHOPS            = array( '3d', 'software' );
	public const ITEM_TYPES       = array( 'page', 'product' );

	/**
	 * Definitions shared by storage, forms and the frontend resolver.
	 *
	 * @return array
	 */
	public static function registry(): array {
		static $fields = array(
			'motion'               => array(
				'label'       => 'Mișcare',
				'description' => 'Urmează preferința vizitatorului sau dezactivează întotdeauna tranzițiile decorative Zion.',
				'section'     => 'design',
				'type'        => 'choice',
				'default'     => 'system',
				'allowed'     => array(
					'system'  => 'Preferința sistemului',
					'reduced' => 'Mișcare redusă',
				),
				'contexts'    => array( 'global' ),
			),
			'highlight_shop'       => array(
				'label'       => 'Evidențiază vitrina activă',
				'description' => 'Evidențiază linkul vitrinei în meniul Zion, inclusiv pe produsele atribuite acelei vitrine.',
				'section'     => 'header',
				'type'        => 'bool',
				'default'     => false,
				'allowed'     => array(
					'0' => 'Nu',
					'1' => 'Da',
				),
				'contexts'    => array( 'global' ),
			),
			'back_to_top'          => array(
				'label'       => 'Link Înapoi sus',
				'description' => 'Adaugă în footer un link care readuce vizitatorul la începutul paginii.',
				'section'     => 'footer',
				'type'        => 'bool',
				'default'     => false,
				'allowed'     => array(
					'0' => 'Nu',
					'1' => 'Da',
				),
				'contexts'    => array( 'global' ),
			),
			'catalog_counts'       => array(
				'label'       => 'Numărul de rezultate',
				'description' => 'Afișează numărul de rezultate în cataloagele native și în vitrinele Zion.',
				'section'     => 'woo',
				'type'        => 'bool',
				'default'     => true,
				'allowed'     => array(
					'0' => 'Ascunde',
					'1' => 'Afișează',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
			),
			'catalog_density'      => array(
				'label'       => 'Densitatea cardurilor',
				'description' => 'Reglează spațiul din interiorul cardurilor Zion pentru catalog, vitrine și colecțiile temei.',
				'section'     => 'cards',
				'type'        => 'choice',
				'default'     => 'standard',
				'allowed'     => array(
					'standard' => 'Standard',
					'compact'  => 'Compact',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
			),
			'catalog_actions'      => array(
				'label'       => 'Acțiuni rapide pe carduri',
				'description' => 'Controlează butonul WooCommerce de pe card. Formularul din pagina produsului rămâne disponibil.',
				'section'     => 'cards',
				'type'        => 'bool',
				'default'     => true,
				'allowed'     => array(
					'0' => 'Ascunde',
					'1' => 'Afișează',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
			),
			'product_meta'         => array(
				'label'       => 'Metadate vizibile',
				'description' => 'Controlează zona cu SKU și categorii din șablonul de produs Zion.',
				'section'     => 'product',
				'type'        => 'bool',
				'default'     => true,
				'allowed'     => array(
					'0' => 'Ascunde',
					'1' => 'Afișează',
				),
				'contexts'    => array( 'global', 'shop', 'product' ),
			),
			'product_related'      => array(
				'label'       => 'Produse asociate',
				'description' => 'Controlează afișarea colecției native WooCommerce de produse asociate din șablonul Zion.',
				'section'     => 'product',
				'type'        => 'bool',
				'default'     => true,
				'allowed'     => array(
					'0' => 'Ascunde',
					'1' => 'Afișează',
				),
				'contexts'    => array( 'global', 'shop', 'product' ),
			),
			'reading_time'         => array(
				'label'       => 'Timp estimat de citire',
				'description' => 'Adaugă estimarea lângă data publicării pe pagina unui articol.',
				'section'     => 'blog',
				'type'        => 'bool',
				'default'     => false,
				'allowed'     => array(
					'0' => 'Ascunde',
					'1' => 'Afișează',
				),
				'contexts'    => array( 'global' ),
			),
			'reading_speed'        => array(
				'label'       => 'Cuvinte pe minut',
				'description' => 'Viteza folosită la calculul timpului estimat de citire.',
				'section'     => 'blog',
				'type'        => 'int',
				'default'     => 220,
				'allowed'     => array(
					'min' => 100,
					'max' => 400,
				),
				'contexts'    => array( 'global' ),
				'requires'    => 'reading_time',
			),
			'page_home_link'       => array(
				'label'       => 'Link către Acasă',
				'description' => 'Adaugă un link deasupra titlului în șablonul paginilor standard. Se aplică și paginilor vitrinelor.',
				'section'     => 'pages',
				'type'        => 'bool',
				'default'     => false,
				'allowed'     => array(
					'0' => 'Ascunde',
					'1' => 'Afișează',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
			),
			'disable_emoji_assets' => array(
				'label'       => 'Oprește compatibilitatea emoji',
				'description' => 'Elimină scriptul și stilurile WordPress pentru compatibilitatea emoji din paginile publice. Caracterele emoji pot fi afișate în continuare de browser.',
				'section'     => 'performance',
				'type'        => 'bool',
				'default'     => false,
				'allowed'     => array(
					'0' => 'Păstrează',
					'1' => 'Oprește',
				),
				'contexts'    => array( 'global' ),
			),
		);
		return $fields;
	}

	/**
	 * Empty storage shape. Defaults are resolved from the registry.
	 *
	 * @return array
	 */
	public static function empty_config(): array {
		return array(
			'version' => self::VERSION,
			'global'  => array(),
			'shops'   => array(),
			'items'   => array(),
		);
	}

	/**
	 * Normalize one scope using the registry. An empty form value inherits.
	 *
	 * @param array $values Scope values.
	 * @param array $fields Permitted field definitions.
	 * @param bool  $form Whether values come from HTML inputs.
	 * @return array
	 * @throws InvalidArgumentException If a key or value is invalid.
	 */
	public static function normalize_scope( array $values, array $fields, bool $form = false ): array {
		$result = array();
		foreach ( $values as $key => $value ) {
			if ( ! isset( $fields[ $key ] ) ) {
				throw new InvalidArgumentException( 'Fișierul sau formularul conține o setare nepermisă în acest context.' );
			}
			if ( $form && '' === $value ) {
				continue;
			}

			$field = $fields[ $key ];
			switch ( $field['type'] ) {
				case 'bool':
					if ( $form && in_array( $value, array( '0', '1' ), true ) ) {
						$value = '1' === $value;
					}
					$valid = is_bool( $value ) && array_key_exists( (int) $value, $field['allowed'] );
					break;
				case 'int':
					if ( $form && is_string( $value ) && ctype_digit( $value ) ) {
						$value = (int) $value;
					}
					$valid = is_int( $value ) && $value >= $field['allowed']['min'] && $value <= $field['allowed']['max'];
					break;
				default:
					$valid = is_string( $value ) && array_key_exists( $value, $field['allowed'] );
			}
			if ( ! $valid ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The controller escapes the message at the point of display.
				throw new InvalidArgumentException( 'Valoare invalidă pentru ' . $field['label'] . '.' );
			}
			$result[ $key ] = $value;
		}

		ksort( $result );
		return $result;
	}

	/**
	 * Select definitions for a scope and optionally a section.
	 *
	 * @param string $context global, shop, page or product.
	 * @param string $section Optional section key.
	 * @return array
	 */
	public static function fields( string $context, string $section = '' ): array {
		return array_filter(
			self::registry(),
			static fn ( array $field ): bool => in_array( $context, $field['contexts'], true ) && ( '' === $section || $field['section'] === $section )
		);
	}

	/**
	 * Migrate and validate a persisted settings document.
	 *
	 * @param mixed $input Raw document.
	 * @return array
	 * @throws InvalidArgumentException If the schema is incompatible.
	 */
	public static function normalize( mixed $input ): array {
		if ( is_array( $input ) && 1 === ( $input['version'] ?? null ) && 2 === count( $input ) && isset( $input['settings'] ) ) {
			$input = array_replace( self::empty_config(), array( 'global' => $input['settings'] ) );
		}

		$output = self::empty_config();
		if ( ! is_array( $input ) || self::VERSION !== ( $input['version'] ?? null ) || array_diff_key( $input, $output ) ) {
			throw new InvalidArgumentException( 'Schema configurației este necunoscută sau incompatibilă.' );
		}
		foreach ( array( 'global', 'shops', 'items' ) as $scope ) {
			if ( ! isset( $input[ $scope ] ) || ! is_array( $input[ $scope ] ) ) {
				throw new InvalidArgumentException( 'Configurația trebuie să includă secțiunile global, shops și items.' );
			}
		}

		$output['global'] = self::normalize_scope( $input['global'], self::fields( 'global' ) );
		foreach ( $input['shops'] as $shop => $values ) {
			if ( ! in_array( $shop, self::SHOPS, true ) || ! is_array( $values ) ) {
				throw new InvalidArgumentException( 'Configurația conține o vitrină necunoscută sau invalidă.' );
			}
			$values = self::normalize_scope( $values, self::fields( 'shop' ) );
			if ( $values ) {
				$output['shops'][ $shop ] = $values;
			}
		}
		foreach ( $input['items'] as $type => $items ) {
			if ( ! in_array( $type, self::ITEM_TYPES, true ) || ! is_array( $items ) ) {
				throw new InvalidArgumentException( 'Tip de pagină sau produs incompatibil.' );
			}
			foreach ( $items as $id => $values ) {
				if ( ! is_int( $id ) || $id < 1 || ! is_array( $values ) ) {
					throw new InvalidArgumentException( 'Configurația conține un ID invalid.' );
				}
				$values = self::normalize_scope( $values, self::fields( $type ) );
				if ( $values ) {
					$output['items'][ $type ][ $id ] = $values;
				}
			}
		}

		ksort( $output['shops'] );
		ksort( $output['items'] );
		foreach ( array_keys( $output['items'] ) as $type ) {
			ksort( $output['items'][ $type ] );
		}
		return $output;
	}

	/**
	 * Resolve an effective setting, including the provenance of the value.
	 *
	 * @param array  $config Normalized configuration.
	 * @param string $key Registry identifier.
	 * @param string $shop Optional storefront.
	 * @param string $type Optional item type.
	 * @param int    $id Optional item ID.
	 * @return array
	 */
	public static function resolve( array $config, string $key, string $shop = '', string $type = '', int $id = 0 ): array {
		foreach (
			array(
				'item'   => $config['items'][ $type ][ $id ] ?? array(),
				'shop'   => $config['shops'][ $shop ] ?? array(),
				'global' => $config['global'],
			) as $source => $values
		) {
			if ( isset( $values[ $key ] ) ) {
				return array(
					'value'  => $values[ $key ],
					'source' => $source,
				);
			}
		}
		return array(
			'value'  => self::registry()[ $key ]['default'],
			'source' => 'default',
		);
	}

	/**
	 * Decode an import before any settings are written.
	 *
	 * @param string $json Exported document.
	 * @return array
	 * @throws InvalidArgumentException For invalid or oversized input.
	 */
	public static function import( string $json ): array {
		if ( strlen( $json ) > self::MAX_IMPORT_BYTES ) {
			throw new InvalidArgumentException( 'Fișierul JSON depășește limita de 256 KiB.' );
		}
		try {
			return self::normalize( json_decode( $json, true, 16, JSON_THROW_ON_ERROR ) );
		} catch ( JsonException $error ) {
			throw new InvalidArgumentException( 'JSON invalid. Folosește un export Zion Control Center.' );
		}
	}
}
