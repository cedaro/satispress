<?php
/**
 * SatisPress_Version_Parser class
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.1.0
 */

/**
 * Simplified version parser from Composer.
 *
 * @package SatisPress
 * @since 0.1.0
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @link https://github.com/composer/composer/blob/master/src/Composer/Package/Version/VersionParser.php
 * @link https://github.com/composer/semver/blob/master/src/VersionParser.php
 */
class SatisPress_Version_Parser {
	/**
	 * Modifier pattern
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
	 * @return array
	 */
	public static function normalize( $version, $fullVersion = null ) {
		$version = trim( $version );

		if ( null === $fullVersion ) {
			$fullVersion = $version;
		}

		// Strip of aliasing.
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
			// Match date-based versioning.
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
			} catch ( Exception $e ) {
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
	 * @return array Branch name.
	 */
	public static function normalizeBranch( $name ) {
		$name = trim( $name );

		if ( in_array( $name, array( 'master', 'trunk', 'default' ), true ) ) {
			return self::normalize( $name );
		}

		if ( preg_match( '{^v?(\d++)(\.(?:\d++|[xX*]))?(\.(?:\d++|[xX*]))?(\.(?:\d++|[xX*]))?$}i', $name, $matches ) ) {
			$version = '';
			for ( $i = 1; $i < 5; $i++ ) {
				$version .= isset( $matches[ $i ] ) ? str_replace( array( '*', 'X' ), 'x', $matches[ $i ] ) : '.x';
			}

			return str_replace( 'x', '9999999', $version ) . '-dev';
		}

		return 'dev-' . $name;
	}

	/**
	 * Allow for stability aliases.
	 *
	 * @since 0.1.0
	 *
	 * @param string $stability Existing stability string.
	 * @return string Consolidated stability string.
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
