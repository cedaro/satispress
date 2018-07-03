<?php
/**
 * PackageManager class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

use Exception;

/**
 * PackageManager class.
 *
 * @since 0.1.0
 */
class PackageManager {
	/**
	 * Package factory.
	 *
	 * @var PackageFactory
	 */
	protected $factory;

	/**
	 * Initialize the package manager.
	 *
	 * @param PackageFactory $factory Package factory.
	 */
	public function __construct( PackageFactory $factory ) {
		$this->factory = $factory;
	}

	/**
	 * Whether a package has been whitelisted.
	 *
	 * @since 0.3.0
	 *
	 * @param string $slug Package slug.
	 * @param string $type Package type.
	 * @return bool
	 */
	public function has_package( string $slug, string $type ): bool {
		$whitelist = $this->get_whitelist();
		return in_array( $slug, $whitelist[ $type ], true );
	}

	/**
	 * Retrieve a package by its slug and type.
	 *
	 * @since 0.2.0
	 *
	 * @throws Exception If package type not known.
	 *
	 * @param string $slug Package slug (plugin basename or theme directory name).
	 * @param string $type Package type.
	 * @return Package
	 */
	public function get_package( string $slug, string $type ): Package {
		return $this->factory->create( $type, $slug );
	}

	/**
	 * Retrieve a list of packages.
	 *
	 * @since 0.2.0
	 *
	 * @return array
	 */
	public function get_packages(): array {
		$packages  = [];
		$whitelist = $this->get_whitelist();

		foreach ( $whitelist as $type => $identifiers ) {
			if ( empty( $identifiers ) ) {
				continue;
			}

			foreach ( $identifiers as $identifier ) {
				try {
					$package = $this->get_package( $identifier, $type );

					if ( $package && $package->is_installed() ) {
						$packages[ $package->get_slug() ] = $package;
					}
				// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				} catch ( Exception $e ) {
					// noop.
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
	 * @return array
	 */
	protected function get_whitelist(): array {
		$plugins = apply_filters( 'satispress_plugins', [] );
		$themes  = apply_filters( 'satispress_themes', [] );

		// @todo Implement these through a filter instead.
		$options = (array) get_option( 'satispress_plugins', [] );
		$plugins = array_filter( array_unique( array_merge( $plugins, $options ) ) );

		$options = (array) get_option( 'satispress_themes', [] );
		$themes  = array_filter( array_unique( array_merge( $themes, $options ) ) );

		return [
			'plugin' => $plugins,
			'theme'  => $themes,
		];
	}
}
