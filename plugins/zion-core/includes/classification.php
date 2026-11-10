<?php
/**
 * Classification plans and audit log for approved taxonomy changes.
 *
 * @package Zion_Core
 */

/**
 * Register an internal, non-queryable journal stored through WordPress.
 */
function zion_core_register_shop_journal(): void {
	register_post_type(
		'zion_shop_journal',
		array(
			'public'    => false,
			'show_ui'   => false,
			'query_var' => false,
			'rewrite'   => false,
			'supports'  => array( 'author' ),
		)
	);
}

/**
 * Compute the next terms, retaining any noncanonical legacy terms.
 *
 * @param array  $before Current term IDs.
 * @param array  $selected Requested canonical term IDs.
 * @param array  $canonical All supported shop term IDs.
 * @param string $operation add, remove or replace.
 * @return array Sorted term IDs.
 */
function zion_core_target_shop_terms( array $before, array $selected, array $canonical, string $operation ): array {
	$after = match ( $operation ) {
		'add' => array_merge( $before, $selected ),
		'remove' => array_diff( $before, $selected ),
		'replace' => array_merge( array_diff( $before, $canonical ), $selected ),
		default => $before,
	};
	$after = array_values( array_unique( $after ) );
	sort( $after, SORT_NUMERIC );
	return $after;
}

/**
 * Get current relationship IDs without relying on a stale product cache.
 *
 * @param int $product_id WooCommerce product ID.
 * @return array|WP_Error
 */
function zion_core_product_shop_ids( int $product_id ): array|WP_Error {
	$ids = wp_get_object_terms(
		$product_id,
		'zion_shop',
		array(
			'fields'        => 'ids',
			'cache_results' => false,
		)
	);
	if ( ! is_wp_error( $ids ) ) {
		sort( $ids, SORT_NUMERIC );
	}
	return $ids;
}

/**
 * Build a taxonomy-only plan for products the current user can edit.
 *
 * @param array  $product_ids Selected product IDs.
 * @param string $operation add, remove or replace.
 * @param array  $shops Selected canonical shop slugs.
 * @return array|WP_Error
 */
function zion_core_classification_plan( array $product_ids, string $operation, array $shops ): array|WP_Error {
	if ( ! current_user_can( 'manage_woocommerce' ) || ! current_user_can( 'edit_products' ) ) {
		return new WP_Error( 'zion_shop_forbidden', __( 'Nu ai acces la clasificarea în masă.', 'zion-core' ) );
	}
	if ( ! $product_ids || count( $product_ids ) > 20 || ! in_array( $operation, array( 'add', 'remove', 'replace' ), true ) || ( 'replace' !== $operation && ! $shops ) ) {
		return new WP_Error( 'zion_shop_selection', __( 'Selectează 1–20 produse, o operație și vitrinele necesare.', 'zion-core' ) );
	}
	$canonical = zion_core_shop_term_ids();
	if ( is_wp_error( $canonical ) || 2 !== count( $canonical ) || array_diff( $shops, array_keys( $canonical ) ) ) {
		return new WP_Error( 'zion_shop_terms', __( 'Vitrinele selectate sunt invalide sau termenii canonici lipsesc.', 'zion-core' ) );
	}
	$selected = array_intersect_key( $canonical, array_fill_keys( $shops, true ) );
	$rows     = array();
	foreach ( array_unique( $product_ids ) as $id ) {
		if ( ! is_int( $id ) || ! zion_core_can_classify_product( $id ) ) {
			return new WP_Error( 'zion_shop_forbidden_product', __( 'Cel puțin un produs lipsește sau nu poate fi editat.', 'zion-core' ) );
		}
		$before = zion_core_product_shop_ids( $id );
		if ( is_wp_error( $before ) ) {
			return $before;
		}
		$after  = zion_core_target_shop_terms( $before, $selected, $canonical, $operation );
		$rows[] = array(
			'id'           => $id,
			'before'       => $before,
			'after'        => $after,
			'before_shops' => array_keys( array_intersect( $canonical, $before ) ),
			'after_shops'  => array_keys( array_intersect( $canonical, $after ) ),
			'state'        => $before === $after ? 'unchanged' : 'pending',
		);
	}
	return array(
		'operation' => $operation,
		'terms'     => $canonical,
		'rows'      => $rows,
	);
}

