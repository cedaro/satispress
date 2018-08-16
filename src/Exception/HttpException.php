<?php
/**
 * HTTP exception.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Exception;

use SatisPress\Package;
use SatisPress\Release;
use Throwable;
use WP_Http as HTTP;

/**
 * HTTP exception class.
 *
 * @since 0.3.0
 */
class HttpException extends \Exception implements SatispressException {
	/**
	 * HTTP status code.
	 *
	 * @var int
	 */
	protected $status_code;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param string    $message     Message.
	 * @param int       $status_code Optional. HTTP status code. Defaults to 500.
	 * @param int       $code        Exception code.
	 * @param Throwable $previous    Previous exception.
	 */
	public function __construct(
		string $message,
		int $status_code = HTTP::INTERNAL_SERVER_ERROR,
		int $code = 0,
		Throwable $previous = null
	) {
		$this->status_code = $status_code;
		$message           = $message ?: 'Internal Server Error';

		parent::__construct( $message, $code, $previous );
	}

	/**
	 * Create an exception for a forbidden resource request.
	 *
	 * @since 0.3.0.
	 *
	 * @param int       $code     Optional. The Exception code.
	 * @param Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return HTTPException
	 */
	public static function forForbiddenResource(
		int $code = 0,
		Throwable $previous = null
	): HttpException {
		$user_id     = get_current_user_id();
		$request_uri = $_SERVER['REQUEST_URI'];
		$message     = "Forbidden resource requested; User: {$user_id}; URI: {$request_uri}";

		return new static( $message, HTTP::FORBIDDEN, $code, $previous );
	}

	/**
	 * Create an exception for an unknown package request.
	 *
	 * @since 0.3.0.
	 *
	 * @param string    $slug     Package slug.
	 * @param int       $code     Optional. The Exception code.
	 * @param Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return HTTPException
	 */
	public static function forUnknownPackage(
		string $slug,
		int $code = 0,
		Throwable $previous = null
	): HttpException {
		$message = "Package does not exist; Package: {$slug}";

		return new static( $message, HTTP::NOT_FOUND, $code, $previous );
	}

	/**
	 * Create an exception for a forbidden package request.
	 *
	 * @since 0.3.0.
	 *
	 * @param Package   $package  Package.
	 * @param int       $code     Optional. The Exception code.
	 * @param Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return HTTPException
	 */
	public static function forForbiddenPackage(
		Package $package,
		int $code = 0,
		Throwable $previous = null
	): HttpException {
		$user_id = get_current_user_id();
		$slug    = $package->get_slug();
		$message = "Forbidden package requested; Package: {$slug}; User: {$user_id}";

		return new static( $message, HTTP::FORBIDDEN, $code, $previous );
	}

	/**
	 * Create an exception for an invalid release request.
	 *
	 * @since 0.3.0.
	 *
	 * @param Package   $package  Package.
	 * @param string    $version  Version.
	 * @param int       $code     Optional. The Exception code.
	 * @param Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return HTTPException
	 */
	public static function forInvalidRelease(
		Package $package,
		string $version,
		int $code = 0,
		Throwable $previous = null
	): HttpException {
		$name    = $package->get_name();
		$message = "An artifact for {$name} {$version} does not exist.";

		return new static( $message, HTTP::NOT_FOUND, $code, $previous );
	}

	/**
	 * Create an exception for a missing release request.
	 *
	 * @since 0.3.0.
	 *
	 * @param Release   $release  Release.
	 * @param int       $code     Optional. The Exception code.
	 * @param Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return HTTPException
	 */
	public static function forMissingRelease(
		Release $release,
		int $code = 0,
		Throwable $previous = null
	): HttpException {
		$name    = $release->get_package()->get_name();
		$version = $release->get_version();
		$file    = $release->get_file();
		$message = "The artifact for {$name} {$version} is missing at {$file}.";

		return new static( $message, HTTP::NOT_FOUND, $code, $previous );
	}

	/**
	 * Retrieve the HTTP status code.
	 *
	 * @since 0.3.0
	 *
	 * @return int
	 */
	public function getStatusCode(): int {
		return $this->status_code;
	}
}
