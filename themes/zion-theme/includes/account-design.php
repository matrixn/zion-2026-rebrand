<?php
/**
 * Account presentation, retaining WooCommerce authentication and authorization.
 *
 * @package Zion_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Decorate the native shortcode for any configured account page or endpoint.
 *
 * @param string $html Shortcode output.
 * @param string $tag Shortcode tag.
 * @return string
 */
function zion_theme_account_design( string $html, string $tag ): string {
	if ( 'woocommerce_my_account' !== $tag ) {
		return $html;
	}
	$design = Zion_Theme_Settings::resolve( zion_theme_settings_config(), 'account_design' )['value'];
	$tags   = new WP_HTML_Tag_Processor( $html );
	if ( $tags->next_tag( array( 'class_name' => 'woocommerce' ) ) ) {
		$tags->add_class( 'zion-account' );
		$tags->add_class( 'zion-account--' . $design );
	}
	return $tags->get_updated_html();
}
add_filter( 'do_shortcode_tag', 'zion_theme_account_design', 10, 2 );

/**
 * One dashboard renderer using only the signed-in customer's native order data.
 */
function zion_theme_account_dashboard(): void {
	if ( ! is_user_logged_in() ) {
		return;
	}
	$menu   = wc_get_account_menu_items();
	$orders = isset( $menu['orders'] ) ? wc_get_orders(
		array(
			'customer_id' => get_current_user_id(),
			'limit'       => 3,
			'orderby'     => 'date',
			'order'       => 'DESC',
		)
	) : array();
	?>
	<div class="zion-account-dashboard">
		<div class="zion-account-shortcuts">
			<?php
			foreach ( array(
				'orders'       => 'Comenzi',
				'downloads'    => 'Descărcări',
				'edit-account' => 'Detalii cont',
			) as $endpoint => $label ) :
				?>
				<?php if ( isset( $menu[ $endpoint ] ) ) : ?>
					<a class="zion-account-shortcut" href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?><span aria-hidden="true">↗</span></a>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<?php if ( isset( $menu['orders'] ) ) : ?>
		<section class="zion-account-panel" aria-label="Comenzi recente">
			<h2>Comenzi recente</h2>
			<?php if ( $orders ) : ?>
				<ul class="zion-account-orders">
					<?php foreach ( $orders as $order ) : ?>
						<li>
							<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">#<?php echo esc_html( $order->get_order_number() ); ?></a>
							<span><?php echo esc_html( $order->get_date_created() ? wc_format_datetime( $order->get_date_created() ) : '' ); ?></span>
							<span class="zion-account-status"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span>
							<span><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p>Nu ai încă nicio comandă. Comenzile tale vor apărea aici după cumpărare.</p>
			<?php endif; ?>
		</section>
		<?php endif; ?>
		<div class="zion-account-resources">
			<?php
			foreach ( array(
				'downloads'    => array( 'Fișierele tale', 'Accesează descărcările disponibile pentru comenzile tale.' ),
				'edit-address' => array( 'Adresele tale', 'Gestionează adresele pentru facturare și livrare.' ),
			) as $endpoint => $copy ) :
				?>
				<?php if ( isset( $menu[ $endpoint ] ) ) : ?>
					<section class="zion-account-panel"><h2><?php echo esc_html( $copy[0] ); ?></h2><p><?php echo esc_html( $copy[1] ); ?></p><a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>">Deschide <span class="screen-reader-text"><?php echo esc_html( $copy[0] ); ?></span><span aria-hidden="true">↗</span></a></section>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_account_dashboard', 'zion_theme_account_dashboard' );
