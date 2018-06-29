<?php
/**
 * Package abstract class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.2.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

use SatisPress\Exception\InvalidReleaseVersion;

/**
 * Abstract Composer package class.
 *
 * Extended by child classes like Plugin and Theme.
 *
 * @since 0.2.0
 */
abstract class Package {
	/**
	 * Releases.
	 *
	 * @var array
	 */
	protected $releases = [];

	/**
	 * Retrieve the package name.
	 *
	 * Includes the vendor prefix.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	public function get_package_name(): string {
		$vendor = apply_filters( 'satispress_vendor', 'satispress' );

		return $vendor . '/' . $this->get_slug();
	}

	/**
	 * Whether the package has any releases.
	 *
	 * @since 0.3.0
	 *
	 * @return boolean
	 */
	public function has_releases(): bool {
		return ! empty( $this->releases );
	}

	/**
	 * Add a release.
	 *
	 * @since 0.3.0
	 *
	 * @param Release $release Release instance.
	 * @return $this
	 */
	public function add_release( Release $release ) {
		$this->releases[ $release->get_version() ] = $release;
		return $this;
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
	public function get_release( $version ): Release {
		if ( ! isset( $this->releases[ $version ] ) ) {
			throw new InvalidReleaseVersion( 'Invalid release version.' );
		}

		return $this->releases[ $version ];
	}

	/**
	 * Retrieve releases.
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	public function get_releases(): array {
		return $this->releases;
	}

	/**
	 * Retrieve the installed release.
	 *
	 * @since 0.3.0
	 *
	 * @return Release
	 */
	public function get_installed_release(): Release {
		return $this->get_release( $this->get_version() );
	}

	/**
	 * Retrieve the latest release version.
	 *
	 * @since 0.3.0
	 *
	 * @throws InvalidReleaseVersion If the package doesn't have any releases.
	 * @return string
	 */
	public function get_latest_release(): Release {
		if ( $this->has_releases() ) {
			return reset( $this->releases );
		}

		throw new InvalidReleaseVersion( 'Invalid release version.' );
	}

	/**
	 * Whether the package is installed.
	 *
	 * @since 0.3.0
	 *
	 * @return boolean
	 */
	abstract public function is_installed(): bool;

	/**
	 * Retrieve the package author.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_author(): string;

	/**
	 * Retrieve the package author's URL.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_author_uri(): string;

	/**
	 * Retrieve the package description.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_description(): string;

	/**
	 * Retrieve the package homepage.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_homepage(): string;

	/**
	 * Retrieve the package name.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_name(): string;

	/**
	 * Retrieve the path to the package.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_path(): string;

	/**
	 * Retrieve the package slug.
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	abstract public function get_slug(): string;

	/**
	 * Retrieve the type of Composer package.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_type(): string;

	/**
	 * Retrieve the package version.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	abstract public function get_version(): string;
}
