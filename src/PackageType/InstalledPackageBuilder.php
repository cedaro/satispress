<?php
/**
 * Installed package builder.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\PackageType;

use ReflectionClass;
use SatisPress\InstalledPackage;
use SatisPress\Package;
use SatisPress\Release;
use SatisPress\ReleaseManager;

/**
 * Installed package builder class.
 *
 * @since 0.3.0
 */
class InstalledPackageBuilder extends PackageBuilder {
	/**
	 * Release manager.
	 *
	 * @var ReleaseManager
	 */
	protected $release_manager;

	/**
	 * Create a builder for installed packages.
	 *
	 * @since 0.3.0
	 *
	 * @param Package        $package         Package instance to build.
	 * @param ReleaseManager $release_manager Release manager.
	 */
	public function __construct( Package $package, ReleaseManager $release_manager ) {
		$this->package         = $package;
		$this->class           = new ReflectionClass( $package );
		$this->release_manager = $release_manager;
	}

	/**
	 * Set a package's directory.
	 *
	 * @since 0.3.0
	 *
	 * @param string $directory Absolute path to the package directory.
	 * @return $this
	 */
	public function set_directory( string $directory ): self {
		return $this->set( 'directory', rtrim( $directory, '/' ) . '/' );
	}

	/**
	 * Set whether the package is installed.
	 *
	 * @since 0.3.0
	 *
	 * @param boolean $is_installed Whether the package is installed.
	 * @return $this
	 */
	public function set_installed( bool $is_installed ): self {
		return $this->set( 'is_installed', (bool) $is_installed );
	}

	/**
	 * Set the installed version.
	 *
	 * @since 0.3.0
	 *
	 * @param string $version Version.
	 * @return $this
	 */
	public function set_installed_version( string $version ): self {
		return $this->set( 'installed_version', $version );
	}

	/**
	 * Add cached releases to a package.
	 *
	 * This must be called after setting the installed state and version for
	 * the package.
	 *
	 * @todo Rename this?
	 *
	 * @since 0.3.0
	 *
	 * @return $this
	 */
	public function add_cached_releases(): self {
		$releases = $this->release_manager->all( $this->package );

		if ( $this->package->is_installed() ) {
			// Add the installed version in case it hasn't been cached yet.
			$installed_version = $this->package->get_installed_version();
			if ( ! isset( $releases[ $installed_version ] ) ) {
				$releases[ $installed_version ] = new Release( $this->package, $installed_version );
			}

			// Add a pending update if one is available.
			$update = $this->get_package_update( $this->package );
			if ( ! empty( $update ) && ( $update instanceof Release ) ) {
				$releases[ $update->get_version() ] = $update;
			}
		}

		uasort( $releases, function( $a, $b ) {
			return version_compare( $b->get_version(), $a->get_version() );
		} );

		foreach ( $releases as $release ) {
			$this->add_release( $release->get_version(), $release->get_source_url() );
		}

		return $this;
	}

	/**
	 * Set properties from an existing package.
	 *
	 * @since 0.3.0
	 *
	 * @param Package $package Package.
	 * @return $this
	 */
	public function with_package( Package $package ): PackageBuilder {
		parent::with_package( $package );

		if ( $package instanceof InstalledPackage ) {
			$this
				->set_directory( $package->get_directory() )
				->set_installed_version( $package->get_installed_version() )
				->set_installed( $package->is_installed() );
		}

		return $this;
	}

	/**
	 * Retrieve a release for a pending theme or plugin update.
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
