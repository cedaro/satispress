<?php
/**
 * Whitelisted packages repository.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Repository;

use SatisPress\Package;

/**
 * Whitelisted packages repository class.
 *
 * @since 0.3.0
 */
class Whitelist extends AbstractRepository implements PackageRepository {
	/**
	 * Installed packages repository.
	 *
	 * @var PackageRepository
	 */
	protected $packages;

	/**
	 * Create the repository.
	 *
	 * @since 0.3.0
	 *
	 * @param PackageRepository $packages Installed packages repository.
	 */
	public function __construct( PackageRepository $packages ) {
		$this->packages = $packages;
	}

	/**
	 * Retrieve all packages in the repository.
	 *
	 * @since 0.3.0
	 *
	 * @return Package[]
	 */
	public function all(): array {
		$packages = [];

		foreach ( $this->get_whitelist() as $type => $slugs ) {
			foreach ( $slugs as $slug ) {
				$package = $this->packages->first_where( compact( 'slug', 'type' ) );

				if ( $package ) {
					$packages[ $package->get_slug() ] = $package;
				}
			}
		}

		return $packages;
	}

	/**
	 * Retrieve a list of whitelisted packages.
	 *
	 * Plugins should be added to the whitelist by hooking into the
	 * 'satispress_plugins' filter and appending a plugin's basename to the
	 * array. The basename is the main plugin file's relative path from the
	 * root plugin directory. Example: plugin-name/plugin-name.php
	 *
	 * Themes should be added by hooking into the 'satispress_themes' filter and
	 * appending the name of the theme directory. Example: genesis
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	protected function get_whitelist(): array {
		$options = (array) get_option( 'satispress_plugins', [] );
		$plugins = array_filter( array_unique( apply_filters( 'satispress_plugins', $options ) ) );

		$options = (array) get_option( 'satispress_themes', [] );
		$themes  = array_filter( array_unique( apply_filters( 'satispress_themes', $options ) ) );

		return [
			'plugin' => $plugins,
			'theme'  => $themes,
		];
	}
}
