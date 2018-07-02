<?php
/**
 * HTTP error message body.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\HTTP\ResponseBody;

use WP_Http as HTTP;

/**
 * HTTP error message body class.
 *
 * @since 0.3.0
 */
class ErrorBody implements ResponseBody {
	/**
	 * Error message.
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * HTTP status code.
	 *
	 * @var integer
	 */
	protected $status_code;

	/**
	 * Create an error message body.
	 *
	 * @since 0.3.0
	 *
	 * @param string $message     Error message.
	 * @param int    $status_code Optional. HTTP status code.
	 */
	public function __construct( string $message, int $status_code = HTTP::INTERNAL_SERVER_ERROR ) {
		$this->message     = $message;
		$this->status_code = $status_code;
	}

	/**
	 * Display the error message.
	 *
	 * @since 0.3.0
	 */
	public function emit() {
		wp_die( $this->message, $this->status_code );
	}
}
