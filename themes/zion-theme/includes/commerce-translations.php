<?php
/**
 * Romanian defaults for WooCommerce customer-facing interfaces.
 *
 * @package Zion_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Keep installed translations and leave the administration language alone.
 *
 * @param string $domain Gettext domain.
 * @return bool
 */
function zion_theme_romanian_commerce( string $domain ): bool {
	return in_array( $domain, array( 'woocommerce', 'woo-gutenberg-products-block' ), true ) &&
		( ! is_admin() || wp_doing_ajax() ) &&
		in_array( determine_locale(), array( 'en_US', 'ro_RO' ), true );
}

/**
 * WooCommerce fallback strings shared by PHP and JavaScript.
 *
 * @return array<string,string>
 */
function zion_theme_commerce_strings(): array {
	static $strings = array(
		'Add to cart'                                   => 'Adaugă în coș',
		'Add to Cart'                                   => 'Adaugă în coș',
		'Add to cart: “%s”'                             => 'Adaugă în coș: „%s”',
		'Add “%s” to your cart'                         => 'Adaugă „%s” în coș',
		'Select options'                                => 'Alege opțiunile',
		'Select options for “%s”'                       => 'Alege opțiunile pentru „%s”',
		'Read more'                                     => 'Citește mai mult',
		'View cart'                                     => 'Vezi coșul',
		'View Cart'                                     => 'Vezi coșul',
		'View my cart'                                  => 'Vezi coșul meu',
		'Your cart is currently empty!'                 => 'Coșul tău este gol.',
		'Your cart is currently empty.'                 => 'Coșul tău este gol.',
		'Browse store'                                  => 'Continuă cumpărăturile',
		'New in store'                                  => 'Produse noi în magazin',
		'Start shopping'                                => 'Vezi produsele',
		'Continue shopping'                             => 'Continuă cumpărăturile',
		'Continue Shopping'                             => 'Continuă cumpărăturile',
		'Cart'                                          => 'Coș',
		'Shopping cart'                                 => 'Coș de cumpărături',
		'Cart totals'                                   => 'Total coș',
		'Cart Totals'                                   => 'Total coș',
		'Checkout'                                      => 'Finalizare comandă',
		'Proceed to Checkout'                           => 'Continuă la finalizarea comenzii',
		'Proceed to checkout'                           => 'Continuă la finalizarea comenzii',
		'Proceed to checkout →'                         => 'Continuă la finalizarea comenzii →',
		'Return to Cart'                                => 'Înapoi la coș',
		'Return to cart'                                => 'Înapoi la coș',
		'Place Order'                                   => 'Plasează comanda',
		'Place order'                                   => 'Plasează comanda',
		'Order summary'                                 => 'Rezumatul comenzii',
		'Order Summary'                                 => 'Rezumatul comenzii',
		'Contact information'                           => 'Date de contact',
		'Contact Information'                           => 'Date de contact',
		'Shipping address'                              => 'Adresă de livrare',
		'Shipping Address'                              => 'Adresă de livrare',
		'Billing address'                               => 'Adresă de facturare',
		'Billing Address'                               => 'Adresă de facturare',
		'Billing details'                               => 'Date de facturare',
		'Use same address for billing'                  => 'Folosește aceeași adresă pentru facturare',
		'Use same address for shipping'                 => 'Folosește aceeași adresă pentru livrare',
		'Shipping options'                              => 'Opțiuni de livrare',
		'Shipping method'                               => 'Metodă de livrare',
		'Delivery'                                      => 'Livrare',
		'Pickup'                                        => 'Ridicare',
		'Payment options'                               => 'Opțiuni de plată',
		'Payment method'                                => 'Metodă de plată',
		'Payment Method'                                => 'Metodă de plată',
		'Payment'                                       => 'Plată',
		'Cash on delivery'                              => 'Plată ramburs',
		'Direct bank transfer'                          => 'Transfer bancar',
		'First name'                                    => 'Prenume',
		'Last name'                                     => 'Nume',
		'Company'                                       => 'Companie',
		'Company name'                                  => 'Denumirea companiei',
		'Address'                                       => 'Adresă',
		'Street address'                                => 'Stradă și număr',
		'Apartment, suite, etc.'                        => 'Apartament, etaj etc.',
		'Apartment, suite, unit, etc.'                  => 'Apartament, etaj etc.',
		'+ Add apartment, suite, etc.'                  => '+ Adaugă apartament, etaj etc.',
		'Town / City'                                   => 'Localitate',
		'City'                                          => 'Localitate',
		'Country / Region'                              => 'Țară / Regiune',
		'Country/Region'                                => 'Țară / Regiune',
		'Country'                                       => 'Țară',
		'State / County'                                => 'Județ / Regiune',
		'State/County'                                  => 'Județ / Regiune',
		'State'                                         => 'Județ / Regiune',
		'County'                                        => 'Județ',
		'Postcode / ZIP'                                => 'Cod poștal',
		'Postal code'                                   => 'Cod poștal',
		'Postcode'                                      => 'Cod poștal',
		'ZIP Code'                                      => 'Cod poștal',
		'Phone'                                         => 'Telefon',
		'Phone number'                                  => 'Număr de telefon',
		'Email address'                                 => 'Adresă de e-mail',
		'Email Address'                                 => 'Adresă de e-mail',
		'Email'                                         => 'E-mail',
		'Optional'                                      => 'Opțional',
		'optional'                                      => 'opțional',
		'(optional)'                                    => '(opțional)',
		'Add a note to your order'                      => 'Adaugă o notă la comandă',
		'Order notes'                                   => 'Note de comandă',
		'Notes about your order, e.g. special notes for delivery.' => 'Note despre comandă, de exemplu instrucțiuni de livrare.',
		'Add coupons'                                   => 'Adaugă cupoane',
		'Add a coupon'                                  => 'Adaugă un cupon',
		'Coupon code'                                   => 'Codul cuponului',
		'Coupon:'                                       => 'Cupon:',
		'Apply'                                         => 'Aplică',
		'Apply coupon'                                  => 'Aplică cuponul',
		'Remove coupon'                                 => 'Elimină cuponul',
		'Subtotal'                                      => 'Subtotal',
		'Shipping'                                      => 'Livrare',
		'Free shipping'                                 => 'Livrare gratuită',
		'Estimated total'                               => 'Total estimat',
		'Discount'                                      => 'Reducere',
		'Discounts'                                     => 'Reduceri',
		'Taxes'                                         => 'Taxe',
		'Tax'                                           => 'Taxă',
		'Product'                                       => 'Produs',
		'Products'                                      => 'Produse',
		'Product categories'                            => 'Categorii de produse',
		'Price'                                         => 'Preț',
		'Quantity'                                      => 'Cantitate',
		'Quantity of %s in your cart.'                  => 'Cantitatea pentru %s din coș.',
		'%s has been removed from your cart.'           => 'Am eliminat %s din coș.',
		'Total price for <quantity/> <productName/> item: <price/>' => '<quantity/> × <productName/> — total: <price/>',
		'Total price for <quantity/> <productName/> items: <price/>' => '<quantity/> × <productName/> — total: <price/>',
		'Increase quantity of %s'                       => 'Mărește cantitatea pentru %s',
		'Reduce quantity of %s'                         => 'Redu cantitatea pentru %s',
		'Details'                                       => 'Detalii',
		'Enter code'                                    => 'Introdu codul',
		'Remove item'                                   => 'Elimină produsul',
		'Remove %s from cart'                           => 'Elimină %s din coș',
		'Remove'                                        => 'Elimină',
		'Update cart'                                   => 'Actualizează coșul',
		'In stock'                                      => 'În stoc',
		'Out of stock'                                  => 'Stoc epuizat',
		'On backorder'                                  => 'Disponibil la comandă',
		'Available on backorder'                        => 'Disponibil la comandă',
		'%s in stock'                                   => '%s în stoc',
		'Choose an option'                              => 'Alege o opțiune',
		'Clear'                                         => 'Resetează',
		'Login'                                         => 'Autentificare',
		'Log in'                                        => 'Autentifică-te',
		'Log In'                                        => 'Autentifică-te',
		'My account'                                    => 'Contul meu',
		'My Account'                                    => 'Contul meu',
		'Password'                                      => 'Parolă',
		'Username or email address'                     => 'Nume de utilizator sau adresă de e-mail',
		'Create an account?'                            => 'Creezi un cont?',
		'Loading…'                                      => 'Se încarcă…',
		'Close'                                         => 'Închide',
		'Product Filters'                               => 'Filtre de produse',
		'Filter products'                               => 'Filtrează produsele',
		'Number of items in the cart: %d'               => 'Număr de produse în coș: %d',
		'&ldquo;%s&rdquo; has been added to your cart.' => '„%s” a fost adăugat în coș.',
		'“%s” has been added to your cart.'             => '„%s” a fost adăugat în coș.',
		'Add to cart: &ldquo;%s&rdquo;'                 => 'Adaugă în coș: &bdquo;%s&rdquo;',
		'Read more about &ldquo;%s&rdquo;'              => 'Citește mai mult despre &bdquo;%s&rdquo;',
		'Select options for &ldquo;%s&rdquo;'           => 'Alege opțiunile pentru &bdquo;%s&rdquo;',
		'%s has been added to your cart.'               => '%s a fost adăugat în coș.',
		'%s have been added to your cart.'              => '%s au fost adăugate în coș.',
		'%d in cart'                                    => '%d în coș',
		'%s in cart'                                    => '%s în coș',
		'Products in cart'                              => 'Produse în coș',
		'Loading products in cart…'                     => 'Se încarcă produsele din coș…',
		'Select a %s'                                   => 'Selectează %s',
		'+ Add %s'                                      => '+ Adaugă %s',
		'Add %s'                                        => 'Adaugă %s',
		'%s (optional)'                                 => '%s (opțional)',
		'Phone (optional)'                              => 'Telefon (opțional)',
		'You are currently checking out as a guest.'    => 'Finalizezi comanda fără cont.',
		'There are no payment methods available. Please contact us for help placing your order.' => 'Nu sunt disponibile metode de plată. Contactează-ne pentru ajutor cu plasarea comenzii.',
		'By proceeding with your purchase you agree to our %1$s and %2$s' => 'Prin continuarea comenzii, accepți %1$s și %2$s',
		'Terms and Conditions'                          => 'Termenii și condițiile',
		'Privacy Policy'                                => 'Politica de confidențialitate',
		'Romania'                                       => 'România',
		'Previous products'                             => 'Produsele anterioare',
		'Next products'                                 => 'Produsele următoare',
		'Remove filter: {{label}}'                      => 'Elimină filtrul: {{label}}',
		'Filter by price'                               => 'Filtrează după preț',
		'Filter products by minimum price'              => 'Filtrează produsele după prețul minim',
		'Filter products by maximum price'              => 'Filtrează produsele după prețul maxim',
		'Default sorting'                               => 'Sortare implicită',
		'Sort by'                                       => 'Sortează după',
		'Shop order'                                    => 'Sortează produsele',
		'Sort by popularity'                            => 'Sortează după popularitate',
		'Sort by average rating'                        => 'Sortează după evaluarea medie',
		'Sort by latest'                                => 'Sortează după cele mai noi',
		'Sort by price: low to high'                    => 'Sortează după preț crescător',
		'Sort by price: high to low'                    => 'Sortează după preț descrescător',
		'Showing the single result'                     => 'Se afișează un produs',
		'Showing all %d results'                        => 'Se afișează toate cele %d produse',
		'Showing %1$d–%2$d of %3$d results'             => 'Se afișează %1$d–%2$d din %3$d produse',
		'Please enter a valid email address.'           => 'Introdu o adresă de e-mail validă.',
		'No shipping options are available.'            => 'Nu sunt disponibile opțiuni de livrare.',
		'Related products'                              => 'Produse similare',
		'Return to shop'                                => 'Înapoi la magazin',
		'Return to Shop'                                => 'Înapoi la magazin',
	);
	return $strings;
}

