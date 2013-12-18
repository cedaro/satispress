<?php
/**
 * Simplified version parser from Composer.
 *
 * @package SatisPress
 * @since 0.1.0
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @link https://github.com/composer/composer/blob/master/src/Composer/Package/Version/VersionParser.php
 */
class SatisPress_Version_Parser {
    private static $modifierRegex = '[._-]?(?:(stable|beta|b|RC|alpha|a|patch|pl|p)(?:[.-]?(\d+))?)?([.-]?dev)?';

    /**
     * Normalizes a version string to be able to perform comparisons on it.
     *
     * @param string $version
     * @param string $fullVersion optional complete version string to give more context.
     * @return array
     */
    public static function normalize( $version, $fullVersion = null )
    {
        $version = trim( $version );
        if ( null === $fullVersion ) {
            $fullVersion = $version;
        }

        // Ignore aliases and just assume the alias is required instead of the source.
        if ( preg_match( '{^([^,\s]+) +as +([^,\s]+)$}', $version, $match ) ) {
            $version = $match[1];
        }

        // Match master-like branches.
        if ( preg_match('{^(?:dev-)?(?:master|trunk|default)$}i', $version ) ) {
            return '9999999-dev';
        }

        if ( 'dev-' === strtolower( substr( $version, 0, 4 ) ) ) {
            return 'dev-' . substr( $version, 4 );
        }

        // Match classical versioning.
        if ( preg_match( '{^v?(\d{1,3})(\.\d+)?(\.\d+)?(\.\d+)?' . self::$modifierRegex . '$}i', $version, $matches ) ) {
            $version = $matches[1]
                . ( ! empty( $matches[2] ) ? $matches[2] : '.0' )
                . ( ! empty( $matches[3] ) ? $matches[3] : '.0' )
                . ( ! empty( $matches[4] ) ? $matches[4] : '.0' );
            $index = 5;
        }
		
		// Match date-based versioning.
		elseif ( preg_match( '{^v?(\d{4}(?:[.:-]?\d{2}){1,6}(?:[.:-]?\d{1,3})?)' . self::$modifierRegex . '$}i', $version, $matches ) ) {
            $version = preg_replace('{\D}', '-', $matches[1]);
            $index = 2;
        }

        // Add version modifiers if a version was matched.
        if ( isset( $index ) ) {
            if ( ! empty( $matches[ $index ] ) ) {
                if ( 'stable' === $matches[ $index ] ) {
                    return $version;
                }
                $version .= '-' . self::expandStability( $matches[ $index ] ) . ( ! empty( $matches[ $index + 1 ] ) ? $matches[ $index + 1 ] : '' );
            }

            if ( ! empty( $matches[ $index + 2 ] ) ) {
                $version .= '-dev';
            }

            return $version;
        }

        // Match dev branches
        if ( preg_match( '{(.*?)[.-]?dev$}i', $version, $match ) ) {
            try {
                return self::normalizeBranch( $match[1] );
            } catch ( Exception $e ) {}
        }
    }

    /**
     * Normalizes a branch name to be able to perform comparisons on it.
     *
     * @param string $name
     * @return array
     */
    public static function normalizeBranch( $name ) {
        $name = trim( $name );

        if ( in_array( $name, array( 'master', 'trunk', 'default' ) ) ) {
            return self::normalize( $name );
        }

        if ( preg_match( '#^v?(\d+)(\.(?:\d+|[x*]))?(\.(?:\d+|[x*]))?(\.(?:\d+|[x*]))?$#i', $name, $matches ) ) {
            $version = '';
            for ( $i = 1; $i < 5; $i++ ) {
                $version .= isset( $matches[ $i ]) ? str_replace( '*', 'x', $matches[ $i ] ) : '.x';
            }

            return str_replace( 'x', '9999999', $version ) . '-dev';
        }

        return 'dev-' . $name;
    }

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
