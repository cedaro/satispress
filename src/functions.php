<?php
/**
 * Helper functions
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.1.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

/**
 * Autoload mapped classes.
 *
 * @since 0.3.0
 *
 * @param string $class Class name.
 */
function autoloader_classmap( $class ) {
	$class_map = array(
		'PclZip' => ABSPATH . 'wp-admin/includes/class-pclzip.php',
	);

	if ( isset( $class_map[ $class ] ) ) {
		require_once $class_map[ $class ];
	}
}

/**
 * Generate a random string.
 *
 * @since 0.3.0
 *
 * @param integer $length Length of the string to generate.
 * @return string
 */
function generate_random_string( $length = 12 ) {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

	$str = '';
	$max = strlen( $chars ) - 1;
	for ( $i = 0; $i < $length; $i++ ) {
		$str .= substr( $chars, random_int( 0, $max ), 1 );
	}

	return $str;
}

/**
 * Retrieve the authorization header.
 *
 * On certain systems and configurations, the Authorization header will be
 * stripped out by the server or PHP. Typically this is then used to
 * generate `PHP_AUTH_USER`/`PHP_AUTH_PASS` but not passed on. We use
 * `getallheaders` here to try and grab it out instead.
 *
 * From https://github.com/WP-API/OAuth1
 *
 * @return string|null Authorization header if set, null otherwise
 */
function get_authorization_header() {
	if ( ! empty( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
		return stripslashes( $_SERVER['HTTP_AUTHORIZATION'] );
	}

	if ( function_exists( 'getallheaders' ) ) {
		$headers = getallheaders();

		// Check for the authoization header case-insensitively.
		foreach ( $headers as $key => $value ) {
			if ( 'authorization' === strtolower( $key ) ) {
				return $value;
			}
		}
	}

	return null;
}

/**
 * Retrieve the permalink for packages.json.
 *
 * @since 0.2.0
 *
 * @param array $args Optional. Query string parameters. Default is an empty array.
 * @return string
 */
function get_packages_permalink( array $args = null ): string {
	if ( null === $args ) {
		$args = [];
	}

	$permalink = get_option( 'permalink_structure' );
	if ( empty( $permalink ) ) {
		$url = add_query_arg( 'satispress', 'packages.json', home_url( '/' ) );
	} else {
		// Leave off the packages.json if 'base' arg is true.
		$suffix = ( isset( $args['base'] ) && $args['base'] ) ? '' : 'packages.json';
		$url    = sprintf( home_url( '/satispress/%s' ), $suffix );
	}

	return $url;
}

/**
 * Send a download.
 *
 * @since 0.1.0
 *
 * @param string $file An absolute file path.
 */
function send_file( string $file ) {
	// phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
	@session_write_close();
	if ( function_exists( 'apache_setenv' ) ) {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_apache_setenv
		@apache_setenv( 'no-gzip', 1 );
	}

	if ( get_magic_quotes_runtime() ) {
		// phpcs:ignore PHPCompatibility.PHP.DeprecatedFunctions.set_magic_quotes_runtimeDeprecatedRemoved, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_set_magic_quotes_runtime
		@set_magic_quotes_runtime( 0 );
	}

	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_ini_set
	@ini_set( 'zlib.output_compression', 'Off' );
	@set_time_limit( 0 );
	@ob_end_clean();
	if ( ob_get_level() ) {
		@ob_end_clean(); // Zip corruption fix.
	}

	nocache_headers();
	header( 'Robots: none' );
	header( 'Content-Type: application/force-download' );
	header( 'Content-Description: File Transfer' );
	header( 'Content-Disposition: attachment; filename="' . basename( $file ) . '";' );
	header( 'Content-Transfer-Encoding: binary' );

	$size = @filesize( $file );
	if ( $size ) {
		header( 'Content-Length: ' . $size );
	}

	@readfile_chunked( $file ) || wp_die( esc_html__( 'File not found', 'satispress' ) );
	exit;
	//phpcs:enable Generic.PHP.NoSilencedErrors.Discouraged
}

/**
 * Readfile chunked.
 *
 * Reads file in chunks so big downloads are possible without changing `php.ini`.
 *
 * @link https://github.com/bcit-ci/CodeIgniter/wiki/Download-helper-for-large-files
 *
 * @since 0.1.0
 *
 * @param string $file A file path.
 * @return bool True if file could be opened, written and closed, false otherwise.
 */
function readfile_chunked( string $file ): bool {
	$buffer     = '';
	$cnt        = 0;
	$chunk_size = 1024 * 1024;

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
	$handle = fopen( $file, 'rb' );
	if ( false === $handle ) {
		return false;
	}

	while ( ! feof( $handle ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fread
		$buffer = fread( $handle, $chunk_size );
		// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
		echo $buffer;
		ob_flush();
		flush();
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
	return fclose( $handle );
}
