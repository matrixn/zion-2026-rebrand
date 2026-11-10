<?php
/**
 * WP-CLI entry point for a guarded clone audit, dry-run, apply and rollback.
 *
 * @package Zion_Theme
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

require_once __DIR__ . '/migration-audit.php';
require_once __DIR__ . '/migration-plan.php';

/**
 * Refuse production hosts and require an exact, explicitly approved clone URL.
 *
 * @param string $expected Operator-provided clone URL.
 * @throws RuntimeException For a mismatched or unsupported environment.
 */
function zion_theme_migration_guard( string $expected ): void {
	$environment = wp_get_environment_type();
	$home        = wp_parse_url( home_url( '/' ) );
	$site        = wp_parse_url( site_url( '/' ) );
	$host        = strtolower( rtrim( $home['host'] ?? '', '.' ) );
	$local       = in_array( $host, array( 'localhost', '127.0.0.1', '[::1]' ), true ) || str_ends_with( $host, '.test' ) || str_ends_with( $host, '.localhost' );
	if ( is_multisite() || ! in_array( $environment, array( 'local', 'staging' ), true ) ||
		! in_array( $home['scheme'] ?? '', array( 'http', 'https' ), true ) ||
		'' === $host || in_array( $host, array( 'zion3d.ro', 'www.zion3d.ro' ), true ) ||
		( 'local' === $environment && ! $local ) || ( 'staging' === $environment && 'https' !== $home['scheme'] ) ||
		home_url( '/' ) !== $expected ||
		( $home['host'] ?? '' ) !== ( $site['host'] ?? '' ) ||
		( $home['port'] ?? null ) !== ( $site['port'] ?? null ) ||
		( $home['scheme'] ?? '' ) !== ( $site['scheme'] ?? '' ) ) {
		throw new RuntimeException( 'Refusing to operate outside the explicitly approved local or HTTPS staging clone.' );
	}
}

/**
 * Resolve a private operator-owned file path outside the WordPress document root.
 *
 * @param string $path Input path.
 * @param bool   $create_file Whether this is a new file.
 * @return string
 * @throws RuntimeException For an unsafe or unavailable path.
 */
function zion_theme_migration_private_path( string $path, bool $create_file = false ): string {
	if ( ! path_is_absolute( $path ) || is_link( $path ) ) {
		throw new RuntimeException( 'Use an absolute path to a private regular file.' );
	}
	$resolved = realpath( $create_file ? dirname( $path ) : $path );
	$root     = trailingslashit( wp_normalize_path( realpath( ABSPATH ) ) );
	if ( ! $resolved || str_starts_with( trailingslashit( wp_normalize_path( $resolved ) ), $root ) ||
		( ! $create_file && ( ! is_file( $resolved ) || ! is_readable( $resolved ) ) ) ) {
		throw new RuntimeException( 'Migration manifests, backups and journals must be outside the WordPress document root.' );
	}
	return $create_file ? trailingslashit( $resolved ) . basename( $path ) : $resolved;
}

/**
 * Read a bounded private JSON document.
 *
 * @param string $path Input path.
 * @return array
 * @throws RuntimeException For invalid JSON input.
 */
function zion_theme_migration_read( string $path ): array {
	$path = zion_theme_migration_private_path( $path );
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- WP-CLI reads a private local file.
	$json = file_get_contents( $path, false, null, 0, 32 * MB_IN_BYTES + 1 );
	if ( false === $json || strlen( $json ) > 32 * MB_IN_BYTES ) {
		throw new RuntimeException( 'The private JSON file could not be read or exceeded 32 MiB.' );
	}
	$value = json_decode( $json, true, 128, JSON_THROW_ON_ERROR );
	if ( ! is_array( $value ) ) {
		throw new RuntimeException( 'Expected a JSON object.' );
	}
	return $value;
}

/**
 * Create a private journal exclusively, before any WordPress mutation.
 *
 * @param string $path New journal path.
 * @param array  $journal Journal document.
 * @throws RuntimeException If a complete journal cannot be written.
 */
