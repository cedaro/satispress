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
	public static function forInvalidChecksum( $file, $code = null, \Exception $previous = null ) {
		$message = sprintf(
			'Cannot compute a checksum for an unknown file at %s',
			$file
		);

		return new static( $message, $code, $previous );
	}
}
