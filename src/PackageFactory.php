<?php
/**
 * PackageFactory class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

use Composer\Semver\VersionParser;
use SatisPress\Exception\InvalidPackageType;
use SatisPress\PackageType\Plugin;
use SatisPress\PackageType\Theme;

/**
 * Simple Factory for creating specific Composer package objects.
 *
 * @since 0.3.0
 */
final class PackageFactory {
	/**
	 * Release manager.
	 *
	 * @var ReleaseManager
	 */
	protected $release_manager;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param ReleaseManager $release_manager Release manager.
	 */
	public function __construct( ReleaseManager $release_manager ) {
		$this->release_manager = $release_manager;
	}

	/**
	 * Create a Composer package object.
	 *
	 * Typical objects returned are Plugin and Theme.
	 *
	 * @since 0.3.0
	 *
	 * @param string $package_type Package type.
	 * @param string $slug         Package slug.
	 * @throws InvalidPackageType If package type not known.
	 * @return Package Package object.
	 */
	public function create( string $package_type, string $slug ): Package {
		$class_name = 'SatisPress\PackageType\\' . ucfirst( $package_type );

		if ( ! class_exists( $class_name ) ) {
			throw InvalidPackageType::forPackageType( $package_type, $class_name );
		}

		$package = new $class_name( $slug );
		$package = $this->add_releases( $package );

		return $package;
	}

	/**
	 * Add releases to a package.
	 *
	 * @since 0.3.0
	 *
	 * @param Package $package Package instance.
	 * @return Package
	 */
	protected function add_releases( Package $package ): Package {
		$releases = $this->release_manager->all( $package );

		if ( $package->is_installed() ) {
			// Add the installed version in case it hasn't been cached yet.
			$installed_version = $package->get_version();
			if ( ! isset( $releases[ $installed_version ] ) ) {
				$releases[ $installed_version ] = new Release( $package, $installed_version );
			}

			// Add a pending update if one is available.
			$update = $this->get_package_update( $package );
			if ( ! empty( $update ) && ( $update instanceof Release ) ) {
				$releases[ $update->get_version() ] = $update;
			}
		}

		uasort( $releases, function( $a, $b ) {
			return version_compare( $b->get_version(), $a->get_version() );
		} );

		foreach ( $releases as $release ) {
			$package->add_release( $release );
		}

		return $package;
	}

	/**
	 * Retrieve a package update.
	 *
	 * @since 0.3.0
	 *
	 * @param Package $package Package instance.
	 * @return Release
	 */
	protected function get_package_update( Package $package ) {
		$release = null;

		if ( $package instanceof Plugin ) {
			$updates = get_site_transient( 'update_plugins' );
			if ( ! empty( $updates->response[ $package->get_basename() ]->package ) ) {
				$update  = $updates->response[ $package->get_basename() ];
				$release = new Release( $package, $update->new_version, $update->package );
			}
		} elseif ( $package instanceof Theme ) {
			$updates = get_site_transient( 'update_themes' );
			if ( ! empty( $updates->response[ $package->get_slug() ]['package'] ) ) {
				$update  = $updates->response[ $package->get_slug() ];
				$release = new Release( $package, $update['new_version'], $update['package'] );
			}
		}

		return $release;
	}
}
