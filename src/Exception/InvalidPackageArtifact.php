<?php
/**
 * Invalid package artifact exception.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.7.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Exception;

/**
 * Invalid package artifact exception class.
 *
 * @since 0.7.0
 */
class InvalidPackageArtifact extends \RuntimeException implements SatispressException {
	/**
	 * Create an exception for invalid package artifact.
	 *
	 * @since 0.7.0
	 *
	 * @param string     $filename File name.
	 * @param int        $code     Optional. The Exception code.
	 * @param \Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return InvalidPackageArtifact
	 */
	public static function forFileName(
		string $filename,
		int $code = 0,
		\Throwable $previous = null
	): InvalidPackageArtifact {
		$message = "Invalid archive for file {$filename}.";

		return new static( $message, $code, $previous );
	}
}
