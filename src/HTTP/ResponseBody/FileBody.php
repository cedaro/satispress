<?php
/**
 * File body handler.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\HTTP\ResponseBody;

use SatisPress\Exception\InvalidFileName;

/**
 * File body handler class.
 *
 * @since 0.3.0
 */
class FileBody implements ResponseBody {
	/**
	 * Absolute path to the file to stream.
	 *
	 * @var string
	 */
	protected $filename;

	/**
	 * Create a file response body.
	 *
	 * @since 0.3.0
	 *
	 * @param string $filename Absolute path to the file to stream.
	 * @throws InvalidFileName If the file name fails validation.
	 */
	public function __construct( string $filename ) {
		$result = validate_file( $filename );
		if ( 0 !== $result ) {
			throw InvalidFileName::withValidationCode( $filename, $result );
		}

		$this->filename = $filename;
	}

	/**
	 * Stream the file.
	 *
	 * @since 0.3.0
	 */
	public function emit() {
		$this->configure_environment();
		$this->clean_buffers();
		$this->readfile_chunked( $this->filename );
	}

	/**
	 * Configure the environment before sending a file.
	 *
	 * @since 0.3.0
	 */
	protected function configure_environment() {
		try {
			session_write_close();

			if ( \function_exists( 'apache_setenv' ) ) {
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_apache_setenv
				apache_setenv( 'no-gzip', '1' );
			}

			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_ini_set
			ini_set( 'zlib.output_compression', 'Off' );
			set_time_limit( 0 );
		// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		} catch ( \Throwable $t ) {
			// noop.
		}
	}

	/**
	 * Clean output buffers.
	 *
	 * @since 0.3.0
	 */
	protected function clean_buffers() {
		$levels = ob_get_level();
		for ( $i = 0; $i < $levels; $i++ ) {
			ob_end_clean();
		}
	}

	/**
	 * Output a file.
	 *
	 * Reads file in chunks so big downloads are possible without changing `php.ini`.
	 *
	 * @link https://github.com/bcit-ci/CodeIgniter/wiki/Download-helper-for-large-files
	 *
	 * @since 0.3.0
	 *
	 * @param string $filename Absolute path to a file.
	 */
	protected function readfile_chunked( $filename ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		$handle = fopen( $filename, 'rb' );
		if ( false === $handle ) {
			// @todo Throw an exception?
			return;
		}

		while ( ! feof( $handle ) ) {
			$chunk_size = 1024 * 1024;
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fread
			$buffer = fread( $handle, $chunk_size );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $buffer;
			flush();
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		fclose( $handle );
	}
}
