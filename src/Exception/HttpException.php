<?php
/**
 * HTTP exception.
 *
 * HTTP exceptions are public-facing, so the messages are translated and should
 * not reveal any sensitive information. Additional information can be attached
 * for debugging.
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
class HttpException extends \Exception implements ExceptionInterface {
	/**
	 * Exception data.
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * HTTP status code.
	 *
	 * @var integer
	 */
	protected $status_code;

	/**
	 * Constructor.
	 *
	 * @since 0.3.0
	 *
	 * @param string    $message     Message.
	 * @param integer   $status_code Optional. HTTP status code. Defaults to 500.
	 * @param array     $data        Optional. Additional data.
	 * @param integer   $code        Exception code.
	 * @param Throwable $previous    Previous exception.
	 */
	public function __construct(
		string $message,
		int $status_code = HTTP::INTERNAL_SERVER_ERROR,
		array $data = null,
		int $code = 0,
		Throwable $previous = null
	) {
		$this->data        = $data;
		$this->status_code = $status_code;
		$message           = $message ?: esc_html( 'Internal Server Error', 'satispress' );

		parent::__construct( $message, $code, $previous );
	}

	/**
	 * Create an exception for a forbidden resource request.
	 *
	 * @since 0.3.0.
	 *
	 * @param array     $data     Optional. Extra data for debugging.
	 * @param int       $code     Optional. The Exception code.
	 * @param Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return HTTPException
	 */
	public static function forForbiddenResource(
		array $data = null,
		int $code = 0,
		Throwable $previous = null
	): HTTPException {
		$message = esc_html__( 'Sorry, you are not allowed to view this resource.', 'satispress' );

		return new static( $message, HTTP::FORBIDDEN, $data, $code, $previous );
	}

	/**
	 * Create an exception for an unknown package request.
	 *
	 * @since 0.3.0.
	 *
	 * @param string    $slug     Package slug.
	 * @param array     $data     Optional. Extra data for debugging.
	 * @param int       $code     Optional. The Exception code.
	 * @param Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return HTTPException
	 */
	public static function forUknownPackage(
		string $slug,
		array $data = null,
		int $code = 0,
		Throwable $previous = null
	): HTTPException {
		$data    = [ 'slug' => $slug ];
		$message = esc_html__( 'Package does not exist.', 'satispress' );

		return new static( $message, HTTP::NOT_FOUND, $data, $code, $previous );
	}

	/**
	 * Create an exception for a forbidden package request.
	 *
	 * @since 0.3.0.
	 *
	 * @param Package   $package  Package.
	 * @param array     $data     Optional. Extra data for debugging.
	 * @param int       $code     Optional. The Exception code.
	 * @param Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return HTTPException
	 */
	public static function forForbiddenPackage(
		Package $package,
		array $data = null,
		int $code = 0,
		Throwable $previous = null
	): HTTPException {
		$data    = [ 'slug' => $package->get_slug() ];
		$message = esc_html__( 'Sorry, you are not allowed to download this file.', 'satispress' );

		return new static( $message, HTTP::FORBIDDEN, $data, $code, $previous );
	}

	/**
	 * Create an exception for an invalid release request.
	 *
	 * @since 0.3.0.
	 *
	 * @param Package   $package  Package.
	 * @param string    $version  Version.
	 * @param array     $data     Optional. Extra data for debugging.
	 * @param int       $code     Optional. The Exception code.
	 * @param Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return HTTPException
	 */
	public static function forInvalidRelease(
		Package $package,
		string $version,
		array $data = null,
		int $code = 0,
		Throwable $previous = null
	): HTTPException {
		$data = [
			'slug'    => $package->get_slug(),
			'version' => $version
		];

		$message = esc_html__( 'Package version does not exist.', 'satispress' );

		return new static( $message, HTTP::NOT_FOUND, $data, $code, $previous );
	}

	/**
	 * Create an exception for a missing release request.
	 *
	 * @since 0.3.0.
	 *
	 * @param Release   $release  Release.
	 * @param array     $data     Optional. Extra data for debugging.
	 * @param int       $code     Optional. The Exception code.
	 * @param Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return HTTPException
	 */
	public static function forMissingRelease(
		Release $release,
		array $data = null,
		int $code = 0,
		Throwable $previous = null
	): HTTPException {
		$data = [
			'file'    => $release->get_file(),
			'slug'    => $release->get_package()->get_slug(),
			'version' => $release->get_version(),
		];

		$message = esc_html__( 'Package artifact is missing.', 'satispress' );

		return new static( $message, HTTP::NOT_FOUND, $data, $code, $previous );
	}

	/**
	 * Retrieve exception data.
	 *
	 * @since 0.3.0
	 *
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Retrieve the HTTP status code.
	 *
	 * @since 0.3.0
	 *
	 * @return integer
	 */
	public function getStatusCode() {
		return $this->status_code;
	}
}
