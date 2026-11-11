<?php
/**
 * Package Zion Theme and Zion Core as separate installable WordPress ZIPs.
 *
 * @package Zion_Theme
 */

// phpcs:disable WordPress.WP.AlternativeFunctions, WordPress.Security.EscapeOutput -- Standalone CLI with native filesystem operations and plain-text output.

/**
 * Build one archive from the component's distributable paths.
 *
 * @param string $root Repository path.
 * @param string $slug Component directory name.
 * @param array  $paths Distributable paths grouped by component.
 * @throws RuntimeException If the component cannot be packaged.
 */
function zion_theme_package_component( string $root, string $slug, array $paths ): void {
	$source = $root . '/' . $paths['source'];
	$header = file_get_contents( $source . '/' . $paths['files'][0] );
	if ( false === $header || ! preg_match( '/^\s*\*?\s*Version:[ \t]*([0-9]+\.[0-9]+\.[0-9]+(?:[-+][0-9A-Za-z.-]+)?)[ \t]*\r?$/mi', $header, $version ) ) {
		throw new RuntimeException( "Invalid version header for {$slug}." );
	}

	$destination = $root . '/build/' . $slug . '-' . $version[1] . '.zip';
	$temporary   = tempnam( $root . '/build', '.zion-' );
	if ( false === $temporary ) {
		throw new RuntimeException( "Cannot create a temporary archive for {$slug}." );
	}

	$zip    = new ZipArchive();
	$opened = false;
	try {
		if ( true !== $zip->open( $temporary, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
			throw new RuntimeException( "Cannot open the archive for {$slug}." );
		}
		$opened = true;

		foreach ( $paths['files'] as $file ) {
			if ( is_link( $source . '/' . $file ) || ! is_file( $source . '/' . $file ) || ! $zip->addFile( $source . '/' . $file, $slug . '/' . $file ) ) {
				throw new RuntimeException( "Cannot archive {$slug}/{$file}." );
			}
		}

		foreach ( $paths['directories'] as $directory => $extensions ) {
			$directory_path = $source . '/' . $directory;
			if ( is_link( $directory_path ) ) {
				throw new RuntimeException( "Cannot archive a symbolic link at {$slug}/{$directory}." );
			}

			$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $directory_path, FilesystemIterator::SKIP_DOTS ) );
			foreach ( $files as $file ) {
				if ( $file->isLink() || ! $file->isFile() || ! in_array( strtolower( $file->getExtension() ), $extensions, true ) ) {
					continue;
				}
				$relative_path = substr( $file->getPathname(), strlen( $source ) + 1 );
				$relative_path = str_replace( '\\', '/', $relative_path );
				if ( preg_match( '~(^|/)\.~', $relative_path ) ) {
					continue;
				}
				if ( ! $zip->addFile( $file->getPathname(), $slug . '/' . $relative_path ) ) {
					throw new RuntimeException( "Cannot archive {$slug}/{$relative_path}." );
				}
			}
		}

		$closed = $zip->close();
		$opened = false;
		if ( ! $closed || ! rename( $temporary, $destination ) ) {
			throw new RuntimeException( "Cannot write {$destination}." );
		}
		echo $destination, PHP_EOL;
	} finally {
		if ( $opened ) {
			$zip->close();
		}
		if ( is_file( $temporary ) ) {
			unlink( $temporary );
		}
	}
}

try {
	if ( ! class_exists( 'ZipArchive' ) ) {
		throw new RuntimeException( 'Enable the PHP zip extension to build the archives.' );
	}

	$zion_theme_root = dirname( __DIR__ );
	if ( ! is_dir( $zion_theme_root . '/build' ) && ! mkdir( $zion_theme_root . '/build', 0755 ) && ! is_dir( $zion_theme_root . '/build' ) ) {
		throw new RuntimeException( 'Cannot create the build directory.' );
	}

	zion_theme_package_component(
		$zion_theme_root,
		'zion-theme',
		array(
			'source'      => 'themes/zion-theme',
			'files'       => array( 'style.css', 'functions.php', 'theme.json', 'assets/admin/control-center.js', 'assets/nexus-cube.svg' ),
			'directories' => array(
				'assets/blocks' => array( 'js' ),
				'assets/admin'  => array( 'css', 'png' ),
				'assets/brand'  => array( 'webp' ),
				'assets/dist'   => array( 'css' ),
				'includes'      => array( 'php' ),
				'parts'         => array( 'html' ),
				'patterns'      => array( 'php' ),
				'templates'     => array( 'html' ),
			),
		)
	);
	zion_theme_package_component(
		$zion_theme_root,
		'zion-core',
		array(
			'source'      => 'plugins/zion-core',
			'files'       => array( 'zion-core.php' ),
			'directories' => array(
				'includes' => array( 'php' ),
				'assets'   => array( 'js' ),
			),
		)
	);
} catch ( Throwable $zion_theme_error ) {
	fwrite( STDERR, $zion_theme_error->getMessage() . PHP_EOL );
	exit( 1 );
}
