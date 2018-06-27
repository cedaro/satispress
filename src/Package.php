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
	 * @return Release|null
	 */
	public function get_release( $version ) {
		$release = null;

		if ( isset( $this->releases[ $version ] ) ) {
			$release = $this->releases[ $version ];
		}

		return $release;
	}

	/**
	 * Retrieve the installed release.
	 *
	 * @since 0.3.0
	 *
	 * @return Release|null
	 */
	public function get_installed_release() {
		$release = null;

		if ( $this->is_installed() ) {
			$release = $this->get_release( $this->get_version() );
		}

		return $release;
	}

	/**
	 * Whether the package has any releases.
	 *
	 * @since 0.3.0
	 *
	 * @return boolean
	 */
	public function has_releases() {
		return ! empty( $this->releases );
	}

	/**
	 * Retrieve releases.
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	public function get_releases() {
		return $this->releases;
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
