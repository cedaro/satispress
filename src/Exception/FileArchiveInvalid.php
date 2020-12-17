<?php
/**
 * Invalid file archive exception.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.5.2
 */

declare ( strict_types = 1 );

namespace SatisPress\Exception;

/**
 * Invalid file archive exception class.
 *
 * @since 0.5.2
 */
class FileArchiveInvalid extends \RuntimeException implements SatispressException {
	/**
	 * Create an exception for invalid file archive.
	 *
	 * @since 0.5.2
	 *
	 * @param string     $filename File name.
	 * @param int        $code     Optional. The Exception code.
	 * @param \Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return FileArchiveInvalid
	 */
	public static function forFileName(
		string $filename,
		int $code = 0,
		\Throwable $previous = null
	): FileDownloadFailed {
		$message = "Invalid archive for file {$filename}.";

		return new static( $message, $code, $previous );
	}
}