/**
 * Supply missing customer-facing PHP translations.
 *
 * @param string $translated Current translation.
 * @param string $text Source message.
 * @param string $domain Gettext domain.
 * @return string
 */
function zion_theme_translate_commerce( string $translated, string $text, string $domain ): string {
	return $translated === $text && zion_theme_romanian_commerce( $domain )
		? ( zion_theme_commerce_strings()[ $text ] ?? $translated )
		: $translated;
}
add_filter( 'gettext_woocommerce', 'zion_theme_translate_commerce', 10, 3 );
add_filter( 'gettext_woo-gutenberg-products-block', 'zion_theme_translate_commerce', 10, 3 );
add_filter(
	'gettext_with_context_woocommerce',
	static fn ( string $translated, string $text, string $context, string $domain ): string => zion_theme_translate_commerce( $translated, $text, $domain ),
	10,
	4
);

/**
 * Translate fallback plural messages such as cart confirmation notices.
 *
 * @param string $translated Current translation.
 * @param string $single Source singular.
 * @param string $plural Source plural.
 * @param int    $number Item count.
 * @param string $domain Gettext domain.
 * @return string
 */
function zion_theme_translate_commerce_plural( string $translated, string $single, string $plural, int $number, string $domain ): string {
	return zion_theme_translate_commerce( $translated, 1 === $number ? $single : $plural, $domain );
}
add_filter( 'ngettext_woocommerce', 'zion_theme_translate_commerce_plural', 10, 5 );

