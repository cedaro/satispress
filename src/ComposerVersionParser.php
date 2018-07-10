<?php
/**
 * Composer version parser.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

/**
 * Composer version parser class.
 *
 * @package SatisPress
 * @since 0.3.0
 */
final class ComposerVersionParser implements VersionParser {
	/**
	 * Version parser instance.
	 *
	 * @var \Composer\Semver\VersionParser
	 */
	private $parser;

	/**
	 * Initialize the version parser.
	 *
	 * @since 0.3.0
	 *
	 * @param \Composer\Semver\VersionParser $parser Version parser.
	 */
	public function __construct( \Composer\Semver\VersionParser $parser ) {
		$this->parser = $parser;
	}

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
	public function normalize( string $version, string $full_version = null ): string {
		return $this->parser->normalize( $version, $full_version );
	}
}
