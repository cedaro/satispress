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
use Psr\Container\ContainerInterface;
use SatisPress\Exception\ExceptionInterface;
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
	 * Container.
	 *
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param ContainerInterface $container Container.
	 */
	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}

	/**
	 * Register hooks.
	 *
	 * @since 0.3.0
	 */
	public function register_hooks() {
		add_action( 'add_option_satispress_plugins', [ $this, 'archive_on_option_add' ], 10, 2 );
		add_action( 'add_option_satispress_themes', [ $this, 'archive_on_option_add' ], 10, 2 );
		add_action( 'update_option_satispress_plugins', [ $this, 'archive_on_option_update' ], 10, 3 );
		add_action( 'update_option_satispress_themes', [ $this, 'archive_on_option_update' ], 10, 3 );
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'archive_updates' ], 9999 );
		add_filter( 'pre_set_site_transient_update_themes', [ $this, 'archive_updates' ], 9999 );

		// Delete the 'satispress_packages' transient.
		add_action( 'upgrader_process_complete', [ $this, 'flush_packages_cache' ] );
		add_action( 'set_site_transient_update_plugins', [ $this, 'flush_packages_cache' ] );
		add_action( 'add_option_satispress_plugins', [ $this, 'flush_packages_cache' ] );
		add_action( 'add_option_satispress_themes', [ $this, 'flush_packages_cache' ] );
		add_action( 'update_option_satispress_plugins', [ $this, 'flush_packages_cache' ] );
		add_action( 'update_option_satispress_themes', [ $this, 'flush_packages_cache' ] );
	}

	/**
	 * Archive packages when they're added to the whitelist.
	 *
	 * Archiving packages when they're whitelisted helps ensure a checksum can
	 * be included in packages.json.
	 *
	 * @since 0.3.0
	 *
	 * @param string $option_name Option name.
	 * @param array  $value       Value.
	 */
	public function archive_on_option_add( string $option_name, $value ) {
		if ( empty( $value ) || ! is_array( $value ) ) {
			return;
		}

		$type = 'satispress_plugins' === $option_name ? 'plugin' : 'theme';
		$this->archive_packages( $value, $type );
	}

	/**
	 * Archive packages when they're added to the whitelist.
	 *
	 * Archiving packages when they're whitelisted helps ensure a checksum can
	 * be included in packages.json.
	 *
	 * @since 0.3.0
	 *
	 * @param array  $old_value   Old value.
	 * @param array  $value       New value.
	 * @param string $option_name Option name.
	 */
	public function archive_on_option_update( $old_value, $value, string $option_name ) {
		$slugs = array_diff( (array) $value, (array) $old_value );

		if ( empty( $slugs ) ) {
			return;
		}

		$type = 'satispress_plugins' === $option_name ? 'plugin' : 'theme';
		$this->archive_packages( $slugs, $type );
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

			$package_manager = $this->container->get( 'package.manager' );
			$type            = isset( $update_data['plugin'] ) ? 'plugin' : 'theme';
			$package         = $package_manager->get_package( $update_data[ $type ], $type );

			// Bail if the package isn't whitelisted.
			if ( empty( $package ) ) {
				continue;
			}

			$release = new Release(
				$package,
				$update_data['new_version'],
				$update_data['package']
			);

			try {
				$this->container->get( 'release.manager' )->archive( $release );
			} catch( ExceptionInterface $e ) { }
		}

		return $value;
	}

	/**
	 * Flush the packages.json cache.
	 *
	 * @since 0.2.0
	 */
	public function flush_packages_cache() {
		delete_transient( 'satispress_packages' );
	}

	/**
	 * Archive n list of packages.
	 *
	 * @since 0.3.0
	 *
	 * @param array $slugs Array of package slugs.
	 * @param string $type Type of packages.
	 */
	protected function archive_packages( array $slugs, string $type ) {
		foreach ( $slugs as $slug ) {
			$this->archive_package( $slug, $type );
		}
	}

	/**
	 * Archive a package.
	 *
	 * @since 0.3.0
	 *
	 * @param string $slug Packge slug.
	 * @param string $type Type of package.
	 * @return Package
	 */
	protected function archive_package( string $slug, string $type ): Package {
		$package_manager = $this->container->get( 'package.manager' );
		$release_manager = $this->container->get( 'release.manager' );

		try {
			$package = $package_manager->get_package( $slug, $type );
			$release_manager->archive( $package->get_installed_release() );
		} catch( ExceptionInterface $e ) { }

		return $package;
	}
}
