<?php
/**
 * SatisPress repository.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Repository;

/**
 * SatisPress repository class.
 *
 * @since 0.3.0
 */
class SatisPress extends AbstractRepository implements PackageRepository {
	/**
	 * Installed packages repository.
	 *
	 * @var PackageRepository
	 */
	protected $packages;

	/**
	 * Create the SatisPress repository.
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
	 * @return array
	 */
	public function all(): array {
		$packages = [];

		foreach ( $this->get_whitelist() as $type => $slugs ) {
			foreach ( $slugs as $slug ) {
				$package = $this->packages->first_where( [ 'slug' => $slug, 'type' => $type ] );

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
		$plugins = apply_filters( 'satispress_plugins', $options );
		$plugins = array_filter( array_unique( $plugins ) );

		$options = (array) get_option( 'satispress_themes', [] );
		$themes  = apply_filters( 'satispress_themes', $options );
		$themes  = array_filter( array_unique( $themes ) );

		return [
			'plugin' => $plugins,
			'theme'  => $themes,
		];
	}
}
