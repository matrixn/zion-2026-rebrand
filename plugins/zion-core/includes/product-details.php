<?php
/**
 * Public editorial information for Zion products.
 *
 * @package Zion_Core
 */

/**
 * Definitions shared by product metadata and the product editor.
 *
 * @return array
 */
function zion_core_product_detail_fields(): array {
	return array(
		'compatibility' => array( 'Compatibilitate', 'textarea', 'Sisteme, aplicații, materiale sau echipamente compatibile.' ),
		'benefits'      => array( 'Beneficii', 'textarea', 'Un beneficiu pe linie.' ),
		'files'         => array( 'Fișiere și formate', 'textarea', 'Descrie conținutul și formatele. Gestionează fișierele protejate în WooCommerce.' ),
		'version'       => array( 'Versiune software', 'text', 'Versiunea afișată public.' ),
		'changelog'     => array( 'Changelog', 'textarea', 'Notele publice ale versiunii.' ),
		'documentation' => array( 'Documentație', 'url', 'URL public HTTP sau HTTPS.' ),
		'faq'           => array( 'FAQ', 'textarea', 'O pereche întrebare | răspuns pe linie.' ),
	);
}

/**
 * Sanitize an editorial field according to its registered type.
 *
 * @param mixed  $value Submitted value.
 * @param string $type Field type.
 * @return string
 */
function zion_core_sanitize_product_detail( mixed $value, string $type ): string {
	if ( ! is_string( $value ) ) {
		return '';
	}
	return match ( $type ) {
		'url' => preg_match( '#^https?://#i', trim( $value ) ) ? sanitize_url( trim( $value ), array( 'http', 'https' ) ) : '',
		'text' => sanitize_text_field( $value ),
		default => sanitize_textarea_field( $value ),
	};
}

/**
 * Register the metadata without publishing editable fields over REST.
 */
function zion_core_register_product_details(): void {
	foreach ( zion_core_product_detail_fields() as $key => $field ) {
		register_post_meta(
			'product',
			'_zion_product_' . $key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => static fn ( mixed $value ): string => zion_core_sanitize_product_detail( $value, $field[1] ),
				'auth_callback'     => static fn ( bool $allowed, string $meta_key, int $id, int $user ): bool => user_can( $user, 'edit_post', $id ),
			)
		);
	}
}
add_action( 'init', 'zion_core_register_product_details', 30 );

/**
 * Read only an editorial key owned by Zion Core.
 *
 * @param int    $id Product ID.
 * @param string $key Editorial field.
 * @return string
 */
function zion_core_product_detail( int $id, string $key ): string {
	$value = isset( zion_core_product_detail_fields()[ $key ] ) ? get_post_meta( $id, '_zion_product_' . $key, true ) : '';
	return is_string( $value ) ? $value : '';
}

/**
 * Add the product editor's Zion information box.
 */
function zion_core_add_product_details_box(): void {
	add_meta_box( 'zion-product-details', __( 'Informații produs Zion', 'zion-core' ), 'zion_core_render_product_details_box', 'product', 'normal' );
}
add_action( 'add_meta_boxes_product', 'zion_core_add_product_details_box' );

/**
 * Render the registered fields in the native product editor.
 *
 * @param WP_Post $post Current product.
 */
function zion_core_render_product_details_box( WP_Post $post ): void {
	wp_nonce_field( 'zion_product_details', 'zion_product_details_nonce' );
	foreach ( zion_core_product_detail_fields() as $key => $field ) {
		$id    = 'zion-product-detail-' . $key;
		$name  = 'zion_product_details[' . $key . ']';
		$value = zion_core_product_detail( $post->ID, $key );
		?>
		<p>
			<label for="<?php echo esc_attr( $id ); ?>"><strong><?php echo esc_html( $field[0] ); ?></strong></label><br>
			<?php if ( 'textarea' === $field[1] ) : ?>
				<textarea class="large-text" rows="3" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" aria-describedby="<?php echo esc_attr( $id . '-help' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
			<?php else : ?>
				<input class="regular-text" type="<?php echo esc_attr( $field[1] ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" aria-describedby="<?php echo esc_attr( $id . '-help' ); ?>">
			<?php endif; ?>
			<span class="description" id="<?php echo esc_attr( $id . '-help' ); ?>"><?php echo esc_html( $field[2] ); ?></span>
		</p>
		<?php
	}
}

/**
 * Save only the submitted fields from the authenticated product editor.
 *
 * @param int $id Product ID.
 */
function zion_core_save_product_details( int $id ): void {
	$nonce = isset( $_POST['zion_product_details_nonce'] ) && is_string( $_POST['zion_product_details_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['zion_product_details_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'zion_product_details' ) || ! current_user_can( 'edit_post', $id ) || wp_is_post_revision( $id ) || wp_is_post_autosave( $id ) || empty( $_POST['zion_product_details'] ) || ! is_array( $_POST['zion_product_details'] ) ) {
		return;
	}
	foreach ( zion_core_product_detail_fields() as $key => $field ) {
		if ( ! isset( $_POST['zion_product_details'][ $key ] ) || ! is_string( $_POST['zion_product_details'][ $key ] ) ) {
			continue;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The per-field sanitizer handles text, paragraphs and URLs.
		$value = zion_core_sanitize_product_detail( wp_unslash( $_POST['zion_product_details'][ $key ] ), $field[1] );
		if ( '' === $value ) {
			delete_post_meta( $id, '_zion_product_' . $key );
		} else {
			update_post_meta( $id, '_zion_product_' . $key, wp_slash( $value ) );
		}
	}
}
add_action( 'woocommerce_process_product_meta', 'zion_core_save_product_details' );
