<?php
/**
 * Helper functions.
 *
 * @package SatisPress
 * @since 0.1.0
 */

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
	@ini_set( 'zlib.output_compression', 'Off' );
	@set_time_limit( 0 );
	@set_magic_quotes_runtime( 0 );
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
 * @link http://codeigniter.com/wiki/Download_helper_for_large_files/
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
