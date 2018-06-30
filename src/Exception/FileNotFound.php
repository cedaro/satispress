<?php
/**
 * File not found exception.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Exception;

/**
 * File not found exception class.
 *
 * @since 0.3.0
 */
class FileNotFound extends \RuntimeException implements ExceptionInterface {
	/**
	 * Create an exception for invalid checksum operations.
	 *
	 * @param string     $file     The filename that couldn't be found.
	 * @param integer    $code     The exception code.
	 * @param \Throwable $previous Previous exception.
	 * @return FileNotFound
	 */
	public static function forInvalidChecksum( string $file, int $code = 0, \Throwable $previous = null ): FileNotFound {
		$message = sprintf(
			'Cannot compute a checksum for an unknown file at %s',
			$file
		);

		return new static( $message, $code, $previous );
	}
}
