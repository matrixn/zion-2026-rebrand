<?php
/**
 * Query configuration shared by Zion's embedded product collections.
 *
 * @package Zion_Core
 */

/**
 * Validate a numeric presentation option and cap its size.
 *
 * @param mixed $value Submitted value.
 * @param int   $fallback Fallback.
 * @param int   $maximum Upper bound.
 * @return int
 */
function zion_core_collection_integer( mixed $value, int $fallback, int $maximum ): int {
	if ( ! is_int( $value ) && ( ! is_string( $value ) || ! ctype_digit( $value ) ) ) {
		return $fallback;
	}
	return max( 1, min( $maximum, (int) $value ) );
}

/**
 * Normalize inputs from a Gutenberg block or shortcode.
 *
 * @param array $attributes Submitted attributes.
 * @return array|null Null for an invalid shop.
 */
function zion_core_collection_options( array $attributes ): ?array {
	$shop = $attributes['shop'] ?? '';
	if ( ! is_string( $shop ) || ( '' !== $shop && ! isset( zion_core_shops()[ $shop ] ) ) ) {
		return null;
	}
	return array(
		'shop'     => $shop,
		'limit'    => zion_core_collection_integer( $attributes['limit'] ?? null, 8, 24 ),
		'columns'  => zion_core_collection_integer( $attributes['columns'] ?? null, 4, 5 ),
		'paginate' => in_array( $attributes['paginate'] ?? false, array( true, '1', 'true' ), true ),
	);
}

/**
 * Build a native Product Collection query with a validated optional shop.
 *
 * @param string $mode Collection mode.
 * @param array  $options Normalized options.
 * @param int    $product_id Context for related products.
 * @return array|null Null when the mode has no valid context.
 */
function zion_core_collection_query( string $mode, array $options, int $product_id ): ?array {
	$query = array(
		'postType'                 => 'product',
		'perPage'                  => $options['limit'],
		'orderBy'                  => 'date',
		'order'                    => 'desc',
		'inherit'                  => false,
		'filterable'               => false,
		'isProductCollectionBlock' => true,
		'featured'                 => 'featured' === $mode,
		'woocommerceOnSale'        => 'sale' === $mode,
		'zionLibrary'              => true,
		'zionPaginate'             => $options['paginate'] && 'related' !== $mode,
	);
	if ( $options['shop'] ) {
		$query['zionShop'] = $options['shop'];
	}
	if ( 'related' === $mode ) {
		$item = $product_id ? wc_get_product( $product_id ) : false;
		if ( ! $item || 'publish' !== $item->get_status() ) {
			return null;
		}
		// Get an oversample before the optional shop restriction is applied.
		$ids = wc_get_related_products( $product_id, 24, $item->get_upsell_ids() );
		if ( ! $ids ) {
			return null;
		}
		$query['zionRelatedIds'] = array_map( 'absint', $ids );
	}
	return $query;
}
