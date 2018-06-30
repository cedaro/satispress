<?php
/**
 * Invalid package type exception.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Exception;

/**
 * Invalid package type exception class.
 *
 * @since 0.3.0
 */
class InvalidPackageType extends \LogicException implements ExceptionInterface {
	/**
	 * Create an exception for an invalid package type.
	 *
	 * @since 0.3.0.
	 *
	 * @param string     $package_type Package type.
	 * @param string     $class_name   Class name.
	 * @param int        $code         Optional. The Exception code.
	 * @param \Throwable $previous     Optional. The previous throwable used for the exception chaining.
	 * @return InvalidPackageType
	 */
	public static function forPackageType(
		string $package_type,
		string $class_name,
		int $code = null,
		\Throwable $previous = null
	): InvalidPackageType {
		$message = "Package type {$package_type} not known. Class {$class_name} not found.";

		return new static( $message, $code, $previous );
	}
}
