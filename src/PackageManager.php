<?php
/**
 * PackageManager class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

namespace SatisPress;

/**
 * PackageManager class.
 *
 * @since 0.1.0
 */
class PackageManager {
	/**
	 * Path to where packages are cached.
	 *
	 * @var string
	 */
	protected $cache_path;

	/**
	 * Initialise SatisPress object.
	 *
	 * @param string $cache_path Path to where packages are cached.
	 */
	public function __construct( $cache_path ) {
		$this->cache_path = $cache_path;
	}

	/**
	 * Retrieve a package by its slug and type.
	 *
	 * @since 0.2.0
	 *
	 * @param string $slug Package slug (plugin basename or theme directory name).
	 * @param string $type Package type.
	 *
	 * @return Package
	 */
	public function get_package( $slug, $type ) {
		$package_factory = new PackageFactory();

		return $package_factory->create( $type, $slug, $this->cache_path );
	}

	/**
	 * Retrieve a list of packages.
	 *
	 * @since 0.2.0
	 *
	 * @return array
	 */
	public function get_packages() {
		$packages  = [];
		$whitelist = $this->get_whitelist();

		foreach ( $whitelist as $type => $identifiers ) {
			if ( empty( $identifiers ) ) {
				continue;
			}

			foreach ( $identifiers as $identifier ) {
				$package = $this->get_package( $identifier, $type );
				if ( $package && $package->is_installed() && '' !== $package->get_version_normalized() ) {
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
	 * root plugin directory. Example: simple-image-widget/simple-image-widget.php
	 *
	 * Themes should be added by hooking into the 'satispress_themes' filter and
	 * appending the name of the theme directory. Example: genesis
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	protected function get_whitelist() {
		$plugins = apply_filters( 'satispress_plugins', [] );
		$themes  = apply_filters( 'satispress_themes', [] );

		// @todo Implement these through a filter instead.
		$options = (array) get_option( 'satispress_plugins' );
		$plugins = array_filter( array_unique( array_merge( $plugins, $options ) ) );

		$options = (array) get_option( 'satispress_themes', [] );
		$themes  = array_filter( array_unique( array_merge( $themes, $options ) ) );

		return [
			'plugin' => $plugins,
			'theme'  => $themes,
		];
	}
}
