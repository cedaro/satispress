<?php
/**
 * Helper functions.
 *
 * @package SatisPress
 * @author Brady Vercher <brady@blazersix.com>
 * @since 0.1.0
 */

/**
 * Retrieve the permalink for packages.json
 *
 * @since 0.2.0
 *
 * @param array $args
 * @return string
 */
function satispress_get_packages_permalink( $args = array() ) {
	$permalink = get_option( 'permalink_structure' );
	if ( empty( $permalink ) ) {
		$url = add_query_arg( 'satispress', 'packages.json', home_url( '/' ) );

	} else {
		// Leave off the packages.json if 'base' arg is true.
		$suffix = ( isset( $args['base'] ) && $args['base'] ) ? '' : 'packages.json';
		$url = sprintf( home_url( '/satispress/%s' ), $suffix );
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
	@session_write_close();
	if ( function_exists( 'apache_setenv' ) ) {
		@apache_setenv( 'no-gzip', 1 );
	}
	if(get_magic_quotes_runtime()) {
		@set_magic_quotes_runtime( 0 );
	}
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

	if ( $size = @filesize( $file ) ) {
		header( 'Content-Length: ' . $size );
	}

	@readfile_chunked( $file ) or wp_die( __( 'File not found', 'satispress' ) );
	exit;
}

if ( ! function_exists( 'readfile_chunked' ) ) :
/**
 * Readfile chunked.
 *
 * Reads file in chunks so big downloads are possible without changing php.ini.
 *
 * @link https://github.com/bcit-ci/CodeIgniter/wiki/Download-helper-for-large-files
 *
 * @since 0.1.0
 *
 * @param string $file A file path.
 */
function readfile_chunked( $file, $retbytes = true ) {
	$buffer = '';
	$cnt = 0;
	$chunk_size = 1024 * 1024;

	$handle = fopen( $file, 'r' );
	if ( false === $handle ) {
		return false;
	}

	while ( ! feof( $handle ) ) {
		$buffer = fread( $handle, $chunk_size );
		echo $buffer;
		ob_flush();
		flush();

		if ( $retbytes ) {
			$cnt += strlen( $buffer );
		}
	}

	$status = fclose( $handle );

	if ( $retbytes && $status ) {
		return $cnt;
	}

	return $status;
}
endif;
