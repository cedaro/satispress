<?php
/**
 * VersionParser class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.1.0
 */

namespace SatisPress;

/**
 * Simplified version parser from Composer.
 *
 * Latest refresh 2018-06-21.
 *
 * @package SatisPress
 * @since 0.1.0
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @link https://github.com/composer/semver/blob/2b303e43d14d15cc90c8e8db4a1cdb6259f1a5c5/src/VersionParser.php
 */
class VersionParser {

	/**
	 * Regex to match pre-release data (sort of).
	 *
	 * Due to backwards compatibility:
	 *   - Instead of enforcing hyphen, an underscore, dot or nothing at all are also accepted.
	 *   - Only stabilities as recognized by Composer are allowed to precede a numerical identifier.
	 *   - Numerical-only pre-release identifiers are not supported, see tests.
	 *
	 *                        |--------------|
	 * [major].[minor].[patch] -[pre-release] +[build-metadata]
	 *
	 * @var string
	 */
	private static $modifierRegex = '[._-]?(?:(stable|beta|b|RC|alpha|a|patch|pl|p)((?:[.-]?\d+)*+)?)?([.-]?dev)?';

	/**
	 * Normalizes a version string to be able to perform comparisons on it.
	 *
	 * @throws \UnexpectedValueException Thrown when given an invalid version string.
	 *
	 * @param string $version     Version string.
	 * @param string $fullVersion Optional complete version string to give more context.
	 * @return string
	 */
	public static function normalize( $version, $fullVersion = null ) {
		$version = trim( $version );

		if ( null === $fullVersion ) {
			$fullVersion = $version;
		}

		// Strip off aliasing.
		if ( preg_match( '{^([^,\s]++) ++as ++([^,\s]++)$}', $version, $match ) ) {
			$version = $match[1];
		}

		// Match master-like branches.
		if ( preg_match( '{^(?:dev-)?(?:master|trunk|default)$}i', $version ) ) {
			return '9999999-dev';
		}

		// If requirement is branch-like, use full name.
		if ( 'dev-' === strtolower( substr( $version, 0, 4 ) ) ) {
			return 'dev-' . substr( $version, 4 );
		}

		// Strip off build metadata.
		if ( preg_match( '{^([^,\s+]++)\+[^\s]++$}', $version, $match ) ) {
			$version = $match[1];
		}

		// Match classical versioning.
		if ( preg_match( '{^v?(\d{1,5})(\.\d++)?(\.\d++)?(\.\d++)?' . self::$modifierRegex . '$}i', $version, $matches ) ) {
			$version = $matches[1]
				. ( ! empty( $matches[2] ) ? $matches[2] : '.0' )
				. ( ! empty( $matches[3] ) ? $matches[3] : '.0' )
				. ( ! empty( $matches[4] ) ? $matches[4] : '.0' );
			$index   = 5;
		} elseif ( preg_match( '{^v?(\d{4}(?:[.:-]?\d{2}){1,6}(?:[.:-]?\d{1,3})?)' . self::$modifierRegex . '$}i', $version, $matches ) ) {
			// Match date(time) based versioning.
			$version = preg_replace( '{\D}', '.', $matches[1] );
			$index   = 2;
		}

		// Add version modifiers if a version was matched.
		if ( isset( $index ) ) {
			if ( ! empty( $matches[ $index ] ) ) {
				if ( 'stable' === $matches[ $index ] ) {
					return $version;
				}
				$version .= '-' . self::expandStability( $matches[ $index ] ) . ( ! empty( $matches[ $index + 1 ] ) ? ltrim( $matches[ $index + 1 ], '.-' ) : '' );
			}

			if ( ! empty( $matches[ $index + 2 ] ) ) {
				$version .= '-dev';
			}

			return $version;
		}

		// Match dev branches.
		if ( preg_match( '{(.*?)[.-]?dev$}i', $version, $match ) ) {
			try {
				return self::normalizeBranch( $match[1] );
			// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			} catch ( \Exception $e ) {
				// noop.
			}
		}

		$extraMessage = '';
		if ( preg_match( '{ +as +' . preg_quote( $version ) . '$}', $fullVersion ) ) {
			$extraMessage = ' in "' . $fullVersion . '", the alias must be an exact version';
		} elseif ( preg_match( '{^' . preg_quote( $version ) . ' +as +}', $fullVersion ) ) {
			$extraMessage = ' in "' . $fullVersion . '", the alias source must be an exact version, if it is a branch name you should prefix it with dev-';
		}

		throw new \UnexpectedValueException( 'Invalid version string "' . $version . '"' . $extraMessage );
	}

	/**
	 * Normalizes a branch name to be able to perform comparisons on it.
	 *
	 * @param string $name Branch name.
	 * @return string Normalized branch name.
	 */
	public static function normalizeBranch( $name ) {
		$name = trim( $name );

		if ( in_array( $name, [ 'master', 'trunk', 'default' ], true ) ) {
			return self::normalize( $name );
		}

		if ( preg_match( '{^v?(\d++)(\.(?:\d++|[xX*]))?(\.(?:\d++|[xX*]))?(\.(?:\d++|[xX*]))?$}i', $name, $matches ) ) {
			$version = '';
			for ( $i = 1; $i < 5; ++$i ) {
				$version .= isset( $matches[ $i ] ) ? str_replace( [ '*', 'X' ], 'x', $matches[ $i ] ) : '.x';
			}

			return str_replace( 'x', '9999999', $version ) . '-dev';
		}

		return 'dev-' . $name;
	}

	/**
	 * Expand shorthand stability string to long version.
	 *
	 * @param string $stability Existing stability.
	 * @return string Normalized stability.
	 */
	private static function expandStability( $stability ) {
		$stability = strtolower( $stability );

		switch ( $stability ) {
			case 'a':
				return 'alpha';
			case 'b':
				return 'beta';
			case 'p':
			case 'pl':
				return 'patch';
			case 'rc':
				return 'RC';
			default:
				return $stability;
		}
	}
}
