<?php
/**
 * Base authentication server.
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.3.0
 */

declare ( strict_types = 1 );

namespace SatisPress\Authentication;

use SatisPress\HTTP\Request;
use WP_Error;

/**
 * Base authentication server class.
 *
 * @since 0.3.0
 */
abstract class AbstractServer implements Server {
	/**
	 * Errors that occurred during authentication.
	 *
	 * @var WP_Error|null|bool True if succeeded, WP_Error if errored, null if
	 *                         not handled by this server.
	 */
	protected $auth_status;

	/**
	 * Server request.
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Whether to attempt to authenticate.
	 *
	 * Helps prevent recursion and processing multiple times per request.
	 *
	 * @var bool
	 */
	protected $should_attempt = true;

	/**
	 * Constructor method.
	 *
	 * @since 0.3.0
	 *
	 * @param Request $request Request instance.
	 */
	public function __construct( Request $request ) {
		$this->request = $request;
	}

	/**
	 * Handle authentication.
	 *
	 * @since 0.3.0
	 *
	 * @param int|bool $user_id Current user ID or false if unknown.
	 * @return int|bool A user on success, or false on failure.
	 */
	abstract public function authenticate( $user_id );

	/**
	 * Handle an authentication error.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_Error $error Error instance.
	 */
	protected function handle_error( WP_Error $error ) {}

	/**
	 * Report authentication errors.
	 *
	 * @since 0.3.0
	 *
	 * @param WP_Error|mixed $value Error from another authentication handler,
	 *                              null if we should handle it, or another value if not.
	 * @return WP_Error|bool|null
	 */
	public function get_authentication_errors( $value ) {
		if ( null !== $value || is_user_logged_in() ) {
			return $value;
		}

		if ( ! is_wp_error( $this->auth_status ) ) {
			return $value;
		}

		$this->handle_error( $this->auth_status );

		return $this->auth_status;
	}

	/**
	 * Retrieve the URL of the current request.
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */
	protected function get_current_url(): string {
		$request_uri = $_SERVER['REQUEST_URI'];

		$wp_base = get_home_url( null, '/', 'relative' );
		if ( 0 === strpos( $request_uri, $wp_base ) ) {
			$request_uri = substr( $request_uri, \strlen( $wp_base ) );
		}

		return get_home_url( null, $request_uri );
	}
}
