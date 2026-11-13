<?php
/**
 * Apply an explicit private editorial plan to the authorized staging clone only.
 * Usage: wp eval-file scripts/launch-editorial.php dry-run|apply|verify snapshot.json plan.json
 *
 * @package Zion_Theme
 */

// phpcs:disable WordPress.WP.AlternativeFunctions -- Privileged standalone CLI reads private local backup/plan files and writes its private journal.
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || 'staging' !== wp_get_environment_type() || 'zion-stage.on-forge.com' !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
	exit( 1 );
}
require_once __DIR__ . '/launch-editorial-helpers.php';

$zion_theme_mode = $args[0] ?? '';
if ( ! in_array( $zion_theme_mode, array( 'dry-run', 'apply', 'verify' ), true ) ) {
	WP_CLI::error( 'An explicit dry-run, apply or verify mode is required.' );
}
$zion_theme_paths = array();
foreach ( array_slice( $args, 1, 2 ) as $zion_theme_path ) {
	$zion_theme_real = realpath( $zion_theme_path );
	if ( ! $zion_theme_real || str_starts_with( $zion_theme_real, trailingslashit( realpath( ABSPATH ) ) ) ) {
		WP_CLI::error( 'Snapshot and plan must be private files outside public.' );
	}
	$zion_theme_paths[] = $zion_theme_real;
}
if ( 2 !== count( $zion_theme_paths ) ) {
	WP_CLI::error( 'Both snapshot and explicit plan paths are required.' );
}
$zion_theme_snapshot = json_decode( file_get_contents( $zion_theme_paths[0] ), true, 512, JSON_THROW_ON_ERROR );
$zion_theme_plan     = json_decode( file_get_contents( $zion_theme_paths[1] ), true, 512, JSON_THROW_ON_ERROR );
$zion_theme_journal  = $zion_theme_paths[1] . '.applied.json';
if ( empty( $zion_theme_snapshot['records'] ) || ! isset( $zion_theme_plan['posts'] ) || ! is_array( $zion_theme_plan['posts'] ) ) {
	WP_CLI::error( 'Invalid snapshot or plan schema.' );
}
$zion_theme_expected = $zion_theme_snapshot['records'];
foreach ( $zion_theme_plan['posts'] as $zion_theme_id => $zion_theme_change ) {
	if ( ! isset( $zion_theme_expected[ $zion_theme_id ] ) || ! is_array( $zion_theme_change ) ) {
		WP_CLI::error( 'Every planned record must be present in the snapshot.' );
	}
	foreach ( $zion_theme_change['fields'] ?? array() as $zion_theme_field => $zion_theme_value ) {
		if ( ! in_array( $zion_theme_field, array( 'post_title', 'post_content', 'post_excerpt', 'post_status', 'page_template' ), true ) || ! is_string( $zion_theme_value ) || strlen( $zion_theme_value ) > 5000000 || ( 'post_status' === $zion_theme_field && ! in_array( $zion_theme_value, array( 'publish', 'draft' ), true ) ) ) {
			WP_CLI::error( 'Only explicit editorial fields and publish/draft statuses are allowed.' );
		}
		$zion_theme_expected[ $zion_theme_id ]['post'][ $zion_theme_field ] = $zion_theme_value;
		if ( 'page_template' === $zion_theme_field ) {
			$zion_theme_template_rows = count( $zion_theme_expected[ $zion_theme_id ]['meta']['_wp_page_template'] ?? array() );
			$zion_theme_expected[ $zion_theme_id ]['meta']['_wp_page_template'] = array_fill( 0, max( 1, $zion_theme_template_rows ), $zion_theme_value );
		}
	}
	if ( ! empty( $zion_theme_change['fields'] ) ) {
		$zion_theme_template_error = zion_theme_launch_template_preflight( (int) $zion_theme_id, $zion_theme_change['fields'] );
		if ( is_wp_error( $zion_theme_template_error ) ) {
			WP_CLI::error( 'Template preflight failed for ' . $zion_theme_id . ': ' . $zion_theme_template_error->get_error_code() );
		}
	}
	foreach ( $zion_theme_change['meta'] ?? array() as $zion_theme_key => $zion_theme_value ) {
		if ( ! in_array( $zion_theme_key, array( '_yoast_wpseo_title', '_yoast_wpseo_metadesc', '_yoast_wpseo_canonical' ), true ) || ! is_string( $zion_theme_value ) || strlen( $zion_theme_value ) > 4000 ) {
			WP_CLI::error( 'The plan cannot modify commercial or arbitrary metadata.' );
		}
		$zion_theme_expected[ $zion_theme_id ]['meta'][ $zion_theme_key ] = array( $zion_theme_value );
	}
}

/**
 * Compare every backed-up record, including all unrelated commercial metadata.
 *
 * @param array $expected Explicit expected state.
 * @param array $modified IDs allowed to have native modification timestamps updated.
 */
