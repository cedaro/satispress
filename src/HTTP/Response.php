<?php
/**
 * HTTP response.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\HTTP;

use SatisPress\Exception\HTTPException;
use SatisPress\HTTP\ResponseBody\ErrorBody;
use SatisPress\HTTP\ResponseBody\FileBody;
use SatisPress\HTTP\ResponseBody\JsonBody;
use SatisPress\HTTP\ResponseBody\ResponseBody;
use WP_Http as HTTP;
use WP_REST_Response;

/**
 * HTTP response class.
 *
 * @since 0.3.0
 */
class Response {
	/**
	 * Message body.
	 *
	 * @var ResponseBody
	 */
	protected $body;

	/**
	 * Response headers.
	 *
	 * @var array
	 */
	protected $headers;

	/**
	 * Response status code.
	 *
	 * @var int
	 */
	protected $status_code;

	/**
	 * Create an HTTP response.
	 *
	 * @since 0.3.0
	 *
	 * @param ResponseBody $body        Message body.
	 * @param int          $status_code HTTP status code.
	 * @param array        $headers     HTTP headers.
	 */
	public function __construct( ResponseBody $body, int $status_code = HTTP::OK, array $headers = [] ) {
		$this->body = $body;
		$this->set_headers( $headers );
		$this->set_status( $status_code );
	}

	/**
	 * Retrieve response body.
	 *
	 * @since 0.3.0
	 *
	 * @return ResponseBody
	 */
	public function get_body(): ResponseBody {
		return $this->body;
	}

	/**
	 * Retrieve headers.
	 *
	 * @since 0.3.0
	 *
	 * @return array Map of header name to header value.
	 */
	public function get_headers(): array {
		return $this->headers;
	}

	/**
	 * Set response headers.
	 *
	 * @since 0.3.0
	 *
	 * @param array $headers Map of header name to header value.
	 */
	public function set_headers( array $headers ) {
		$this->headers = $headers;
	}

	/**
	 * Retrieve the HTTP status code.
	 *
	 * @since 0.3.0
	 *
	 * @return int The 3-digit HTTP status code.
	 */
	public function get_status(): int {
		return $this->status_code;
	}

	/**
	 * Set the HTTP status code.
	 *
	 * @since 0.3.0
	 *
	 * @param int $status_code HTTP status.
	 * @throws \InvalidArgumentException If the status code is not between 100 and 599.
	 */
	public function set_status( int $status_code ) {
		if ( $status_code < 100 || $status_code > 599 ) {
			throw new \InvalidArgumentException( "Invalid status code '${status_code}'." );
		}

		$this->status_code = $status_code;
	}

	/**
	 * Create a response to stream a file.
	 *
	 * @since 0.3.0
	 *
	 * @param string $filename Absolute path to the file to stream.
	 * @return Response
	 */
	public static function for_file( string $filename ) {
		$headers = [
			'Robots'                    => 'none',
			'Content-Type'              => 'application/force-download',
			'Content-Description'       => 'File Transfer',
			'Content-Disposition'       => 'attachment; filename="' . basename( $filename ) . '";',
			'Content-Transfer-Encoding' => 'binary',
		];

		$headers = array_merge( wp_get_nocache_headers(), $headers );

		// phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
		$size = @filesize( $filename );
		if ( $size ) {
			$headers['Content-Length'] = $size;
		}

		return new static(
			new FileBody( $filename ),
			HTTP::OK,
			$headers
		);
	}

	/**
	 * Create a response from an exception.
	 *
	 * @since 0.3.0
	 *
	 * @param \Exception $e Exception.
	 * @return Response
	 */
	public static function from_exception( \Exception $e ): Response {
		$status_code = 500;

		if ( $e instanceof HTTPException ) {
			$status_code = $e->getStatusCode();
		}

		$message = 'Internal Server Error';
		if ( HTTP::NOT_FOUND === $status_code ) {
			$message = 'Resource not found.';
		} elseif ( HTTP::FORBIDDEN === $status_code ) {
			$message = 'Sorry, you cannot view this resource.';
		}

		return new static(
			new ErrorBody( $message, $status_code ),
			$status_code
		);
	}

	/**
	 * Create a response from a REST authentication error.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_Error|WP_REST_Response $response WP REST response.
	 * @return Response
	 */
	public static function from_rest_authentication_error( $response ): Response {
		$response = rest_ensure_response( $response );

		if ( is_wp_error( $response ) ) {
			$response = self::error_to_response( $response );
		} elseif ( $response->is_error() ) {
			$response = self::error_to_response( $response->as_error() );
		}

		return new static(
			new JsonBody( $response->get_data() ),
			$response->get_status(),
			$response->get_headers()
		);
	}

	/**
	 * Convert an error to a response object.
	 *
	 * @see WP_REST_Server::error_to_response()
	 *
	 * @since 0.3.0
	 *
	 * @param  WP_Error $error Error object.
	 * @return WP_REST_Response
	 */
	protected function error_to_response( WP_Error $error ) {
		$error_data = $error->get_error_data();

		$status = 500;
		if ( is_array( $error_data ) && ! empty( $error_data['status'] ) ) {
			$status = $error_data['status'];
		}

		$errors = array();

		foreach ( (array) $error->errors as $code => $messages ) {
			foreach ( (array) $messages as $message ) {
				$errors[] = array(
					'code'    => $code,
					'message' => $message,
					'data'    => $error->get_error_data( $code ),
				);
			}
		}

		$data = $errors[0];
		if ( count( $errors ) > 1 ) {
			// Remove the primary error.
			array_shift( $errors );
			$data['additional_errors'] = $errors;
		}

		return new WP_REST_Response( $data, $status );
	}
}