/**
 * Check product eligibility and its individual edit capability.
 *
 * @param int $id Product ID.
 * @return bool
 */
function zion_core_can_classify_product( int $id ): bool {
	return $id > 0 && 'product' === get_post_type( $id ) &&
		in_array( get_post_status( $id ), array( 'publish', 'private', 'draft', 'pending', 'future' ), true ) &&
		current_user_can( 'edit_post', $id );
}

/**
 * Apply an approved plan, recording intent before modifying any terms.
 *
 * @param array $plan Server-side plan.
 * @return array|WP_Error Result rows.
 */
function zion_core_apply_classification_plan( array $plan ): array|WP_Error {
	if ( ! current_user_can( 'manage_woocommerce' ) || ! current_user_can( 'edit_products' ) ) {
		return new WP_Error( 'zion_shop_forbidden', __( 'Nu ai acces la clasificarea în masă.', 'zion-core' ) );
	}
	$canonical = zion_core_shop_term_ids();
	if ( $canonical !== $plan['terms'] ) {
		return new WP_Error( 'zion_shop_stale_terms', __( 'Termenii vitrinelor s-au schimbat. Generează o nouă previzualizare.', 'zion-core' ) );
	}
	$rows = $plan['rows'];
	foreach ( $rows as $row ) {
		$current = zion_core_product_shop_ids( $row['id'] );
		if ( ! zion_core_can_classify_product( $row['id'] ) || $current !== $row['before'] ) {
			return new WP_Error( 'zion_shop_stale_plan', __( 'Un produs s-a schimbat după previzualizare. Generează o nouă previzualizare.', 'zion-core' ) );
		}
	}
	if ( ! in_array( 'pending', array_column( $rows, 'state' ), true ) ) {
		return $rows;
	}

	$journal_id = wp_insert_post(
		array(
			'post_type'    => 'zion_shop_journal',
			'post_status'  => 'private',
			'post_author'  => get_current_user_id(),
			'post_title'   => __( 'Clasificare vitrine Zion', 'zion-core' ),
			'post_content' => wp_slash(
				wp_json_encode(
					array(
						'operation' => $plan['operation'],
						'rows'      => $rows,
					)
				)
			),
		),
		true
	);
	if ( is_wp_error( $journal_id ) || ! $journal_id ) {
		return new WP_Error( 'zion_shop_journal_failed', __( 'Jurnalul nu a putut fi creat. Clasificarea nu a fost modificată.', 'zion-core' ) );
	}

	foreach ( $rows as $index => $row ) {
		if ( 'pending' !== $row['state'] ) {
			continue;
		}
		$current = zion_core_product_shop_ids( $row['id'] );
		if ( $current !== $row['before'] || ! zion_core_can_classify_product( $row['id'] ) ) {
			$rows[ $index ]['state'] = 'stale';
		} else {
			$result                  = wp_set_object_terms( $row['id'], $row['after'], 'zion_shop' );
			$rows[ $index ]['state'] = ! is_wp_error( $result ) && zion_core_product_shop_ids( $row['id'] ) === $row['after'] ? 'applied' : 'failed';
		}
	}
	$record = wp_update_post(
		array(
			'ID'           => $journal_id,
			'post_content' => wp_slash(
				wp_json_encode(
					array(
						'operation' => $plan['operation'],
						'rows'      => $rows,
					)
				)
			),
		),
		true
	);
	if ( is_wp_error( $record ) || ! $record ) {
		return new WP_Error( 'zion_shop_journal_incomplete', __( 'Rezultatul nu a putut fi salvat în jurnal. Consultă intrarea în așteptare și verifică produsele înainte de a încerca din nou.', 'zion-core' ) );
	}
	return $rows;
}
