<?php
/**
 * Invalid release source exception.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Exception;

use SatisPress\Release;

/**
 * Invalid release source exception class.
 *
 * @since 0.3.0
 */
class InvalidReleaseSource extends \LogicException implements ExceptionInterface {
	/**
	 * Create an exception for an invalid release source.
	 *
	 * @since 0.3.0.
	 *
	 * @param Release    $release  Release instance.
	 * @param int        $code     Optional. The Exception code.
	 * @param \Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return InvalidReleaseSource
	 */
	public static function forRelease(
		Release $release,
		int $code = null,
		\Throwable $previous = null
	): InvalidReleaseSource {
		$name = $release->get_package()->get_package_name();

		$message = "Unable to create release artifact for {$name}; source could not be determined.";

		return new static( $message, $code, $previous );
	}
}
