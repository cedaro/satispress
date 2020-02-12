<?php
/**
 * Authentication exception.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.4.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Exception;

use SatisPress\Package;
use SatisPress\Release;
use Throwable;
use WP_Http as HTTP;

/**
 * Authentication exception class.
 *
 * @since 0.4.0
 */
class AuthenticationException extends HttpException {
	/**
	 * Error code.
	 *
	 * @var string
	 */
	protected $code = '';

	/**
	 * Response headers.
	 *
	 * @var array
	 */
	protected $headers;

	/**
	 * Constructor.
	 *
	 * @since 0.4.0
	 *
	 * @param string    $code        Exception code.
	 * @param string    $message     Message.
	 * @param int       $status_code Optional. HTTP status code. Defaults to 500.
	 * @param array     $headers     Optional. Response headers.
	 * @param Throwable $previous    Optional. Previous exception.
	 */
	public function __construct(
		string $code,
		string $message,
		int $status_code = HTTP::INTERNAL_SERVER_ERROR,
		array $headers = [],
		Throwable $previous = null
	) {
		$this->code    = $code;
		$this->headers = $headers;

		parent::__construct( $message, $status_code, 0, $previous );
	}

	/**
	 * Create an exception for requests that require authentication.
	 *
	 * @since 0.4.0
	 *
	 * @param array     $headers  Response headers.
	 * @param string    $code     Optional. The Exception code.
	 * @param Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return HTTPException
	 */
	public static function forAuthenticationRequired(
		array $headers = [],
		string $code = 'invalid_request',
		Throwable $previous = null
	): HttpException {
		$headers = $headers ?: [ 'WWW-Authenticate' => 'Basic realm="SatisPress"' ];
		$message = 'Authentication is required for this resource.';

		return new static( $code, $message, HTTP::UNAUTHORIZED, $headers, $previous );
	}

	/**
	 * Create an exception for invalid credentials.
	 *
	 * @since 0.4.0
	 *
	 * @param array     $headers  Response headers.
	 * @param string    $code     Optional. The Exception code.
	 * @param Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return HTTPException
	 */
	public static function forInvalidCredentials(
		array $headers = [],
		string $code = 'invalid_credentials',
		Throwable $previous = null
	): HttpException {
		$headers = $headers ?: [ 'WWW-Authenticate' => 'Basic realm="SatisPress"' ];
		$message = 'Invalid credentials.';

		return new static( $code, $message, HTTP::UNAUTHORIZED, $headers, $previous );
	}

	/**
	 * Create an exception for a missing authorization header.
	 *
	 * @since 0.4.0
	 *
	 * @param array     $headers  Response headers.
	 * @param string    $code     Optional. The Exception code.
	 * @param Throwable $previous Optional. The previous throwable used for the exception chaining.
	 * @return HTTPException
	 */
	public static function forMissingAuthorizationHeader(
		array $headers = [],
		string $code = 'invalid_credentials',
		Throwable $previous = null
	): HttpException {
		$headers = $headers ?: [ 'WWW-Authenticate' => 'Basic realm="SatisPress"' ];
		$message = 'Missing authorization header.';

		return new static( $code, $message, HTTP::UNAUTHORIZED, $headers, $previous );
	}

	/**
	 * Retrieve the response headers.
	 *
	 * @since 0.4.0
	 *
	 * @return array Map of header name to header value.
	 */
	public function getHeaders(): array {
		return $this->headers;
	}
}
