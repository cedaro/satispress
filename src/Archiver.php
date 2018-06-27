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
	 * @return string|WP_Error Absolute path to the artifact or an error on failure.
	 */
	public function archive_from_source( Release $release ) {
		$excludes = apply_filters( 'satispress_archive_excludes', [
			'.',
			'..',
			'.DS_Store',
			'.git',
			'coverage',
			'dist',
			'node_modules',
			'tests',
		], $release );

		$package_directory = $release->get_package()->get_path();

		$files = scandir( $package_directory );
		$files = array_diff( $files, $excludes );

		foreach ( $files as $index => $file ) {
			$files[ $index ] = $package_directory . '/' . $file;
		}

		$filename = $this->get_absolute_path( $release->get_file() );

		if ( ! wp_mkdir_p( dirname( $filename ) ) ) {
			return new WP_Error( 'mkdir_failed', esc_html__( 'Unable to create temporary directory.', 'satispress' ) );
		}

		$zip = new PclZip( $filename );

		$contents = $zip->create(
			$files,
			PCLZIP_OPT_REMOVE_PATH, dirname( $package_directory )
		);

		if ( 0 === $contents ) {
			return new WP_Error( 'pclzip_create_failed', esc_html__( 'Unable to create archive.', 'satispress' ) );
		}

		return $filename;
	}

	/**
	 * Create a package artifact from a URL.
	 *
	 * @since 0.3.0
	 *
	 * @param Release $release Release instance.
	 * @return string|WP_Error Absolute path to the artifact or an error on failure.
	 */
	public function archive_from_url( Release $release ) {
		include_once ABSPATH . 'wp-admin/includes/file.php';

		$filename = $this->get_absolute_path( $release->get_file() );
		$tmpfname = download_url( $release->get_source_url() );

		if ( is_wp_error( $tmpfname ) ) {
			return $tmpfname;
		}

		if ( ! wp_mkdir_p( dirname( $filename ) ) ) {
			return new WP_Error( 'mkdir_failed', esc_html__( 'Unable to create temporary directory.', 'satispress' ) );
		}

		if ( ! rename( $tmpfname, $filename ) ) {
			return new WP_Error( 'file_rename_failed', esc_html__( 'Unable to rename temporary artifact.', 'satispress' ) );
		}

		return $filename;
	}

	/**
	 * Retrieve the absolute path to a file.
	 *
	 * @since 0.3.0
	 *
	 * @param string $path Relative path.
	 * @return string
	 */
	protected function get_absolute_path( $path = '' ): string {
		return get_temp_dir() . 'satispress/' . ltrim( $path, '/' );
	}
}
