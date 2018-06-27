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
use SatisPress\Release;

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
			add_filter( 'upgrader_pre_install', [ $this, 'archive_source_before_update' ], 10, 2 );
		}

		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'archive_updates' ], 9999 );
		add_filter( 'pre_set_site_transient_update_themes', [ $this, 'archive_updates' ], 9999 );

		// Delete the 'satispress_packages' transient.
		add_action( 'upgrader_process_complete', [ $this, 'flush_packages_cache' ] );
		add_action( 'set_site_transient_update_plugins', [ $this, 'flush_packages_cache' ] );
	}

	/**
	 * Archive updates as they become available.
	 *
	 * @since 0.3.0
	 *
	 * @param object $value Update transient value.
	 * @return object
	 */
	public function archive_updates( $value ) {
		if ( empty( $value->response ) ) {
			return $value;
		}

		// The $id will be a theme slug or the plugin file.
		foreach ( $value->response as $id => $update_data ) {
			// Bail if a URL isn't available.
			if ( empty( $update_data->package ) ) {
				continue;
			}

			// Plugin data is stored as an object. Coerce to an array.
			$update_data = (array) $update_data;

			$type    = isset( $update_data['plugin'] ) ? 'plugin' : 'theme';
			$package = $this->package_manager->get_package( $update_data[ $type ], $type );

			// Bail if the package isn't whitelisted.
			if ( empty( $package ) ) {
				continue;
			}

			$release = new Release(
				$package,
				$update_data['new_version'],
				$update_data['package']
			);

			$this->plugin->get_container()->get( 'release.manager' )->archive( $release );
		}

		return $value;
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
	public function archive_source_before_update( bool $result, array $data ): bool {
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
