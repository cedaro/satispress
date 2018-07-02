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
	 */
	public function __construct( $filename ) {
		$this->filename = $filename;
	}

	/**
	 * Stream the file.
	 *
	 * Reads file in chunks so big downloads are possible without changing `php.ini`.
	 *
	 * @link https://github.com/bcit-ci/CodeIgniter/wiki/Download-helper-for-large-files
	 *
	 * @since 0.3.0
	 */
	public function emit() {
		$this->configure_environment();

		$buffer     = '';
		$chunk_size = 1024 * 1024;

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		$handle = fopen( $this->filename, 'rb' );
		if ( false === $handle ) {
			// @todo Throw an exception?
			return false;
		}

		while ( ! feof( $handle ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fread
			$buffer = fread( $handle, $chunk_size );
			// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
			echo $buffer;
			@ob_flush();
			flush();
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		fclose( $handle );
	}

	/**
	 * Configure the environment before sending a file.
	 *
	 * @since 0.3.0
	 */
	protected function configure_environment() {
		// phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
		@session_write_close();
		if ( function_exists( 'apache_setenv' ) ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_apache_setenv
			@apache_setenv( 'no-gzip', '1' );
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
		//phpcs:enable Generic.PHP.NoSilencedErrors.Discouraged
	}
}
