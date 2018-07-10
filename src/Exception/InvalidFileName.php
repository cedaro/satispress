<?php
/**
 * Invalid file name exception.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Exception;

/**
 * Invalid file name exception class.
 *
 * @since 0.3.0
 */
class InvalidFileName extends \InvalidArgumentException implements ExceptionInterface {
	/**
	 * Create an exception for an invalid file name argument.
	 *
	 * @since 0.3.0.
	 *
	 * @param string     $filename        File name.
	 * @param int        $validation_code Validation code returned from validate_file().
	 * @param int        $code            Optional. The Exception code.
	 * @param \Throwable $previous        Optional. The previous throwable used for the exception chaining.
	 * @return InvalidFileName
	 */
	public static function withValidationCode(
		string $filename,
		int $validation_code,
		int $code = 0,
		\Throwable $previous = null
	): InvalidFileName {
		$message = "File name '{$filename}' ";

		switch ( $validation_code ) {
			case 1:
				$message .= ' contains directory traversal.';
				break;
			case 2:
				$message .= ' contains a Windows drive path.';
				break;
			case 3:
				$message .= ' is not in the allowed files list.';
				break;
		}

		return new static( $message, $code, $previous );
	}
}
