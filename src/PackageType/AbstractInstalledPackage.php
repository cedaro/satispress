<?php
/**
 * Base installed package class.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\PackageType;

use SatisPress\InstalledPackage;
use SatisPress\Release;

/**
 * Abstract installed package class.
 *
 * @since 0.3.0
 */
abstract class AbstractInstalledPackage extends BasePackage implements InstalledPackage {
	/**
	 * Absolute path to the package directory.
	 *
	 * @var string
	 */
	protected $directory;

	/**
	 * Installed version.
	 *
	 * @var string
	 */
	protected $installed_version = '';

	/**
	 * Retrieve the package directory.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_directory(): string {
		return $this->directory;
	}

	/**
	 * Retrieve the list of files in the package.
	 *
	 * @since 0.3.0
	 *
	 * @param array $excludes Optional. Array of file names to exclude.
	 * @return array
	 */
	public function get_files( array $excludes = [] ): array {
		$directory = $this->get_directory();
		$files     = scandir( $directory, SCANDIR_SORT_NONE );
		$files     = array_values( array_diff( $files, $excludes, [ '.', '..' ] ) );

		return array_map( function( $file ) {
			return $this->get_path( $file );
		}, $files );
	}

	/**
	 * Retrieve the path to a file in the package.
	 *
	 * @since 0.3.0
	 *
	 * @param string $path Optional. Path relative to the package root.
	 * @return string
	 */
	public function get_path( string $path = '' ): string {
		return $this->directory . ltrim( $path, '/' );
	}

	/**
	 * Retrieve the installed version.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_installed_version(): string {
		return $this->installed_version;
	}

	/**
	 * Retrieve the installed release.
	 *
	 * @since 0.3.0
	 *
	 * @return Release
	 */
	public function get_installed_release(): Release {
		if ( $this->is_installed() ) {
			return $this->get_release( $this->get_installed_version() );
		}

		// @todo Throw an exception.
		return null;
	}

	/**
	 * Whether a given release is the currently installed version.
	 *
	 * @since 0.3.0
	 *
	 * @param Release $release Release.
	 * @return bool
	 */
	public function is_installed_release( Release $release ): bool {
		return version_compare( $release->get_version(), $this->get_installed_version(), '=' );
	}

	/**
	 * Whether an update is available.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	public function is_update_available(): bool {
		return $this->is_installed() && version_compare( $this->get_installed_version(), $this->get_latest_version(), '<' );
	}
}