function zion_theme_launch_verify_records( array $expected, array $modified = array() ): void {
	foreach ( $expected as $id => $saved ) {
		$post = get_post( $id, ARRAY_A );
		if ( ! $post ) {
			WP_CLI::error( 'Missing backed-up record: ' . $id );
		}
		$current = array(
			'post'  => $post,
			'meta'  => get_post_meta( $id ),
			'terms' => wp_get_object_terms(
				$id,
				get_object_taxonomies( $post['post_type'] ),
				array(
					'fields'  => 'all',
					'orderby' => 'term_id',
				)
			),
		);
		if ( in_array( (int) $id, $modified, true ) ) {
			foreach ( array( 'post_modified', 'post_modified_gmt' ) as $key ) {
				$current['post'][ $key ] = $saved['post'][ $key ];
			}
		}
		// JSON comparison ignores PHP object-vs-array representation, not field values.
		if ( zion_theme_launch_normalize( json_decode( wp_json_encode( $current ), true ) ) !== zion_theme_launch_normalize( $saved ) ) {
			WP_CLI::error( 'Unexpected content, metadata or taxonomy difference: ' . $id );
		}
	}
}

/**
 * Associative database key order is not a content change; list order is preserved.
 *
 * @param mixed $value Snapshot value.
 * @return mixed
 */
function zion_theme_launch_normalize( $value ) {
	if ( ! is_array( $value ) ) {
		return $value;
	}
	if ( ! array_is_list( $value ) ) {
		ksort( $value );
	}
	return array_map( 'zion_theme_launch_normalize', $value );
}

if ( 'verify' !== $zion_theme_mode ) {
	if ( file_exists( $zion_theme_journal ) ) {
		WP_CLI::error( 'An applied journal exists; verify it instead of reapplying.' );
	}
	zion_theme_launch_verify_records( $zion_theme_snapshot['records'] );
}
if ( 'apply' === $zion_theme_mode ) {
	$zion_theme_state = array(
		'plan_sha256' => hash_file( 'sha256', $zion_theme_paths[1] ),
		'completed'   => array(),
		'attempted'   => array(),
		'finished'    => false,
	);
	file_put_contents( $zion_theme_journal, wp_json_encode( $zion_theme_state ) );
	chmod( $zion_theme_journal, 0600 );
	foreach ( $zion_theme_plan['posts'] as $zion_theme_id => $zion_theme_change ) {
		$zion_theme_state['attempted'][] = (int) $zion_theme_id;
		file_put_contents( $zion_theme_journal, wp_json_encode( $zion_theme_state ) );
		$zion_theme_fields       = $zion_theme_change['fields'] ?? array();
		$zion_theme_fields['ID'] = (int) $zion_theme_id;
		if ( count( $zion_theme_fields ) > 1 ) {
			$zion_theme_result = wp_update_post( wp_slash( $zion_theme_fields ), true );
			if ( is_wp_error( $zion_theme_result ) ) {
				$zion_theme_state['last_error'] = array(
					'id'   => (int) $zion_theme_id,
					'code' => $zion_theme_result->get_error_code(),
				);
				file_put_contents( $zion_theme_journal, wp_json_encode( $zion_theme_state ) );
				WP_CLI::error( 'Native editorial update failed: ' . $zion_theme_id . ' [' . $zion_theme_result->get_error_code() . ']' );
			}
		}
		foreach ( $zion_theme_change['meta'] ?? array() as $zion_theme_key => $zion_theme_value ) {
			delete_post_meta( $zion_theme_id, $zion_theme_key );
			add_post_meta( $zion_theme_id, $zion_theme_key, wp_slash( $zion_theme_value ), true );
		}
		$zion_theme_state['completed'][] = (int) $zion_theme_id;
		file_put_contents( $zion_theme_journal, wp_json_encode( $zion_theme_state ) );
	}
	zion_theme_launch_verify_records( $zion_theme_expected, array_map( 'intval', array_keys( $zion_theme_plan['posts'] ) ) );
	$zion_theme_state['finished'] = true;
	file_put_contents( $zion_theme_journal, wp_json_encode( $zion_theme_state ) );
} elseif ( 'verify' === $zion_theme_mode ) {
	$zion_theme_state = file_exists( $zion_theme_journal ) ? json_decode( file_get_contents( $zion_theme_journal ), true ) : array();
	if ( empty( $zion_theme_state['finished'] ) || hash_file( 'sha256', $zion_theme_paths[1] ) !== ( $zion_theme_state['plan_sha256'] ?? '' ) ) {
		WP_CLI::error( 'A completed journal for this exact plan is required.' );
	}
	zion_theme_launch_verify_records( $zion_theme_expected, array_map( 'intval', array_keys( $zion_theme_plan['posts'] ) ) );
}
WP_CLI::line(
	wp_json_encode(
		array(
			'mode'              => $zion_theme_mode,
			'backed_up_records' => count( $zion_theme_expected ),
			'planned_records'   => count( $zion_theme_plan['posts'] ),
			'plan_sha256'       => hash_file( 'sha256', $zion_theme_paths[1] ),
			'all_unplanned_fields_metadata_terms_preserved' => true,
		)
	)
);
WP_CLI::success( 'Launch editorial plan validated.' );
