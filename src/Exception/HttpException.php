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
		$message           = $message ?: 'Internal Server Error';

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
		$message = 'Sorry, you are not allowed to view this resource.';

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
	public static function forUnknownPackage(
		string $slug,
		array $data = null,
		int $code = 0,
		Throwable $previous = null
	): HTTPException {
		$data    = [ 'slug' => $slug ];
		$message = 'Package does not exist.';

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
		$message = 'Sorry, you are not allowed to download this file.';

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

		$message = 'Package version does not exist.';

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

		$message = 'Package artifact is missing.';

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

	/**
	 * Display the exception message and stop the request.
	 *
	 * @since 0.3.0
	 */
	public function displayMessage() {
		$message = $this->getMessage();

		if ( $this->can_show_extra_data() ) {
			$data = $this->getData();
			$data['file'] = $this->getFile();
			$data['line'] = $this->getLine();

			$message .= '<br>';
			foreach( $data as $key => $value ) {
				$message .= sprintf(
					'<br><strong>%1$s:</strong> %2$s',
					esc_html( ucwords( $key ) ),
					esc_html( $value )
				);
			}
		}

		wp_die( wp_kses_post( $message ), $this->getStatusCode() );
	}

	/**
	 * Whether extra data should be displayed.
	 *
	 * @since 0.3.0
	 *
	 * @return boolean
	 */
	protected function can_show_extra_data() {
		return current_user_can( 'manage_options' ) || defined( 'WP_DEBUG' ) && true === WP_DEBUG;
	}
}
