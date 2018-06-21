<?php
/**
 * VersionParser interface
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.1.0
 */

namespace SatisPress;

/**
 * Version parser interface.
 *
 * @package SatisPress
 * @since 0.3.0
 */
interface VersionParser {
	/**
	 * Normalizes a version string to be able to perform comparisons on it.
	 *
	 * @throws \UnexpectedValueException Thrown when given an invalid version string.
	 *
	 * @param string $version      Version string.
	 * @param string $full_version Optional complete version string to give more context.
	 * @return string
	 */
	public function normalize( $version, $full_version = null );
}
