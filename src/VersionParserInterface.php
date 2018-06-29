<?php
/**
 * VersionParser interface
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

/**
 * Version parser interface.
 *
 * @package SatisPress
 * @since 0.3.0
 */
interface VersionParserInterface {
	/**
	 * Normalizes a version string to be able to perform comparisons on it.
	 *
	 * @since 0.3.0
	 *
	 * @throws \UnexpectedValueException Thrown when given an invalid version string.
	 *
	 * @param string $version      Version string.
	 * @param string $full_version Optional complete version string to give more context.
	 * @return string Normalized version string.
	 */
	public function normalize( string $version, string $full_version = null ): string;
}
