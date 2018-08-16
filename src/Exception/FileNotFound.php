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
class FileNotFound extends \RuntimeException implements SatispressException {
	/**
	 * Create an exception for invalid checksum operations.
	 *
	 * @param string     $filename The filename that couldn't be found.
	 * @param int        $code     Optional. The Exception code.
	 * @param \Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return FileNotFound
	 */
	public static function forInvalidChecksum(
		string $filename,
		int $code = 0,
		\Throwable $previous = null
	): FileNotFound {
		$message = "Cannot compute a checksum for an unknown file at {$filename}.";

		return new static( $message, $code, $previous );
	}
}
