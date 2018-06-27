<?php
/**
 * Package archiver.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Provider;

use Cedaro\WP\Plugin\AbstractHookProvider;
use SatisPress\Package;
use SatisPress\PackageManager;

/**
 * Package archiver class.
 *
 * @since 0.3.0
 */
class PackageArchiver extends AbstractHookProvider {
	/**
	 * Package manager.
	 *
	 * @var PackageManager
	 */
	protected $package_manager;

	/**
	 * Initialise SatisPress object.
	 *
	 * @param PackageManager $package_manager Package manager.
	 */
	public function __construct( PackageManager $package_manager ) {
		$this->package_manager = $package_manager;
	}

	/**
	 * Register hooks.
	 *
	 * @since 0.3.0
	 */
	public function register_hooks() {
		// Cache the existing version of a plugin before it's updated.
		if ( apply_filters( 'satispress_cache_packages_before_update', true ) ) {
			add_filter( 'upgrader_pre_install', [ $this, 'cache_package_before_update' ], 10, 2 );
		}

		// Delete the 'satispress_packages' transient.
		add_action( 'upgrader_process_complete', [ $this, 'flush_packages_cache' ] );
		add_action( 'set_site_transient_update_plugins', [ $this, 'flush_packages_cache' ] );
	}

	/**
	 * Cache the current version of a plugin before it's udpated.
	 *
	 * @since 0.2.0
	 *
	 * @param bool  $result Whether the plugin update/install process should continue.
	 * @param array $data   Extra data passed by the update/install process.
	 * @return bool
	 */
	public function cache_package_before_update( bool $result, array $data ): bool {
		if ( empty( $data['plugin'] ) && empty( $data['theme'] ) ) {
			return $result;
		}

		$type    = isset( $data['plugin'] ) ? 'plugin' : 'theme';
		$package = $this->package_manager->get_package( $data[ $type ], $type );

		if ( $package ) {
			$container       = $this->plugin->get_container();
			$release_manager = $container->get( 'release.manager' );

			$release_manager->archive( $package->get_installed_release() );
		}

		return $result;
	}

	/**
	 * Flush the packages.json cache.
	 *
	 * @since 0.2.0
	 */
	public function flush_packages_cache() {
		delete_transient( 'satispress_packages' );
	}
}
