<?php
/**
 * Package builder.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\PackageType;

use ReflectionClass;
use SatisPress\Package;
use SatisPress\Release;
use SatisPress\ReleaseManager;

/**
 * Package builder class.
 *
 * @since 0.3.0
 */
class PackageBuilder {
	/**
	 * Reflection class instance.
	 *
	 * @var ReflectionClass
	 */
	protected $class;

	/**
	 * Package instance.
	 *
	 * @var Package
	 */
	protected $package;

	/**
	 * Package releases.
	 *
	 * @var Release[]
	 */
	protected $releases = [];

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
		$this->package = $package;
		try {
			$this->class = new ReflectionClass( $package );
		} catch ( \ReflectionException $e ) {
		}
		$this->release_manager = $release_manager;
	}

	/**
	 * Finalize the package build.
	 *
	 * @since 0.3.0
	 *
	 * @return Package
	 */
	public function build(): Package {
		$this->set( 'releases', $this->releases );
		return $this->package;
	}

	/**
	 * Set the author.
	 *
	 * @since 0.3.0
	 *
	 * @param string $author Author.
	 * @return $this
	 */
	public function set_author( string $author ): self {
		return $this->set( 'author', $author );
	}

	/**
	 * Set the author URL.
	 *
	 * @since 0.3.0
	 *
	 * @param string $author_url Author URL.
	 * @return $this
	 */
	public function set_author_url( string $author_url ): self {
		return $this->set( 'author_url', $author_url );
	}

	/**
	 * Set the description.
	 *
	 * @since 0.3.0
	 *
	 * @param string $description Description.
	 * @return $this
	 */
	public function set_description( string $description ): self {
		return $this->set( 'description', $description );
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
	 * Set the homepage URL.
	 *
	 * @since 0.3.0
	 *
	 * @param string $url URL.
	 * @return $this
	 */
	public function set_homepage( string $url ): self {
		return $this->set( 'homepage', $url );
	}

	/**
	 * Set whether the package is installed.
	 *
	 * @since 0.3.0
	 *
	 * @param bool $is_installed Whether the package is installed.
	 * @return $this
	 */
	public function set_installed( bool $is_installed ): self {
		return $this->set( 'is_installed', $is_installed );
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
	 * Set the name.
	 *
	 * @since 0.3.0
	 *
	 * @param string $name Package name.
	 * @return $this
	 */
	public function set_name( string $name ): self {
		return $this->set( 'name', $name );
	}

	/**
	 * Set the slug.
	 *
	 * @since 0.3.0
	 *
	 * @param string $slug Slug.
	 * @return $this
	 */
	public function set_slug( string $slug ): self {
		return $this->set( 'slug', $slug );
	}

	/**
	 * Set the type.
	 *
	 * @since 0.3.0
	 *
	 * @param string $type Package type.
	 * @return $this
	 */
	public function set_type( string $type ): self {
		return $this->set( 'type', $type );
	}

	/**
	 * Add a release.
	 *
	 * @since 0.3.0
	 *
	 * @param string $version    Version.
	 * @param string $source_url Optional. Release source URL.
	 * @return $this
	 */
	public function add_release( string $version, string $source_url = '' ): self {
		$this->releases[ $version ] = new Release( $this->package, $version, $source_url );
		return $this;
	}

	/**
	 * Remove a release.
	 *
	 * @since 0.3.0
	 *
	 * @param string $version Version.
	 * @return $this
	 */
	public function remove_release( string $version ): self {
		unset( $this->releases[ $version ] );
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
	public function with_package( Package $package ): self {
		$this
			->set_author( $package->get_author() )
			->set_author_url( $package->get_author_url() )
			->set_description( $package->get_description() )
			->set_name( $package->get_name() )
			->set_homepage( $package->get_homepage() )
			->set_slug( $package->get_slug() )
			->set_type( $package->get_type() );

		if ( $package->is_installed() ) {
			$this
				->set_directory( $package->get_directory() )
				->set_installed_version( $package->get_installed_version() )
				->set_installed( $package->is_installed() );
		}

		foreach ( $package->get_releases() as $release ) {
			$this->add_release( $release->get_version(), $release->get_source_url() );
		}

		return $this;
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
			if ( $update instanceof Release ) {
				$releases[ $update->get_version() ] = $update;
			}
		}

		uasort( $releases, function( Release $a, Release $b ) {
			return version_compare( $b->get_version(), $a->get_version() );
		} );

		foreach ( $releases as $release ) {
			$this->add_release( $release->get_version(), $release->get_source_url() );
		}

		return $this;
	}

	/**
	 * Retrieve a release for a pending theme or plugin update.
	 *
	 * @since 0.3.0
	 *
	 * @param Package $package Package instance.
	 * @return null|Release
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

	/**
	 * Set a property on the package instance.
	 *
	 * Uses the reflection API to assign values to protected properties of the
	 * package instance to make the returned instance immutable.
	 *
	 * @since 0.3.0
	 *
	 * @param string $name  Property name.
	 * @param mixed  $value Property value.
	 * @return $this
	 */
	protected function set( $name, $value ): self {
		$property = $this->class->getProperty( $name );
		$property->setAccessible( true );
		$property->setValue( $this->package, $value );
		return $this;
	}
}