/**
 * Translate stock titles of WooCommerce pages while preserving custom page titles.
 *
 * @param string $title Current page title.
 * @param int    $id Page ID.
 * @return string
 */
function zion_theme_commerce_page_title( string $title, int $id ): string {
	if ( ! zion_theme_romanian_commerce( 'woocommerce' ) || ! function_exists( 'wc_get_page_id' ) ) {
		return $title;
	}
	$stock = array(
		'cart'      => 'Cart',
		'checkout'  => 'Checkout',
		'myaccount' => 'My account',
	);
	foreach ( $stock as $page => $english ) {
		if ( 0 === strcasecmp( $title, $english ) && wc_get_page_id( $page ) === $id ) {
			return zion_theme_commerce_strings()[ $english ];
		}
	}
	return $title;
}
add_filter( 'the_title', 'zion_theme_commerce_page_title', 10, 2 );

/**
 * Seed the shared script catalog once. Installed script catalogs are loaded afterwards.
 */
function zion_theme_commerce_script_translations(): void {
	if ( ! zion_theme_romanian_commerce( 'woocommerce' ) ) {
		return;
	}
	$data               = array_map( static fn ( string $value ): array => array( $value ), zion_theme_commerce_strings() );
	$total_key          = 'Total price for <quantity/> <productName/> item: <price/>';
	$data[ $total_key ] = array_fill( 0, 3, $data[ $total_key ][0] );
	foreach ( array( 'woocommerce', 'woo-gutenberg-products-block' ) as $domain ) {
		wp_add_inline_script(
			'wp-i18n',
			'wp.i18n.setLocaleData(' . wp_json_encode( $data, JSON_HEX_TAG ) . ',' . wp_json_encode( $domain ) . ');',
			'after'
		);
	}
}
add_action( 'wp_enqueue_scripts', 'zion_theme_commerce_script_translations' );
