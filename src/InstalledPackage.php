<?php
/**
 * Installed package interface.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

/**
 * Installed package interface.
 *
 * @since 0.3.0
 */
interface InstalledPackage extends Package {
	/**
	 * Retrieve the package directory.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_directory(): string;

	/**
	 * Retrieve the list of files in the package.
	 *
	 * @since 0.3.0
	 *
	 * @param array $excludes Optional. Array of file names to exclude.
	 * @return array
	 */
	public function get_files( array $excludes = [] ): array;

	/**
	 * Retrieve the path to a file in the package.
	 *
	 * @since 0.3.0
	 *
	 * @param string $path Optional. Path relative to the package root.
	 * @return string
	 */
	public function get_path( string $path = '' ): string;

	/**
	 * Retrieve the installed version.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_installed_version(): string;

	/**
	 * Retrieve the installed release.
	 *
	 * @since 0.3.0
	 *
	 * @return Release
	 */
	public function get_installed_release(): Release;

	/**
	 * Whether an update is available.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	public function is_update_available(): bool;
}
