<?php
/**
 * Package interface.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

use SatisPress\Exception\InvalidReleaseVersion;

/**
 * Package interface.
 *
 * @since 0.3.0
 */
interface Package {
	/**
	 * Retrieve the author.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_author(): string;

	/**
	 * Retrieve the author URL.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_author_url(): string;

	/**
	 * Retrieve the description.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_description(): string;

	/**
	 * Retrieve the homepage URL.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_homepage(): string;

	/**
	 * Retrieve the name.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_name(): string;

	/**
	 * Retrieve the slug.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_slug(): string;

	/**
	 * Retrieve the package type.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	public function get_type(): string;

	/**
	 * Whether the package is installed.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	public function is_installed(): bool;

	/**
	 * Whether the package has any releases.
	 *
	 * @since 0.3.0
	 *
	 * @return bool
	 */
	public function has_releases(): bool;

	/**
	 * Retrieve a release by version.
	 *
	 * @since 0.3.0
	 *
	 * @param string $version Version string.
	 * @throws InvalidReleaseVersion If the version is invalid.
	 * @return Release
	 */
	public function get_release( string $version ): Release;

	/**
	 * Retrieve releases.
	 *
	 * @since 0.3.0
	 *
	 * @return Release[]
	 */
	public function get_releases(): array;

	/**
	 * Retrieve the latest release.
	 *
	 * @since 0.3.0
	 *
	 * @throws InvalidReleaseVersion If the package doesn't have any releases.
	 * @return Release
	 */
	public function get_latest_release(): Release;

	/**
	 * Retrieve the version for the latest release.
	 *
	 * @since 0.3.0
	 *
	 * @throws InvalidReleaseVersion If the package doesn't have any releases.
	 * @return string
	 */
	public function get_latest_version(): string;

	/**
	 * Retrieve a link to download the latest release.
	 *
	 * @since 0.3.0
	 *
	 * @throws InvalidReleaseVersion If the package doesn't have any releases.
	 * @return string
	 */
	public function get_latest_download_url(): string;
}
