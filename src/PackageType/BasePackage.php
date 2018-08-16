<?php
/**
 * Base package.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\PackageType;

use SatisPress\Exception\InvalidReleaseVersion;
use SatisPress\Exception\PackageNotInstalled;
use SatisPress\Package;
use SatisPress\Release;

/**
 * Base package class.
 *
 * @since 0.3.0
 */
class BasePackage implements \ArrayAccess, Package {
	/**
	 * Package name.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Author.
	 *
	 * @var string
	 */
	protected $author = '';

	/**
	 * Author URL.
	 *
	 * @var string
	 */
	protected $author_url = '';

	/**
	 * Description.
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * Absolute path to the package directory.
	 *
	 * @var string
	 */
	protected $directory;

	/**
	 * Homepage URL.
	 *
	 * @var string
	 */
	protected $homepage = '';

	/**
	 * Whether the package is installed.
	 *
	 * @var bool
	 */
	protected $is_installed = false;

	/**
	 * Installed version.
	 *
	 * @var string
	 */
	protected $installed_version = '';

	/**
	 * Releases.
	 *
	 * @var Release[]
	 */
	protected $releases = [];

	/**
	 * Package slug.
	 *
	 * @var string
	 */
	protected $slug = '';

	/**
	 * Package type.
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 * Magic setter.
	 *
	 * @since 0.3.0
	 *
	 * @param string $name  Property name.
	 * @param mixed  $value Property value.
	 */
	public function __set( string $name, $value ) {
		// Don't allow undefined properties to be set.
	}

	/**
	 * Retrieve the author.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_author(): string {
		return $this->author;
	}

	/**
	 * Retrieve the author URL.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_author_url(): string {
		return $this->author_url;
	}

	/**
	 * Retrieve the description.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_description(): string {
		return $this->description;
	}

	/**
	 * Retrieve the package directory.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_directory(): string {
		return $this->directory;
	}

	/**
	 * Retrieve the list of files in the package.
	 *
	 * @since 0.3.0
	 *
	 * @param array $excludes Optional. Array of file names to exclude.
	 * @throws PackageNotInstalled If the package is not installed.
	 * @return array
	 */
	public function get_files( array $excludes = [] ): array {
		if ( ! $this->is_installed() ) {
			throw PackageNotInstalled::forInvalidMethodCall( __FUNCTION__, $this );
		}

		$directory = $this->get_directory();
		$files     = scandir( $directory, SCANDIR_SORT_NONE );
		$files     = array_values( array_diff( $files, $excludes, [ '.', '..' ] ) );

		return array_map( function( $file ) {
			return $this->get_path( $file );
		}, $files );
	}

	/**
	 * Retrieve the homepage URL.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_homepage(): string {
		return $this->homepage;
	}

	/**
	 * Retrieve the name.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Retrieve the path to a file in the package.
	 *
	 * @since 0.3.0
	 *
	 * @param string $path Optional. Path relative to the package root.
	 * @return string
	 */
	public function get_path( string $path = '' ): string {
		return $this->directory . ltrim( $path, '/' );
	}

	/**
	 * Retrieve the slug.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Retrieve the package type.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Whether the package is installed.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	public function is_installed(): bool {
		return $this->is_installed;
	}

	/**
	 * Whether the package has any releases.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	public function has_releases(): bool {
		return ! empty( $this->releases );
	}

	/**
	 * Retrieve a release by version.
	 *
	 * @since 0.3.0
	 *
	 * @param string $version Version string.
	 * @throws InvalidReleaseVersion If the version is invalid.
	 * @return Release
	 */
	public function get_release( string $version ): Release {
		if ( ! isset( $this->releases[ $version ] ) ) {
			throw InvalidReleaseVersion::fromVersion( $version, $this->get_name() );
		}

		return $this->releases[ $version ];
	}

	/**
	 * Retrieve releases.
	 *
	 * @since 0.3.0
	 *
	 * @return Release[]
	 */
	public function get_releases(): array {
		return $this->releases;
	}

	/**
	 * Retrieve the installed version.
	 *
	 * @since 0.3.0
	 *
	 * @throws PackageNotInstalled If the package is not installed.
	 * @return string
	 */
	public function get_installed_version(): string {
		if ( ! $this->is_installed() ) {
			throw PackageNotInstalled::forInvalidMethodCall( __FUNCTION__, $this );
		}

		return $this->installed_version;
	}

	/**
	 * Retrieve the installed release.
	 *
	 * @since 0.3.0
	 *
	 * @throws PackageNotInstalled If the package is not installed.
	 * @return Release
	 */
	public function get_installed_release(): Release {
		if ( ! $this->is_installed() ) {
			throw PackageNotInstalled::forInvalidMethodCall( __FUNCTION__, $this );
		}

		return $this->get_release( $this->get_installed_version() );
	}

	/**
	 * Whether a given release is the currently installed version.
	 *
	 * @since 0.3.0
	 *
	 * @param Release $release Release.
	 * @return bool
	 */
	public function is_installed_release( Release $release ): bool {
		if ( ! $this->is_installed() ) {
			return false;
		}

		return version_compare( $release->get_version(), $this->get_installed_version(), '=' );
	}

	/**
	 * Retrieve the version for the latest release.
	 *
	 * @since 0.3.0
	 *
	 * @throws InvalidReleaseVersion If the package doesn't have any releases.
	 * @return string
	 */
	public function get_latest_version(): string {
		return $this->get_latest_release()->get_version();
	}

	/**
	 * Retrieve the latest release.
	 *
	 * @since 0.3.0
	 *
	 * @throws InvalidReleaseVersion If the package doesn't have any releases.
	 * @return Release
	 */
	public function get_latest_release(): Release {
		if ( $this->has_releases() ) {
			return reset( $this->releases );
		}

		throw InvalidReleaseVersion::hasNoReleases( $this->get_name() );
	}

	/**
	 * Retrieve a link to download the latest release.
	 *
	 * @since 0.3.0
	 *
	 * @throws InvalidReleaseVersion If the package doesn't have any releases.
	 * @return string
	 */
	public function get_latest_download_url(): string {
		$url = $this->get_latest_release()->get_download_url();
		$url = substr( $url, 0, strrpos( $url, '/' ) );
		return $url . '/latest';
	}

	/**
	 * Whether an update is available.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	public function is_update_available(): bool {
		return $this->is_installed() && version_compare( $this->get_installed_version(), $this->get_latest_version(), '<' );
	}

	/**
	 * Whether a property exists.
	 *
	 * Checks for an accessor method rather than the actual property.
	 *
	 * @since 0.3.0
	 *
	 * @param string $name Property name.
	 * @return bool
	 */
	public function offsetExists( $name ): bool {
		return method_exists( $this, "get_{$name}" );
	}

	/**
	 * Retrieve a property value.
	 *
	 * @since 0.3.0
	 *
	 * @param string $name Property name.
	 * @return mixed
	 */
	public function offsetGet( $name ) {
		$method = "get_{$name}";

		if ( ! method_exists( $this, $method ) ) {
			return null;
		}

		return $this->$method();
	}

	/**
	 * Set a property value.
	 *
	 * @since 0.3.0
	 *
	 * @param string $name  Property name.
	 * @param array  $value Property value.
	 */
	public function offsetSet( $name, $value ) {
		// Prevent properties from being modified.
	}

	/**
	 * Unset a property.
	 *
	 * @since 0.3.0
	 *
	 * @param string $name Property name.
	 */
	public function offsetUnset( $name ) {
		// Prevent properties from being modified.
	}
}