function zion_theme_migration_journal( string $path, array $journal ): void {
	$path = zion_theme_migration_private_path( $path, true );
	$json = wp_json_encode( $journal, JSON_THROW_ON_ERROR );
	if ( strlen( $json ) > 32 * MB_IN_BYTES ) {
		throw new RuntimeException( 'The journal exceeds 32 MiB. Split the migration into smaller reviewed batches.' );
	}
	$mask = umask( 0077 );
	// phpcs:disable WordPress.WP.AlternativeFunctions -- WP-CLI needs exclusive local creation, flushing and restrictive permissions.
	try {
		$file = fopen( $path, 'xb' );
	} finally {
		umask( $mask );
	}
	if ( ! $file ) {
		throw new RuntimeException( 'Could not create the journal exclusively.' );
	}
	try {
		if ( strlen( $json ) !== fwrite( $file, $json ) || ! fflush( $file ) || ( function_exists( 'fsync' ) && ! fsync( $file ) ) ) {
			throw new RuntimeException( 'Could not persist the complete journal.' );
		}
	} finally {
		fclose( $file );
	}
	// phpcs:enable WordPress.WP.AlternativeFunctions
}

/**
 * Require permission for every approved mutation.
 *
 * @param array $operations Planned operations.
 * @throws RuntimeException If the selected user lacks permission.
 */
function zion_theme_migration_permissions( array $operations ): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		throw new RuntimeException( 'Apply and rollback require an explicit WP-CLI administrator via --user.' );
	}
	foreach ( $operations as $row ) {
		if ( 'option' !== $row['kind'] && ! current_user_can( 'edit_post', $row['id'] ) ) {
			throw new RuntimeException( 'The selected administrator cannot edit a planned record.' );
		}
		foreach ( 'shop' === $row['kind'] ? array_merge( $row['before'], $row['after'] ) : array() as $slug ) {
			if ( ! get_term_by( 'slug', $slug, 'zion_shop' ) ) {
				throw new RuntimeException( 'A planned storefront term is missing.' );
			}
		}
	}
}

/**
 * Apply approved operations or reconcile the rollback snapshot.
 *
 * @param array  $operations Reviewed operations.
 * @param string $side Target state.
 * @throws RuntimeException If a record has drifted beyond the journal.
 */
function zion_theme_migration_reconcile( array $operations, string $side ): void {
	zion_theme_migration_permissions( $operations );
	foreach ( $operations as $row ) {
		$current = zion_theme_migration_state( $row['kind'], $row['id'] );
		$valid   = $current === $row['before'] || ( 'before' === $side && $current === $row['after'] );
		if ( ! $valid && 'before' === $side && 'page' === $row['kind'] ) {
			// A partial page operation can update content before its Elementor edit mode.
			$valid = array_diff_assoc(
				array_diff_key(
					$current,
					array(
						'content' => true,
						'mode'    => true,
					)
				),
				$row['before']
			) === array() &&
				in_array( $current['content'], array( $row['before']['content'], $row['after']['content'] ), true ) &&
				in_array( $current['mode'], array( $row['before']['mode'], $row['after']['mode'] ), true );
		}
		if ( ! $valid ) {
			throw new RuntimeException( 'Clone state has drifted outside the journal. Review changes or restore the verified database backup.' );
		}
	}
	foreach ( 'before' === $side ? array_reverse( $operations ) : $operations as $row ) {
		$current = zion_theme_migration_state( $row['kind'], $row['id'] );
		if ( 'after' === $side && $current !== $row['before'] ) {
			throw new RuntimeException( 'Clone state changed during apply. Review the rollback journal.' );
		}
		if ( $row[ $side ] !== $current ) {
			zion_theme_migration_write( $row, $side );
		}
	}
}

