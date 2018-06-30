<?php
/**
 * Failed file download exception.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Exception;

/**
 * Failed file download exception class.
 *
 * @since 0.3.0
 */
class FileDownloadFailed extends \RuntimeException implements ExceptionInterface {
	/**
	 * Create an exception for artifact download failure.
	 *
	 * @since 0.3.0.
	 *
	 * @param string     $filename File name.
	 * @param int        $code     Optional. The Exception code.
	 * @param \Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return FileDownloadFailed
	 */
	public static function forFileName(
		string $filename,
		int $code = null,
		\Throwable $previous = null
	): FileDownloadFailed {
		$message = "Artifact download failed for file {$filename}.";

		return new static( $message, $code, $previous );
	}
}
