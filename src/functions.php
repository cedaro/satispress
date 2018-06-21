<?php
/**
 * Helper functions
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.1.0
 */

namespace SatisPress;

/**
 * Retrieve the permalink for packages.json.
 *
 * @since 0.2.0
 *
 * @param array $args Query string parameters.
 * @return string
 */
function satispress_get_packages_permalink( $args = [] ) {
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
function satispress_send_file( $file ) {
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

	@satispress_readfile_chunked( $file ) || wp_die( esc_html__( 'File not found', 'satispress' ) );
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
 * @param string $file     A file path.
 * @param bool   $retbytes Optional. Whether to return the number of
 *                         bytes. Default is true.
 */
function satispress_readfile_chunked( $file, $retbytes = true ) {
	$buffer     = '';
	$cnt        = 0;
	$chunk_size = 1024 * 1024;

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
	$handle = fopen( $file, 'r' );
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

		if ( $retbytes ) {
			$cnt += strlen( $buffer );
		}
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
	$status = fclose( $handle );

	if ( $retbytes && $status ) {
		return $cnt;
	}

	return $status;
}
