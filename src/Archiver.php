<?php
/**
 * Archiver.
 *
 * Creates package artifacts in the system's temporary directory. Methods return
 * the absolute path to the artifact. Code making use of this class should move
 * or delete the artifacts as necessary.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

use PclZip;
use SatisPress\Exception\FileDownloadFailed;
use SatisPress\Exception\FileOperationFailed;
use SatisPress\PackageType\Plugin;
use WP_Error;

/**
 * Archiver class.
 *
 * @since 0.3.0
 */
class Archiver {
	/**
	 * Create a package artifact from the installed source.
	 *
	 * @since 0.3.0
	 *
	 * @param Release $release Release instance.
	 * @throws FileOperationFailed If a temporary working directory can't be created.
	 * @throws FileOperationFailed If zip creation fails.
	 * @return string Absolute path to the artifact.
	 */
	public function archive_from_source( Release $release ): string {
		$excludes = apply_filters( 'satispress_archive_excludes', [
			'.',
			'..',
			'.DS_Store',
			'.git',
			'coverage',
			'docs',
			'dist',
			'node_modules',
			'tests',
		], $release );

		$package           = $release->get_package();
		$package_directory = $package->get_path();
		$remove_path       = dirname( $package_directory );

		$files = scandir( $package_directory );
		$files = array_diff( $files, $excludes );

		foreach ( $files as $index => $file ) {
			$files[ $index ] = $package_directory . '/' . $file;
		}

		// Single-file plugins should only include the main plugin file.
		if ( $package instanceof Plugin && false === strpos( $package->get_basename(), '/' ) ) {
			$files       = [ $package->get_file() ];
			$remove_path = $package_directory;
		}

		$filename = $this->get_absolute_path( $release->get_file() );

		if ( ! wp_mkdir_p( dirname( $filename ) ) ) {
			throw new FileOperationFailed( 'Unable to create temporary directory.' );
		}

		$zip = new PclZip( $filename );

		$contents = $zip->create(
			$files,
			PCLZIP_OPT_REMOVE_PATH, $remove_path
		);

		if ( 0 === $contents ) {
			throw new FileOperationFailed( 'Unable to create zip file.' );
		}

		return $filename;
	}

	/**
	 * Create a package artifact from a URL.
	 *
	 * @since 0.3.0
	 *
	 * @param Release $release Release instance.
	 * @throws FileDownloadFailed  If the artifact can't be downloaded.
	 * @throws FileOperationFailed If a temporary working directory can't be created.
	 * @throws FileOperationFailed If a temporary artifact can't be renamed.
	 * @return string Absolute path to the artifact.
	 */
	public function archive_from_url( Release $release ): string {
		include_once ABSPATH . 'wp-admin/includes/file.php';

		$filename = $this->get_absolute_path( $release->get_file() );
		$tmpfname = download_url( $release->get_source_url() );

		if ( is_wp_error( $tmpfname ) ) {
			throw new FileDownloadFailed( 'Artifact download failed.' );
		}

		if ( ! wp_mkdir_p( dirname( $filename ) ) ) {
			throw new FileOperationFailed( 'Unable to create temporary directory.' );
		}

		if ( ! rename( $tmpfname, $filename ) ) {
			throw new FileOperationFailed( 'Unable to rename temporary artifact.' );
		}

		return $filename;
	}

	/**
	 * Retrieve the absolute path to a file.
	 *
	 * @since 0.3.0
	 *
	 * @param string $path Optional. Relative path.
	 * @return string
	 */
	protected function get_absolute_path( string $path = '' ): string {
		return get_temp_dir() . 'satispress/' . ltrim( $path, '/' );
	}
}
