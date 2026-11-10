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
	public const ITEM_TYPES       = array( 'page', 'product', 'post' );

	/**
	 * Definitions shared by storage, forms and the frontend resolver.
	 *
	 * @return array
	 */
	public static function registry(): array {
		static $fields   = array(
			'motion'                  => array(
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
			'highlight_shop'          => array(
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
			'back_to_top'             => array(
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
			'catalog_counts'          => array(
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
			'catalog_category_filter' => array(
				'label'       => 'Filtru de categorie',
				'description' => 'Afișează filtrul după categoriile WooCommerce pe pagina dedicată a vitrinei Zion.',
				'section'     => 'woo',
				'type'        => 'bool',
				'default'     => true,
				'allowed'     => array(
					'0' => 'Ascunde',
					'1' => 'Afișează',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
				'requires'    => 'catalog_filters',
			),
			'catalog_layout'          => array(
				'label'       => 'Layout vitrină',
				'description' => 'Alege grilă sau listă pentru cataloagele din shortcode-ul Zion.',
				'section'     => 'woo',
				'type'        => 'choice',
				'default'     => 'grid',
				'allowed'     => array(
					'grid' => 'Grilă',
					'list' => 'Listă',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
			),
			'catalog_columns_desktop' => array(
				'label'       => 'Coloane desktop',
				'description' => 'Numărul de coloane în grilă la peste 1024 px.',
				'section'     => 'woo',
				'type'        => 'int',
				'default'     => 3,
				'allowed'     => array(
					'min' => 2,
					'max' => 5,
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
			),
			'catalog_columns_tablet'  => array(
				'label'       => 'Coloane tabletă',
				'description' => 'Numărul de coloane între 601 și 1024 px.',
				'section'     => 'woo',
				'type'        => 'int',
				'default'     => 2,
				'allowed'     => array(
					'min' => 1,
					'max' => 4,
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
			),
			'catalog_columns_mobile'  => array(
				'label'       => 'Coloane mobil',
				'description' => 'Numărul de coloane la cel mult 600 px.',
				'section'     => 'woo',
				'type'        => 'int',
				'default'     => 1,
				'allowed'     => array(
					'min' => 1,
					'max' => 2,
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
			),
			'catalog_page_size'       => array(
				'label'       => 'Produse pe pagină',
				'description' => 'Limita aplicată în colecția nativă WooCommerce.',
				'section'     => 'woo',
				'type'        => 'int',
				'default'     => 12,
				'allowed'     => array(
					'min' => 1,
					'max' => 48,
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
			),
			'catalog_load_more'       => array(
				'label'       => 'Încarcă mai multe',
				'description' => 'Încarcă următoarele produse fără reîncărcarea paginii. Paginarea numerică rămâne disponibilă.',
				'section'     => 'woo',
				'type'        => 'bool',
				'default'     => true,
				'allowed'     => array(
					'0' => 'Ascunde',
					'1' => 'Afișează',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
			),
			'catalog_load_mode'       => array(
				'label'       => 'Mod de încărcare',
				'description' => 'Încarcă automat când vizitatorul ajunge la finalul listei sau doar la apăsarea butonului.',
				'section'     => 'woo',
				'type'        => 'choice',
				'default'     => 'auto',
				'allowed'     => array(
					'auto'   => 'Automat la derulare',
					'manual' => 'Buton manual',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
			),
			'catalog_gap'             => array(
				'label'       => 'Spațiere între carduri',
				'description' => 'Alege spațiul din grilă sau listă.',
				'section'     => 'woo',
				'type'        => 'choice',
				'default'     => 'regular',
				'allowed'     => array(
					'compact' => 'Compactă',
					'regular' => 'Standard',
					'wide'    => 'Generosă',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
			),
			'catalog_width'           => array(
				'label'       => 'Lățimea catalogului',
				'description' => 'Catalogul poate folosi lățimea conținutului sau lățimea extinsă din temă.',
				'section'     => 'woo',
				'type'        => 'choice',
				'default'     => 'content',
				'allowed'     => array(
					'content' => 'Conținut',
					'wide'    => 'Extinsă',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
			),
			'catalog_sort'            => array(
				'label'       => 'Sortare implicită',
				'description' => 'Vizitatorul poate schimba ordinea. Recomandate afișează întâi produsele evidențiate în WooCommerce.',
				'section'     => 'woo',
				'type'        => 'choice',
				'default'     => 'menu_order',
				'allowed'     => array(
					'featured'   => 'Recomandate',
					'date'       => 'Cele mai noi',
					'price'      => 'Preț crescător',
					'price-desc' => 'Preț descrescător',
					'popularity' => 'Popularitate',
					'rating'     => 'Rating',
					'menu_order' => 'Ordine manuală',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
			),
			'catalog_filters'         => array(
				'label'       => 'Afișează filtrele',
				'description' => 'Controlează panoul de filtrare al vitrinei.',
				'section'     => 'woo',
				'type'        => 'bool',
				'default'     => true,
				'allowed'     => array(
					'0' => 'Ascunde',
					'1' => 'Afișează',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
			),
			'catalog_filter_layout'   => array(
				'label'       => 'Poziția filtrelor',
				'description' => 'Bara laterală se deschide ca drawer pe mobil.',
				'section'     => 'woo',
				'type'        => 'choice',
				'default'     => 'sidebar',
				'allowed'     => array(
					'sidebar' => 'Bară laterală',
					'drawer'  => 'Drawer',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
				'requires'    => 'catalog_filters',
			),
			'catalog_price_filter'    => array(
				'label'       => 'Filtru de preț',
				'description' => 'Afișează intervalul de preț calculat de WooCommerce.',
				'section'     => 'woo',
				'type'        => 'bool',
				'default'     => true,
				'allowed'     => array(
					'0' => 'Ascunde',
					'1' => 'Afișează',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
				'requires'    => 'catalog_filters',
			),
			'catalog_stock_filter'    => array(
				'label'       => 'Filtru de disponibilitate',
				'description' => 'Afișează stările de stoc disponibile.',
				'section'     => 'woo',
				'type'        => 'bool',
				'default'     => true,
				'allowed'     => array(
					'0' => 'Ascunde',
					'1' => 'Afișează',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
				'requires'    => 'catalog_filters',
			),
			'catalog_sale_filter'     => array(
				'label'       => 'Filtru de reduceri',
				'description' => 'Adaugă comutatorul pentru produse cu reducere activă.',
				'section'     => 'woo',
				'type'        => 'bool',
				'default'     => true,
				'allowed'     => array(
					'0' => 'Ascunde',
					'1' => 'Afișează',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
				'requires'    => 'catalog_filters',
			),
			'catalog_brand_filter'    => array(
				'label'       => 'Filtru de brand',
				'description' => 'Afișează filtrul dacă taxonomia Brand există și WooCommerce o poate folosi.',
				'section'     => 'woo',
				'type'        => 'bool',
				'default'     => true,
				'allowed'     => array(
					'0' => 'Ascunde',
					'1' => 'Afișează',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
				'requires'    => 'catalog_filters',
			),
			'catalog_attributes'      => array(
				'label'       => 'Atribute filtrabile',
				'description' => 'Folosește auto, none sau maximum șase sluguri de atribute globale WooCommerce, separate de virgulă, de exemplu material,format.',
				'section'     => 'woo',
				'type'        => 'slugs',
				'default'     => 'auto',
				'allowed'     => array( 'max' => 6 ),
				'contexts'    => array( 'global', 'shop', 'page' ),
				'requires'    => 'catalog_filters',
			),
			'catalog_active_filters'  => array(
				'label'       => 'Filtre active',
				'description' => 'Afișează selecțiile active ca etichete care pot fi eliminate.',
				'section'     => 'woo',
				'type'        => 'bool',
				'default'     => true,
				'allowed'     => array(
					'0' => 'Ascunde',
					'1' => 'Afișează',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
				'requires'    => 'catalog_filters',
			),
			'catalog_reset_filters'   => array(
				'label'       => 'Resetare filtre',
				'description' => 'Afișează un link care golește filtrele și păstrează sortarea.',
				'section'     => 'woo',
				'type'        => 'bool',
				'default'     => true,
				'allowed'     => array(
					'0' => 'Ascunde',
					'1' => 'Afișează',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
				'requires'    => 'catalog_filters',
			),
			'catalog_hide_empty'      => array(
				'label'       => 'Ascunde opțiunile fără rezultate',
				'description' => 'Folosește opțiunea WooCommerce pentru categorii, branduri, atribute și stoc.',
				'section'     => 'woo',
				'type'        => 'bool',
				'default'     => true,
				'allowed'     => array(
					'0' => 'Afișează',
					'1' => 'Ascunde',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
				'requires'    => 'catalog_filters',
			),
			'catalog_density'         => array(
				'label'       => 'Densitatea cardurilor',
				'description' => 'Păstrează densitatea colecțiilor vechi. Cardurile Zion folosesc Compact când nu este ales un preset explicit.',
				'section'     => 'cards',
				'type'        => 'choice',
				'default'     => 'standard',
				'allowed'     => array(
					'standard' => 'Standard',
					'compact'  => 'Compact',
				),
				'contexts'    => array( 'global', 'shop', 'page' ),
			),
			'catalog_actions'         => array(
				'label'       => 'Buton WooCommerce pe carduri',
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
			'product_meta'            => array(
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
			'product_related'         => array(
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
			'reading_time'            => array(
				'label'       => 'Timp estimat de citire',
				'description' => 'Afișează estimarea în articol și pe cardul din jurnal.',
				'section'     => 'blog',
				'type'        => 'bool',
				'default'     => false,
				'allowed'     => array(
					'0' => 'Ascunde',
					'1' => 'Afișează',
				),
				'contexts'    => array( 'global', 'post' ),
			),
			'reading_speed'           => array(
				'label'       => 'Cuvinte pe minut',
				'description' => 'Viteza folosită la calculul timpului estimat de citire.',
				'section'     => 'blog',
				'type'        => 'int',
				'default'     => 220,
				'allowed'     => array(
					'min' => 100,
					'max' => 400,
				),
				'contexts'    => array( 'global', 'post' ),
				'requires'    => 'reading_time',
			),
			'page_home_link'          => array(
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
			'disable_emoji_assets'    => array(
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
		static $cards    = null;
		$cards         ??= self::card_fields();
		static $products = null;
		$products      ??= self::product_fields();
		static $site     = null;
		$site          ??= self::site_fields();
		return $fields + $cards + $products + $site;
	}

	/**
	 * Presentation settings for navigation, editorial content and pages.
	 *
	 * @return array
	 */
	private static function site_fields(): array {
		$groups = array(
			'header' => array(
				'header_sticky'      => array( 'Header fix', 'Păstrează headerul la vedere la derulare.', false ),
				'header_transparent' => array( 'Header transparent pe homepage', 'Afișează headerul transparent deasupra secțiunii introductive.', false ),
				'header_mega'        => array( 'Submeniuri late', 'Afișează submeniurile de primul nivel în coloane pe desktop. Editează linkurile în blocul Navigație.', false ),
				'header_search'      => array( 'Căutare produse', 'Afișează formularul WooCommerce pentru căutare în catalog.', true ),
				'header_account'     => array( 'Cont WooCommerce', 'Afișează accesul la Contul meu când pagina este configurată în WooCommerce.', true ),
				'header_cart'        => array( 'Mini-coș', 'Afișează mini-coșul nativ WooCommerce.', true ),
				'header_cart_badge'  => array( 'Badge cantitate coș', 'Afișează cantitatea calculată de mini-coșul WooCommerce.', true, 'header_cart' ),
				'header_cta'         => array( 'Buton principal', 'Afișează butonul al cărui text și link se editează în Header din Site Editor.', false ),
			),
			'footer' => array(
				'footer_cta'       => array( 'CTA Zion Nexus', 'Afișează grupul principal de acțiuni din Footer.', true ),
				'footer_contact'   => array( 'Contact', 'Afișează grupul Contact din Footer când are conținut.', true ),
				'footer_resources' => array( 'Resurse și navigație', 'Afișează grupul de resurse din Footer.', true ),
				'footer_social'    => array( 'Rețele sociale', 'Afișează linkurile sociale configurate în blocul Social Icons.', true ),
				'footer_legal'     => array( 'Linkuri legale', 'Afișează linkurile către paginile legale configurate.', true ),
				'footer_copyright' => array( 'Copyright', 'Afișează linia de copyright editabilă din Footer.', true ),
				'footer_search'    => array( 'Căutare în subsol', 'Afișează formularul de căutare generală.', true ),
			),
			'blog'   => array(
				'blog_image'      => array( 'Imagine reprezentativă', 'Afișează imaginea în jurnal și în articol.', true ),
				'blog_categories' => array( 'Categorii', 'Afișează categoriile articolului.', true ),
				'blog_date'       => array( 'Data publicării', 'Afișează data nativă a articolului.', true ),
				'blog_author'     => array( 'Autor', 'Afișează autorul WordPress.', true ),
				'blog_excerpt'    => array( 'Rezumat', 'Afișează rezumatul pe card și în articol când există.', true ),
				'blog_related'    => array( 'Articole asociate', 'Afișează până la trei articole din categorii comune.', true ),
				'blog_share'      => array( 'Distribuire', 'Afișează partajarea prin e-mail și copierea linkului.', true ),
				'blog_comments'   => array( 'Comentarii', 'Afișează comentariile și formularul conform regulilor WordPress.', true ),
			),
			'pages'  => array(
				'page_breadcrumb' => array( 'Breadcrumb', 'Afișează legăturile către pagina principală și paginile părinte.', false ),
				'page_title'      => array( 'Titlul paginii', 'Afișează titlul WordPress în antetul paginii.', true ),
				'page_image'      => array( 'Imagine reprezentativă', 'Afișează imaginea reprezentativă din editorul paginii.', true ),
				'page_sidebar'    => array( 'Bară laterală', 'Afișează Template Part-ul Bara laterală în paginile standard.', false ),
			),
		);
		$fields = array();
		foreach ( $groups as $section => $items ) {
			$contexts = match ( $section ) {
				'header', 'pages' => array( 'global', 'page' ),
				'blog' => array( 'global', 'post' ),
				default => array( 'global' ),
			};
			foreach ( $items as $key => $item ) {
				$fields[ $key ] = array(
					'label'       => $item[0],
					'description' => $item[1],
					'default'     => $item[2],
					'section'     => $section,
					'contexts'    => $contexts,
					'type'        => 'bool',
					'allowed'     => array(
						'0' => 'Nu',
						'1' => 'Da',
					),
				);
				if ( isset( $item[3] ) ) {
					$fields[ $key ]['requires'] = $item[3];
				}
			}
		}
		$fields['blog_layout']   = array(
			'label'       => 'Aspect jurnal',
			'description' => 'Alege grila sau lista pentru jurnal și arhivele articolelor.',
			'section'     => 'blog',
			'contexts'    => array( 'global' ),
			'type'        => 'choice',
			'default'     => 'grid',
			'allowed'     => array(
				'grid' => 'Grilă',
				'list' => 'Listă',
			),
		);
		$fields['blog_per_page'] = array(
			'label'       => 'Articole pe pagină',
			'description' => 'Suprascrie limita WordPress pentru jurnal și arhivele articolelor când este configurată explicit.',
			'section'     => 'blog',
			'contexts'    => array( 'global' ),
			'type'        => 'int',
			'default'     => 10,
			'allowed'     => array(
				'min' => 1,
				'max' => 48,
			),
		);
		foreach ( array(
			'page_width'  => array(
				'Lățime pagină',
				'boxed',
				array(
					'boxed' => 'Boxed',
					'full'  => 'Full-width',
				),
			),
			'page_header' => array(
				'Stil antet pagină',
				'simple',
				array(
					'simple' => 'Simplu',
					'panel'  => 'Panou',
					'hidden' => 'Ascuns',
				),
			),
		) as $key => $choice ) {
			$fields[ $key ] = array(
				'label'       => $choice[0],
				'description' => 'Se aplică șablonului standard Zion și poate fi suprascrisă per pagină.',
				'section'     => 'pages',
				'contexts'    => array( 'global', 'page' ),
				'type'        => 'choice',
				'default'     => $choice[1],
				'allowed'     => $choice[2],
			);
		}
		return $fields;
	}

	/**
	 * Ordered product page sections.
	 *
	 * @return array
	 */
	public static function product_sections(): array {
		return array(
			'breadcrumb'     => 'Breadcrumb',
			'hero'           => 'Galerie și rezumat',
			'description'    => 'Descriere',
			'specifications' => 'Specificații',
			'compatibility'  => 'Compatibilitate',
			'benefits'       => 'Beneficii',
			'files'          => 'Fișiere și formate',
			'version'        => 'Versiune software',
			'changelog'      => 'Changelog',
			'documentation'  => 'Documentație',
			'faq'            => 'FAQ',
			'reviews'        => 'Recenzii',
			'related'        => 'Produse asociate',
			'upsells'        => 'Upsells',
			'cross_sells'    => 'Cross-sells',
		);
	}

	/**
	 * Ordered components in the purchase summary.
	 *
	 * @return array
	 */
	public static function product_summary(): array {
		return array(
			'title'             => 'Titlu',
			'price'             => 'Preț',
			'short_description' => 'Descriere scurtă',
			'availability'      => 'Disponibilitate',
			'purchase'          => 'Formular WooCommerce',
			'meta'              => 'SKU și categorii',
		);
	}

	/**
	 * Register product layout options in the existing settings schema.
	 *
	 * @return array
	 */
	private static function product_fields(): array {
		$common  = array(
			'section'  => 'product',
			'contexts' => array( 'global', 'shop', 'product' ),
		);
		$choices = array(
			'preset'       => array(
				'Preset pagină',
				'Auto folosește vitrina produsului și tipul fizic sau virtual pentru produsele fără o vitrină unică.',
				'auto',
				array(
					'auto'     => 'Auto',
					'physical' => 'Physical 3D',
					'software' => 'Software',
					'minimal'  => 'Minimal',
					'premium'  => 'Premium',
				),
			),
			'style'        => array(
				'Stilul componentelor',
				'Stil aplicat rezumatului și secțiunilor informative.',
				'preset',
				array(
					'preset' => 'Conform presetului',
					'plain'  => 'Simplu',
					'panel'  => 'Panouri',
					'accent' => 'Contur accent',
				),
			),
			'gallery_side' => array(
				'Poziția galeriei',
				'Pe mobil galeria rămâne deasupra rezumatului.',
				'left',
				array(
					'left'  => 'Stânga',
					'right' => 'Dreapta',
				),
			),
		);
		$fields  = array();
		foreach ( $choices as $key => $choice ) {
			$fields[ 'product_' . $key ] = $common + array(
				'label'       => $choice[0],
				'description' => $choice[1],
				'type'        => 'choice',
				'default'     => $choice[2],
				'allowed'     => $choice[3],
			);
		}
		$sections = self::product_sections();
		$summary  = self::product_summary();
		foreach ( array(
			'section_order'   => array( 'Ordinea secțiunilor', $sections ),
			'summary_order'   => array( 'Ordinea în rezumat', $summary ),
			'accent_sections' => array( 'Secțiuni accentuate', array( 'none' => 'Niciuna' ) + $sections ),
		) as $key => $list ) {
			$fields[ 'product_' . $key ] = $common + array(
				'label'       => $list[0],
				'description' => ( 'accent_sections' === $key ? 'Alege cheile secțiunilor care primesc accent vizual. ' : 'Separă cheile prin virgulă. Secțiunile omise urmează în ordinea implicită. ' ) . implode( ', ', array_keys( $list[1] ) ) . '.',
				'type'        => 'ordered_keys',
				'default'     => 'accent_sections' === $key ? 'none' : implode( ',', array_keys( $list[1] ) ),
				'allowed'     => $list[1],
			);
		}
		unset( $sections['hero'], $sections['related'], $summary['meta'] );
		$extra = array(
			'gallery'    => 'Galerie imagini',
			'zoom'       => 'Zoom galerie',
			'lightbox'   => 'Lightbox galerie',
			'variations' => 'Selector variații',
			'quantity'   => 'Cantitate',
			'buy_now'    => 'Cumpără acum',
			'sticky'     => 'Bară de cumpărare',
		);
		foreach ( $sections + $summary + $extra as $key => $label ) {
			$fields[ 'product_show_' . $key ] = $common + array(
				'label'       => $label,
				'description' => match ( $key ) {
					'variations' => 'Ascunderea selectorului ascunde formularul pentru produse variabile.',
					'quantity' => 'Cantitatea implicită WooCommerce rămâne în formular. Cantitățile produselor grupate rămân vizibile.',
					'buy_now' => 'Adaugă produsul prin formularul WooCommerce și deschide finalizarea comenzii după o adăugare reușită.',
					'sticky' => 'Folosește același formular WooCommerce, inclusiv variația și cantitatea selectate.',
					default => 'Urmează presetul sau controlează afișarea explicit. Secțiunile fără date sunt omise.',
				},
				'type'        => 'choice',
				'default'     => 'preset',
				'allowed'     => array(
					'preset' => 'Conform presetului',
					'show'   => 'Afișează',
					'hide'   => 'Ascunde',
				),
			);
		}
		return $fields;
	}

	/**
	 * Ordered card elements and their Control Center labels.
	 *
	 * @return array
	 */
	public static function card_elements(): array {
		return array(
			'category'     => 'Categorie',
			'brand'        => 'Brand',
			'title'        => 'Titlu',
			'sku'          => 'SKU',
			'price'        => 'Preț',
			'rating'       => 'Rating',
			'reviews'      => 'Număr de recenzii',
			'description'  => 'Descriere scurtă',
			'availability' => 'Disponibilitate',
			'cart'         => 'Buton WooCommerce',
			'quick_view'   => 'Quick View',
			'wishlist'     => 'Wishlist',
			'compare'      => 'Compare',
			'details'      => 'Vezi detalii',
		);
	}

	/**
	 * Define the card controls in the same registry and option as other theme settings.
	 *
	 * @return array
	 */
	private static function card_fields(): array {
		$common  = array(
			'section'  => 'cards',
			'contexts' => array( 'global', 'shop', 'page' ),
		);
		$choices = array(
			'preset'      => array(
				'Preset card',
				'Schimbă aspectul și elementele implicite. Controalele explicite au prioritate.',
				'classic',
				array(
					'minimal'    => 'Minimal',
					'classic'    => 'Classic',
					'premium'    => 'Premium',
					'compact'    => 'Compact',
					'horizontal' => 'Horizontal',
				),
			),
			'ratio'       => array(
				'Raport imagine',
				'Raportul cadrului în care este afișată imaginea produsului.',
				'4/3',
				array(
					'1/1'  => '1:1',
					'4/3'  => '4:3',
					'3/4'  => '3:4',
					'16/9' => '16:9',
				),
			),
			'fit'         => array(
				'Încadrare imagine',
				'Umple cadrul sau afișează integral fotografia.',
				'cover',
				array(
					'cover'   => 'Umple cadrul',
					'contain' => 'Imagine integrală',
				),
			),
			'color'       => array(
				'Culori card',
				'Folosește paleta Zion din theme.json.',
				'dark',
				array(
					'dark'   => 'Antracit',
					'accent' => 'Accent lime',
					'light'  => 'Deschis',
				),
			),
			'buttons'     => array(
				'Mărime butoane',
				'Schimbă spațierea butoanelor și păstrează o țintă de minimum 44 px.',
				'regular',
				array(
					'small'   => 'Mică',
					'regular' => 'Normală',
					'large'   => 'Mare',
				),
			),
			'badge_place' => array(
				'Poziție badge-uri',
				'Alege colțul imaginii în care apar badge-urile.',
				'top-start',
				array(
					'top-start'    => 'Sus stânga',
					'top-end'      => 'Sus dreapta',
					'bottom-start' => 'Jos stânga',
					'bottom-end'   => 'Jos dreapta',
				),
			),
			'title_lines' => array(
				'Linii maxime pentru titlu',
				'Limita vizuală păstrează titlul complet disponibil pentru cititoarele de ecran.',
				'2',
				array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
			),
		);
		$fields  = array();
		foreach ( $choices as $key => $choice ) {
			$fields[ 'card_' . $key ] = $common + array(
				'label'       => $choice[0],
				'description' => $choice[1],
				'type'        => 'choice',
				'default'     => $choice[2],
				'allowed'     => $choice[3],
			);
		}
		$elements             = self::card_elements();
		$fields['card_order'] = $common + array(
			'label'       => 'Ordinea elementelor din card',
			'description' => 'Separă cheile prin virgulă. Elementele omise păstrează ordinea implicită după cele enumerate. Chei: ' . implode( ', ', array_keys( $elements ) ) . '.',
			'type'        => 'ordered_keys',
			'default'     => implode( ',', array_keys( $elements ) ),
			'allowed'     => $elements,
		);
		unset( $elements['cart'] );
		foreach ( $elements + array(
			'image'          => 'Imagine',
			'sale_price'     => 'Preț redus',
			'badge_sale'     => 'Badge reducere',
			'badge_featured' => 'Badge recomandat',
			'badge_stock'    => 'Badge stoc epuizat',
			'placeholder'    => 'Placeholder WooCommerce',
			'zoom'           => 'Zoom la hover',
			'secondary'      => 'A doua imagine la hover',
		) as $key => $label ) {
			$fields[ 'card_show_' . $key ] = $common + array(
				'label'       => $label,
				'description' => match ( $key ) {
					'wishlist', 'compare' => 'Acțiunea este afișată dacă un modul compatibil este activ.',
					'sale_price' => 'Când este ascuns, cardul afișează prețul obișnuit. Prețul de cumpărare este stabilit de WooCommerce.',
					default => 'Urmează presetul sau alege explicit dacă elementul apare pe card.',
				},
				'type'        => 'choice',
				'default'     => 'preset',
				'allowed'     => array(
					'preset' => 'Conform presetului',
					'show'   => 'Afișează',
					'hide'   => 'Ascunde',
				),
			);
		}
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
				case 'slugs':
					if ( $form && is_string( $value ) ) {
						$value = implode( ',', array_unique( array_map( 'trim', explode( ',', strtolower( $value ) ) ) ) );
					}
					$valid = is_string( $value ) && preg_match( '/^[a-z0-9_-]{1,28}(?:,[a-z0-9_-]{1,28})*$/D', $value ) &&
						count( explode( ',', $value ) ) <= $field['allowed']['max'];
					break;
				case 'ordered_keys':
					$keys  = is_string( $value ) ? array_map( 'trim', explode( ',', $value ) ) : array();
					$valid = $keys && count( $keys ) === count( array_unique( $keys ) ) && ! array_diff( $keys, array_keys( $field['allowed'] ) );
					$value = implode( ',', $keys );
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