/**
 * Execute the selected operator command.
 *
 * @param array $arguments WP-CLI arguments.
 * @return array
 * @throws RuntimeException For invalid approval, backup or journal input.
 */
function zion_theme_migration_command( array $arguments ): array {
	$mode = $arguments[0] ?? '';
	$site = $arguments[1] ?? '';
	zion_theme_migration_guard( $site );
	add_filter( 'pre_http_request', static fn () => new WP_Error( 'zion_migration_network_blocked', 'Outbound WordPress HTTP is disabled during migration.' ), PHP_INT_MAX );
	if ( 'audit' === $mode ) {
		return zion_theme_migration_audit();
	}
	$identity = array(
		'home'    => $site,
		'siteurl' => site_url( '/' ),
		'blog'    => get_current_blog_id(),
	);
	if ( 'rollback' === $mode ) {
		$journal = zion_theme_migration_read( $arguments[2] ?? '' );
		if ( 1 !== ( $journal['version'] ?? null ) || ( $journal['site'] ?? null ) !== $identity ||
			! is_array( $journal['operations'] ?? null ) || ! hash_equals( zion_theme_migration_hash( $journal['operations'] ), (string) ( $journal['plan_sha256'] ?? '' ) ) ) {
			throw new RuntimeException( 'The journal does not belong to this clone or its checksum changed.' );
		}
		zion_theme_migration_reconcile( $journal['operations'], 'before' );
		return array(
			'mode'     => 'rollback',
			'verified' => count( $journal['operations'] ),
		);
	}
	if ( ! in_array( $mode, array( 'dry-run', 'apply' ), true ) ) {
		throw new RuntimeException( 'Expected audit, dry-run, apply or rollback.' );
	}
	$operations = zion_theme_migration_plan( zion_theme_migration_read( $arguments[2] ?? '' ) );
	$sha        = zion_theme_migration_hash( $operations );
	if ( 'dry-run' === $mode ) {
		return array(
			'mode'        => $mode,
			'site'        => $site,
			'plan_sha256' => $sha,
			'changes'     => array_map(
				static fn ( array $row ): array => 'page' === $row['kind'] ? array(
					'kind'                       => 'page',
					'id'                         => $row['id'],
					'url'                        => get_permalink( $row['id'] ),
					'before_sha256'              => hash( 'sha256', $row['before']['content'] ),
					'after_sha256'               => hash( 'sha256', $row['after']['content'] ),
					'clears_elementor_edit_mode' => (bool) $row['before']['mode'],
				) : $row,
				$operations
			),
		);
	}
	if ( 'zion-theme' !== get_stylesheet() || ! hash_equals( $sha, $arguments[3] ?? '' ) || ! $operations ) {
		throw new RuntimeException( 'Activate Zion on the clone and approve a fresh, nonempty dry-run SHA-256.' );
	}
	zion_theme_migration_permissions( $operations );
	$backup = zion_theme_migration_private_path( $arguments[4] ?? '' );
	$digest = hash_file( 'sha256', $backup );
	if ( ! filesize( $backup ) || ! $digest || ! hash_equals( $digest, $arguments[5] ?? '' ) ) {
		throw new RuntimeException( 'A verified nonempty database backup and its SHA-256 are required.' );
	}
	zion_theme_migration_journal(
		$arguments[6] ?? '',
		array(
			'version'       => 1,
			'site'          => $identity,
			'plan_sha256'   => $sha,
			'backup_sha256' => $digest,
			'operations'    => $operations,
		)
	);
	zion_theme_migration_reconcile( $operations, 'after' );
	return array(
		'mode'        => 'apply',
		'plan_sha256' => $sha,
		'verified'    => count( $operations ),
	);
}

if ( ! defined( 'ZION_THEME_MIGRATION_LIBRARY' ) ) {
	try {
		WP_CLI::line( wp_json_encode( zion_theme_migration_command( $args ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR ) );
	} catch ( Throwable $error ) {
		WP_CLI::error( $error->getMessage() );
	}
}
